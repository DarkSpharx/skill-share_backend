<?php
// mise en place de l'autoload 

use App\core\Database;
use App\core\Router;

require_once __DIR__ . "/../bootstrap.php";

// L’autoload en PHP permet de charger automatiquement les classes ou fichiers nécessaires lorsqu’ils sont utilisés, sans avoir à faire un require ou include manuel à chaque fois.
// Cela simplifie la gestion des dépendances et rend le code plus propre et modulaire.
// la ligne de commande ligne 2, charge le fichier bootstrap.php qui, en général, configure l’autoload (souvent via Composer ou une fonction personnalisée) pour que toutes les classes de ton projet soient chargées automatiquement quand tu en as besoin.


// Composer est un gestionnaire de dépendances pour PHP.
// Il permet de :
// Installer automatiquement les bibliothèques dont ton projet a besoin.
// Gérer les versions de ces bibliothèques.
// Générer un autoload pour charger automatiquement toutes les classes des dépendances et de ton projet.
// Faciliter la mise à jour et la maintenance de tes dépendances.
// En résumé, Composer simplifie la gestion des librairies externes et l’organisation de ton code PHP.

$router = new Router();

try {
    $router = new Router();

    $db = Database::getConnexion();

    $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    $method = $_SERVER["REQUEST_METHOD"];

    $router->dispatch($uri, $method);
} catch (Exception $e) {
    return $json = json_encode([
        "error" => "Une erreur est survenue",
        "message" => $e->getMessage()
    ]);
}
