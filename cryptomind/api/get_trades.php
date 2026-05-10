<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';
$userId = requireAuth();
$pdo = getDB();

$s = $pdo->prepare("SELECT * FROM trades WHERE user_id=? ORDER BY trade_date DESC LIMIT 50");
$s->execute([$userId]);
$trades = $s->fetchAll();

foreach ($trades as &$t) {
    $t['amount']=(float)$t['amount'];
    $t['price']=(float)$t['price'];
    $t['total_value']=(float)$t['total_value'];
    $t['profit_loss']=$t['profit_loss']!==null?(float)$t['profit_loss']:null;
    $t['signal_confidence']=$t['signal_confidence']!==null?(int)$t['signal_confidence']:null;
}

$totalPnl=0; $wins=0; $closed=0;
foreach ($trades as $t) {
    if ($t['profit_loss']!==null) { $totalPnl+=$t['profit_loss']; $closed++; if ($t['profit_loss']>0) $wins++; }
}

jsonResponse(['status'=>'success','data'=>$trades,'stats'=>[
    'total'=>count($trades),
    'win_rate'=>$closed>0?round(($wins/$closed)*100):0,
    'total_pnl'=>round($totalPnl,2),
    'wins'=>$wins,
    'losses'=>$closed-$wins
]]);
