<?php
require_once __DIR__ . '/../core/includes/auth_guard.php';
require_once __DIR__ . '/../core/includes/db.php';
requireLogin();
header('Content-Type: application/json');

$pdo = getDB();
$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM referrals WHERE referrer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} 
elseif ($action === 'invite' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM referrals WHERE referrer_id = ? AND referred_email = ?");
    $stmt->execute([$userId, $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'You already invited this email.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO referrals (referrer_id, referred_email) VALUES (?, ?)");
    $stmt->execute([$userId, $email]);
    
    // In a real app we would send an email here using mail() or a service
    
    echo json_encode(['success' => true]);
}
