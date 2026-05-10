<?php
require_once __DIR__ . '/../core/includes/db.php';
require_once __DIR__ . '/../core/includes/auth_guard.php';

requireLogin();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get') {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, phone_number, address, kyc_status, ai_credit_score, is_verified FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    echo json_encode(['success' => true, 'data' => $user]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($firstName) || empty($lastName)) {
        echo json_encode(['success' => false, 'error' => 'First and Last name are required.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, address = ? WHERE id = ?");
    if ($stmt->execute([$firstName, $lastName, $phone, $address, $userId])) {
        // Update session
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['user_initials'] = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update profile.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action.']);
}
