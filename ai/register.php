<?php
require_once 'core/Config.php';
require_once 'core/Auth.php';
use AI\Core\Auth;

if (Auth::isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Phaze AI | Operative Recruitment</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Montserrat:wght@800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="aurora-bg">
        <div class="aurora-orb" style="top: -10%; left: -10%; background: var(--accent-ceo);"></div>
        <div class="aurora-orb" style="bottom: -10%; right: -10%; background: var(--accent-primary);"></div>
    </div>

    <div class="auth-container">
        <div class="glass-card auth-card" style="max-width: 450px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="font-family: Montserrat; letter-spacing: -1px; font-size: 1.8rem;">NEW OPERATIVE</h2>
                <p style="color: var(--text-dim); font-size: 0.85rem; margin-top: 5px;">REQUESTING CLEARANCE FOR MATRIX ACCESS</p>
            </div>

            <form id="register-form">
                <div class="form-group">
                    <label>Operative Codename</label>
                    <input type="text" name="username" placeholder="e.g. Neo" required>
                </div>
                <div class="form-group">
                    <label>Neural Email</label>
                    <input type="email" name="email" placeholder="email@nexus.com" required>
                </div>
                <div class="form-group">
                    <label>Encryption Key (Password)</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Enlist in Matrix</button>
                
                <div style="margin-top: 25px; text-align: center; font-size: 0.8rem; color: var(--text-dim);">
                    Already cleared? <a href="login.php" style="color: var(--accent-ceo); text-decoration: none; font-weight: 700;">Login</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const response = await fetch('api/auth.php?action=register', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                alert('Clearance Granted. Proceed to Login.');
                window.location.href = 'login.php';
            } else {
                alert(data.message || "Registration failed.");
            }
        });
    </script>
</body>
</html>
