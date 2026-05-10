<?php
require_once __DIR__ . '/../../core/includes/admin_guard.php';

requireAdmin();
header('Content-Type: application/json');

$pdo = getDB();
$action = $_GET['action'] ?? '';
$adminId = $_SESSION['user_id'];

function logAdminAction($pdo, $adminId, $targetUserId, $action, $description) {
    $stmt = $pdo->prepare("INSERT INTO admin_audit_logs (admin_id, target_user_id, action, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$adminId, $targetUserId, $action, $description]);
}

function writeAuditLog(PDO $pdo, string $action, string $subjectType, ?int $subjectId, array $metadata = []): void {
    try {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (actor_user_id, action, subject_type, subject_id, metadata) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            $action,
            $subjectType,
            $subjectId,
            json_encode($metadata)
        ]);
    } catch (Exception $e) {
        return;
    }
}

// ── OVERVIEW STATS ──
if ($action === 'stats') {
    $stats = [];
    
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['verified_users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 1")->fetchColumn();
    $stats['total_deposits'] = $pdo->query("SELECT COALESCE(SUM(balance), 0) FROM wallets WHERE currency = 'USD'")->fetchColumn();
    $stats['total_staked'] = $pdo->query("SELECT COALESCE(SUM(balance), 0) FROM wallets WHERE currency = 'USD_STAKED'")->fetchColumn();
    $stats['active_loans'] = $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'active'")->fetchColumn();
    $stats['total_loan_amount'] = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM loans WHERE status = 'active'")->fetchColumn();
    $stats['total_repaid'] = $pdo->query("SELECT COALESCE(SUM(repaid_amount), 0) FROM loans")->fetchColumn();
    $stats['total_transactions'] = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
    
    echo json_encode(['success' => true, 'data' => $stats]);

// ── ALL USERS ──
} elseif ($action === 'users') {
    $stmt = $pdo->query("SELECT id, first_name, last_name, email, is_verified, is_admin, kyc_status, ai_credit_score, account_status, phone_number, created_at FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();
    
    // Get wallet balances for each user
    foreach ($users as &$u) {
        $stmt2 = $pdo->prepare("SELECT currency, balance FROM wallets WHERE user_id = ?");
        $stmt2->execute([$u['id']]);
        $u['wallets'] = $stmt2->fetchAll();
    }
    
    echo json_encode(['success' => true, 'data' => $users]);

// ── ALL LOANS ──
} elseif ($action === 'loans') {
    $stmt = $pdo->query("SELECT l.*, u.first_name, u.last_name, u.email FROM loans l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC");
    $loans = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $loans]);

// ── ALL TRANSACTIONS ──
} elseif ($action === 'transactions') {
    $stmt = $pdo->query("SELECT t.*, u.first_name, u.last_name, u.email FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 100");
    $txns = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $txns]);

// ── UPDATE USER ──
} elseif ($action === 'update_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)($_POST['user_id'] ?? 0);
    $field = $_POST['field'] ?? '';
    $value = $_POST['value'] ?? '';
    
    $allowed = ['kyc_status', 'is_verified', 'is_admin', 'ai_credit_score', 'account_status'];
    if (!in_array($field, $allowed) || $uid <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid field or user.']);
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE users SET $field = ? WHERE id = ?");
    if ($stmt->execute([$value, $uid])) {
        logAdminAction($pdo, $adminId, $uid, 'UPDATE_USER', "Updated $field to $value");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed.']);
    }

// ── UPDATE LOAN STATUS ──
} elseif ($action === 'update_loan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanId = (int)($_POST['loan_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    
    $allowed = ['pending', 'active', 'paid', 'defaulted'];
    if (!in_array($status, $allowed) || $loanId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid status or loan.']);
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE loans SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $loanId])) {
        logAdminAction($pdo, $adminId, null, 'UPDATE_LOAN', "Updated loan $loanId to $status");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed.']);
    }

// ── DELETE USER ──
} elseif ($action === 'delete_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)($_POST['user_id'] ?? 0);
    if ($uid <= 0 || $uid == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'Cannot delete self or invalid user.']);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$uid])) {
        logAdminAction($pdo, $adminId, $uid, 'DELETE_USER', "Deleted user ID $uid");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Delete failed.']);
    }

