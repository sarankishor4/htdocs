<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/auth.php';
$userId = requireAuth();
$pdo = getDB();

// Try live prices from Binance
$syms = ['BTCUSDT','ETHUSDT','SOLUSDT','BNBUSDT','ADAUSDT','XRPUSDT'];
$url = 'https://api.binance.com/api/v3/ticker/24hr?symbols='.urlencode(json_encode($syms));
$live = false;

$ch = curl_init();
curl_setopt_array($ch, [CURLOPT_URL=>$url, CURLOPT_RETURNTRANSFER=>1, CURLOPT_TIMEOUT=>4, CURLOPT_SSL_VERIFYPEER=>false, CURLOPT_USERAGENT=>'CryptomindApp']);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code==200 && $resp) {
    $data = json_decode($resp, true);
    if (is_array($data)) {
        $map = ['BTCUSDT'=>'bitcoin','ETHUSDT'=>'ethereum','SOLUSDT'=>'solana','BNBUSDT'=>'binancecoin','ADAUSDT'=>'cardano','XRPUSDT'=>'ripple'];
        $upd = $pdo->prepare("UPDATE coins SET price=?,price_change=?,high_24h=?,low_24h=?,volume=? WHERE id=?");
        $hist = $pdo->prepare("INSERT INTO price_history (coin_id,price) VALUES (?,?)");
        foreach ($data as $t) {
            $id = $map[$t['symbol']]??null;
            if ($id) {
                $p = floatval($t['lastPrice']);
                $c = floatval($t['priceChangePercent']);
                $h = floatval($t['highPrice']);
                $l = floatval($t['lowPrice']);
                $v = floatval($t['quoteVolume']);
                $vs = $v>=1e9 ? number_format($v/1e9,1).'B' : number_format($v/1e6,0).'M';
                $upd->execute([$p,$c,$h,$l,$vs,$id]);
                $hist->execute([$id,$p]);
            }
        }
        $live = true;
    }
}

// Get watchlist
$wl = $pdo->prepare("SELECT coin_id FROM watchlist WHERE user_id=?"); $wl->execute([$userId]);
$watched = array_column($wl->fetchAll(),'coin_id');

$stmt = $pdo->query("SELECT * FROM coins ORDER BY FIELD(id,'bitcoin','ethereum','solana','binancecoin','cardano','ripple')");
$coins = $stmt->fetchAll();

foreach ($coins as &$c) {
    $c['price'] = (float)$c['price'];
    $c['price_change'] = (float)$c['price_change'];
    $c['high_24h'] = (float)$c['high_24h'];
    $c['low_24h'] = (float)$c['low_24h'];
    $c['watched'] = in_array($c['id'], $watched);
}

jsonResponse(['status'=>'success','data'=>$coins,'live'=>$live]);
