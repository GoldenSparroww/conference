<?php
namespace App\Models;

use PDO;
use PDOException;

class UserModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function create(
        string $nickname, string $first_name, string $last_name, string $email, string $password,
    ): bool
    {
        if(!$this->isUserDataValid($nickname, $first_name, $last_name, $email, $password)){
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $author_role_id = RolesID::AUTHOR->value;

        try {
            $this->db->beginTransaction();

            $stmt_create_user = $this->db->prepare("
            INSERT INTO users (nickname, first_name, last_name, email, password_hash)
            VALUES (?, ?, ?, ?, ?)
            ");
            $stmt_create_user_success = $stmt_create_user->execute([$nickname, $first_name, $last_name, $email, $hash]);

            // Získání ID právě vloženého uživatele (btw, to je mega hustý že to jde)
            $user_id = $this->db->lastInsertId();

            $stmt_role_assign = $this->db->prepare("INSERT INTO users_roles (user_id, role_id) VALUES (?, ?)");
            $stmt_role_assign_success = $stmt_role_assign->execute([$user_id, $author_role_id]);

            $this->db->commit();

            return true;
        } catch (PDOException $e) {
            echo $e;
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function verify(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return null;
    }

    private function isUserDataValid(
        string $nickname, string $first_name, string $last_name, string $email, string $password
    ): bool
    {
        if (empty($nickname) || empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        if(!$this->isNicknameTaken($nickname)) return false;

        if(!$this->isEmailTaken($email)) return false;

        return true;
    }

    public function isNicknameTaken(string $nickname): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(id) FROM users WHERE nickname = ?");
        $stmt->execute([$nickname]);
        echo $stmt->fetchColumn();
        return $stmt->fetchColumn() == 0;
    }

    public function isEmailTaken(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(id) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() == 0;
    }
}
