<?php
require_once __DIR__ . '/core/includes/auth_guard.php';
requireGuest();

$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Set New Password - GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h2>Set New Password</h2>
        <div id="errorMsg" class="error-msg"></div>
        <div id="successMsg" style="display:none; color:var(--green); background:#00e87a15; padding:12px; border-radius:4px; font-size:12px; margin-bottom:16px;"></div>
        
        <?php if(empty($token)): ?>
            <p style="color:var(--red);">No reset token provided. Please use the link from your email.</p>
        <?php else: ?>
            <form id="resetForm">
                <input type="hidden" id="token" value="<?= htmlspecialchars($token) ?>">
                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" id="password" required>
                </div>
                <div class="input-group">
                    <label>Confirm Password</label>
                    <input type="password" id="confirm_password" required>
                </div>
                <button type="submit" class="btn">Update Password</button>
            </form>
        <?php endif; ?>
        
        <div class="auth-links">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</div>

<script>
const form = document.getElementById('resetForm');
if(form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const token = document.getElementById('token').value;
        const password = document.getElementById('password').value;
        const confirm_password = document.getElementById('confirm_password').value;
        const errorMsg = document.getElementById('errorMsg');
        const successMsg = document.getElementById('successMsg');
        
        errorMsg.style.display = 'none';
        
        if(password !== confirm_password) {
            errorMsg.textContent = 'Passwords do not match.';
            errorMsg.style.display = 'block';
            return;
        }

        const formData = new FormData();
        formData.append('token', token);
        formData.append('password', password);

        try {
            const res = await fetch('api/password.php?action=reset', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (data.success) {
                successMsg.textContent = 'Password reset successfully! You can now log in.';
                successMsg.style.display = 'block';
                form.style.display = 'none';
            } else {
                errorMsg.textContent = data.error;
                errorMsg.style.display = 'block';
            }
        } catch(err) {
            errorMsg.textContent = 'A network error occurred.';
            errorMsg.style.display = 'block';
        }
    });
}
</script>
</body>
</html>
