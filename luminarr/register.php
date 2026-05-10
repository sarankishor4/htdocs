<?php require_once 'includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h2 class="brand-font" style="text-align: center; font-size: 2rem; margin-bottom: 30px;">Join Luminarr</h2>
        <form id="registerForm">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 10px;">Create Account</button>
        </form>
        <p style="text-align: center; margin-top: 20px; color: var(--text-secondary);">
            Already have an account? <a href="login.php" style="color: var(--accent);">Login here</a>
        </p>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const response = await fetch('api/auth.php?action=register', {
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
