<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\UserModel;
use PDO;
use PDOException;
use App\Core\EnvHandler;
use App\Models\RolesID;

class AuthController extends Controller
{
    private PDO $db;
    private UserModel $userModel;

    public function __construct()
    {
        parent::__construct();
        $db_type = EnvHandler::get('DB_TYPE');
        $db_charset = EnvHandler::get('DB_CHARSET');
        $db_host = EnvHandler::get('DB_HOST');
        $db_name = EnvHandler::get('DB_NAME');
        $db_user = EnvHandler::get('DB_USER');
        $db_pass = EnvHandler::get('DB_PASS');

        try {
            $this->db = new PDO("$db_type:host=$db_host;dbname=$db_name;charset=$db_charset", $db_user, $db_pass);
        } catch (PDOException) {
            throw new PDOException("There was an error connecting to the database. Try again later.");
        }
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
            $email = htmlspecialchars($_POST['email']) ?? '';
            $password = htmlspecialchars($_POST['password']) ?? '';

            $user = $this->userModel->verify($email, $password);
            if ($user) {
                Session::set('user', $user);
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
            $nickname = htmlspecialchars($_POST['nickname']) ?? '';
            $first_name = htmlspecialchars($_POST['first_name']) ?? '';
            $last_name = htmlspecialchars($_POST['last_name']) ?? '';
            $email = htmlspecialchars($_POST['email']) ?? '';
            $password = htmlspecialchars($_POST['password']) ?? '';

            if ($this->userModel->create($nickname, $first_name, $last_name, $email, $password)) {
                header('Location: /auth/login');
                exit;
            } else {
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
