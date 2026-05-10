<?php
// thumb_proxy.php - Bypasses hotlink protection for thumbnails
if (empty($_GET['url'])) exit;

$url = $_GET['url'];

// Basic validation
if (strpos($url, 'http') !== 0) exit;

$ch = curl_init($url);

// --- PROXY SHIELD: Mask your IP ---
$proxy_file = 'python/proxy_list.txt';
if (file_exists($proxy_file)) {
    $proxies = array_filter(explode("\n", file_get_contents($proxy_file)));
    if (!empty($proxies)) {
        $p = trim($proxies[array_rand($proxies)]);
        curl_setopt($ch, CURLOPT_PROXY, $p);
    }
}

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

$data = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

if ($data && $info['http_code'] == 200) {
    header("Content-Type: " . $info['content_type']);
    echo $data;
} else {
    // Fallback to a local placeholder if proxy fails
    header("Location: assets/no-thumb.jpg");
}
