<?php

namespace App\Core;

use App\Core\EnvHandler;
use PDO;
use PDOException;

abstract class Model
{
    protected PDO $db;

    //TODO, udělat singleton na DB
    public function __construct()
    {
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
    }
}