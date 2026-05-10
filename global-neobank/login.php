<?php
require_once __DIR__ . '/core/includes/auth_guard.php';
requireGuest();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p>Log in to access your global portfolio.</p>
        <div id="errorMsg" class="error-msg"></div>
        <form id="loginForm">
            <div class="input-group">
                <label>Email</label>
                <input type="email" id="email" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" id="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="auth-links">
            <a href="register.php">Don't have an account? Register</a>
            <a href="forgot.php">Forgot Password?</a>
        </div>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorMsg = document.getElementById('errorMsg');
    
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);

    const res = await fetch('api/auth.php?action=login', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    
    if (data.success) {
        window.location.href = 'home.php';
    } else {
        errorMsg.textContent = data.error;
        errorMsg.style.display = 'block';
    }
});
</script>
</body>
</html>
