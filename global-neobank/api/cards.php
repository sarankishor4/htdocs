<?php
require_once __DIR__ . '/../core/includes/auth_guard.php';
require_once __DIR__ . '/../core/includes/db.php';
requireLogin();
header('Content-Type: application/json');

$pdo = getDB();
$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM virtual_cards WHERE user_id = ? AND status != 'terminated' ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} 
elseif ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? 'Virtual Card';
    $limit = (float)($_POST['limit'] ?? 1000);
    $lastFour = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $colors = ['blue', 'green', 'gold', 'purple', 'cyan', 'red'];
    $color = $colors[array_rand($colors)];
    $network = rand(0, 1) ? 'Visa' : 'Mastercard';

    $stmt = $pdo->prepare("INSERT INTO virtual_cards (user_id, card_name, last_four, network, monthly_limit, color) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $name, $lastFour, $network, $limit, $color]);
    echo json_encode(['success' => true]);
}
elseif ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? 'active';
    if(in_array($status, ['active', 'frozen'])) {
        $stmt = $pdo->prepare("UPDATE virtual_cards SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$status, $id, $userId]);
    }
    echo json_encode(['success' => true]);
}
elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $stmt = $pdo->prepare("UPDATE virtual_cards SET status = 'terminated' WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    echo json_encode(['success' => true]);
}
