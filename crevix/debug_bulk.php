<?php
// debug_bulk.php - Diagnostic tool for Bulk Fetch
require 'db.php';
header('Content-Type: text/plain');

$url = "https://xhamster45.desi/creators/sammy-sins";
$python = 'python';

echo "--- CREVIX DIAGNOSTIC ---\n";
echo "Testing URL: $url\n";

$test_py = shell_exec("python --version 2>&1");
echo "Python Version: $test_py\n";

$cmd = "python -m yt_dlp --dump-json --flat-playlist --playlist-items 1-3 " . escapeshellarg($url) . " 2>&1";
echo "Running Command: $cmd\n\n";

$output = shell_exec($cmd);
echo "--- RAW OUTPUT ---\n";
echo $output;
echo "\n--- END DIAGNOSTIC ---\n";
