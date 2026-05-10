<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../includes/auth.php';
requireAdmin();
$pdo = getDB();

$users = $pdo->query("
    SELECT id, username, email, role, balance, created_at,
    (SELECT COUNT(*) FROM trades WHERE user_id = users.id) as trade_count
    FROM users 
    ORDER BY created_at DESC
")->fetchAll();

jsonResponse(['status'=>'success', 'data'=>$users]);
