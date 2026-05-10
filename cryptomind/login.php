<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoMind AI — Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

<div class="auth-bg">
    <div class="bg-orb orb-1"></div>
    <div class="bg-orb orb-2"></div>
    <div class="bg-orb orb-3"></div>
    <div class="grid-overlay"></div>
</div>

<div class="auth-container">
    <div class="auth-brand">
        <a href="index.php" style="text-decoration:none">
            <div class="brand-label">AI TRADING PLATFORM</div>
            <h1 class="brand-title">CRYPTOMIND<span class="brand-dot">.</span>AI</h1>
        </a>
        <p class="brand-desc">Your intelligent gateway to crypto markets</p>
    </div>

    <!-- Login Form -->
    <div id="login-form" class="auth-card">
        <h2 class="auth-heading">Welcome Back</h2>
        <p class="auth-sub">Sign in to your trading dashboard</p>

        <div id="login-error" class="auth-error" style="display:none"></div>

        <div class="form-group">
            <label class="form-label">Username or Email</label>
            <input type="text" id="login-input" class="form-input" placeholder="Enter username or email" autocomplete="username">
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" id="login-pass" class="form-input" placeholder="Enter password" autocomplete="current-password">
        </div>
        <button id="login-btn" class="btn btn-primary btn-full">
            <span class="btn-text">Sign In</span>
            <span class="btn-loader" style="display:none"></span>
        </button>
        <p class="auth-switch">Don't have an account? <a href="#" id="show-register">Create one</a></p>
    </div>

    <!-- Register Form -->
    <div id="register-form" class="auth-card" style="display:none">
        <h2 class="auth-heading">Create Account</h2>
        <p class="auth-sub">Start your AI trading journey</p>

        <div id="register-error" class="auth-error" style="display:none"></div>
        <div id="register-success" class="auth-success" style="display:none"></div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" id="reg-user" class="form-input" placeholder="Choose username" autocomplete="username">
            </div>
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" id="reg-name" class="form-input" placeholder="Your name" autocomplete="name">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" id="reg-email" class="form-input" placeholder="you@email.com" autocomplete="email">
        </div>
        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" id="reg-pass" class="form-input" placeholder="Min 6 characters" autocomplete="new-password">
        </div>
        <button id="register-btn" class="btn btn-primary btn-full">
            <span class="btn-text">Create Account</span>
            <span class="btn-loader" style="display:none"></span>
        </button>
        <p class="auth-switch">Already have an account? <a href="#" id="show-login">Sign in</a></p>
    </div>

    <div class="auth-footer">
        <span>⚠ Demo Platform — Not Financial Advice</span>
    </div>
</div>

<script src="js/auth.js"></script>
</body>
</html>
