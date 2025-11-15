<?php
require_once '../vendor/autoload.php';

use App\Core\Router;
use App\Core\ErrorHandler;
use App\Core\Session;
use App\Core\EnvHandler;

EnvHandler::load();

Session::start();

$handler = new ErrorHandler();
$handler->register();

$router = new Router();
$router->run();

//TODO, osadit aplikaci ikonami z bootsrapu (popř. je nastahovat)