<?php
require_once __DIR__ . '/../core/includes/auth_guard.php';
require_once __DIR__ . '/../core/includes/db.php';

requireLogin();
header('Content-Type: application/json');

$pdo = getDB();
$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'get_data') {
    try {
        $stmt = $pdo->prepare("SELECT kyc_status, two_factor_enabled FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        $stmtBanks = $pdo->prepare("SELECT bank_name, account_last_four, status FROM linked_banks WHERE user_id = ?");
        $stmtBanks->execute([$userId]);
        $banks = $stmtBanks->fetchAll();
        
        $stmtPrefs = $pdo->prepare("SELECT theme, email_alerts, push_notifications, trade_updates FROM user_preferences WHERE user_id = ?");
        $stmtPrefs->execute([$userId]);
        $prefs = $stmtPrefs->fetch();
        
        if (!$prefs) {
            $pdo->prepare("INSERT INTO user_preferences (user_id) VALUES (?)")->execute([$userId]);
            $prefs = ['theme' => 'dark', 'email_alerts' => 1, 'push_notifications' => 1, 'trade_updates' => 0];
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'kyc_status' => $user['kyc_status'],
                'two_factor_enabled' => (bool)$user['two_factor_enabled'],
                'banks' => $banks,
                'prefs' => $prefs
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to fetch data.']);
    }

} elseif ($action === 'submit_kyc' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE users SET kyc_status = 'pending' WHERE id = ?");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Submission failed.']);
    }

} elseif ($action === 'toggle_tfa' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $state = (int)($_POST['state'] ?? 0);
    try {
        $stmt = $pdo->prepare("UPDATE users SET two_factor_enabled = ? WHERE id = ?");
        $stmt->execute([$state, $userId]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Update failed.']);
    }

} elseif ($action === 'update_prefs' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pref = $_POST['pref'] ?? '';
    $state = $_POST['state'] ?? '';
    
    $allowed = ['theme', 'email_alerts', 'push_notifications', 'trade_updates'];
    if (!in_array($pref, $allowed)) {
        echo json_encode(['success' => false, 'error' => 'Invalid preference.']);
        exit;
    }
    
    try {
        if ($pref === 'theme') {
            $stmt = $pdo->prepare("UPDATE user_preferences SET theme = ? WHERE user_id = ?");
            $stmt->execute([$state == '1' ? 'dark' : 'light', $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE user_preferences SET $pref = ? WHERE user_id = ?");
            $stmt->execute([(int)$state, $userId]);
        }
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Update failed.']);
    }

} elseif ($action === 'link_bank' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $bankName = trim($_POST['bank_name'] ?? '');
    $lastFour = trim($_POST['last_four'] ?? '');
    
    if (empty($bankName) || strlen($lastFour) !== 4) {
        echo json_encode(['success' => false, 'error' => 'Invalid bank details.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO linked_banks (user_id, bank_name, account_last_four) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $bankName, $lastFour]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Linking failed.']);
    }

} elseif ($action === 'change_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $newPwd = $_POST['new_password'] ?? '';

    if (empty($current) || empty($newPwd)) {
        echo json_encode(['success' => false, 'error' => 'All fields required.']);
        exit;
    }

    if (strlen($newPwd) < 6) {
        echo json_encode(['success' => false, 'error' => 'New password must be at least 6 characters.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) {
        echo json_encode(['success' => false, 'error' => 'Current password is incorrect.']);
        exit;
    }

    $newHash = password_hash($newPwd, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    if ($stmt->execute([$newHash, $userId])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update password.']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
}