// ── IMPERSONATE USER ──
} elseif ($action === 'impersonate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)($_POST['user_id'] ?? 0);
    if ($uid <= 0) { echo json_encode(['success'=>false, 'error'=>'Invalid user']); exit; }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();
    if (!$user) { echo json_encode(['success'=>false, 'error'=>'User not found']); exit; }
    
    logAdminAction($pdo, $adminId, $uid, 'IMPERSONATE', "Impersonated {$user['email']}");
    setUserSession([
        'id' => $user['id'],
        'name' => trim($user['first_name'] . ' ' . $user['last_name']),
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'email' => $user['email'],
        'is_verified' => $user['is_verified']
    ]);
    echo json_encode(['success' => true]);

// ── MANAGE FUNDS ──
} elseif ($action === 'manage_funds' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)($_POST['user_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $currency = strtoupper(trim($_POST['currency'] ?? 'USD'));
    $type = $_POST['type'] ?? 'add';
    
    if ($amount <= 0 || $uid <= 0 || !in_array($type, ['add', 'deduct'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT id, balance FROM wallets WHERE user_id = ? AND currency = ? FOR UPDATE");
        $stmt->execute([$uid, $currency]);
        $wallet = $stmt->fetch();
        
        if ($type === 'deduct') {
            if (!$wallet || $wallet['balance'] < $amount) throw new Exception("Insufficient balance.");
            $stmt = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$amount, $wallet['id']]);
        } else {
            if ($wallet) {
                $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$amount, $wallet['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO wallets (user_id, currency, balance) VALUES (?, ?, ?)");
                $stmt->execute([$uid, $currency, $amount]);
            }
        }
        
        $desc = "Admin " . ($type === 'add' ? "credited" : "deducted") . " funds manually.";
        $tAmount = $type === 'add' ? $amount : -$amount;
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, description) VALUES (?, 'transfer', ?, ?, ?)");
        $stmt->execute([$uid, $tAmount, $currency, $desc]);
        
        logAdminAction($pdo, $adminId, $uid, 'MANAGE_FUNDS', "Admin $type $amount $currency");
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

// ── AUDIT LOGS ──
} elseif ($action === 'audit_logs') {
    $stmt = $pdo->query("SELECT a.*, admin.email as admin_email, target.email as target_email FROM admin_audit_logs a LEFT JOIN users admin ON a.admin_id = admin.id LEFT JOIN users target ON a.target_user_id = target.id ORDER BY a.created_at DESC LIMIT 200");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

// ── JOBS MANAGEMENT ──
} elseif ($action === 'jobs') {
    $stmt = $pdo->query("SELECT * FROM jobs ORDER BY id DESC");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

} elseif ($action === 'job_add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $reward = (float)($_POST['reward'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    
    if (!$title || !$category || $reward <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO jobs (title, category, reward, description) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$title, $category, $reward, $desc])) {
        logAdminAction($pdo, $adminId, null, 'ADD_JOB', "Added job: $title");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add job.']);
    }

} elseif ($action === 'job_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = (int)($_POST['job_id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
    if ($stmt->execute([$jobId])) {
        logAdminAction($pdo, $adminId, null, 'DELETE_JOB', "Deleted job ID: $jobId");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete job.']);
    }

// ── PLATFORM VOLUME (CHART DATA) ──
} elseif ($action === 'platform_volume') {
    // Generate mock volume data for the chart (last 7 days)
    $dates = [];
    $deposits = [];
    $loans = [];
    for ($i = 6; $i >= 0; $i--) {
        $dates[] = date('M d', strtotime("-$i days"));
        // Since we don't have historical snapshot data in DB, mock it realistically
        $deposits[] = rand(10000, 50000);
        $loans[] = rand(5000, 20000);
    }
    echo json_encode(['success' => true, 'data' => [
        'labels' => $dates,
        'datasets' => [
            ['label' => 'Deposits ($)', 'data' => $deposits, 'borderColor' => '#00bfff', 'tension' => 0.4],
            ['label' => 'Loans ($)', 'data' => $loans, 'borderColor' => '#ff4560', 'tension' => 0.4]
        ]
    ]]);

// ── KYC QUEUE ──
} elseif ($action === 'kyc_queue') {
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, kyc_status, updated_at AS kyc_submitted_at FROM users WHERE kyc_status = 'pending' ORDER BY updated_at DESC");
    $stmt->execute();
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);

// ── FRAUD ALERTS ──
} elseif ($action === 'fraud_alerts') {
    $openStmt = $pdo->prepare("SELECT COUNT(*) FROM fraud_alerts WHERE status = 'open'");
    $openStmt->execute();
    $resolvedStmt = $pdo->prepare("SELECT COUNT(*) FROM fraud_alerts WHERE status = 'resolved' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $resolvedStmt->execute();

    $stmt = $pdo->prepare("SELECT fa.*, u.email FROM fraud_alerts fa LEFT JOIN users u ON fa.user_id = u.id ORDER BY fa.created_at DESC LIMIT 50");
    $stmt->execute();
    echo json_encode([
        'success' => true,
        'open' => (int)$openStmt->fetchColumn(),
        'resolved' => (int)$resolvedStmt->fetchColumn(),
        'data' => $stmt->fetchAll()
    ]);

} elseif ($action === 'resolve_fraud' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $alertId = (int)($_POST['alert_id'] ?? 0);
    $status  = $_POST['status'] ?? 'resolved';
    if (!in_array($status, ['resolved', 'false_positive'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid status.']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE fraud_alerts SET status = ? WHERE id = ?");
    $stmt->execute([$status, $alertId]);
    echo json_encode(['success' => true]);

// ── SYSTEM SETTINGS ──
} elseif ($action === 'system_settings') {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM system_settings");
    $stmt->execute();
    $settings = [];
    foreach ($stmt->fetchAll() as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    echo json_encode(['success' => true, 'data' => $settings]);

} elseif ($action === 'update_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed = ['maintenance_mode','withdrawal_fee_pct','transfer_fee_pct','crypto_trade_fee_pct','max_loan_multiplier','referral_bonus'];
    $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($allowed as $key) {
        if (isset($_POST[$key])) {
            $stmt->execute([$key, $_POST[$key]]);
        }
    }
    echo json_encode(['success' => true]);

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
}
