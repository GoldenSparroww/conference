<?php
namespace App\Core;

use App\Core\Helper;
use Dotenv\Dotenv;

class EnvHandler {
    public static function load(): void {
        // __DIR__ je 'C:\...\web\Public'
        // /../ znamená "jít o adresář výš" do 'C:\...\web'
        // Dotenv stačí adrsář, kde se .env nachází
        $path = Helper::path_join(__DIR__, '..');
        $dotenv = Dotenv::createImmutable($path);
        $dotenv->load();
    }

    public static function get(string $key): string {
        return $_ENV[$key];
    }
}