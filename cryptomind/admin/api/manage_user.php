<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../includes/auth.php';
requireAdmin();
$pdo = getDB();

$in = json_decode(file_get_contents('php://input'), true);
$action = $in['action'] ?? '';
$uid = $in['user_id'] ?? 0;

if (!$uid) jsonResponse(['status'=>'error', 'message'=>'Invalid user ID']);

if ($action === 'reset_balance') {
    $pdo->prepare("UPDATE users SET balance = 10000.00 WHERE id = ?")->execute([$uid]);
    // Optionally wipe portfolio
    $pdo->prepare("DELETE FROM portfolio WHERE user_id = ?")->execute([$uid]);
    jsonResponse(['status'=>'success', 'message'=>'Balance reset']);
} 
elseif ($action === 'delete_user') {
    // Check if trying to delete another admin
    $target = $pdo->prepare("SELECT role FROM users WHERE id=?");
    $target->execute([$uid]);
    $t = $target->fetch();
    if($t && $t['role'] === 'admin') {
        jsonResponse(['status'=>'error', 'message'=>'Cannot delete an admin']);
    }

    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
    jsonResponse(['status'=>'success', 'message'=>'User deleted']);
}

jsonResponse(['status'=>'error', 'message'=>'Invalid action']);
