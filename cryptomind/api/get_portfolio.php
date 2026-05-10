<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';
$userId = requireAuth();
$pdo = getDB();

$s = $pdo->prepare("SELECT p.*, c.price as current_price, c.price_change FROM portfolio p LEFT JOIN coins c ON c.id=p.coin_id WHERE p.user_id=? ORDER BY (p.amount*COALESCE(c.price,p.avg_buy_price)) DESC");
$s->execute([$userId]);
$holdings = $s->fetchAll();

$totalVal = 0; $totalCost = 0;
foreach ($holdings as &$h) {
    $h['amount'] = (float)$h['amount'];
    $h['avg_buy_price'] = (float)$h['avg_buy_price'];
    $h['current_price'] = (float)($h['current_price']?:$h['avg_buy_price']);
    $h['price_change'] = (float)($h['price_change']??0);
    $h['value'] = round($h['amount']*$h['current_price'],2);
    $h['cost'] = round($h['amount']*$h['avg_buy_price'],2);
    $h['pnl'] = round($h['value']-$h['cost'],2);
    $h['pnl_pct'] = $h['cost']>0 ? round((($h['value']-$h['cost'])/$h['cost'])*100,2) : 0;
    $totalVal += $h['value'];
    $totalCost += $h['cost'];
}

jsonResponse(['status'=>'success','data'=>$holdings,'summary'=>[
    'total_value'=>round($totalVal,2),
    'total_cost'=>round($totalCost,2),
    'total_pnl'=>round($totalVal-$totalCost,2),
    'total_pnl_pct'=>$totalCost>0?round((($totalVal-$totalCost)/$totalCost)*100,2):0
]]);
