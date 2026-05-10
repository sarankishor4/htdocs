<?php
require_once __DIR__ . '/core/includes/auth_guard.php';
requireGuest();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h2>Create Account</h2>
        <p>Join the world's first global AI-powered neobank.</p>
        <div id="errorMsg" class="error-msg"></div>
        <form id="registerForm">
            <div class="input-row">
                <div class="input-group">
                    <label>First Name</label>
                    <input type="text" id="first_name" required>
                </div>
                <div class="input-group">
                    <label>Last Name</label>
                    <input type="text" id="last_name" required>
                </div>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" id="email" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" id="password" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <div class="auth-links">
            <a href="login.php">Already have an account? Login</a>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorMsg = document.getElementById('errorMsg');
    
    const formData = new FormData();
    formData.append('first_name', document.getElementById('first_name').value);
    formData.append('last_name', document.getElementById('last_name').value);
    formData.append('email', document.getElementById('email').value);
    formData.append('password', document.getElementById('password').value);

    const res = await fetch('api/auth.php?action=register', {
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
