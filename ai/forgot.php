<?php
require_once 'core/Config.php';
require_once 'core/Auth.php';
use AI\Core\Auth;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nexus Recovery | Password Reset</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Montserrat:wght@800&display=swap" rel="stylesheet">
    <style>
        .auth-container { display: flex; align-items: center; justify-content: center; height: 100vh; }
        .auth-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 24px;
            width: 100%;
            max-width: 400px;
            box-shadow: var(--glass-shadow);
        }
        .auth-card h2 { font-family: var(--font-header); text-align: center; margin-bottom: 30px; letter-spacing: 2px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.7rem; color: var(--text-dim); margin-bottom: 8px; text-transform: uppercase; }
        .form-group input {
            width: 100%;
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--glass-border);
            padding: 12px;
            border-radius: 10px;
            color: #fff;
            outline: none;
        }
    </style>
</head>
<body class="nexus-body">
    <div class="aurora-bg">
        <div class="aurora-orb" style="top: -10%; left: -10%; background: var(--nexus-primary);"></div>
        <div class="aurora-orb" style="bottom: -10%; right: -10%; background: var(--nexus-secondary);"></div>
    </div>

    <div class="auth-container">
        <div class="auth-card">
            <h2>RECOVER KEY</h2>
            <p style="font-size: 0.8rem; color: var(--text-dim); text-align: center; margin-bottom: 25px;">Enter your operative email to receive an encryption recovery token.</p>
            <form id="forgot-form">
                <div class="form-group">
                    <label>Operational Email</label>
                    <input type="email" name="email" required>
                </div>
                <button type="submit" class="btn-summon">SEND RECOVERY KEY</button>
                <div style="margin-top: 20px; text-align: center; font-size: 0.8rem; color: var(--text-dim);">
                    Remembered it? <a href="login.php" style="color: var(--nexus-primary);">Return to Login</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('forgot-form').addEventListener('submit', (e) => {
            e.preventDefault();
            alert('Recovery signal sent. Please check your simulated inbox.');
            // In a real app, this would trigger an email.
        });
    </script>
</body>
</html>
