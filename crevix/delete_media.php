<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$uid = $_SESSION['user_id'];
$mid = (int)($_GET['id'] ?? 0);

// Owner check
$media = $conn->query("SELECT * FROM media WHERE id=$mid AND user_id=$uid")->fetch_assoc();

// Admins can delete any
if (!$media && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $media = $conn->query("SELECT * FROM media WHERE id=$mid")->fetch_assoc();
}

if ($media) {
    if (!empty($media['file_path']) && file_exists($media['file_path'])) unlink($media['file_path']);
    if (!empty($media['thumbnail']) && file_exists($media['thumbnail'])) unlink($media['thumbnail']);
    $conn->query("DELETE FROM media WHERE id=$mid");
}

header("Location: dashboard.php");
exit();
?>
