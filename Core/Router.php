<?php

namespace App\Core;

use Exception;

class Router
{
    public function run(): void
    {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $segments = explode('/', $uri);

        $controllerName = !empty($segments[0]) ? ucfirst($segments[0]) . 'Controller' : 'HomeController';
        $actionName = $segments[1] ?? 'index';
        $controllerClass = HelperFuncs::path_join("App", "Controllers", $controllerName);

        $pageName = $segments[0];

        if (!class_exists($controllerClass)) {
            throw new Exception("Page '$pageName' not found", 404);
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $actionName)) {
            throw new Exception("Action '$actionName' not found in page '$pageName'", 404);
        }

        $controller->$actionName();
    }
}
