<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';
requireAuth();
$pdo = getDB();

// Calculate total portfolio value for all users
// This is an expensive query in real life, but fine for our sandbox
$q = "
SELECT 
    u.id, 
    u.username, 
    u.avatar_color,
    u.balance,
    COALESCE((SELECT SUM(p.amount * c.price) FROM portfolio p JOIN coins c ON p.coin_id = c.id WHERE p.user_id = u.id), 0) as portfolio_value,
    (SELECT COUNT(*) FROM trades WHERE user_id = u.id) as total_trades,
    (SELECT COALESCE(SUM(profit_loss), 0) FROM trades WHERE user_id = u.id AND profit_loss IS NOT NULL) as total_pnl
FROM users u
ORDER BY (balance + COALESCE((SELECT SUM(p.amount * c.price) FROM portfolio p JOIN coins c ON p.coin_id = c.id WHERE p.user_id = u.id), 0)) DESC
LIMIT 50
";

$stmt = $pdo->query($q);
$leaders = $stmt->fetchAll();

foreach ($leaders as &$l) {
    $total_assets = $l['balance'] + $l['portfolio_value'];
    // Assuming starting capital is always $10,000 for calculation
    $roi = (($total_assets - 10000) / 10000) * 100;
    
    $l['total_assets'] = round($total_assets, 2);
    $l['roi'] = round($roi, 2);
    $l['total_pnl'] = round($l['total_pnl'], 2);
    unset($l['balance']);
    unset($l['portfolio_value']);
}

jsonResponse(['status'=>'success', 'data'=>$leaders]);
