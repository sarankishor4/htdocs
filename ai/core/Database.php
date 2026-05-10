<?php
namespace AI\Core;

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new \mysqli(Config::DB_HOST, Config::DB_USER, Config::DB_PASS, Config::DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function query($sql) {
        return $this->conn->query($sql);
    }

    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
}
?>
