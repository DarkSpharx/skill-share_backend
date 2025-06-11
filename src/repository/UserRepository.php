<?php

declare(strict_types=1);

namespace App\repository;

use App\core\Database;
use App\model\User;
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
        $stmt = $this->pdo->prepare("INSERT INTO `user` (username, avatar, email, password_hash, `role`, created_at) VALUES (?, ?, ?, ?, ?, ?);");

        return $stmt->execute([
            $user->getUsername(),
            $user->getAvatar(),
            $user->getEmail(),
            $user->getPassword(),
            json_encode($user->getRole()),
            $user->getCreatedAt()
        ]);
    }

    public function saveAvatar(string $avatarFileName): bool
    {
        // requetes préparée obligatoire pour éviter les injections SQL
        $stmt = $this->pdo->prepare("UPDATE `user` SET avatar VALUES (?);");

        return $stmt->execute([
            $avatarFileName
        ]);
    }
}
