<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\ArticleModel;
use App\Models\RolesID;

class AuthorController extends Controller {

    private ArticleModel $articleModel;

    public function __construct() {
        parent::__construct();

        $this->articleModel = new ArticleModel();

        if (
            !Session::get('user') ||
            !(Session::get('user')['role'] == RolesID::AUTHOR->value)) {
            header("Location: /");
            exit;
        }
    }

    public function dashboard(): void {
        $authorId = Session::get('user')['id'];
        $articles = $this->articleModel->getArticlesByAuthorId($authorId);

        echo $this->view->render('AuthorDashboard.twig', [
            'articles' => $articles,
            'session' => Session::all()
        ]);
    }

    public function create(): void {
        echo $this->view->render('AuthorCreate.twig', [
            'session' => Session::all()
        ]);
    }

    public function submit(): void {
        // 1. Ověření, že jde o POST požadavek
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /author/create');
            exit;
        }

        $formData = $_POST;
        $fileData = $_FILES['article_file'];
        $errors = [];

        // 2. Validace vstupu
        $title = htmlspecialchars($formData['title']) ?? '';
        $abstract = htmlspecialchars($formData['abstract']) ?? '';

        if (empty($title)) {
            $errors[] = 'Titulek je povinný.';
        } elseif (strlen($title) > 255) {
            $errors[] = 'Titulek nesmí být delší než 255 znaků.'.' ('.strlen($title).')';
        }

        if (empty($abstract)) {
            $errors[] = 'Abstrakt je povinný.';
        } elseif (strlen($abstract) > 1000) {
            $errors[] = 'Abstrakt nesmí být delší než 1000 znaků.'.' ('.strlen($abstract).')';
        }

        // 3. Validace souboru
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Při nahrávání souboru došlo k chybě. Pravděpodobně nebyl vybrán žádný soubor.';
        } else {
            $fileType = mime_content_type($fileData['tmp_name']);
            $allowedTypes = [
                'application/pdf', //pdf
                'application/msword', // .doc
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' // .docx
            ];
            $maxSize = 10 * 1024 * 1024; // 10 MB

            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = 'Nepovolený typ souboru. Nahrávejte pouze .pdf, .doc, nebo .docx.';
            }
            if ($fileData['size'] > $maxSize) {
                $errors[] = 'Soubor je příliš velký (max. 10 MB).';
            }
        }

        // 4. Zpracování chyb validace
        if (!empty($errors)) {
            // Pokud jsou chyby, znovu zobrazíme formulář s chybami a předvyplněnými daty
            echo $this->view->render('AuthorCreate.twig', [
                'errors' => $errors,
                'form_data' => $formData, // Pro zachování hodnot v inputech
                'session' => $_SESSION
            ]);
            return;
        }

        // 5. Zpracování nahrání souboru (pokud je vše v pořádku)

        // Cesta k adresáři pro nahrání (musí být zapisovatelný!)
        // Jdeme o 2 úrovně výš (z App/Controllers do rootu) a pak do public/uploads
        $uploadDir = __DIR__ . '/../uploads/articles/';

        // Zajistíme, že adresář existuje
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Vytvoření unikátního názvu souboru
        $fileName = uniqid('article_') . '-' . basename($fileData['name']);
        $uploadFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($fileData['tmp_name'], $uploadFilePath)) {
            // Soubor byl úspěšně přesunut

            // Cesta pro uložení do DB (relativní k public rootu)
            $dbFilePath = '/uploads/articles/' . $fileName;
            $authorId = $_SESSION['user']['id'];

            // 6. Uložení do databáze
            $success = $this->articleModel->create($title, $abstract, $dbFilePath, $authorId);

            if ($success) {
                // 7. Přesměrování na dashboard po úspěchu
                // TODO, Zde by se hodila i "flash message" o úspěchu
                header('Location: /author/dashboard');
                exit;
            } else {
                $errors[] = 'Článek se nepodařilo uložit do databáze.';
                // Pokud selže DB, smažeme nahraný soubor
                unlink($uploadFilePath);
            }

        } else {
            $errors[] = 'Soubor se nepodařilo přesunout na cílové umístění na serveru.';
        }

        // 8. Zobrazení chyb (pokud selhalo nahrání souboru nebo uložení do DB)
        echo $this->view->render('AuthorCreate.twig', [
            'errors' => $errors,
            'form_data' => $formData,
            'session' => $_SESSION
        ]);
    }
}