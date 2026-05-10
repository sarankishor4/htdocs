<?php require_once 'includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h2 class="brand-font" style="text-align: center; font-size: 2rem; margin-bottom: 30px;">Login</h2>
        <form id="loginForm">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">Sign In</button>
        </form>
        <p style="text-align: center; margin-top: 20px; color: var(--text-secondary);">
            Don't have an account? <a href="register.php" style="color: var(--accent);">Register here</a>
        </p>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const response = await fetch('api/auth.php?action=login', {
        method: 'POST',
        body: formData
    });
    const result = await response.json();
    if (result.success) {
        window.location.href = result.redirect;
    } else {
        alert(result.error);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
