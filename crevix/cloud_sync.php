<?php
require 'db.php';
require 'google_config.php';

header('Content-Type: application/json');

if (!isset($_GET['account_id'])) {
    echo json_encode(['error' => 'Missing account_id']);
    exit;
}

$account_id = (int)$_GET['account_id'];
$acc = $conn->query("SELECT * FROM cloud_accounts WHERE id=$account_id")->fetch_assoc();

if (!$acc) {
    echo json_encode(['error' => 'Account not found']);
    exit;
}

// 1. Refresh Access Token
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

    if (isset($data['access_token'])) {
        $pdo->prepare("UPDATE cloud_accounts SET access_token = ? WHERE id = ?")
            ->execute([$data['access_token'], $acc['id']]);
        return $data['access_token'];
    }
    return $acc['access_token'];
}

$token = get_valid_token($acc, $pdo);

// 2. Ensure Remote Folder Exists
$folder_id = $acc['remote_folder_id'];
if (!$folder_id) {
    // Check if "Crevix_Media" exists
    $q = urlencode("name = 'Crevix_Media' and mimeType = 'application/vnd.google-apps.folder' and trashed = false");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/drive/v3/files?q=$q");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $search_res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!empty($search_res['files'])) {
        $folder_id = $search_res['files'][0]['id'];
    } else {
        // Create it
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/drive/v3/files");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'name' => 'Crevix_Media',
            'mimeType' => 'application/vnd.google-apps.folder'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $create_res = json_decode(curl_exec($ch), true);
        curl_close($ch);
        $folder_id = $create_res['id'] ?? null;
    }

    if ($folder_id) {
        $conn->query("UPDATE cloud_accounts SET remote_folder_id = '$folder_id' WHERE id = {$acc['id']}");
    }
}

// 3. Find files to upload (not yet offloaded)
$media_list = $conn->query("SELECT * FROM media WHERE is_offloaded = 0 LIMIT 10"); // Batch of 10 for performance

$results = [];
while ($m = $media_list->fetch_assoc()) {
    $file_path = __DIR__ . '/' . ltrim($m['file_path'], '/');
    if (!file_exists($file_path)) {
        $results[] = ['id' => $m['id'], 'status' => 'skipped', 'reason' => 'file_not_found'];
        continue;
    }

    // Upload to Google Drive
    $filename = basename($file_path);
    $mime = mime_content_type($file_path);
    
    $meta_array = ['name' => $filename];
    if ($folder_id) {
        $meta_array['parents'] = [$folder_id];
    }
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
        // Success! Update DB
        $drive_id = $upload_res['id'];
        $conn->query("UPDATE media SET cloud_id='$drive_id', cloud_service='google_drive', is_offloaded=1 WHERE id={$m['id']}");
        
        // REMOVE OFFLINE (Delete local file to save space)
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
        
        $results[] = ['id' => $m['id'], 'status' => 'success', 'drive_id' => $drive_id, 'offloaded' => true];
    } else {
        $results[] = ['id' => $m['id'], 'status' => 'error', 'details' => $upload_res];
    }
}

echo json_encode(['results' => $results]);
?>
