<?php
require_once __DIR__ . '/../core/includes/auth_guard.php';
require_once __DIR__ . '/../core/includes/db.php';
requireLogin();
header('Content-Type: application/json');

$pdo = getDB();
$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM savings_goals WHERE user_id = ? AND status != 'completed' ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} 
elseif ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? 'Goal';
    $target = (float)($_POST['target'] ?? 1000);
    $emoji = $_POST['emoji'] ?? '💰';

    $stmt = $pdo->prepare("INSERT INTO savings_goals (user_id, name, target_amount, emoji) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $name, $target, $emoji]);
    echo json_encode(['success' => true]);
}
elseif ($action === 'fund' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $amount = (float)($_POST['amount'] ?? 0);
    
    // In a real app we would deduct from main balance here
    $stmt = $pdo->prepare("UPDATE savings_goals SET current_amount = current_amount + ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$amount, $id, $userId]);
    echo json_encode(['success' => true]);
}
elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    // In a real app we would refund current_amount to main balance here
    $stmt = $pdo->prepare("UPDATE savings_goals SET status = 'completed' WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    echo json_encode(['success' => true]);
}
