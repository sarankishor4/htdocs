<?php
session_start();
$_SESSION['user_id'] = 1; // Simulate admin
require 'db.php';

$python = 'C:/xampp/php/php.exe'; // Not used here, just placeholder
$python_exe = 'python';

$url = "https://scontent.cdninstagram.com/v/t51.2885-15/441463138_18332155050117070_5806495333552084961_n.jpg?_nc_cat=101&ccb=1-7&_nc_sid=18de2a&_nc_ohc=...fake_url..."; 
// I need a REAL direct URL to test. 
// Actually I'll just use the Post URL and the Python Fallback test.

$post_url = "https://www.instagram.com/p/C6q0qQ_vFmI/";
$target_dir = "uploads/photos/";
$proxy_url = "";
$cookie_file = "python/instagram_cookies.txt";

$cmd = "python python/ytdl_bridge.py " . escapeshellarg($post_url) . " " . escapeshellarg($target_dir) . " " . escapeshellarg($proxy_url) . " " . escapeshellarg($cookie_file) . " 2>&1";
echo "RUNNING: $cmd\n";
$out = shell_exec($cmd);
echo "OUTPUT: $out\n";
?>
