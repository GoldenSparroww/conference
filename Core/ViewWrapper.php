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
        // Pokud uživatel namá SESSION_COOKIE, tak vrátí prázné pole -> např. odhlášen
        $this->twig->addGlobal('session', Session::all());
    }

    public function render(string $template, array $data = []): string {
        //todo, exceptions
        return $this->twig->render($template, $data);
    }
}
