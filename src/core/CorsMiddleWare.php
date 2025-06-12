<?php

declare(strict_types=1);

namespace App\core;

class CorsMiddleWare
{
    public function handle()
    {
        // Définition de l'origine autorisée pour les requêtes CORS
        // Ici, on autorise les requêtes provenant de http://localhost:3001
        // Cela est utile pour les applications front-end qui tournent sur un serveur de développement différent de l'API.
        // Les requêtes CORS (Cross-Origin Resource Sharing) permettent à un serveur de contrôler qui peut accéder à ses ressources depuis un autre domaine.
        header("Access-Control-Allow-Origin: http://localhost:3001");

        // Définition des types de contenu autorisés pour les requêtes CORS
        header("Content-Type: application/json; charset=UTF-8");

        // Définition des méthodes HTTP autorisées pour les requêtes CORS
        header(("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"));

        // Définition des headers HTTP autorisées pour les requêtes CORS
        header(("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"));

        // CSP adaptée pour Google Fonts
        header(
            "Content-Security-Policy: "
                . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
                . "style-src-elem 'self' https://fonts.googleapis.com; "
                . "font-src 'self' https://fonts.gstatic.com https://garet.typeforward.com;"
        );

        // Gérer les requêtes préflight (OPTIONS)
        // Les requêtes préflight sont envoyées par certain navigateur pour vérifier si la requête réelle est autorisée.
        if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
            http_response_code(200);
            exit();
        }
    }
}
