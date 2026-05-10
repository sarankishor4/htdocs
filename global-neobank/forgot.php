<?php
require_once __DIR__ . '/core/includes/auth_guard.php';
requireGuest();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password - GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h2>Reset Password</h2>
        <p>Enter your email address and we'll send you a link to reset your password.</p>
        <div id="errorMsg" class="error-msg"></div>
        <div id="successMsg" style="display:none; color:var(--green); background:#00e87a15; padding:12px; border-radius:4px; font-size:12px; margin-bottom:16px;"></div>
        <form id="forgotForm">
            <div class="input-group">
                <label>Email</label>
                <input type="email" id="email" required>
            </div>
            <button type="submit" class="btn">Send Reset Link</button>
        </form>
        <div class="auth-links">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</div>

<script>
document.getElementById('forgotForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const errorMsg = document.getElementById('errorMsg');
    const successMsg = document.getElementById('successMsg');
    const btn = document.querySelector('.btn');
    
    errorMsg.style.display = 'none';
    successMsg.style.display = 'none';
    btn.innerText = 'Sending...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('email', email);

    try {
        const res = await fetch('api/password.php?action=forgot', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            successMsg.textContent = 'If an account exists, a reset link was sent.';
            successMsg.style.display = 'block';
            document.getElementById('forgotForm').reset();
        } else {
            errorMsg.textContent = data.error;
            errorMsg.style.display = 'block';
        }
    } catch(err) {
        errorMsg.textContent = 'A network error occurred.';
        errorMsg.style.display = 'block';
    }
    
    btn.innerText = 'Send Reset Link';
    btn.disabled = false;
});
</script>
</body>
</html>
