<?php
namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class Controller {
    protected $twig;

    public function __construct() {
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $this->twig = new Environment($loader);
    }

    public function view($template, $data = []) {
        echo $this->twig->render($template, $data);
    }
}
