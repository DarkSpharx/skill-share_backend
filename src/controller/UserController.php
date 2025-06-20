<?php

declare(strict_types=1);

namespace App\controller;

use App\core\attributes\Route;
use App\model\User;
use App\repository\UserRepository;
use App\services\FileUploadService;
use App\services\JWTService;
use App\services\MailService;
use DateTime;
use Exception;

class UserController
{
    private UserRepository $userRepository;
    public function __construct()
    {
        $this->userRepository = new UserRepository;
    }


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
                "password" => password_hash($data["password"], PASSWORD_BCRYPT) ?? "",
                "email_token" => $emailToken
            ];

            // création de l'objet User
            $user = new User($userData);
            $this->verifyUniqueUserEntry($data, $user);
            $user->setCreatedAt((new DateTime())->format("Y-m-d H:i:s"));

            $saved = $this->userRepository->save($user);

            if (!$saved) throw new Exception("Erreur lors de la sauvegarde de l'utilisateur");

            if (!$user->getEmail_token()) throw new Exception("Erreur lors de la génération du token d'email");

            MailService::sendEmailVerification($user->getEmail(), $user->getEmail_token());

            echo json_encode([
                "success" => true,
                "message" => "Inscription réussie. Veuillez vérifier vos mails." . json_encode($data)
            ]);
        } catch (Exception $e) {
            error_log("Erreur lors de l'inscription: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }

    #[Route("/api/verify-email", "GET")]
    public function verifyEmail()
    {
        try {
            $token = $_GET["token"] ?? null;

            if (!$token) throw new Exception("Token manquant");


            $user = $this->userRepository->findUserByToken($token);

            if (!$user) throw new Exception("Utilisateur introuvable");

            $user->setEmail_token(null);
            $user->setIs_verified(true);

            $updated = $this->userRepository->update($user);
            if (!$updated) throw new Exception("Erreur lors de la mise à jour de l'utilisateur.");
            echo json_encode([
                "success" => true,
                "error" => "Email vérifié avec succès. Vous pouvez maintenant vous connecter."
            ]);
        } catch (Exception $e) {
            error_log("Erreur inscription: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }

    #[Route("/api/login", "POST")]
    public function login()
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) throw new Exception("Fichier .json invalide");

            $user = $this->userRepository->findUserByEmail($data["email"]);
            if (!$user) throw new Exception(("Email ou mot de passe incorrect"));
            if (!password_verify($data["password"], $user->getPassword())) throw new Exception(("Email ou mot de passe incorrect"));
            if (!$user->getIs_verified()) throw new Exception("Veuillez confirmer votre email avant de vous connecter.");

            // Générer un token de session ou JWT ici si nécessaire
            $token = JWTService::generate([
                "id_user" => $user->getIduser(),
                "role" => $user->getRole(),
                "email" => $user->getEmail()
            ]);

            echo json_encode([
                "success" => true,
                "token" => $token,
                "user" => [
                    "avatar" => $user->getAvatar(),
                    "username" => $user->getUsername(),
                    "role" => $user->getRole()
                ]
            ]);
        } catch (Exception $e) {
            error_log("Erreur lors de l'inscription: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }

    #[Route("/api/user/update", "POST")]
    public function updateProfil()
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) throw new Exception("Fichier .json invalide");



            // récupération du token
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

            $token = str_replace('Bearer ', '', $authHeader);
            if (!$token) throw new Exception('Not authorized');

            // Appel du service JWT pour faire la verification du token
            $verifToken = JWTService::verify($token);
            if (!$verifToken) throw new Exception("Token invalide");

            $user = $this->userRepository->findUserById($verifToken["id_user"]);
            if (!$user) throw new Exception("utilisateur non trouvé");

            $this->verifyUniqueUserEntry($data, $user);

            // mise a jour des infos utilisateurs (ici le pseudo)
            if (isset($data["username"])) $user->setUsername($data["username"]);
            if (isset($data["email"])) $user->setEmail($data["email"]);

            // Exemple si autre champs a modifier (ici first name) 
            // if(isset($data["firstname"])) $user->setFirstname($data["firstname"]);

            $updated = $this->userRepository->update($user);

            if (!$updated) throw new Exception("Probleme de mise a jour de la BDD");

            echo json_encode([
                "success" => true,
                "message" => "Profil mis a jours avec succés !"
            ]);
        } catch (Exception $e) {
            error_log("Erreur lors de la mise a jour du profile: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
    }

    public function verifyUniqueUserEntry(array $data, ?User $currentUser = null): void
    {
        error_log("Données de validation: " . json_encode($data));
        error_log("Utilisateur actuel: " . ($currentUser ? $currentUser->getUsername() . " / " . $currentUser->getEmail() : "null"));

        $usernameExists = false;
        $emailExists = false;

        // Vérifiez le nom d'utilisateur uniquement s'il est fourni et différent du nom d'utilisateur actuel
        if (!empty($data['username'])) {
            error_log("Vérification du nom d'utilisateur: " . $data['username']);
            if ($currentUser === null || $data['username'] !== $currentUser->getUsername()) {
                error_log("Le nom d'utilisateur est différent de celui actuel, vérification de la base de données...");
                $existingUser = $this->userRepository->findUserByUsername($data['username']);
                $usernameExists = $existingUser ? true : false;
                error_log("Le nom d'utilisateur existe: " . ($usernameExists ? "yes" : "no"));
            } else {
                error_log("Nom d'utilisateur identique à celui de l'utilisateur actuel, vérification ignorée");
            }
        }

        if (!empty($data['email'])) {
            error_log("Vérification des e-mails: " . $data['email']);
            if ($currentUser === null || $data['email'] !== $currentUser->getEmail()) {
                error_log("Le courrier électronique est différent de la base de données de vérification actuelle...");
                $existingUser = $this->userRepository->findUserByEmail($data['email']);
                $emailExists = $existingUser ? true : false;
                error_log("L'email existe: " . ($emailExists ? "yes" : "no"));
            } else {
                error_log("E-mail identique à celui de l'utilisateur actuel, vérification ignorée");
            }
        }
    }

    #[Route('/api/user/update-avatar', 'POST')]
    /**
     * Met à jour l'avatar de l'utilisateur
     * Route : POST /api/user/update-avatar
     */
    public function updateAvatar(): void
    {
        try {
            // Récupération token
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

            $token = str_replace('Bearer ', '', $authHeader);
            if (!$token) throw new \Exception('Not authorized');


            if (!$token) {
                throw new \Exception('Non autorisé');
            }

            $payload = JWTService::verify($token);
            if (!$payload) {
                throw new \Exception('Token invalide');
            }

            if (!isset($_FILES['avatar'])) {
                throw new \Exception('Aucun fichier envoyé');
            }

            $user = $this->userRepository->findUserById($payload['id_user']);

            if (!$user) {
                throw new \Exception('Utilisateur non trouvé');
            }

            // Gérer l'upload de l'avatar
            try {
                $upload_dir = __DIR__ . "/../../public/uploads/avatar/";
                $avatarFilename = FileUploadService::handleAvatarUpload($_FILES["avatar"], $upload_dir);

                // Supprimer l'ancien avatar si il existe
                if ($user->getAvatar() && $user->getAvatar() !== "avatar-default.png") {
                    FileUploadService::deleteOldAvatar($user->getAvatar(), $upload_dir);
                }

                $user->setAvatar($avatarFilename);
                $updated = $this->userRepository->update($user);

                if (!$updated) {
                    throw new \Exception("Erreur lors de la mise à jour de l'avatar");
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Avatar mis à jour avec succès',
                    'avatar' => $avatarFilename
                ]);
            } catch (\Exception $e) {
                throw new \Exception('Erreur lors de l\'upload: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    #[Route('/api/user/request-reset', 'POST')]
    /**
     * Envoie un email avec un lien de réinitialisation
     * Route : POST /api/user/request-reset
     */
    public function requestPasswordReset(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['email'])) {
                throw new \Exception('Email requis');
            }

            $user = $this->userRepository->findUserByEmail($data['email']);

            if (!$user) {
                throw new \Exception('Email non trouvé');
            }

            // Générer un token de réinitialisation
            $resetToken = bin2hex(random_bytes(32));
            $user->setReset_token($resetToken);
            $user->setReset_at((new DateTime())->format("Y-m-d H:i:s"));


            if (!$this->userRepository->update($user)) {
                throw new \Exception('Erreur lors de la génération du token');
            }

            // Envoyer l'email
            MailService::sendPasswordResetEmail($user->getEmail(), $resetToken);

            echo json_encode([
                'success' => true,
                'message' => 'Email de réinitialisation envoyé'
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    #[Route('/api/user/reset-password', 'POST')]
    /**
     * Réinitialise le mot de passe avec un token
     * Route : POST /api/user/reset-password
     */
    public function resetPassword(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['token']) || empty($data['password'])) {
                throw new \Exception('Token et mot de passe requis');
            }

            $user = $this->userRepository->findByResetToken($data['token']);

            if (!$user) {
                throw new \Exception('Token invalide ou expiré');
            }

            // Vérifier si le token n'est pas expiré (24h)
            $resetAt = $user->getReset_at();
            if (!$resetAt) {
                throw new \Exception('Date de réinitialisation invalide');
            }

            $resetAtDateTime = new \DateTime($resetAt);
            $now = new \DateTime();
            if ($resetAtDateTime->diff($now)->h >= 24) {
                throw new \Exception('Token expiré');
            }

            // Mettre à jour le mot de passe
            $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
            $user->setReset_token(null);
            $user->setReset_at(null);

            if (!$this->userRepository->update($user)) {
                throw new \Exception('Erreur lors de la mise à jour du mot de passe');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
