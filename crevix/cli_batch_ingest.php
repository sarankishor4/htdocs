<?php
require 'db.php';

if ($argc < 2) {
    die("Usage: php cli_batch_ingest.php <json_output_file>\n");
}

$json_file = $argv[1];
if (!file_exists($json_file)) {
    die("File not found: $json_file\n");
}

$content = file_get_contents($json_file);
// Remove NULL bytes and handle potential UTF-16 issues
$content = str_replace("\0", "", $content);

preg_match_all('/STREAM:(.*)/', $content, $matches);
foreach ($matches[1] as $json_str) {
    $data = json_decode(trim($json_str), true);
    if (!$data || ($data['type'] ?? '') !== 'post') continue;

    $shortcode = $data['shortcode'] ?? 'Post';
    echo "📦 Processing: $shortcode\n";

    $items_to_download = [];
    if (!empty($data['carousel_items'])) {
        foreach ($data['carousel_items'] as $i => $item) {
            $items_to_download[] = [
                'url' => $item['video_url'] ?: $item['thumbnail'],
                'media_type' => $item['media_type'],
                'title' => ($data['title'] ?: 'Post') . " (Part " . ($i + 1) . ")",
                'thumbnail' => $item['thumbnail'],
                'original_url' => $data['url']
            ];
        }
    } else {
        $items_to_download[] = [
            'url' => $data['direct_url'] ?: $data['thumbnail'],
            'media_type' => $data['media_type'],
            'title' => $data['title'] ?: 'Post',
            'thumbnail' => $data['thumbnail'],
            'original_url' => $data['url']
        ];
    }

    // 2. PARALLEL DOWNLOAD ENGINE
    $mh = curl_multi_init();
    $handles = [];
    $file_pointers = [];
    $meta = [];

    foreach ($items_to_download as $i => $item) {
        $media_url = $item['url'];
        $type = $item['media_type'];
        $target_dir = ($type === 'photo') ? "uploads/photos/" : "uploads/videos/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $ext = ($type === 'photo') ? 'jpg' : 'mp4';
        $save_path = $target_dir . "cli_" . md5($media_url) . "." . $ext;

        if (file_exists($save_path) && filesize($save_path) > 1000) {
            // Already exists on disk, but might not be in DB (orphaned)
            // We'll still process it through DB later
            $handles[$i] = null;
            $meta[$i] = ['path' => $save_path, 'status' => 'exists', 'item' => $item];
            continue;
        }

        $ch = curl_init($media_url);
        $fp = fopen($save_path, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 1048576); // 1MB Buffer for 100x speed

        curl_multi_add_handle($mh, $ch);

        $handles[$i] = $ch;
        $file_pointers[$i] = $fp;
        $meta[$i] = ['path' => $save_path, 'status' => 'downloading', 'item' => $item];
    }

    // Execute Multi-Download
    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);

    // 3. FINALIZE AND DB INSERT
    foreach ($meta as $i => $m) {
        $success = false;
        if ($m['status'] === 'exists') {
            $success = true;
        } else {
            $ch = $handles[$i];
            $http_code = curl_getinfo($handles[$i], CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($mh, $handles[$i]);
            curl_close($handles[$i]);
            fclose($file_pointers[$i]);
            if ($http_code == 200 && filesize($m['path']) > 1000) {
                $success = true;
            } else {
                @unlink($m['path']);
                echo "   ❌ Failed: " . basename($m['path']) . "\n";
            }
        }

        if ($success) {
            $item = $m['item'];

            // DB DUPLICATE CHECK
            $db_check = $conn->query("SELECT id FROM media WHERE file_path = '" . $conn->real_escape_string($m['path']) . "'");
            if ($db_check->num_rows > 0) {
                echo "   ⏩ Record already in DB: " . basename($m['path']) . "\n";
                continue;
            }

            $uploader = $data['uploader'] ?? 'instagram_user';

            // User handling
            $user_check = $conn->query("SELECT id FROM users WHERE username = '$uploader'");
            if ($user_check->num_rows > 0) {
                $user_id = $user_check->fetch_assoc()['id'];
            } else {
                $temp_email = $uploader . "@crevix.internal";
                $conn->query("INSERT INTO users (username, email, password, role) VALUES ('$uploader', '$temp_email', 'external_user', 'user')");
                $user_id = $conn->insert_id;
            }

            // Thumbnail Localization
            $local_thumb = $m['path']; // Photo default
            if ($item['media_type'] === 'video') {
                $thumb_dir = "uploads/thumbs/";
                if (!file_exists($thumb_dir)) mkdir($thumb_dir, 0777, true);
                $local_thumb = $thumb_dir . "thumb_" . md5($item['thumbnail']) . ".jpg";
                if (!file_exists($local_thumb)) {
                    @file_put_contents($local_thumb, file_get_contents($item['thumbnail']));
                }
            }

            // DB Insert
            $stmt = $conn->prepare("INSERT INTO media (user_id, title, file_path, thumbnail, type, original_url, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
            $stmt->bind_param("isssss", $user_id, $item['title'], $m['path'], $local_thumb, $item['media_type'], $item['original_url']);
            $stmt->execute();
            echo "   ✅ Ingested: " . basename($m['path']) . "\n";
        }
    }
    curl_multi_close($mh);
}

echo "\n✨ TURBO INGESTION COMPLETE!\n";
