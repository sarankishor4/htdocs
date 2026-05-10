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
    <title>Phaze AI | Operative Entry</title>
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
        <div class="glass-card auth-card">
            <div style="text-align: center; margin-bottom: 35px;">
                <div style="width: 50px; height: 50px; background: var(--accent-ceo); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 15px; box-shadow: 0 0 20px rgba(255, 71, 87, 0.4);">
                    <i class="fas fa-bolt"></i>
                </div>
                <h2 style="font-family: Montserrat; letter-spacing: -1px; font-size: 1.8rem;">PHAZE AI</h2>
                <p style="color: var(--text-dim); font-size: 0.85rem; margin-top: 5px;">ENTERPRISE BUSINESS OS</p>
            </div>

            <form id="login-form">
                <div class="form-group">
                    <label>Operative ID</label>
                    <input type="text" name="username" placeholder="Username or Email" required>
                </div>
                <div class="form-group">
                    <label>Security Key</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Initialize Session</button>
                
                <div style="margin-top: 25px; text-align: center; font-size: 0.8rem; color: var(--text-dim);">
                    New operative? <a href="register.php" style="color: var(--accent-ceo); text-decoration: none; font-weight: 700;">Request Clearance</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const response = await fetch('api/auth.php?action=login', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.status === 'success') {
                window.location.href = 'index.php';
            } else {
                alert(data.message || "Invalid credentials.");
            }
        });
    </script>
</body>
</html>
