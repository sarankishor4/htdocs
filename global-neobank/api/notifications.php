<?php
require_once __DIR__ . '/../core/includes/db.php';
require_once __DIR__ . '/../core/includes/auth_guard.php';

requireLogin();
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$pdo = getDB();
$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

} elseif ($action === 'count') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true, 'count' => $stmt->fetchColumn()]);

} elseif ($action === 'read_all' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true]);

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
}
