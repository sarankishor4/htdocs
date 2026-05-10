<?php
require 'db.php';
require 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if already exists
    $check = $conn->query("SELECT id FROM users WHERE username='$user' OR email='$email'");
    if ($check->num_rows > 0) {
        $error = "Username or email already taken.";
    } else {
        $sql = "INSERT INTO users (username, email, password) VALUES ('$user', '$email', '$pass')";
        if ($conn->query($sql)) {
            $success = "Account created! You can now login.";
        } else {
            $error = "Something went wrong: " . $conn->error;
        }
    }
}
?>

<div class="auth-wrap">
    <div class="auth-card">
        <h2>Create Account</h2>
        <p>Join Crevix and start sharing your creative work.</p>
        <?php if($error) echo "<div class='alert alert-error'>$error</div>"; ?>
        <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Choose a username">
            </div>
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="your@email.com">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Min 6 characters" minlength="6">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;margin-top:10px;">Create My Account</button>
        </form>
        <p style="text-align:center; margin-top:20px; font-size:0.85rem; color:var(--muted);">
            Already have an account? <a href="login.php" style="color:var(--gold);">Login here</a>
        </p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
