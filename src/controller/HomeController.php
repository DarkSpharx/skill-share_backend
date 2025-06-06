<?php

declare(strict_types=1);

namespace App\controller;

use App\core\attributes\Route;

class HomeController
{
    #[Route("/", "GET")]
    function homeView()
    {
        echo "<!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <title>Skill Share - Accueil</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 80px auto; background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
                h1 { color: #2d3748; }
                p { color: #4a5568; }
                .logo { font-size: 48px; color: #3182ce; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='logo' style='text-align:center'>🤝</div>
                <h1>Bienvenue sur Skill-Share API</h1>
                <p>Votre backend est opérationnel !</p>
                <p>Consultez la documentation ou commencez à utiliser l’API.</p>
            </div>
        </body>
        </html>";
    }
}
