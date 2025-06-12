<?php

declare(strict_types=1);

namespace App\repository;

use App\core\Database;
use App\model\User;
use DateTime;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnexion();
    }

    public function save(User $user): bool
    {
        // requetes préparée obligatoire pour éviter les injections SQL
        $stmt = $this->pdo->prepare("INSERT INTO `user` (username, avatar, email, email_token, is_verified, password, `role`, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");

        return $stmt->execute([
            $user->getUsername(),
            $user->getAvatar(),
            $user->getEmail(),
            $user->getEmail_Token(),
            (int)$user->getIs_verified(),
            $user->getPassword(),
            json_encode($user->getRole()),
            $user->getCreatedAt()
        ]);
    }

    public function findUserByToken($token): User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM `user` WHERE email_token = ?");

        $stmt->execute([$token]);

        $data = $stmt->fetch();
        $user = new User($data);
        $user->setIduser($data["id_user"]);
        $user->setVerified_at((new DateTime())->format("Y-m-d H:i:s"));
        $user->setRole($data["role"]);

        return $user;
    }

    public function update(User $user): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE user SET 
            username = ?, 
            email = ?, 
            role = ?, 
            is_verified = ?, 
            email_token = ?,
            verified_at = ?,
            password = ?,
            avatar = ?
            WHERE id = ?"
        );

        return $stmt->execute([
            $user->getUserName(),
            $user->getEmail(),
            $user->getRole(),
            (int)$user->getIs_verified(),
            $user->getEmail_token(),
            $user->getVerified_at(),
            $user->getPassword(),
            $user->getAvatar(),
            $user->getIduser()
        ]);
    }
}
