<?php
require 'db.php';
require 'includes/header.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];

    $sql = "SELECT id, username, password, role FROM users WHERE username='$user'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that username.";
    }
}
?>

<div class="auth-wrap">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p>Login to your Crevix account.</p>
        <?php if($error) echo "<div class='alert alert-error'>$error</div>"; ?>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Your username">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Your password">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;margin-top:10px;">Login</button>
        </form>
        <p style="text-align:center; margin-top:20px; font-size:0.85rem; color:var(--muted);">
            <a href="forgot_password.php" style="color:var(--gold);">Forgot your password?</a>
        </p>
        <p style="text-align:center; margin-top:8px; font-size:0.85rem; color:var(--muted);">
            Don't have an account? <a href="register.php" style="color:var(--gold);">Sign Up</a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
