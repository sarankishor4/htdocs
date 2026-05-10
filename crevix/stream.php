<?php
// stream.php — Crevix Smart Streamer
// Supports: local files + Google Drive Proxy (Dual-Mode)
set_time_limit(0);
ini_set('memory_limit', '512M');
while (ob_get_level()) ob_end_clean();

$ffmpeg = 'C:\Users\kisho\AppData\Local\Microsoft\WinGet\Packages\Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe\ffmpeg-8.1.1-full_build\bin\ffmpeg.exe';

$conn = new mysqli('localhost', 'root', '', 'crevix_db');
if ($conn->connect_error) { http_response_code(500); exit; }

if (!isset($_GET['id'])) { http_response_code(400); exit; }

$id = (int)$_GET['id'];
$res = $conn->query("SELECT * FROM media WHERE id=$id");
if (!$res || $res->num_rows == 0) { http_response_code(404); exit; }
$row = $res->fetch_assoc();

// === CLOUD STREAMING MODE ===
if ($row['is_offloaded'] && $row['cloud_id']) {
    require 'google_config.php';
    // Fetch specifically the account that owns this file
    $acc_id = $row['cloud_account_id'];
    $acc_res = $conn->query("SELECT * FROM cloud_accounts WHERE id=$acc_id");
    
    // Fallback if ID is missing (for older entries)
    if (!$acc_res || $acc_res->num_rows == 0) {
        $acc_res = $conn->query("SELECT * FROM cloud_accounts WHERE service_name='google_drive' LIMIT 1");
    }

    if ($acc_res && $acc_res->num_rows > 0) {
        $acc = $acc_res->fetch_assoc();
        
        // Refresh Token
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'refresh_token' => $acc['refresh_token'],
            'grant_type'    => 'refresh_token'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $token_data = json_decode(curl_exec($ch), true);
        curl_close($ch);
        $token = $token_data['access_token'] ?? $acc['access_token'];

        // === DUAL-MODE CLOUD PROXY ===
        $is_video = ($row['type'] === 'video');
        $url = "https://www.googleapis.com/drive/v3/files/{$row['cloud_id']}?alt=media";
        $headers = ['Authorization: Bearer ' . $token];

        if (!$is_video) {
            // High-Speed Direct Injection (Bypasses API Access Denied)
            $url = "https://drive.google.com/uc?export=download&id={$row['cloud_id']}";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $output = curl_exec($ch);
            $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            header("Content-Type: $content_type");
            echo $output;
            exit;
        } else {
            // Industrial Byte-Range Proxy for Videos (Enables Seeking)
            if (isset($_SERVER['HTTP_RANGE'])) $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($ch, $header) {
                $h = strtolower($header);
                if (strpos($h, 'content-type:') === 0 || strpos($h, 'content-length:') === 0 || 
                    strpos($h, 'content-range:') === 0 || strpos($h, 'accept-ranges:') === 0) {
                    header($header);
                }
                if (strpos($h, 'http/') === 0) {
                    $parts = explode(' ', $header);
                    if (count($parts) > 1) http_response_code((int)$parts[1]);
                }
                return strlen($header);
            });
            curl_exec($ch);
            curl_close($ch);
            exit;
        }
    }
}

// === LOCAL FILE MODE ===
$file_path = $row['file_path'];
$rel_path = ltrim($file_path, '/');
$path = __DIR__ . '/' . $rel_path;

if (!file_exists($path)) { http_response_code(404); die("File not found."); }

$size = filesize($path);
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mimes = ['mp4'=>'video/mp4', 'webm'=>'video/webm', 'ts'=>'video/mp2t', 'jpg'=>'image/jpeg', 'png'=>'image/png'];
$mime = $mimes[$ext] ?? 'application/octet-stream';

// Handle Range for Local Files
$start = 0; $end = $size - 1;
if (isset($_SERVER['HTTP_RANGE'])) {
    if (preg_match('/bytes=(\d*)-(\d*)/i', $_SERVER['HTTP_RANGE'], $m)) {
        $start = ($m[1] !== '') ? intval($m[1]) : 0;
        $end   = ($m[2] !== '') ? min(intval($m[2]), $size - 1) : $size - 1;
        http_response_code(206);
        header("Content-Range: bytes $start-$end/$size");
    }
}
$length = $end - $start + 1;
header("Content-Type: $mime");
header("Accept-Ranges: bytes");
header("Content-Length: $length");

$fp = fopen($path, 'rb');
fseek($fp, $start);
$remaining = $length;
while (!feof($fp) && $remaining > 0) {
    $chunk = min(65536, $remaining);
    echo fread($fp, $chunk);
    $remaining -= $chunk;
    flush();
}
fclose($fp);
exit;
