<?php 
require_once 'includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$db = getDB();
$user_id = $_SESSION['user_id'];

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
    if ($stmt->execute([$name, $phone, $address, $user_id])) {
        $_SESSION['user_name'] = $name; // Update session name
        $msg = "Profile updated successfully!";
    } else {
        $error = "Failed to update profile.";
    }
}

// Fetch current user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<div class="container" style="padding-top: 150px; min-height: 80vh; max-width: 800px;">
    <h1 class="brand-font" style="margin-bottom: 40px;">My Profile</h1>

    <?php if (isset($msg)): ?>
        <div style="background: rgba(0, 230, 118, 0.1); border: 1px solid var(--success); color: var(--success); padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div style="background: rgba(255, 77, 77, 0.1); border: 1px solid var(--error); color: var(--error); padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div style="background: var(--bg-secondary); padding: 40px; border: 1px solid var(--border); border-radius: 8px;">
        <form method="POST" action="profile.php">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="opacity: 0.7; cursor: not-allowed;">
                    <small style="color: var(--text-muted); display: block; margin-top: 5px;">Email cannot be changed.</small>
                </div>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="e.g. +1 234 567 8900">
            </div>

            <div class="form-group">
                <label>Default Shipping Address</label>
                <textarea name="address" rows="4" placeholder="Your full address..."><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 20px;">Save Changes</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
