<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';
$userId = requireAuth();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $s = $pdo->prepare("SELECT bot_active, bot_risk, bot_allocation FROM users WHERE id=?");
    $s->execute([$userId]);
    $u = $s->fetch();
    jsonResponse(['status'=>'success', 'data'=>[
        'active' => (bool)$u['bot_active'],
        'risk' => $u['bot_risk'],
        'allocation' => (float)$u['bot_allocation']
    ]]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true);
    $active = isset($in['active']) ? (int)$in['active'] : 0;
    $risk = in_array($in['risk']??'', ['low','medium','high']) ? $in['risk'] : 'medium';
    $alloc = isset($in['allocation']) ? (float)$in['allocation'] : 10.0;
    
    if ($alloc < 1 || $alloc > 100) jsonResponse(['status'=>'error', 'message'=>'Allocation must be between 1% and 100%']);
    
    $pdo->prepare("UPDATE users SET bot_active=?, bot_risk=?, bot_allocation=? WHERE id=?")->execute([$active, $risk, $alloc, $userId]);
    jsonResponse(['status'=>'success', 'message'=>'Bot settings saved']);
}
