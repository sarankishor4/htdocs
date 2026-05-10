<?php
require 'db.php';
require 'google_config.php';

if (!isset($_GET['id'])) die("Missing ID");

$mid = (int)$_GET['id'];
$media = $conn->query("SELECT * FROM media WHERE id=$mid")->fetch_assoc();

if (!$media || !$media['cloud_id']) {
    // Fallback to local if not offloaded yet
    if (file_exists($media['file_path'])) {
        header("Content-Type: " . mime_content_type($media['file_path']));
        readfile($media['file_path']);
    } else {
        die("Media not found local or cloud.");
    }
    exit;
}

// 1. Get Google Token
$acc = $conn->query("SELECT * FROM cloud_accounts WHERE service_name='google_drive' LIMIT 1")->fetch_assoc();
if (!$acc) die("No cloud account linked.");

function get_token($acc, $pdo) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id'     => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'refresh_token' => $acc['refresh_token'],
        'grant_type'    => 'refresh_token'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $data['access_token'] ?? $acc['access_token'];
}

$token = get_token($acc, $pdo);

// 2. Stream from Google Drive
$drive_id = $media['cloud_id'];
$url = "https://www.googleapis.com/drive/v3/files/$drive_id?alt=media";

header("Content-Type: " . ($media['type'] == 'video' ? 'video/mp4' : 'image/jpeg'));
header("Cache-Control: public, max-age=86400");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
// Stream directly to output to save memory
curl_exec($ch);
curl_close($ch);
?>
