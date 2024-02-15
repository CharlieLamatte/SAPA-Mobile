<?php

namespace Sportsante86\Sapa\Outils;

use PDO;
use PDOException;

class Connection
{
    private static $host = "localhost";
    private static $db_name = "sportsanzbtest2";
    private static $username = "root";
    private static $password = "root";

    public static function make()
    {
        try {
            $conn = new PDO(
                "mysql:host=" . self::$host . ";dbname=" . self::$db_name,
                self::$username,
                self::$password,
                [PDO::ATTR_PERSISTENT => true]);
            //$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;
        } catch(PDOException $exception) {
            exit('Database failed to connect: ' . $exception);
        }
    }
}