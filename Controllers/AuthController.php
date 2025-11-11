<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\UserModel;
use PDO;

class AuthController extends Controller
{
    private PDO $db;
    private UserModel $userModel;

    public function __construct()
    {
        parent::__construct();

        //TODO, env
        $this->db = new PDO('mysql:host=localhost;dbname=conference;charset=utf8', 'root', 'root');
        $this->userModel = new UserModel($this->db);
    }

    public function index(): void
    {
        header('Location: /auth/login');
        exit;
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->verify($email, $password);
            if ($user) {
                Session::set('user', $user);
                //file_put_contents('log_user.txt', print_r($user, true), FILE_APPEND);
                //file_put_contents('log_SESSION.txt', print_r($_SESSION, true), FILE_APPEND);
                header('Location: /home/index');
                exit;
            } else {
                echo $this->view->render('login.twig', ['error' => 'Neplatné přihlašovací údaje']);
                return;
            }
        }

        echo $this->view->render('login.twig');
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //TODO, ošetřit a ne jen vyplnit prazdnymi znaky
            $nickname = $_POST['nickname'] ?? '';
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // TODO, potřebuji ještě pořešit role a její zápis do DB
            if ($this->userModel->create($nickname, $first_name, $last_name, $email, $password)) {
                header('Location: /auth/login');
                exit;
            } else {
                //TODO, vyřešit errory lépe
                echo $this->view->render('register.twig', ['error' => 'Chyba při registraci']);
                return;
            }
        }

        // Pokud prisel GET
        echo $this->view->render('register.twig');
    }

    public function logout(): void
    {
        Session::destroy();
        header('Location: /home/index');
        exit;
    }
}
