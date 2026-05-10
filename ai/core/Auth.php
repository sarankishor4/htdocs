<?php
namespace AI\Core;

class Auth {
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function register($username, $email, $password) {
        $db = Database::getInstance();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        $user = $db->escape($username);
        $mail = $db->escape($email);
        
        $sql = "INSERT INTO users (username, email, password) VALUES ('$user', '$mail', '$hash')";
        return $db->query($sql);
    }

    public static function login($username, $password) {
        $db = Database::getInstance();
        $user = $db->escape($username);
        
        $sql = "SELECT * FROM users WHERE username = '$user' OR email = '$user'";
        $result = $db->query($sql);
        $row = $result->fetch_assoc();

        if ($row && password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            return true;
        }
        return false;
    }

    public static function logout() {
        self::init();
        session_destroy();
    }

    public static function isLoggedIn() {
        self::init();
        return isset($_SESSION['user_id']);
    }

    public static function user() {
        if (!self::isLoggedIn()) return null;
        $db = Database::getInstance();
        $id = $_SESSION['user_id'];
        return $db->query("SELECT * FROM users WHERE id = $id")->fetch_assoc();
    }
}
?>
