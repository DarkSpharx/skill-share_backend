<?php

declare(strict_types=1);

namespace App\controller;

use App\core\attributes\Route;
use App\model\User;
use App\repository\UserRepository;
use App\services\FileUploadService;
use App\services\MailService;
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

        try {
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) throw new Exception("Fichier .json invalide");

            $emailToken = bin2hex(random_bytes(32));

            $userData = [
                "username" => $data["username"] ?? "",
                "avatar" => $data["avatar"] ?? "avatar-default.png",
                "email" => $data["email"] ?? "",
                "password" => password($data["password"], PASSWORD_BCRYPT) ?? "",
                "email_token" => $emailToken
            ];

            // création de l'objet User
            $user = new User($userData);
            $user->setCreatedAt((new DateTime())->format("Y-m-d H:i:s"));
            $userRepository = new UserRepository();
            $saved = $userRepository->save($user);

            if (!$saved) throw new Exception("Erreur lors de la sauvegarde de l'utilisateur");

            if (!$user->getEmail_token()) throw new Exception("Erreur lors de la génération du token d'email");

            MailService::sendEmailVerification($user->getEmail(), $user->getEmail_token());

            echo json_encode([
                "success" => true,
                "message" => "Inscription réussie. Veuillez vérifier vos mails." . json_encode($data)
            ]);
        } catch (\Exception $e) {
            error_log("Erreur lors de l'inscription: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    #[Route("/api/verify-email", "GET")]
    public function verifyEmail()
    {
        try {
            $token = $_GET["token"] ?? null;

            if (!$token) throw new Exception("Token manquant");

            $userRepository = new UserRepository();
            $user = $userRepository->findUserByToken($token);

            if (!$user) throw new Exception("Utilisateur introuvable");

            $user->setEmail_token(null);
            $user->setIs_verified(true);

            $updated = $userRepository->update($user);
            if (!$updated) throw new Exception("Erreur lors de la mise à jour de l'utilisateur.");
            echo json_encode([
                "success" => true,
                "error" => "Email vérifié avec succès. Vous pouvez maintenant vous connecter."
            ]);
        } catch (\Exception $e) {
            error_log("Erreur inscription: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }
}
