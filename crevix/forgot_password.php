<?php
require 'db.php';
require 'includes/header.php';

$error = '';
$success = '';
$step = 'email'; // email -> token -> reset

if (isset($_GET['token'])) {
    $step = 'reset';
    $token = $conn->real_escape_string($_GET['token']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_password'])) {
        $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $check = $conn->query("SELECT id FROM users WHERE reset_token='$token' AND reset_expires > NOW()");
        if ($check->num_rows > 0) {
            $uid = $check->fetch_assoc()['id'];
            $conn->query("UPDATE users SET password='$new_pass', reset_token=NULL, reset_expires=NULL WHERE id=$uid");
            $success = "Password reset successfully! You can now <a href='login.php' style='color:var(--gold)'>login</a>.";
            $step = 'done';
        } else {
            $error = "Invalid or expired reset link.";
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");

    if ($check->num_rows > 0) {
        $uid = $check->fetch_assoc()['id'];
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $conn->query("UPDATE users SET reset_token='$token', reset_expires='$expires' WHERE id=$uid");

        $reset_link = "http://localhost/crevix/forgot_password.php?token=$token";
        $success = "Reset link generated! <br><br>
            <div style='background:var(--surface2); padding:14px; border-radius:8px; word-break:break-all; font-size:0.85rem;'>
            <strong>Copy this link:</strong><br><a href='$reset_link' style='color:var(--gold);'>$reset_link</a></div>
            <br><small style='color:var(--muted)'>In production, this would be emailed to you.</small>";
        $step = 'done';
    } else {
        $error = "No account found with that email.";
    }
}
?>

<div class="auth-wrap">
    <div class="auth-card">
        <?php if($step == 'email'): ?>
            <h2>Forgot Password</h2>
            <p>Enter your email and we'll generate a reset link.</p>
            <?php if($error) echo "<div class='alert alert-error'>$error</div>"; ?>
            <form method="POST">
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="your@email.com">
                </div>
                <button type="submit" class="btn-primary" style="width:100%;">Get Reset Link</button>
            </form>

        <?php elseif($step == 'reset'): ?>
            <h2>Set New Password</h2>
            <p>Enter your new password below.</p>
            <?php if($error) echo "<div class='alert alert-error'>$error</div>"; ?>
            <form method="POST">
                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required placeholder="Min 6 characters" minlength="6">
                </div>
                <button type="submit" class="btn-primary" style="width:100%;">Reset Password</button>
            </form>

        <?php elseif($step == 'done'): ?>
            <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php endif; ?>

        <p style="text-align:center; margin-top:20px; font-size:0.85rem; color:var(--muted);">
            <a href="login.php" style="color:var(--gold);">← Back to Login</a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
