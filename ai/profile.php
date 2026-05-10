<?php
require_once 'core/Config.php';
require_once 'core/Database.php';
require_once 'core/Auth.php';

use AI\Core\Auth;

Auth::init();
if (!Auth::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Operative Profile | AI Nexus</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Montserrat:wght@800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-wrap { max-width: 800px; margin: 100px auto; }
        .profile-header {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            gap: 30px;
        }
        .profile-pic-large {
            width: 120px; height: 120px;
            background: var(--nexus-primary);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 3rem; font-weight: 800; color: #000;
        }
    </style>
</head>
<body class="nexus-body">
    <div class="aurora-bg">
        <div class="aurora-orb" style="top: -10%; left: -10%; background: var(--nexus-primary);"></div>
        <div class="aurora-orb" style="bottom: -10%; right: -10%; background: var(--nexus-secondary);"></div>
    </div>

    <div class="nexus-container" style="display: block; border: none;">
        <header class="nexus-header" style="margin-bottom: 0; padding: 30px 60px;">
            <div class="user-welcome">
                <h1>OPERATIVE PROFILE</h1>
                <p>MANAGING CREDENTIALS FOR <?php echo strtoupper($user['username']); ?></p>
            </div>
            <a href="index.php" class="btn-secondary" style="background: var(--glass-white); color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none;">BACK TO MATRIX</a>
        </header>

        <div class="profile-wrap">
            <div class="profile-header">
                <div class="profile-pic-large">
                    <?php echo substr($user['username'], 0, 1); ?>
                </div>
                <div class="profile-info">
                    <h2 style="font-family: Montserrat; font-size: 2rem;"><?php echo $user['username']; ?></h2>
                    <p style="color: var(--text-dim);"><?php echo $user['email']; ?></p>
                    <div style="margin-top: 15px; display: flex; gap: 10px;">
                        <span style="background: var(--nexus-primary); color: #000; padding: 4px 10px; border-radius: 4px; font-size: 0.6rem; font-weight: 800;">ADMIN LEVEL 5</span>
                        <span style="background: rgba(255,255,255,0.1); color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 0.6rem; font-weight: 800;">ID: <?php echo $user['id']; ?></span>
                    </div>
                </div>
            </div>

            <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="agent-card">
                    <h3>Matrix Settings</h3>
                    <p>Configure your workspace preferences and neural node sensitivity.</p>
                    <button class="btn-summon" style="margin-top: 10px;">Edit Preferences</button>
                </div>
                <div class="agent-card">
                    <h3>Security Clearance</h3>
                    <p>Update your encryption keys and multi-factor authentication nodes.</p>
                    <button class="btn-summon" style="margin-top: 10px; background: var(--nexus-secondary);">Update Security</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
