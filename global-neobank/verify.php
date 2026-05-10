<?php
require_once __DIR__ . '/core/includes/db.php';
require_once __DIR__ . '/core/includes/auth_guard.php';

$token = $_GET['token'] ?? '';
$msg = '';

if (!empty($token)) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['is_verified']) {
            $msg = "Your email is already verified!";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            if ($stmt->execute([$user['id']])) {
                $msg = "Email verified successfully! You can now use all features.";
                // Update session if user is currently logged in
                startSecureSession();
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id']) {
                    $_SESSION['user_is_verified'] = 1;
                }
            } else {
                $msg = "Verification failed due to a server error.";
            }
        }
    } else {
        $msg = "Invalid or expired verification token.";
    }
} else {
    $msg = "No token provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Email Verification — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card" style="text-align:center;">
        <div style="font-size:40px; margin-bottom:16px;">✉️</div>
        <h2>Email Verification</h2>
        <p style="margin-top:16px; margin-bottom:24px; color:var(--text);"><?= htmlspecialchars($msg) ?></p>
        <a href="home.php" class="btn" style="text-decoration:none; display:inline-block;">Go to Dashboard</a>
    </div>
</div>
</body>
</html>
