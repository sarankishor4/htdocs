<?php
require 'db.php';
require 'cloud_router.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['url'])) {
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

$source_url = $input['url'];
$title = $input['title'] ?? 'Cloud Upload';
$type = $input['type'] ?? 'photo';
$uid = 1; // Default admin user

$original_url = $input['parent_url'] ?? $input['url'];

// 1. Generate unique filename (for DB tracking)
$ext = ($type == 'video') ? 'mp4' : 'jpg';
$filename = "cloud_" . md5($source_url) . ".$ext";
$virtual_path = ($type == 'video') ? "uploads/videos/$filename" : "uploads/photos/$filename";

$router = new CloudRouter($pdo);

// 2. Direct Cloud Ingestion (Zero Disk - Pipe Bridge)
$result = $router->upload_direct($source_url, $filename, ($type == 'video' ? 'video/mp4' : 'image/jpeg'));

if (isset($result['success'])) {
    // 3. Save Metadata to DB
    $stmt = $pdo->prepare("INSERT INTO media (user_id, title, file_path, type, cloud_id, cloud_service, cloud_account_id, is_offloaded, original_url) 
                          VALUES (?, ?, ?, ?, ?, 'google_drive', ?, 1, ?)");
    $stmt->execute([$uid, $title, $virtual_path, $type, $result['drive_id'], $result['account_id'], $original_url]);
    
    echo json_encode([
        'status' => 'success',
        'drive_id' => $result['drive_id'],
        'account' => $result['account_email']
    ]);
} else {
    echo json_encode($result);
}
?>
