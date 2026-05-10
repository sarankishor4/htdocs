<?php
require 'db.php';

echo "🔍 Starting Directory Sync...\n";

$dirs = [
    'photo' => 'uploads/photos/',
    'video' => 'uploads/videos/'
];

$user_id = 1; // Default admin for manual uploads
$count = 0;

foreach ($dirs as $type => $path) {
    if (!file_exists($path)) continue;
    
    $files = scandir($path);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (strpos($file, 'thumb_') === 0) continue; // IGNORE THUMBNAILS
        
        $file_path = $path . $file;
        if (is_dir($file_path)) continue;

        // Check if already in DB
        $stmt = $conn->prepare("SELECT id FROM media WHERE file_path = ?");
        $stmt->bind_param("s", $file_path);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            echo "   🆕 Found new $type: $file\n";
            
            $title = "Manual Upload: " . pathinfo($file, PATHINFO_FILENAME);
            $thumbnail = $file_path; // Default for photos
            
            if ($type === 'video') {
                // Try to find a thumb if it's a known format
                $thumb_path = "uploads/thumbs/thumb_" . md5($file_path) . ".jpg";
                if (file_exists($thumb_path)) {
                    $thumbnail = $thumb_path;
                } else {
                    $thumbnail = "assets/img/video_placeholder.jpg"; // Fallback
                }
            }

            $stmt_ins = $conn->prepare("INSERT INTO media (user_id, title, file_path, thumbnail, type, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt_ins->bind_param("issss", $user_id, $title, $file_path, $thumbnail, $type);
            $stmt_ins->execute();
            $count++;
        }
    }
}

echo "\n✨ SYNC COMPLETE! Added $count new items to the gallery.\n";
?>
