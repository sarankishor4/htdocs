<?php
require_once __DIR__ . '/../core/includes/db.php';
require_once __DIR__ . '/../core/includes/auth_guard.php';

requireLogin();
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$pdo = getDB();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    $transactions = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $transactions]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'send') {
    $amount = (float)($_POST['amount'] ?? 0);
    $currency = 'USD'; // Simplified

    if ($amount <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid amount.']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? AND currency = ? FOR UPDATE");
        $stmt->execute([$userId, $currency]);
        $balance = $stmt->fetchColumn();

        if ($balance < $amount) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Insufficient funds.']);
            exit;
        }

        // Deduct balance
        $stmt = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ? AND currency = ?");
        $stmt->execute([$amount, $userId, $currency]);

        // Record transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, description) VALUES (?, 'transfer', ?, ?, ?)");
        $stmt->execute([$userId, -$amount, $currency, 'Send Money']);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Transaction failed.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
}
