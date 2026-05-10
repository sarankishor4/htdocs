<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$python = 'python';
$test = shell_exec('python --version 2>&1');
if (stripos($test, 'Python') === false) $python = 'py';

if ($action == 'check_duplicates') {
    $data = json_decode(file_get_contents('php://input'), true);
    $urls = $data['urls'] ?? [];
    if (empty($urls)) { echo json_encode([]); exit; }

    $placeholders = implode(',', array_fill(0, count($urls), '?'));
    $stmt = $pdo->prepare("SELECT original_url FROM media WHERE original_url IN ($placeholders)");
    $stmt->execute($urls);
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($existing);
    exit;
}

if ($action == 'scan') {
    $url = $_GET['url'] ?? '';
    if (!$url) { echo json_encode(['error' => 'No URL provided']); exit; }

    // --- INSTAGRAM ROUTE: Use specialized scraper ---
    if (strpos($url, 'instagram.com') !== false && preg_match('#instagram\.com/([^/\?]+)/?$#', $url, $m)) {
        $uname = $m[1]; // e.g. "sofia9__official"
        $cookie_file = 'python/instagram_cookies.txt';
        $cfile_arg = file_exists($cookie_file) ? escapeshellarg($cookie_file) : '""';
        
        $cmd = "$python python/insta_scraper.py " . escapeshellarg($uname) . " $cfile_arg 30 2>&1";
        $out = shell_exec($cmd);
        
        // Hardened Parsing (Removes NULL bytes and uses regex)
        $out = str_replace("\0", "", $out);
        $insta_data = null;
        if (preg_match('/INSTA_JSON:(.*)/', $out, $matches)) {
            $insta_data = json_decode(trim($matches[1]), true);
        }
        
        if (!$insta_data || isset($insta_data['error'])) {
            echo json_encode(['error' => $insta_data['error'] ?? 'Instagram scan failed. Cookies may be required.']);
        } else {
            $entries = [];
            $profile_pic = $insta_data['profile_pic'] ?? '';
            
            foreach($insta_data['posts'] as $post) {
                // Determine if it's a carousel to provide proper UI data
                $is_carousel = !empty($post['carousel_items']);
                
                $entries[] = [
                    'url' => $post['url'],
                    'direct_url' => $post['video_url'] ?: $post['thumbnail'],
                    'title' => substr($post['title'], 0, 80),
                    'thumbnail' => $post['thumbnail'],
                    'type' => $is_carousel ? 'carousel' : $post['type'],
                    'video_url' => $post['video_url'] ?? '',
                    'carousel_items' => $post['carousel_items'] ?? [],
                    'uploader' => $insta_data['username'],
                    'duration' => $post['duration'] ? $post['duration'] . 's' : ''
                ];
            }
            echo json_encode(['entries' => $entries, 'profile_pic' => $profile_pic, 'profile' => [
                'username' => $insta_data['username'],
                'full_name' => $insta_data['full_name'] ?? '',
                'followers' => $insta_data['followers'] ?? 0
            ]]);
        }
        exit;
}
    // --- END INSTAGRAM ROUTE ---

    // Standard yt-dlp for all other sites
    $cookie_file = 'python/instagram_cookies.txt';
    $cookie_opt = file_exists($cookie_file) ? "--cookies " . escapeshellarg($cookie_file) : "";
    $headers = "--add-header \"User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\" --add-header \"Accept-Language:en-US,en;q=0.9\"";
    $cmd = "$python -m yt_dlp --dump-json --no-warnings --flat-playlist --playlist-items 1-50 " . escapeshellarg($url) . " 2>&1";
    $output = shell_exec($cmd);

    $lines = explode("\n", trim($output));
    $entries = [];
    $profile_pic = '';

    foreach($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $data = json_decode($line, true);
        if ($data) {
            // Capture Profile Pic
            if (!$profile_pic) {
                $profile_pic = $data['uploader_url'] ?? $data['thumbnail'] ?? '';
            }

            // Handle Carousel (Multiple items)
            if (isset($data['entries']) && !empty($data['entries'])) {
                foreach($data['entries'] as $sub) {
                    $entries[] = [
                        'url' => $sub['webpage_url'] ?? $sub['url'] ?? '',
                        'title' => ($sub['title'] ?? $data['title'] ?? 'Carousel Item'),
                        'thumbnail' => $sub['thumbnail'] ?? $data['thumbnail'] ?? '',
                        'uploader' => $data['uploader'] ?? 'Instagram User',
                        'duration' => $sub['duration_string'] ?? ''
                    ];
                }
            } else {
                $entries[] = [
                    'url' => $data['url'] ?? $data['webpage_url'] ?? '',
                    'title' => $data['title'] ?? 'Instagram Post',
                    'thumbnail' => $data['thumbnail'] ?? '',
                    'uploader' => $data['uploader'] ?? 'Instagram User',
                    'duration' => $data['duration_string'] ?? ''
                ];
            }
        }
    }

    if (empty($entries)) {
        echo json_encode(['error' => 'No content found. Ensure the profile is public.']);
    } else {
        echo json_encode(['entries' => $entries, 'profile_pic' => $profile_pic]);
    }
    exit;
}

