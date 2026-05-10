<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD']!=='POST') jsonResponse(['status'=>'error','message'=>'POST only'],405);

$in = json_decode(file_get_contents('php://input'), true);
$user = trim($in['username']??'');
$email = trim($in['email']??'');
$pass = $in['password']??'';
$name = trim($in['full_name']??'');

$err = [];
if (strlen($user)<3) $err[]='Username min 3 chars';
if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $err[]='Invalid email';
if (strlen($pass)<6) $err[]='Password min 6 chars';
if ($err) jsonResponse(['status'=>'error','message'=>implode('. ',$err)]);

$pdo = getDB();
$chk = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
$chk->execute([$user,$email]);
if ($chk->fetch()) jsonResponse(['status'=>'error','message'=>'Username or email already taken']);

$hash = password_hash($pass, PASSWORD_BCRYPT);
$colors = ['#00ff88','#627EEA','#F7931A','#9945FF','#ff4466','#00aaff','#F3BA2F','#23292F'];
$color = $colors[array_rand($colors)];

$s = $pdo->prepare("INSERT INTO users (username,email,password_hash,full_name,avatar_color) VALUES (?,?,?,?,?)");
$s->execute([$user,$email,$hash,$name?:$user,$color]);
$uid = $pdo->lastInsertId();

// Starter portfolio
$coins = [
    ['bitcoin','BTC','Bitcoin','#F7931A',0.01,61000],
    ['ethereum','ETH','Ethereum','#627EEA',0.5,3000],
    ['solana','SOL','Solana','#9945FF',5,140],
    ['binancecoin','BNB','BNB','#F3BA2F',1,560],
];
$ps = $pdo->prepare("INSERT INTO portfolio (user_id,coin_id,coin_symbol,coin_name,coin_color,amount,avg_buy_price) VALUES (?,?,?,?,?,?,?)");
foreach ($coins as $c) $ps->execute([$uid,$c[0],$c[1],$c[2],$c[3],$c[4],$c[5]]);

// Starter trades
$ts = $pdo->prepare("INSERT INTO trades (user_id,coin_id,coin_symbol,trade_type,amount,price,total_value,profit_loss,signal_confidence,trade_date) VALUES (?,?,?,?,?,?,?,?,?,?)");
$ts->execute([$uid,'bitcoin','BTC','BUY',0.05,59800,2990,NULL,78,date('Y-m-d H:i:s',strtotime('-5 days'))]);
$ts->execute([$uid,'ethereum','ETH','BUY',2.0,2980,5960,NULL,82,date('Y-m-d H:i:s',strtotime('-4 days'))]);
$ts->execute([$uid,'bitcoin','BTC','SELL',0.05,62100,3105,115,74,date('Y-m-d H:i:s',strtotime('-3 days'))]);
$ts->execute([$uid,'solana','SOL','BUY',20,138,2760,NULL,88,date('Y-m-d H:i:s',strtotime('-2 days'))]);
$ts->execute([$uid,'ethereum','ETH','SELL',2.0,3110,6220,260,80,date('Y-m-d H:i:s',strtotime('-1 day'))]);
$ts->execute([$uid,'solana','SOL','SELL',20,147,2940,180,85,date('Y-m-d H:i:s',strtotime('-12 hours'))]);

// Watchlist
$ws = $pdo->prepare("INSERT INTO watchlist (user_id,coin_id) VALUES (?,?)");
$ws->execute([$uid,'bitcoin']);
$ws->execute([$uid,'solana']);

loginUser($uid);
jsonResponse(['status'=>'success','message'=>'Account created! Welcome to CryptoMind.']);
