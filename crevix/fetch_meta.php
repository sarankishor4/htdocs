<?php
// fetch_meta.php — AJAX endpoint for live metadata preview
header('Content-Type: application/json');

if (empty($_GET['url'])) {
    echo json_encode(['error' => 'No URL provided']);
    exit;
}

$url = trim($_GET['url']);

$python = 'python';
$test = shell_exec('python --version 2>&1');
if (stripos($test, 'Python') === false) $python = 'py';

$cmd = "$python -m yt_dlp --dump-json --no-download --no-playlist --no-warnings " . escapeshellarg($url) . " 2>NUL";
$json = shell_exec($cmd);
$info = json_decode($json, true);

if ($info) {
    echo json_encode([
        'title'     => $info['title'] ?? '',
        'thumbnail' => $info['thumbnail'] ?? '',
        'duration'  => $info['duration_string'] ?? '',
        'uploader'  => $info['uploader'] ?? '',
    ]);
} else {
    echo json_encode(['error' => 'Could not extract metadata from this URL']);
}
