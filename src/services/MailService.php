<?php

declare(strict_types=1);

namespace App\services;

class MailService
{
    public static function sendEmailVerification(string $email, string $token): void
    {
        $link = "http://localhost:3001/verify-email?token=$token";

        $subject = "Vérification de votre adresse e-mail";
        $message = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
            body {
                background: #1D1D1B;
                color: #F9B233;
                font-family: Arial, sans-serif;
                padding: 40px 0;
            }
            .container {
                max-width: 480px;
                margin: 0 auto;
                background: #1D1D1B;
                border-radius: 8px;
                padding: 32px 24px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            }
            h2 {
                color: #F9B233;
                margin-bottom: 24px;
            }
            p {
                color: #F9B233;
                font-size: 16px;
                line-height: 1.6;
            }
            .btn {
                display: inline-block;
                margin-top: 24px;
                padding: 12px 32px;
                background: #005A70;
                color: #FFF !important;
                text-decoration: none;
                border-radius: 4px;
                font-weight: bold;
                font-size: 16px;
                transition: background 0.2s;
                font-family: Arial, sans-serif;
            }
            .btn:hover {
                background: #004556;
            }
            </style>
        </head>
        <body>
            <div class="container">
            <h2>Vérification de votre adresse e-mail</h2>
            <p>Bonjour,</p>
            <p>
                Pour finaliser votre inscription, veuillez vérifier votre adresse e-mail en cliquant sur le bouton ci-dessous :
            </p>
            <a href="' . $link . '" class="btn">Vérifier mon e-mail</a>
            <p style="margin-top:32px;">
                Si vous n\'avez pas créé de compte, vous pouvez ignorer ce message.<br><br>
                Cordialement,<br>
                La team SkillShare
            </p>
            </div>
        </body>
        </html>
        ';
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: noreply@skillshare.com\r\n";

        mail($email, $subject, $message, $headers);
    }
}
