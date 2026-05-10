<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php"); exit();
}

// Promote/Demote
if (isset($_GET['promote'])) {
    $uid = (int)$_GET['promote'];
    $current = $conn->query("SELECT role FROM users WHERE id=$uid")->fetch_assoc()['role'];
    $new_role = ($current == 'admin') ? 'user' : 'admin';
    $conn->query("UPDATE users SET role='$new_role' WHERE id=$uid");
    header("Location: admin_users.php"); exit();
}

// Delete user
if (isset($_GET['delete'])) {
    $uid = (int)$_GET['delete'];
    if ($uid != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id=$uid");
    }
    header("Location: admin_users.php"); exit();
}

$users = $conn->query("SELECT users.*, (SELECT COUNT(*) FROM media WHERE user_id=users.id) as media_count, (SELECT IFNULL(SUM(views),0) FROM media WHERE user_id=users.id) as total_views FROM users ORDER BY created_at DESC");
?>

<div class="dash-layout">
    <aside class="sidebar">
        <p class="sidebar-title">Admin Panel</p>
        <a href="admin.php"><span>⚡</span> Overview</a>
        <a href="admin_users.php" class="active"><span>👥</span> All Users</a>
        <a href="admin_media.php"><span>🎬</span> All Media</a>
        <a href="sync.php"><span>📁</span> Folder Sync</a>
        <p class="sidebar-title">User</p>
        <a href="dashboard.php"><span>📊</span> My Dashboard</a>
        <a href="logout.php"><span>🚪</span> Logout</a>
    </aside>
    <main class="dash-main">
        <div class="admin-header">
            <h2>👥 Manage Users</h2>
        </div>
        <div style="background:var(--surface); border-radius:12px; overflow:hidden; border:1px solid var(--border);">
            <table>
                <thead><tr>
                    <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Uploads</th><th>Total Views</th><th>Joined</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php while($u = $users->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $u['id']; ?></td>
                    <td><strong>@<?php echo htmlspecialchars($u['username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo $u['role']; ?></span></td>
                    <td><?php echo $u['media_count']; ?></td>
                    <td><?php echo number_format($u['total_views']); ?></td>
                    <td><?php echo date("M j, Y", strtotime($u['created_at'])); ?></td>
                    <td style="display:flex; gap:6px;">
                        <a href="admin_users.php?promote=<?php echo $u['id']; ?>" class="btn-secondary" style="font-size:0.75rem; padding:4px 10px;">
                            <?php echo ($u['role']=='admin') ? '⬇ Demote' : '⬆ Promote'; ?>
                        </a>
                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                        <a href="admin_users.php?delete=<?php echo $u['id']; ?>" class="btn-danger" onclick="return confirm('Delete user and all their content?')">🗑</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require 'includes/footer.php'; ?>
