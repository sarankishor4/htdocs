<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit(); }

if (isset($_POST['update_name'])) {
    $pid = (int)$_POST['profile_id'];
    $name = $conn->real_escape_string($_POST['new_name']);
    $conn->query("UPDATE profiles SET name='$name' WHERE id=$pid");
}

if (isset($_POST['delete_profile'])) {
    $pid = (int)$_POST['profile_id'];
    $conn->query("DELETE FROM profiles WHERE id=$pid");
}

$profiles = $conn->query("SELECT p.*, (SELECT COUNT(*) FROM media_faces WHERE profile_id=p.id) as media_count FROM profiles p ORDER BY p.id DESC");
?>

<div class="admin-container" style="max-width:1000px; margin:40px auto; color:white;">
    <h1 style="color:var(--gold);">⚙️ People Administration</h1>
    <p style="color:var(--muted);">Manage identified individuals and correct AI matches.</p>

    <div class="glass-card" style="margin-top:30px; overflow:hidden; border-radius:15px; border:1px solid rgba(255,255,255,0.1);">
        <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead style="background:rgba(0,0,0,0.4); color:var(--gold);">
                <tr>
                    <th style="padding:15px;">Face</th>
                    <th>Name</th>
                    <th>Appearances</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($p = $profiles->fetch_assoc()): ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:15px;">
                            <img src="<?php echo $p['thumbnail'] ?: 'assets/no-avatar.jpg'; ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
                        </td>
                        <td>
                            <form method="POST" style="display:flex; gap:10px;">
                                <input type="hidden" name="profile_id" value="<?php echo $p['id']; ?>">
                                <input type="text" name="new_name" value="<?php echo htmlspecialchars($p['name']); ?>" style="padding:5px 10px; background:rgba(0,0,0,0.2); border:1px solid var(--surface2); color:white; border-radius:5px;">
                                <button type="submit" name="update_name" class="btn-primary" style="padding:5px 10px; font-size:0.8rem;">Update</button>
                            </form>
                        </td>
                        <td><?php echo $p['media_count']; ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Delete this profile and all matches?');">
                                <input type="hidden" name="profile_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" name="delete_profile" style="background:none; border:none; color:#ff6b6b; cursor:pointer;">🗑 Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
