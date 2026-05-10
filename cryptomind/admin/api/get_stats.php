<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../includes/auth.php';
requireAdmin();
$pdo = getDB();

$stats = [];
$stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['trades'] = $pdo->query("SELECT COUNT(*) FROM trades")->fetchColumn();
$stats['volume'] = $pdo->query("SELECT COALESCE(SUM(total_value), 0) FROM trades")->fetchColumn();
$stats['ai_queries'] = $pdo->query("SELECT COUNT(*) FROM analysis_history")->fetchColumn();

jsonResponse(['status'=>'success', 'data'=>$stats]);
