<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';
$userId = requireAuth();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD']!=='POST') jsonResponse(['status'=>'error','message'=>'POST only'],405);

$in = json_decode(file_get_contents('php://input'), true);
$coinId = $in['coin_id']??'';

if (!$coinId) jsonResponse(['status'=>'error','message'=>'No coin specified']);

// Toggle
$ex = $pdo->prepare("SELECT id FROM watchlist WHERE user_id=? AND coin_id=?"); $ex->execute([$userId,$coinId]);
if ($ex->fetch()) {
    $pdo->prepare("DELETE FROM watchlist WHERE user_id=? AND coin_id=?")->execute([$userId,$coinId]);
    jsonResponse(['status'=>'success','watched'=>false,'message'=>'Removed from watchlist']);
} else {
    $pdo->prepare("INSERT INTO watchlist (user_id,coin_id) VALUES (?,?)")->execute([$userId,$coinId]);
    jsonResponse(['status'=>'success','watched'=>true,'message'=>'Added to watchlist']);
}
