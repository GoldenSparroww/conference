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

    public function getUserById(string $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function getUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function create(
        string $nickname, string $first_name, string $last_name, string $email, string $password
    ): bool
    {
        if(!$this->isUserDataValid($nickname, $first_name, $last_name, $email, $password)){
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $this->db->beginTransaction();

            $stmt_create_user = $this->db->prepare("
            INSERT INTO users (nickname, first_name, last_name, email, password_hash)
            VALUES (?, ?, ?, ?, ?)
            ");
            $stmt_create_user_success = $stmt_create_user->execute([$nickname, $first_name, $last_name, $email, $hash]);

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
        $user = $this->getUserByEmail($email);
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

    public function getRole(int $id): ?string
    {
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT id, nickname, first_name, last_name, email, role, is_active FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUser(
        int $id, string $nickname = null, string $first_name = null, string $last_name = null, string $email = null, string $password = null,
        int $role = null, bool $is_active = null
    ): bool
    {
        //TODO, htmlspecialchars
        $target_user = $this->getUserById($id);

        if (!$target_user) {
            return false;
        }

        $attributes = [
            ':id'       => $id,
            'nickname'  => $nickname ?? $target_user['nickname'],
            'first_name'=> $first_name ?? $target_user['first_name'],
            'last_name' => $last_name ?? $target_user['last_name'],
            'email'     => $email ?? $target_user['email'],
            'password_hash'  => $password !== null
                                ? password_hash($password, PASSWORD_DEFAULT)
                                : $target_user['password_hash'],
            'role'      => $role ?? $target_user['role'],
            'is_active' => $is_active !== null ? (int)$is_active : $target_user['is_active'],
        ];

        $sql = "UPDATE users 
        SET 
            nickname = :nickname,
            first_name = :first_name,
            last_name = :last_name,
            email = :email,
            password_hash = :password_hash,
            role = :role,
            is_active = :is_active
        WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($attributes);
    }
}
