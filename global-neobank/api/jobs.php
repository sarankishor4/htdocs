<?php
require_once __DIR__ . '/../core/includes/db.php';
require_once __DIR__ . '/../core/includes/auth_guard.php';

requireLogin();
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$pdo = getDB();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $stmt = $pdo->query("SELECT id, title, reward, time_estimate as time, category as skill, description FROM jobs WHERE status = 'active' ORDER BY id DESC");
    $jobs = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $jobs]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'complete') {
    $jobId = (int)($_POST['job_id'] ?? 0);
    $reward = (float)($_POST['reward'] ?? 0);
    
    if ($reward <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid reward.']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Find an active/defaulted loan to apply reward to
        $stmt = $pdo->prepare("SELECT id, amount, repaid_amount FROM loans WHERE user_id = ? AND status IN ('active', 'defaulted') ORDER BY id ASC LIMIT 1 FOR UPDATE");
        $stmt->execute([$userId]);
        $loan = $stmt->fetch();
        
        if ($loan) {
            $newRepaid = $loan['repaid_amount'] + $reward;
            $status = ($newRepaid >= $loan['amount']) ? 'paid' : $loan['status'];
            
            $stmt = $pdo->prepare("UPDATE loans SET repaid_amount = ?, status = ? WHERE id = ?");
            $stmt->execute([$newRepaid, $status, $loan['id']]);
            
            $desc = 'HC Task Completed - Applied to Loan #' . $loan['id'];
        } else {
            // No loan? Add to wallet directly
            $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ? AND currency = 'USD'");
            $stmt->execute([$reward, $userId]);
            $desc = 'HC Task Completed - Credited to Wallet';
        }
        
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, description) VALUES (?, 'loan_repayment', ?, 'USD', ?)");
        $stmt->execute([$userId, $reward, $desc]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => $desc]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Task completion failed.']);
    }
}
