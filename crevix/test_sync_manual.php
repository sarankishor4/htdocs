<?php
require 'db.php';
require 'google_config.php';

// TARGET TEST FILES
$test_ids = [1, 2]; 
$account_id = 1;

$acc = $conn->query("SELECT * FROM cloud_accounts WHERE id=$account_id")->fetch_assoc();
if (!$acc) die("Account not found");

function get_valid_token($acc, $pdo) {
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

$token = get_valid_token($acc, $pdo);

// Ensure Remote Folder Exists
$folder_id = $acc['remote_folder_id'];
if (!$folder_id) {
    echo "Creating 'Crevix_Media' folder...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/drive/v3/files");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['name' => 'Crevix_Media', 'mimeType' => 'application/vnd.google-apps.folder']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $create_res = json_decode(curl_exec($ch), true);
    curl_close($ch);
    $folder_id = $create_res['id'] ?? null;
    if ($folder_id) {
        $conn->query("UPDATE cloud_accounts SET remote_folder_id = '$folder_id' WHERE id = 1");
        echo "  CREATED: $folder_id\n";
    }
}

foreach ($test_ids as $mid) {
    $m = $conn->query("SELECT * FROM media WHERE id=$mid")->fetch_assoc();
    $file_path = __DIR__ . '/' . ltrim($m['file_path'], '/');
    echo "Processing ID $mid: {$m['title']}...\n";

    if (!file_exists($file_path)) { echo "  FAILED: File not found\n"; continue; }

    $filename = basename($file_path);
    $mime = mime_content_type($file_path);
    $meta_array = ['name' => $filename];
    if ($folder_id) $meta_array['parents'] = [$folder_id];
    $metadata = json_encode($meta_array);
    $file_content = file_get_contents($file_path);

    $boundary = "-------" . md5(time());
    $data = "--$boundary\r\n";
    $data .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
    $data .= $metadata . "\r\n";
    $data .= "--$boundary\r\n";
    $data .= "Content-Type: $mime\r\n\r\n";
    $data .= $file_content . "\r\n";
    $data .= "--$boundary--";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: multipart/related; boundary=' . $boundary,
        'Content-Length: ' . strlen($data)
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $upload_res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($upload_res['id'])) {
        echo "  SUCCESS: Drive ID " . $upload_res['id'] . "\n";
        $conn->query("UPDATE media SET cloud_id='{$upload_res['id']}', cloud_service='google_drive', is_offloaded=1 WHERE id=$mid");
    } else {
        echo "  ERROR: " . print_r($upload_res, true) . "\n";
    }
}
?>
