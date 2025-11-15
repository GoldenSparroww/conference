<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\UserModel;
use App\Models\RolesID;
use App\Core\EnvHandler;
use PDO;
use PDOException;

class AdminController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        parent::__construct();

        $this->userModel = new UserModel();

        if (
            !Session::get('user') ||
            !(Session::get('user')['role'] == RolesID::ADMIN->value or Session::get('user')['role'] == RolesID::SUPERADMIN->value)) {
            header("Location: /");
            exit;
        }
    }

    public function index() {
        header('Location: /admin/dashboard');
        exit;
    }

    public function dashboard(): void
    {
        // TODO datum je zde pro zabránění cachování, dát na konci pryč aby AJAX měl smysl
        $timestamp = time();
        echo $this->view->render("Admin.twig", ['cache_buster' => $timestamp]);
    }

    //---------------------------------------------------------
    // AJAX API - vrací JSON
    //---------------------------------------------------------

    public function apiGetUsers(): void
    {
        header("Content-Type: application/json");

        $users = $this->userModel->getAll();
        echo json_encode($users);
        exit;
    }

    public function apiUpdateUser(): void {
        header("Content-Type: application/json");
        //  php://input je speciální stream v PHP, který obsahuje RAW tělo HTTP requestu
        $body = json_decode(file_get_contents("php://input"), true);

        //TODO, vyřešit lépe, než jen vypsat. Navíc je bezpečnostní díra (ukazuju error)
        if (!$body) {
            echo json_encode(["error" => "Invalid JSON"]);
            exit;
        }

        $target_user_id = $body['user_id'];
        $target_user_role_new = RolesID::getValFromStr($body['role']);
        $target_user_active_new = $body['is_active'] ? 1 : 0;

        $target_user_role_old = $this->userModel->getRole($target_user_id);

        $current_user = Session::get('user');

        // Test práv admina
        if ($current_user['role'] == RolesID::ADMIN->value) {

            // Admin nemůže měnit sám sebe
            if ($target_user_id == $current_user['id']) {
                echo json_encode(["error" => "Admin nemůže upravovat sám sebe"]);
                exit;
            }

            // Admin nemůže měnit roli admina a superadmina
            if ($target_user_role_old == RolesID::ADMIN->value || $target_user_role_old == RolesID::SUPERADMIN->value) {
                echo json_encode(["error" => "Admin nemůže upravit jiného admina nebo superadmina"]);
                exit;
            }

            // Admin nemůže nastavovat nového admina a superadmina
            if ($target_user_role_new == RolesID::ADMIN->value || $target_user_role_new == RolesID::SUPERADMIN->value) {
                echo json_encode(["error" => "Admin nemůže přidělovat roli admin, nebo superadmin"]);
                exit;
            }
        }

        // SuperAdmin může vše (sem se dostane i admin, pokud nedělá něco nekalého)
        $this->userModel->updateUser(
            id: $target_user_id,
            role: $target_user_role_new,
            is_active: $target_user_active_new,
        );

        echo json_encode(["success" => true]);
        exit;
    }
}
