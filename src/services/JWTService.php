<?php

declare(strict_types=1);

namespace App\services;

use Exception;

class JWTService
{

    private static ?string $key = null;

    private static function initKey(): void
    {
        if (self::$key === null) {
            self::$key = $_ENV["JWT_SECRET_KEY"] ?? "";
            if (empty(self::$key)) throw new Exception("Clé secrète JWT non définie dans les variables d'environnement (JWT_SECRET_KEY).");
        }
    }

    public static function generate(array $payload = []): string
    {
        self::initKey();

        // header
        $header = [
            "typ" => "JWT",
            "alg" => "HS256",
        ];

        // Ajoute l'expiration au payload
        $payload["exp"] = time() + (24 * 60 * 60); // 24 heures

        // Encoder le header et le payload en base64
        $base64Header = self::base64url_encode(json_encode($header));
        $base64Payload = self::base64url_encode(json_encode($payload));

        // Création de la signature
        $signature = hash_hmac(
            "sha256",
            $base64Header . "." . $base64Payload,
            self::$key,
            true
        );
        $base64Signature = self::base64url_encode($signature);

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    private static function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
    }

    private static function base64url_decode(string $data)
    {
        return base64_decode(strtr($data, "-_", "+/"));
    }

    public static function verify(string $token)
    {
        self::initKey();

        // séparer les trois parties du token (paylod / header / signature)
        $parts = explode(".", $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$base64Header, $base64Payload, $base64Signature] = $parts;

        // on refait la signature car elle est encodée
        $signature = hash_hmac(
            "sha256",
            $base64Header . "." . $base64Payload,
            self::$key,
            true
        );

        // verification de la signature
        if (!hash_equals(self::base64url_decode($base64Signature), $signature)) return false;

        // docadage du payload
        $payload = json_decode(self::base64url_decode($base64Payload), true);

        // verification du payload décodé
        if (isset($payload["exp"]) && $payload["exp"] > time()) return false;

        return $payload;
    }
}
