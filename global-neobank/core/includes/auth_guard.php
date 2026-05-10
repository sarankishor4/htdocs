<?php
// ─────────────────────────────────────────
//  GlobalBank — Auth Guard (Session Check)
// ─────────────────────────────────────────

require_once __DIR__ . '/../config.php';

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false,      // Set true on HTTPS production
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

function checkAccountStatus(): void {
    require_once __DIR__ . '/db.php';
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT account_status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $status = $stmt->fetchColumn();
    
    if ($status === 'banned') {
        $_SESSION = [];
        session_destroy();
        // If it's an API request, return JSON
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Account Banned']);
            exit;
        }
        header('Location: ' . APP_URL . '/login.php?error=Account+Banned');
        exit;
    }
    
    if ($status === 'frozen' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Your account is frozen. Transactions are disabled.']);
        exit;
    }
}

function requireLogin(): void {
    startSecureSession();
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
    checkAccountStatus();
}

function requireGuest(): void {
    startSecureSession();
    if (!empty($_SESSION['user_id'])) {
        header('Location: ' . APP_URL . '/home.php');
        exit;
    }
}

function currentUser(): ?array {
    startSecureSession();
    if (empty($_SESSION['user_id'])) return null;
    return [
        'id'          => $_SESSION['user_id'],
        'name'        => $_SESSION['user_name'],
        'first_name'  => $_SESSION['user_first_name'] ?? '',
        'last_name'   => $_SESSION['user_last_name'] ?? '',
        'email'       => $_SESSION['user_email'],
        'initials'    => $_SESSION['user_initials'],
        'is_verified' => $_SESSION['user_is_verified'] ?? 0,
    ];
}

function setUserSession(array $user): void {
    startSecureSession();
    session_regenerate_id(true);
    $_SESSION['user_id']         = $user['id'];
    $_SESSION['user_name']       = $user['name'];
    $_SESSION['user_first_name'] = $user['first_name'] ?? '';
    $_SESSION['user_last_name']  = $user['last_name'] ?? '';
    $_SESSION['user_email']      = $user['email'];
    $_SESSION['user_initials']   = strtoupper(
        substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)
    );
    $_SESSION['user_is_verified'] = $user['is_verified'] ?? 0;
}

function destroySession(): void {
    startSecureSession();
    $_SESSION = [];
    session_destroy();
}
