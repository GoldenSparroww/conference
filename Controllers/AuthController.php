<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\UserModel;

class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        parent::__construct();

        $this->userModel = new UserModel();
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

    public function blocked(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: /auth/login');
            exit;
        }

        $is_active = Session::get('is_active');

        if ($is_active === null || $is_active == 1) {
            header('Location: /home/index');
            exit;
        }

        echo $this->view->render('UserBlocked.twig');
    }
}
