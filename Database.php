<?php
require_once "config.php";

/**
 * Klasa odpowiedzialna za bezpieczne nawiązywanie i współdzielenie połączenia z bazą danych PostgreSQL.
 */
class Database {
    private $username;
    private $password;
    private $host;
    private $database;

    private static $connection = null;

    public function __construct()
    {
        $this->username = USERNAME;
        $this->password = PASSWORD;
        $this->host = HOST;
        $this->database = DATABASE;
    }

    /**
     * Zwraca aktywne połączenie z bazą danych. Jeśli nie istnieje, tworzy je.
     */
    public function connect() {
        if (self::$connection !== null) {
            return self::$connection;
        }

        try {
            self::$connection = new PDO(
                "pgsql:host=$this->host;port=5432;dbname=$this->database",
                $this->username,
                $this->password,
                ["sslmode" => "prefer"]
            );

            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->exec("SET TIME ZONE 'Europe/Warsaw'");
            return self::$connection;
        } catch (PDOException $e) {
            die("Połączenie z bazą danych nie powiodło się: " . $e->getMessage());
        }
    }
}