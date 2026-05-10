<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = $conn->real_escape_string(trim($_POST['bio']));

    // Password change
    if (!empty($_POST['new_password'])) {
        if (password_verify($_POST['current_password'], $user['password'])) {
            $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET bio='$bio', password='$new_pass' WHERE id=$uid");
            $message = "<div class='alert alert-success'>Profile and password updated!</div>";
        } else {
            $message = "<div class='alert alert-error'>Current password is wrong.</div>";
        }
    } else {
        $conn->query("UPDATE users SET bio='$bio' WHERE id=$uid");
        $message = "<div class='alert alert-success'>Profile updated!</div>";
    }
    $user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
}

$media_count = $conn->query("SELECT COUNT(*) as c FROM media WHERE user_id=$uid")->fetch_assoc()['c'];
$total_views = $conn->query("SELECT IFNULL(SUM(views),0) as v FROM media WHERE user_id=$uid")->fetch_assoc()['v'];
$my_media = $conn->query("SELECT * FROM media WHERE user_id=$uid ORDER BY created_at DESC LIMIT 6");
?>

<div class="profile-banner"></div>
<div class="profile-info">
    <div class="profile-avatar" style="display:flex;align-items:center;justify-content:center;background:var(--surface2);font-size:3rem;">👤</div>
    <div class="profile-name">
        <h2>@<?php echo htmlspecialchars($user['username']); ?></h2>
        <p style="color:var(--muted);"><?php echo htmlspecialchars($user['email']); ?> • <span class="badge badge-<?php echo $user['role']; ?>"><?php echo $user['role']; ?></span></p>
        <p style="color:var(--muted); font-size:0.85rem;"><?php echo $media_count; ?> uploads • <?php echo number_format($total_views); ?> total views</p>
    </div>
</div>

<div style="padding:0 60px; display:grid; grid-template-columns:1fr 1fr; gap:40px; align-items:start;">
    <!-- PROFILE EDIT -->
    <div>
        <h3 style="color:var(--gold); margin-bottom:20px;">✏️ Edit Profile</h3>
        <?php echo $message; ?>
        <form method="POST">
            <div class="input-group">
                <label>Bio</label>
                <textarea name="bio" rows="4" placeholder="Tell the world about yourself..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
            </div>
            <h4 style="margin:20px 0 12px; color:var(--muted);">Change Password</h4>
            <div class="input-group">
                <label>Current Password</label>
                <input type="password" name="current_password" placeholder="Leave blank to keep current">
            </div>
            <div class="input-group">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="New password">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;">Save Changes</button>
        </form>
    </div>

    <!-- RECENT UPLOADS -->
    <div>
        <h3 style="color:var(--gold); margin-bottom:20px;">🎬 My Recent Uploads</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
            <?php while($row = $my_media->fetch_assoc()): ?>
            <a href="watch.php?id=<?php echo $row['id']; ?>" class="media-card">
                <div class="media-card-thumb">
                    <?php if($row['thumbnail']): ?>
                        <img src="<?php echo $row['thumbnail']; ?>" alt="thumb">
                    <?php elseif($row['type']=='video'): ?>
                        <video src="<?php echo $row['file_path']; ?>" muted preload="metadata"></video>
                    <?php else: ?>
                        <img src="<?php echo $row['file_path']; ?>" alt="media">
                    <?php endif; ?>
                    <span class="media-card-type"><?php echo $row['type']; ?></span>
                </div>
                <div class="media-card-info">
                    <h3><?php echo htmlspecialchars(substr($row['title'],0,20)); ?></h3>
                    <p class="meta"><?php echo $row['views']; ?> views</p>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
        <a href="dashboard.php" class="btn-secondary" style="margin-top:16px; display:inline-block;">View All →</a>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
