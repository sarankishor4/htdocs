<?php
require_once __DIR__ . '/../core/includes/db.php';
require_once __DIR__ . '/../core/includes/auth_guard.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    $amount = (float)($_POST['amount'] ?? 0);
    if ($amount <= 0 || !in_array($action, ['stake', 'unstake'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
        exit;
    }

    $pdo = getDB();
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? AND currency = 'USD' FOR UPDATE");
        $stmt->execute([$userId]);
        $usdWallet = $stmt->fetch();
        $usdBal = $usdWallet ? $usdWallet['balance'] : 0;

        $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? AND currency = 'USD_STAKED' FOR UPDATE");
        $stmt->execute([$userId]);
        $stakedWallet = $stmt->fetch();
        $stakedBal = $stakedWallet ? $stakedWallet['balance'] : 0;

        if ($action === 'stake') {
            if ($usdBal < $amount) {
                throw new Exception("Insufficient USD balance.");
            }
            
            // Deduct USD
            $stmt = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ? AND currency = 'USD'");
            $stmt->execute([$amount, $userId]);
            
            // Add Staked USD
            if ($stakedWallet) {
                $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ? AND currency = 'USD_STAKED'");
                $stmt->execute([$amount, $userId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO wallets (user_id, currency, balance) VALUES (?, 'USD_STAKED', ?)");
                $stmt->execute([$userId, $amount]);
            }
            
            $desc = "Staked $$amount into High Yield Savings";

        } else { // unstake
            if ($stakedBal < $amount) {
                throw new Exception("Insufficient staked balance.");
            }
            
            // Deduct Staked USD
            $stmt = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ? AND currency = 'USD_STAKED'");
            $stmt->execute([$amount, $userId]);
            
            // Add USD
            $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ? AND currency = 'USD'");
            $stmt->execute([$amount, $userId]);

            $desc = "Unstaked $$amount from High Yield Savings";
        }

        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, description) VALUES (?, 'transfer', ?, 'USD', ?)");
        $stmt->execute([$userId, 0, $desc]); // amount 0 or keeping it out of total portfolio calculation depending on needs

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => $desc]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'status') {
    requireLogin();
    $pdo = getDB();
    
    $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? AND currency = 'USD'");
    $stmt->execute([$userId]);
    $usdWallet = $stmt->fetch();
    $usdBal = $usdWallet ? $usdWallet['balance'] : 0;

    $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? AND currency = 'USD_STAKED'");
    $stmt->execute([$userId]);
    $stakedWallet = $stmt->fetch();
    $stakedBal = $stakedWallet ? $stakedWallet['balance'] : 0;

    echo json_encode(['success' => true, 'usd_balance' => $usdBal, 'staked_balance' => $stakedBal, 'apy' => 8.5]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}
