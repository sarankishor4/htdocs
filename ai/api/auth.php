<?php
require_once '../core/Config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';

use AI\Core\Auth;

Auth::init();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $user = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    if (Auth::register($user, $email, $pass)) {
        echo json_encode(['status' => 'success', 'message' => 'Registration complete.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed or user exists.']);
    }
} 
elseif ($action === 'login') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    if (Auth::login($user, $pass)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
    }
}
elseif ($action === 'logout') {
    Auth::logout();
    echo json_encode(['status' => 'success']);
}
?>
