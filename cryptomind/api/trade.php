<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';
$userId = requireAuth();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD']!=='POST') jsonResponse(['status'=>'error','message'=>'POST only'],405);

$in = json_decode(file_get_contents('php://input'), true);
$coinId = $in['coin_id']??'';
$type = strtoupper($in['trade_type']??'');
$amount = (float)($in['amount']??0);

if (!$coinId||!in_array($type,['BUY','SELL'])||$amount<=0)
    jsonResponse(['status'=>'error','message'=>'Invalid trade data']);

// Get coin info
$c = $pdo->prepare("SELECT * FROM coins WHERE id=?"); $c->execute([$coinId]);
$coin = $c->fetch();
if (!$coin) jsonResponse(['status'=>'error','message'=>'Coin not found']);

$price = (float)$coin['price'];
$totalValue = round($amount * $price, 2);

if ($type==='BUY') {
    // Check balance
    $b = $pdo->prepare("SELECT balance FROM users WHERE id=?"); $b->execute([$userId]);
    $bal = (float)$b->fetchColumn();
    if ($totalValue > $bal) jsonResponse(['status'=>'error','message'=>'Insufficient balance ($'.number_format($bal,2).')']);

    // Deduct balance
    $pdo->prepare("UPDATE users SET balance=balance-? WHERE id=?")->execute([$totalValue,$userId]);

    // Update or create portfolio entry
    $ex = $pdo->prepare("SELECT id,amount,avg_buy_price FROM portfolio WHERE user_id=? AND coin_id=?"); $ex->execute([$userId,$coinId]);
    $existing = $ex->fetch();
    if ($existing) {
        $oldAmt = (float)$existing['amount'];
        $oldAvg = (float)$existing['avg_buy_price'];
        $newAmt = $oldAmt + $amount;
        $newAvg = (($oldAmt*$oldAvg)+($amount*$price))/$newAmt;
        $pdo->prepare("UPDATE portfolio SET amount=?, avg_buy_price=? WHERE id=?")->execute([$newAmt,$newAvg,$existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO portfolio (user_id,coin_id,coin_symbol,coin_name,coin_color,amount,avg_buy_price) VALUES (?,?,?,?,?,?,?)")
            ->execute([$userId,$coinId,$coin['symbol'],$coin['name'],$coin['color'],$amount,$price]);
    }

    // Record trade
    $pdo->prepare("INSERT INTO trades (user_id,coin_id,coin_symbol,trade_type,amount,price,total_value,signal_confidence) VALUES (?,?,?,?,?,?,?,?)")
        ->execute([$userId,$coinId,$coin['symbol'],'BUY',$amount,$price,$totalValue,rand(70,90)]);

    jsonResponse(['status'=>'success','message'=>"Bought {$amount} {$coin['symbol']} at \${$price}"]);
}

if ($type==='SELL') {
    // Check holding
    $ex = $pdo->prepare("SELECT id,amount,avg_buy_price FROM portfolio WHERE user_id=? AND coin_id=?"); $ex->execute([$userId,$coinId]);
    $existing = $ex->fetch();
    if (!$existing || (float)$existing['amount'] < $amount)
        jsonResponse(['status'=>'error','message'=>'Insufficient '.$coin['symbol'].' holdings']);

    $avgBuy = (float)$existing['avg_buy_price'];
    $pnl = round(($price - $avgBuy) * $amount, 2);

    // Add balance
    $pdo->prepare("UPDATE users SET balance=balance+? WHERE id=?")->execute([$totalValue,$userId]);

    // Update portfolio
    $newAmt = (float)$existing['amount'] - $amount;
    if ($newAmt < 0.00000001) {
        $pdo->prepare("DELETE FROM portfolio WHERE id=?")->execute([$existing['id']]);
    } else {
        $pdo->prepare("UPDATE portfolio SET amount=? WHERE id=?")->execute([$newAmt,$existing['id']]);
    }

    // Record trade
    $pdo->prepare("INSERT INTO trades (user_id,coin_id,coin_symbol,trade_type,amount,price,total_value,profit_loss,signal_confidence) VALUES (?,?,?,?,?,?,?,?,?)")
        ->execute([$userId,$coinId,$coin['symbol'],'SELL',$amount,$price,$totalValue,$pnl,rand(70,90)]);

    $pnlStr = $pnl >= 0 ? "+\${$pnl}" : "-\$".abs($pnl);
    jsonResponse(['status'=>'success','message'=>"Sold {$amount} {$coin['symbol']} at \${$price} ({$pnlStr})"]);
}
