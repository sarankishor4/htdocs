<?php
// CRON JOB FOR AUTOMATED TRADING BOT
// This script should be run via CLI every minute: php cron_bot.php

require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

echo "[".date('Y-m-d H:i:s')."] Starting AI Bot Execution Cycle...\n";

// 1. Simulate Market Price Changes (To trigger signals)
$coins = $pdo->query("SELECT * FROM coins")->fetchAll();
foreach ($coins as &$c) {
    // Randomly change price between -3% and +3%
    $changePct = (rand(-300, 300) / 100); 
    $newPrice = $c['price'] * (1 + ($changePct / 100));
    $c['price'] = $newPrice;
    $c['price_change'] = $changePct;
    $pdo->prepare("UPDATE coins SET price=?, price_change=? WHERE id=?")->execute([$newPrice, $changePct, $c['id']]);
}
echo "Market prices updated.\n";

// 2. Generate signals
$signals = [];
foreach ($coins as $c) {
    $price = (float)$c['price'];
    $change = (float)$c['price_change'];
    
    // Simulate AI logic natively for speed in CRON
    $signal = 'HOLD'; 
    $confidence = rand(40, 60);
    
    if ($change > 1.5) { 
        $signal = 'BUY'; 
        $confidence = rand(65, 95); 
    } elseif ($change < -1.5) { 
        $signal = 'SELL'; 
        $confidence = rand(60, 95); 
    }
    
    $signals[$c['id']] = [
        'signal' => $signal,
        'confidence' => $confidence,
        'price' => $price,
        'symbol' => $c['symbol']
    ];
}

// 3. Process active bot users
$users = $pdo->query("SELECT id, balance, bot_risk, bot_allocation FROM users WHERE bot_active = 1")->fetchAll();
$tradesExecuted = 0;

if (count($users) === 0) {
    echo "No active bots found.\n";
}

foreach ($users as $u) {
    $uid = $u['id'];
    $risk = $u['bot_risk']; // low, medium, high
    $allocPct = floatval($u['bot_allocation']) / 100.0;
    
    // Risk Management Thresholds
    $buyThreshold = 75; // medium
    if ($risk === 'low') $buyThreshold = 85;
    if ($risk === 'high') $buyThreshold = 65;
    
    echo "Processing User $uid (Risk: $risk, Alloc: ".($allocPct*100)."%)...\n";

    foreach ($signals as $cid => $sig) {
        $action = $sig['signal'];
        $conf = $sig['confidence'];
        $price = $sig['price'];
        $symbol = $sig['symbol'];
        
        // --- BUY LOGIC ---
        if ($action === 'BUY' && $conf >= $buyThreshold && $u['balance'] > 10) {
            $tradeSize = $u['balance'] * $allocPct;
            if ($tradeSize < 10) $tradeSize = 10;
            if ($tradeSize > $u['balance']) $tradeSize = $u['balance'];
            
            $amount = $tradeSize / $price;
            
            $pdo->beginTransaction();
            try {
                $pdo->exec("UPDATE users SET balance = balance - $tradeSize WHERE id = $uid");
                
                $port = $pdo->query("SELECT id, amount, avg_buy_price FROM portfolio WHERE user_id=$uid AND coin_id='$cid'")->fetch();
                if ($port) {
                    $newAmt = $port['amount'] + $amount;
                    $newCost = ($port['amount'] * $port['avg_buy_price']) + $tradeSize;
                    $newAvg = $newCost / $newAmt;
                    $pdo->prepare("UPDATE portfolio SET amount=?, avg_buy_price=? WHERE id=?")->execute([$newAmt, $newAvg, $port['id']]);
                } else {
                    $pdo->prepare("INSERT INTO portfolio (user_id, coin_id, amount, avg_buy_price) VALUES (?,?,?,?)")->execute([$uid, $cid, $amount, $price]);
                }
                
                $pdo->prepare("INSERT INTO trades (user_id, coin_id, trade_type, amount, price, total_value, signal_confidence) VALUES (?,?,'BUY',?,?,?,?)")
                    ->execute([$uid, $cid, $amount, $price, $tradeSize, $conf]);
                
                $u['balance'] -= $tradeSize;
                $tradesExecuted++;
                $pdo->commit();
                echo " -> BOUGHT $symbol (Conf: $conf%)\n";
            } catch(Exception $e) { $pdo->rollBack(); echo " -> Error buying $symbol: ".$e->getMessage()."\n"; }
        }
        
        // --- SELL LOGIC ---
        $sellThreshold = $buyThreshold - 10; 
        
        if ($action === 'SELL' && $conf >= $sellThreshold) {
            $port = $pdo->query("SELECT id, amount, avg_buy_price FROM portfolio WHERE user_id=$uid AND coin_id='$cid'")->fetch();
            if ($port && $port['amount'] > 0) {
                $sellAmount = $port['amount'];
                $sellValue = $sellAmount * $price;
                $pnl = $sellValue - ($sellAmount * $port['avg_buy_price']);
                
                $pdo->beginTransaction();
                try {
                    $pdo->exec("UPDATE users SET balance = balance + $sellValue WHERE id = $uid");
                    $pdo->exec("DELETE FROM portfolio WHERE id = " . $port['id']);
                    
                    $pdo->prepare("INSERT INTO trades (user_id, coin_id, trade_type, amount, price, total_value, profit_loss, signal_confidence) VALUES (?,?,'SELL',?,?,?,?,?)")
                        ->execute([$uid, $cid, $sellAmount, $price, $sellValue, $pnl, $conf]);
                    
                    $u['balance'] += $sellValue;
                    $tradesExecuted++;
                    $pdo->commit();
                    echo " -> SOLD $symbol (PnL: $".number_format($pnl, 2).")\n";
                } catch(Exception $e) { $pdo->rollBack(); echo " -> Error selling $symbol: ".$e->getMessage()."\n"; }
            }
        }
    }
}

echo "[".date('Y-m-d H:i:s')."] Cycle Complete. $tradesExecuted trades executed.\n";
