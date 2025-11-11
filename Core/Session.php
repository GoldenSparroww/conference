<?php
namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        // vymaže paměť, pole $_SESSION by bylo jinak stále k dispozici po zbytek aktuálního skriptu
        $_SESSION = [];
        // zničí session na server
        session_destroy();
        // nechá vypršet platnost session cookie u uživatele
        setcookie(session_name(), '', time() - 3600, '/');
    }

    public static function all(): array
    {
        return $_SESSION ?? [];
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
}