if ($action == 'download') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['url'])) { echo json_encode(['error' => 'Invalid data']); exit; }

    $uid = $_SESSION['user_id'];
    $original_url = $data['parent_url'] ?? $data['url'];
    $title = $data['title'] ?? 'Bulk Fetched';
    
    // Check if it's a carousel with multiple items
    $items_to_download = [];
    if (!empty($data['carousel_items'])) {
        foreach($data['carousel_items'] as $i => $item) {
            $items_to_download[] = array_merge($item, [
                'parent_url' => $original_url,
                'part_title' => $title . " (Part " . ($i+1) . ")"
            ]);
        }
    } else {
        $items_to_download[] = array_merge($data, [
            'parent_url' => $original_url,
            'part_title' => $title
        ]);
    }
    
    $mh = curl_multi_init();
    $handles = [];
    $file_meta = [];

    foreach ($items_to_download as $i => $item) {
        $url = $item['video_url'] ?: ($item['thumbnail'] ?: ($item['direct_url'] ?? ''));
        if (!$url) continue;

        $media_type = $item['media_type'] ?? 'video';
        $duration = (int)($item['duration'] ?? 0);
        $target_dir = ($media_type === 'photo') ? "uploads/photos/" : "uploads/videos/";
        if ($media_type === 'video' && $duration > 0 && $duration <= 60) $target_dir = "uploads/reels/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        // Duplicate check
        $url_sig = basename(parse_url($url, PHP_URL_PATH));
        $check = $conn->prepare("SELECT id FROM media WHERE original_url = ? AND file_path LIKE ?");
        $check->bind_param("ss", $original_url, $p_sig);
        $p_sig = "%$url_sig%";
        $check->execute();
        if ($check->get_result()->num_rows > 0) continue;

        $ext = ($media_type === 'photo') ? 'jpg' : 'mp4';
        $filename = "stream_" . time() . "_" . $i . "_" . rand(100,999) . "." . $ext;
        $save_path = $target_dir . $filename;

        $ch = curl_init($url);
        $fp = fopen($save_path, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 1048576);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        
        curl_multi_add_handle($mh, $ch);
        $handles[$i] = $ch;
        $file_meta[$i] = ['path' => $save_path, 'fp' => $fp, 'item' => $item, 'dir' => $target_dir];
    }

    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);

    $success_count = 0;
    foreach ($handles as $i => $ch) {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
        fclose($file_meta[$i]['fp']);

        if ($http_code == 200 && filesize($file_meta[$i]['path']) > 1000) {
            $m = $file_meta[$i];
            $item = $m['item'];
            
            // Localization of thumb
            $thumb = $item['thumbnail'] ?? '';
            if ($thumb && strpos($thumb, 'http') === 0) {
                $local_thumb = $m['dir'] . "thumb_" . basename($m['path']);
                @file_put_contents($local_thumb, @file_get_contents($thumb));
                $thumb = $local_thumb;
            }

            $sql_type = ($item['media_type'] === 'photo') ? 'photo' : 'video';
            $category = (strpos($m['dir'], 'reels') !== false) ? 'Reels' : (strpos($m['dir'], 'photos') !== false ? 'Photos' : 'Videos');
            
            $stmt = $conn->prepare("INSERT INTO media (user_id, title, file_path, thumbnail, type, category, original_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $uid, $item['part_title'], $m['path'], $thumb, $sql_type, $category, $original_url);
            if ($stmt->execute()) $success_count++;
        } else {
            @unlink($file_meta[$i]['path']);
        }
    }
    curl_multi_close($mh);

    echo json_encode(['success' => true, 'count' => $success_count]);
    exit;
}
