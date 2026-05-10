<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';
$userId = requireAuth();
$pdo = getDB();

$s = $pdo->prepare("SELECT * FROM analysis_log WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
$s->execute([$userId]);
$logs = $s->fetchAll();

foreach ($logs as &$l) {
    $l['confidence']=(int)$l['confidence'];
    $l['rsi']=(float)$l['rsi'];
    $l['support']=(float)$l['support'];
    $l['resistance']=(float)$l['resistance'];
}

jsonResponse(['status'=>'success','data'=>$logs]);
