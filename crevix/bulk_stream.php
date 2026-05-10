<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "data: " . json_encode(['type' => 'error', 'message' => 'Unauthorized']) . "\n\n";
    exit;
}

// SSE Headers - keep connection alive and stream data
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
set_time_limit(180);

$url = $_GET['url'] ?? '';
if (!$url) {
    echo "data: " . json_encode(['type' => 'error', 'message' => 'No URL']) . "\n\n";
    exit;
}

$python = 'python';

// --- PROXY SHIELD: Mask your IP ---
$proxy_file = 'python/proxy_list.txt';
$proxy_arg = '""';
$ytdl_proxy = "";
if (file_exists($proxy_file)) {
    $proxies = array_filter(explode("\n", file_get_contents($proxy_file)));
    if (!empty($proxies)) {
        $p = trim($proxies[array_rand($proxies)]);
        $proxy_arg = escapeshellarg($p);
        $ytdl_proxy = "--proxy " . escapeshellarg($p);
    }
}

// === XHAMSTER ROUTE ===
if (strpos($url, 'xhamster') !== false) {
    $cmd = "$python python/xhamster_scraper.py " . escapeshellarg($url) . " 50 2>&1";
    $handle = popen($cmd, 'r');
    if ($handle) {
        while (!feof($handle)) {
            $line = fgets($handle);
            if (strpos($line, 'STREAM:') === 0) {
                $json = str_replace('STREAM:', '', $line);
                echo "data: $json\n\n";
                ob_flush(); flush();
            }
        }
        pclose($handle);
        exit;
    }
}

// === INSTAGRAM ROUTE: Use specialized streaming scraper ===
if (strpos($url, 'instagram.com') !== false && preg_match('#instagram\.com/([^/\?]+)/?$#', $url, $m)) {
    $uname = $m[1];
    $cookie_file = 'python/instagram_cookies.txt';
    $cfile_arg = file_exists($cookie_file) ? escapeshellarg($cookie_file) : '""';
    
    $cmd = "$python python/insta_scraper.py " . escapeshellarg($uname) . " $cfile_arg 1000 $proxy_arg 2>&1";
    $handle = popen($cmd, 'r');
    
    if (!$handle) {
        echo "data: " . json_encode(['type' => 'error', 'message' => 'Could not start scanner']) . "\n\n";
        exit;
    }
    
    while (!feof($handle)) {
        $line = fgets($handle);
        $line = trim($line);
        if (empty($line)) continue;
        
        if (strpos($line, 'STREAM:') === 0) {
            $json = str_replace('STREAM:', '', $line);
            echo "data: $json\n\n";
            ob_flush();
            flush();
        }
    }
    pclose($handle);
    exit;
}

// === PINTEREST ROUTE ===
if (strpos($url, 'pinterest.com') !== false) {
    $cmd = "$python python/pinterest_native.py " . escapeshellarg($url) . " 100 2>&1";
    $handle = popen($cmd, 'r');
    if ($handle) {
        while (!feof($handle)) {
            $line = fgets($handle);
            if (strpos($line, 'STREAM:') === 0) {
                $json = str_replace('STREAM:', '', $line);
                echo "data: $json\n\n";
                ob_flush(); flush();
            }
        }
        pclose($handle);
        exit;
    }
}
$cmd = "$python -m yt_dlp $ytdl_proxy --dump-json --flat-playlist --no-warnings --playlist-items 1-50 " . escapeshellarg($url) . " 2>&1";
$handle = popen($cmd, 'r');

if (!$handle) {
    echo "data: " . json_encode(['type' => 'error', 'message' => 'Scanner failed to start']) . "\n\n";
    exit;
}

while (!feof($handle)) {
    $line = fgets($handle);
    $line = trim($line);
    if (empty($line)) continue;
    
    $data = json_decode($line, true);
    if ($data) {
        $title = $data['title'] ?? $data['webpage_url_basename'] ?? 'Video';
        $title = str_replace(['-', '_'], ' ', $title);
        $title = ucwords($title);
        
        $entry = [
            'type'       => 'post',
            'url'        => $data['url'] ?? $data['webpage_url'] ?? '',
            'title'      => $title,
            'thumbnail'  => $data['thumbnail'] ?? '',
            'media_type' => 'video',
            'uploader'   => $data['uploader'] ?? $data['playlist_uploader'] ?? 'Creator',
            'duration'   => $data['duration_string'] ?? ''
        ];
        
        echo "data: " . json_encode($entry) . "\n\n";
        ob_flush();
        flush();
    }
}
pclose($handle);
echo "data: " . json_encode(['type' => 'done']) . "\n\n";
ob_flush();
flush();
