<?php
// fin de mise place autoload

use Dotenv\Dotenv;

require_once __DIR__ . "/vendor/autoload.php";

// initialisation de la librairie vlucas/phpdotenv
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
