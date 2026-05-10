<?php
require_once __DIR__ . '/../core/includes/db.php';
require_once __DIR__ . '/../core/includes/auth_guard.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'pairs';
$userId = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'pairs') {
    // Demo pairs
    $pairs = [
        ['symbol' => 'BTC', 'name' => 'Bitcoin', 'price' => 67240.50, 'change' => 2.34],
        ['symbol' => 'ETH', 'name' => 'Ethereum', 'price' => 3520.00, 'change' => 1.82],
        ['symbol' => 'SOL', 'name' => 'Solana', 'price' => 178.40, 'change' => -0.95],
        ['symbol' => 'AAPL', 'name' => 'Apple', 'price' => 189.50, 'change' => 0.74],
        ['symbol' => 'NVDA', 'name' => 'NVIDIA', 'price' => 875.40, 'change' => 2.10],
    ];
    echo json_encode(['success' => true, 'data' => $pairs]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'balances') {
    requireLogin();
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT currency, balance FROM wallets WHERE user_id = ?");
    $stmt->execute([$userId]);
    $wallets = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $wallets]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'execute') {
    requireLogin();
    $asset = $_POST['asset'] ?? '';
    $type = $_POST['type'] ?? ''; // 'BUY' or 'SELL'
    $amount_usd = (float)($_POST['amount_usd'] ?? 0);

    if ($amount_usd <= 0 || !in_array($type, ['BUY', 'SELL'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
        exit;
    }

    // Determine mock price for the requested asset
    $pairs = [
        'BTC' => 67240.50,
        'ETH' => 3520.00,
        'SOL' => 178.40,
        'AAPL' => 189.50,
        'NVDA' => 875.40
    ];

    if (!isset($pairs[$asset])) {
        echo json_encode(['success' => false, 'error' => 'Asset not supported.']);
        exit;
    }

    $price = $pairs[$asset];
    $asset_qty = $amount_usd / $price;

    $pdo = getDB();
    try {
        $pdo->beginTransaction();

        // Get USD balance
        $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? AND currency = 'USD' FOR UPDATE");
        $stmt->execute([$userId]);
        $usdWallet = $stmt->fetch();
        $usdBal = $usdWallet ? $usdWallet['balance'] : 0;

        // Get Asset balance
        $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? AND currency = ? FOR UPDATE");
        $stmt->execute([$userId, $asset]);
        $assetWallet = $stmt->fetch();
        $assetBal = $assetWallet ? $assetWallet['balance'] : 0;

        if ($type === 'BUY') {
            if ($usdBal < $amount_usd) {
                throw new Exception("Insufficient USD balance.");
            }
            
            // Deduct USD
            $stmt = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ? AND currency = 'USD'");
            $stmt->execute([$amount_usd, $userId]);
            
            // Add Asset
            if ($assetWallet) {
                $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ? AND currency = ?");
                $stmt->execute([$asset_qty, $userId, $asset]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO wallets (user_id, currency, balance) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $asset, $asset_qty]);
            }
        } else { // SELL
            // For a SELL, amount_usd means "I want to sell $X worth of Asset"
            // So we need to check if they have $asset_qty
            if ($assetBal < $asset_qty) {
                throw new Exception("Insufficient $asset balance.");
            }
            
            // Add USD
            $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ? AND currency = 'USD'");
            $stmt->execute([$amount_usd, $userId]);
            
            // Deduct Asset
            $stmt = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ? AND currency = ?");
            $stmt->execute([$asset_qty, $userId, $asset]);
        }

        // Record transaction
        $desc = ($type === 'BUY') ? "Bought $asset_qty $asset for $$amount_usd" : "Sold $asset_qty $asset for $$amount_usd";
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, currency, description) VALUES (?, 'trade', ?, 'USD', ?)");
        $stmt->execute([$userId, ($type === 'BUY' ? -$amount_usd : $amount_usd), $desc]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => $desc]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'chart') {
    $asset = $_GET['asset'] ?? 'BTC';
    $pairs = [
        'BTC' => 67240.50,
        'ETH' => 3520.00,
        'SOL' => 178.40,
        'AAPL' => 189.50,
        'NVDA' => 875.40
    ];
    if (!isset($pairs[$asset])) {
        echo json_encode(['success' => false, 'error' => 'Asset not supported.']);
        exit;
    }
    $currentPrice = $pairs[$asset];
    
    // Generate 100 days of mock data
    $data = [];
    $time = time() - (100 * 86400); // 100 days ago
    $price = $currentPrice * 0.8; // Start ~20% lower
    
    // Seed random number generator based on asset to keep it consistent
    mt_srand(crc32($asset));
    
    for ($i = 0; $i < 100; $i++) {
        $volatility = $price * 0.05; // 5% daily volatility
        $open = $price;
        $close = $open + (mt_rand(-100, 100) / 100) * $volatility;
        $high = max($open, $close) + (mt_rand(0, 100) / 100) * ($volatility / 2);
        $low = min($open, $close) - (mt_rand(0, 100) / 100) * ($volatility / 2);
        
        // Force the last day to match the exact current price
        if ($i === 99) {
            $close = $currentPrice;
            $high = max($open, $close) * 1.01;
            $low = min($open, $close) * 0.99;
        }
        
        $data[] = [
            'time' => date('Y-m-d', $time),
            'open' => round($open, 2),
            'high' => round($high, 2),
            'low' => round($low, 2),
            'close' => round($close, 2)
        ];
        
        $price = $close;
        $time += 86400;
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}
