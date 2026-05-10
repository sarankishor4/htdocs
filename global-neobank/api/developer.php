<?php
require_once __DIR__ . '/../core/includes/auth_guard.php';
require_once __DIR__ . '/../core/includes/db.php';
requireLogin();
header('Content-Type: application/json');

$pdo = getDB();
$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

function getUserTier($pdo, $uid) {
    $stmt = $pdo->prepare("SELECT subscription_tier FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    return $stmt->fetchColumn() ?: 'standard';
}

if ($action === 'get_tier') {
    echo json_encode(['success' => true, 'tier' => getUserTier($pdo, $userId)]);
}
elseif ($action === 'set_tier' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $tier = $_POST['tier'] ?? 'standard';
    if(in_array($tier, ['standard', 'premium', 'metal'])) {
        $stmt = $pdo->prepare("UPDATE users SET subscription_tier = ? WHERE id = ?");
        $stmt->execute([$tier, $userId]);
    }
    echo json_encode(['success' => true]);
}
elseif ($action === 'list_keys') {
    if(getUserTier($pdo, $userId) !== 'metal') {
        echo json_encode(['success' => false, 'error' => 'tier_required']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id, key_name, api_key, created_at FROM api_keys WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
}
elseif ($action === 'create_key' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if(getUserTier($pdo, $userId) !== 'metal') {
        echo json_encode(['success' => false, 'error' => 'Tier required']);
        exit;
    }
    $name = $_POST['name'] ?? 'API Key';
    $key = 'gb_live_' . bin2hex(random_bytes(24));
    
    $stmt = $pdo->prepare("INSERT INTO api_keys (user_id, key_name, api_key) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $name, $key]);
    echo json_encode(['success' => true]);
}
elseif ($action === 'revoke_key' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $stmt = $pdo->prepare("UPDATE api_keys SET status = 'revoked' WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    echo json_encode(['success' => true]);
}
