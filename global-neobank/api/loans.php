<?php
require_once __DIR__ . '/../core/includes/db.php';
require_once __DIR__ . '/../core/includes/auth_guard.php';

requireLogin();
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$pdo = getDB();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM loans WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $loans = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $loans]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'apply') {
    $amount = (float)($_POST['amount'] ?? 0);
    
    // Check AI score
    $stmt = $pdo->prepare("SELECT ai_credit_score FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $score = $stmt->fetchColumn() ?: 0;
    
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid amount.']);
        exit;
    }
    
    // Basic AI score check
    if ($score < 300) {
        echo json_encode(['success' => false, 'error' => 'AI Credit Score too low for a loan. Complete skills assessment.']);
        exit;
    }
    
    $maxLoan = $score * 5; // e.g. score 800 = max $4000
    if ($amount > $maxLoan) {
        echo json_encode(['success' => false, 'error' => 'Requested amount exceeds your limit of $' . $maxLoan]);
        exit;
    }

    try {
        $pdo->beginTransaction();
        
        $interest = ($score > 700) ? 5.0 : 10.0;
        
        $stmt = $pdo->prepare("INSERT INTO loans (user_id, amount, interest_rate, status) VALUES (?, ?, ?, 'active')");
        $stmt->execute([$userId, $amount, $interest]);
        
        $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ? AND currency = 'USD'");
        $stmt->execute([$amount, $userId]);
        
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, description) VALUES (?, 'loan_disbursement', ?, 'USD', ?)");
        $stmt->execute([$userId, $amount, 'Loan Disbursed']);
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Loan application failed.']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'repay') {
    $loanId = (int)($_POST['loan_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);

    if ($loanId <= 0 || $amount <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
        exit;
    }

    // Get the loan
    $stmt = $pdo->prepare("SELECT * FROM loans WHERE id = ? AND user_id = ?");
    $stmt->execute([$loanId, $userId]);
    $loan = $stmt->fetch();

    if (!$loan || !in_array($loan['status'], ['active', 'pending'])) {
        echo json_encode(['success' => false, 'error' => 'Loan not found or not active.']);
        exit;
    }

    $remaining = $loan['amount'] - $loan['repaid_amount'];
    if ($amount > $remaining) {
        $amount = $remaining; // Cap at remaining
    }

    // Check USD balance
    $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? AND currency = 'USD'");
    $stmt->execute([$userId]);
    $usdBal = $stmt->fetchColumn() ?: 0;

    if ($usdBal < $amount) {
        echo json_encode(['success' => false, 'error' => 'Insufficient USD balance.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Deduct USD
        $stmt = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ? AND currency = 'USD'");
        $stmt->execute([$amount, $userId]);

        // Add to repaid
        $stmt = $pdo->prepare("UPDATE loans SET repaid_amount = repaid_amount + ? WHERE id = ?");
        $stmt->execute([$amount, $loanId]);

        // Check if fully repaid
        $newRepaid = $loan['repaid_amount'] + $amount;
        if ($newRepaid >= $loan['amount']) {
            $stmt = $pdo->prepare("UPDATE loans SET status = 'paid' WHERE id = ?");
            $stmt->execute([$loanId]);
        }

        // Record transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, description) VALUES (?, 'loan_repayment', ?, 'USD', ?)");
        $stmt->execute([$userId, -$amount, "Repaid \$$amount on Loan #$loanId"]);

        $pdo->commit();
        $msg = ($newRepaid >= $loan['amount']) ? "Loan #$loanId fully repaid! 🎉" : "Repaid \$$amount on Loan #$loanId.";
        echo json_encode(['success' => true, 'message' => $msg]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Repayment failed.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
}
