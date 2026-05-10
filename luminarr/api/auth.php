<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$name || !$email || !$password) {
        jsonResponse(['error' => 'All fields are required'], 400);
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['error' => 'Invalid email format'], 400);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'Email already registered'], 400);
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$name, $email, $hashedPassword])) {
        $userId = $db->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = 'user';
        $_SESSION['user_name'] = $name;
        jsonResponse(['success' => 'Registration successful', 'redirect' => 'index.php']);
    } else {
        jsonResponse(['error' => 'Registration failed'], 500);
    }
}

if ($action === 'login') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$email || !$password) {
        jsonResponse(['error' => 'Email and password are required'], 400);
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        
        $redirect = ($user['role'] === 'admin') ? 'admin/index.php' : 'index.php';
        jsonResponse(['success' => 'Login successful', 'redirect' => $redirect]);
    } else {
        jsonResponse(['error' => 'Invalid email or password'], 401);
    }
}

if ($action === 'logout') {
    session_destroy();
    header('Location: ../login.php');
    exit;
}
?>
