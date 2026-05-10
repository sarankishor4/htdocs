<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

// Very simple proxy to bypass CORS if needed, or format for frontend
$action = $_GET['action'] ?? 'prices';

if ($action === 'prices') {
    // Demo prices if CoinGecko is rate limiting
    $prices = [
        ['symbol' => 'BTC', 'name' => 'Bitcoin', 'price' => 67240.50, 'change' => 2.34],
        ['symbol' => 'ETH', 'name' => 'Ethereum', 'price' => 3520.00, 'change' => 1.82],
        ['symbol' => 'SOL', 'name' => 'Solana', 'price' => 178.40, 'change' => -0.95],
    ];
    echo json_encode(['success' => true, 'data' => $prices]);
}
