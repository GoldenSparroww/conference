<?php
namespace App\Core;

class Router {
    public function run(): void {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $segments = explode('/', $uri);

        $controllerName = !empty($segments[0]) ? ucfirst($segments[0]) . 'Controller' : 'HomeController';
        //TODO, index() je typicky metoda pro nejake vypsani vseho, pokud nebyla nalezena shoda
        $actionName = $segments[1] ?? 'index';

        $controllerClass = "App\\Controllers\\$controllerName";

        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if (method_exists($controller, $actionName)) {
                $controller->$actionName();
            } else {
                //TODO, co s tim
                echo "Metoda $actionName neexistuje.";
            }
        } else {
            //TODO, co s tim
            echo "Controller $controllerName neexistuje.";
        }
    }
}
