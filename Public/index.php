<?php
require_once '../vendor/autoload.php';

use App\Core\Router;
use App\Core\ErrorHandler;
use App\Core\Session;

Session::start();

$handler = new ErrorHandler();
$handler->register();

$router = new Router();
$router->run();