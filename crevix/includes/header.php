<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crevix – Premium Media Platform</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="logo"><a href="home.php">CREVIX</a></div>
    <div class="nav-search">
        <span>🔍</span>
        <input type="text" placeholder="Search videos, creators..." id="searchInput">
    </div>
    <ul class="nav-links">
        <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="home.php">🏠 Home</a></li>
            <li><a href="explore.php">🔥 Explore</a></li>
            <li><a href="reels.php">🎥 Reels</a></li>
            <li><a href="photos.php">🖼️ Photos</a></li>
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="upload.php">⬆️ Upload</a></li>
            <li><a href="fetch.php">🌐 Fetch</a></li>
            <li><a href="cloud_hub.php" style="color:var(--gold);">☁️ Cloud</a></li>
            <li><a href="profile.php">👤 Profile</a></li>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <li><a href="admin.php" style="color:#d4af37;">⚡ Admin</a></li>
            <?php endif; ?>
            <li><a href="logout.php" class="btn-secondary">Logout</a></li>
        <?php else: ?>
            <li><a href="explore.php">Explore</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php" class="btn-primary">Join Free</a></li>
        <?php endif; ?>
    </ul>
</nav>
