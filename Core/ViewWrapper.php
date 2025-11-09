<?php
namespace App\Core;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class ViewWrapper {
    private Environment $twig;

    public function __construct() {
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $this->twig = new Environment($loader, [
            'cache' => false, // nebo __DIR__ . '/../../cache'
            'debug' => true,
        ]);

        // Globální proměnné – dostupné ve všech šablonách
        $this->twig->addGlobal('base_url', '/my_app/public');
        $this->twig->addGlobal('app_name', 'My App');
    }

    public function render(string $template, array $data = []): string {
        return $this->twig->render($template, $data);
    }
}
