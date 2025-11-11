<?php
namespace App\Models;

use PDO;

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
        string $nickname, string $first_name, string $last_name ,string $email,
        string $password, string $role = 'author'
    ): bool
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            INSERT INTO users (nickname, first_name, last_name, email, password_hash)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$nickname, $first_name, $last_name, $email, $hash]);
        //TODO, role
    }

    public function verify(string $email, string $password): ?array
    {
        //TODO, lépe, nejen podle mailu, jestli se to tak dělá teda
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return null;
    }
}
