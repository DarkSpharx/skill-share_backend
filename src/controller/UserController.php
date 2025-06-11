<?php

declare(strict_types=1);

namespace App\controller;

use App\core\attributes\Route;
use App\model\User;
use App\repository\UserRepository;
use App\services\FileUploadService;
use DateTime;
use Exception;

class UserController
{
    #[Route("/api/upload-avatar", "POST")]
    public function uploadAvatar()
    {
        if (!isset($_FILES["avatar"])) throw new Exception("Aucun fichier uploadé");

        try {
            $filename = FileUploadService::handleAvatarUpload($_FILES["avatar"], __DIR__ . "/../../public/uploads/avatar/");

            // Supprimer l'ancien avatar si ce n'est pas le défaut
            // if ($user->getAvatar() !== "avatar-default.png") {
            //     FileUploadService::deleteAvatar($user->getAvatar());
            // }

            $userRepository = new UserRepository();
            $saved = $userRepository->saveAvatar($filename);

            if (!$saved) throw new Exception("Erreur lors de la sauvegarde de l'utilisateur");

            echo json_encode([
                "success" => true,
                "message" => "Image d'avatar uploadé avec succès",
                "filename" => $filename
            ]);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'upload:" . $e->getMessage());
        }
    }

    #[Route("/api/register", "POST")]
    public function register()
    {

        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) throw new Exception("Fichier .json invalide");

        $userData = [
            "username" => $data["username"] ?? "",
            "avatar" => $data["avatar"] ?? "",
            "email" => $data["email"] ?? "",
            "password" => password_hash($data["password"], PASSWORD_BCRYPT) ?? "",
        ];

        // création de l'objet User
        $user = new User($userData);
        $user->setCreatedAt((new DateTime())->format("Y-m-d H:i:s"));
        $userRepository = new UserRepository();
        $saved = $userRepository->save($user);

        if (!$saved) throw new Exception("Erreur lors de la sauvegarde de l'utilisateur");

        echo json_encode([
            "success" => true,
            "message" => "Inscription réussie. Veuillez vérifier vos mails." . json_encode($data)
        ]);
    }
}
