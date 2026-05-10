<?php
require_once __DIR__ . '/../core/includes/auth_guard.php';
require_once __DIR__ . '/../core/includes/db.php';
requireLogin();
header('Content-Type: application/json');

$pdo = getDB();
$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

$assetPrices = ['USD'=>1,'USD_STAKED'=>1,'BTC'=>67000,'ETH'=>3500,'SOL'=>178.40,'AAPL'=>189.50,'NVDA'=>875.40];

if ($action === 'summary') {
    $stmt = $pdo->prepare("SELECT
        COALESCE(SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END), 0) AS inflow,
        COALESCE(SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END), 0) AS outflow,
        COUNT(*) AS transaction_count
        FROM transactions WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute([$userId]);
    $cf = $stmt->fetch();

    $inflow = (float)$cf['inflow'];
    $outflow = (float)$cf['outflow'];
    $net = $inflow - $outflow;
    $savingsRate = $inflow > 0 ? round(($net / $inflow) * 100, 1) : 0;

    // Daily breakdown
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(created_at,'%d %b') AS day,
        SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) AS inflow,
        SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) AS outflow
        FROM transactions WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC");
    $stmt->execute([$userId]);
    $daily = $stmt->fetchAll();

    // Category breakdown (use description field as proxy)
    $stmt = $pdo->prepare("SELECT
        CASE
          WHEN description LIKE '%loan%' THEN 'Loans'
          WHEN description LIKE '%transfer%' OR description LIKE '%send%' THEN 'Transfers'
          WHEN description LIKE '%trade%' OR description LIKE '%buy%' OR description LIKE '%sell%' THEN 'Trading'
          WHEN description LIKE '%earn%' OR description LIKE '%reward%' THEN 'Rewards'
          ELSE 'Other'
        END AS category,
        SUM(ABS(amount)) AS total
        FROM transactions WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND amount < 0
        GROUP BY category ORDER BY total DESC LIMIT 6");
    $stmt->execute([$userId]);
    $categories = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'inflow' => $inflow,
        'outflow' => $outflow,
        'net_flow' => $net,
        'savings_rate' => $savingsRate,
        'transaction_count' => (int)$cf['transaction_count'],
        'date_range' => date('d M', strtotime('-30 days')) . ' – ' . date('d M Y'),
        'daily' => $daily,
        'categories' => $categories
    ]);

} elseif ($action === 'portfolio') {
    $stmt = $pdo->prepare("SELECT currency, balance FROM wallets WHERE user_id = ?");
    $stmt->execute([$userId]);
    $wallets = $stmt->fetchAll();
    $result = [];
    foreach ($wallets as $w) {
        $usdVal = (float)$w['balance'] * ($assetPrices[$w['currency']] ?? 1);
        if ($usdVal > 0) {
            $result[] = ['currency' => $w['currency'], 'balance' => (float)$w['balance'], 'usd_value' => $usdVal];
        }
    }
    echo json_encode(['success' => true, 'data' => $result]);
}
