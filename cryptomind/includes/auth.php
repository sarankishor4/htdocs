<?php
session_start();
require_once __DIR__ . '/db.php';

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'Not authenticated']);
        exit;
    }
    return $_SESSION['user_id'];
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) return null;
    $s = getDB()->prepare("SELECT id,username,email,full_name,avatar_color,bio,balance,role,created_at,last_login FROM users WHERE id=?");
    $s->execute([$_SESSION['user_id']]);
    return $s->fetch();
}

function loginUser($id) {
    $_SESSION['user_id'] = $id;
    getDB()->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$id]);
}

function requireAdmin() {
    $uid = requireAuth();
    $u = getCurrentUser();
    if (!$u || $u['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status'=>'error','message'=>'Admin access required']);
        exit;
    }
    return $uid;
}

function logoutUser() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(),'',time()-42000,$p["path"],$p["domain"],$p["secure"],$p["httponly"]);
    }
    session_destroy();
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
