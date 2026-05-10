<?php
// ─────────────────────────────────────────
//  GlobalBank — Admin Guard
// ─────────────────────────────────────────

require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

function requireAdmin(): void {
    requireLogin();
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $isAdmin = $stmt->fetchColumn();
    
    if (!$isAdmin) {
        header('Location: ' . APP_URL . '/home.php');
        exit;
    }
}
