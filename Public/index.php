<?php
require_once '../vendor/autoload.php';

use App\Core\Router;
use App\Core\ErrorHandler;

$handler = new ErrorHandler();
$handler->register();

$router = new Router();
$router->run();