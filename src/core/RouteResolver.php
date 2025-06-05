<?php

declare(strict_types=1);

namespace App\core;

use App\core\attributes\Route;
use ReflectionClass;

class RouteResolver
{
    public static function getRoutes(): array
    {
        $routes = [];
        $controllerPath = __DIR__ . "/../controller";
        $controllerFiles = glob($controllerPath . "/*Controller.php");
        foreach ($controllerFiles as $controllerFile) {
            $className = "App\\Controller\\" . basename($controllerFile, ".php");
            $reflection = new ReflectionClass($className);

            foreach ($reflection->getMethods() as $method) {
                $attriutes = $method->getAttributes(Route::class);

                foreach ($attriutes as $attriute) {
                    $route = $attriute->newInstance();
                    $routes[$route->method][$route->path] = [
                        $className,
                        $method->getName()
                    ];
                }
            }
        }
        return $routes;
    }
}
