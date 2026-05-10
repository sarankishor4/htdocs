<?php
require_once __DIR__ . '/../core/includes/db.php';
require_once __DIR__ . '/../core/includes/auth_guard.php';

requireLogin();
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$pdo = getDB();
$action = $_GET['action'] ?? '';

// ── SEND TO USER BY EMAIL ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'send') {
    $email = trim($_POST['email'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $note = trim($_POST['note'] ?? 'P2P Transfer');

    if (empty($email) || $amount <= 0) {
        echo json_encode(['success' => false, 'error' => 'Email and valid amount required.']);
        exit;
    }

    // Find recipient
    $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    $recipient = $stmt->fetch();

    if (!$recipient) {
        echo json_encode(['success' => false, 'error' => 'Recipient not found.']);
        exit;
    }

    // Check sender balance
    $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? AND currency = 'USD'");
    $stmt->execute([$userId]);
    $senderBal = $stmt->fetchColumn() ?: 0;

    if ($senderBal < $amount) {
        echo json_encode(['success' => false, 'error' => 'Insufficient balance.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Deduct from sender
        $stmt = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ? AND currency = 'USD'");
        $stmt->execute([$amount, $userId]);

        // Add to recipient (create wallet if needed)
        $stmt = $pdo->prepare("SELECT id FROM wallets WHERE user_id = ? AND currency = 'USD'");
        $stmt->execute([$recipient['id']]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ? AND currency = 'USD'");
            $stmt->execute([$amount, $recipient['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO wallets (user_id, currency, balance) VALUES (?, 'USD', ?)");
            $stmt->execute([$recipient['id'], $amount]);
        }

        // Sender transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, description) VALUES (?, 'transfer', ?, 'USD', ?)");
        $stmt->execute([$userId, -$amount, "Sent \$$amount to {$recipient['first_name']} {$recipient['last_name']}"]);

        // Recipient transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, description) VALUES (?, 'transfer', ?, 'USD', ?)");
        $senderName = $_SESSION['user_name'];
        $stmt->execute([$recipient['id'], $amount, "Received \$$amount from $senderName"]);

        // Create notification for recipient
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
        $stmt->execute([$recipient['id'], 'Money Received', "You received \$$amount from $senderName. Note: $note"]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "Sent \$$amount to {$recipient['first_name']} {$recipient['last_name']}"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Transfer failed.']);
    }

// ── DEPOSIT ──
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'deposit') {
    $amount = (float)($_POST['amount'] ?? 0);
    if ($amount <= 0 || $amount > 50000) {
        echo json_encode(['success' => false, 'error' => 'Amount must be between $1 and $50,000.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ? AND currency = 'USD'");
        $stmt->execute([$amount, $userId]);

        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, description) VALUES (?, 'deposit', ?, 'USD', ?)");
        $stmt->execute([$userId, $amount, "Deposited \$$amount via Bank Transfer"]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "Deposited \$$amount successfully."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Deposit failed.']);
    }

// ── WITHDRAW ──
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'withdraw') {
    $amount = (float)($_POST['amount'] ?? 0);
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid amount.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? AND currency = 'USD'");
    $stmt->execute([$userId]);
    $bal = $stmt->fetchColumn() ?: 0;

    if ($bal < $amount) {
        echo json_encode(['success' => false, 'error' => 'Insufficient balance.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ? AND currency = 'USD'");
        $stmt->execute([$amount, $userId]);

        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, description) VALUES (?, 'withdrawal', ?, 'USD', ?)");
        $stmt->execute([$userId, -$amount, "Withdrew \$$amount to Bank Account"]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => "Withdrew \$$amount successfully."]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Withdrawal failed.']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
}
