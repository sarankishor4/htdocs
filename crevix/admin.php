<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php"); exit();
}

// Stats
$total_users  = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_media  = $conn->query("SELECT COUNT(*) as c FROM media")->fetch_assoc()['c'];
$total_views  = $conn->query("SELECT IFNULL(SUM(views),0) as v FROM media")->fetch_assoc()['v'];
$total_comments = $conn->query("SELECT COUNT(*) as c FROM comments")->fetch_assoc()['c'];

// Recent users
$recent_users = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 6");
// Recent media
$recent_media = $conn->query("SELECT media.*, users.username FROM media JOIN users ON media.user_id=users.id ORDER BY media.created_at DESC LIMIT 6");
?>

<div class="dash-layout">
    <aside class="sidebar">
        <p class="sidebar-title">Admin Panel</p>
        <a href="admin.php" class="active"><span>⚡</span> Overview</a>
        <a href="admin_users.php"><span>👥</span> All Users</a>
        <a href="admin_media.php"><span>🎬</span> All Media</a>
        <a href="sync.php"><span>📁</span> Folder Sync</a>
        <p class="sidebar-title">User</p>
        <a href="dashboard.php"><span>📊</span> My Dashboard</a>
        <a href="logout.php"><span>🚪</span> Logout</a>
    </aside>

    <main class="dash-main">
        <div class="admin-header">
            <h2>⚡ Admin Overview</h2>
            <span style="color:var(--muted); font-size:0.85rem;">Logged in as <strong style="color:var(--gold);"><?php echo $_SESSION['username']; ?></strong></span>
        </div>

        <!-- STAT CARDS -->
        <div class="stat-cards">
            <div class="stat-card">
                <div class="num"><?php echo $total_users; ?></div>
                <div class="label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $total_media; ?></div>
                <div class="label">Total Media</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo number_format($total_views); ?></div>
                <div class="label">Total Views</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $total_comments; ?></div>
                <div class="label">Comments</div>
            </div>
        </div>

        <!-- RECENT USERS TABLE -->
        <h3 class="section-title" style="margin-bottom:14px;">👥 Recent Users</h3>
        <div style="background:var(--surface); border-radius:12px; overflow:hidden; border:1px solid var(--border); margin-bottom:36px;">
            <table>
                <thead><tr>
                    <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php while($u = $recent_users->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $u['id']; ?></td>
                    <td><strong>@<?php echo htmlspecialchars($u['username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo $u['role']; ?></span></td>
                    <td><?php echo date("M j, Y", strtotime($u['created_at'])); ?></td>
                    <td>
                        <a href="admin_users.php?promote=<?php echo $u['id']; ?>" class="btn-secondary" style="font-size:0.75rem; padding:4px 10px;">
                            <?php echo ($u['role']=='admin') ? '⬇ Demote' : '⬆ Promote'; ?>
                        </a>
                        <a href="admin_users.php?delete=<?php echo $u['id']; ?>" class="btn-danger" onclick="return confirm('Delete this user?')" style="margin-left:6px;">🗑</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <a href="admin_users.php" class="btn-secondary" style="margin-bottom:36px; display:inline-block;">View All Users →</a>

        <!-- RECENT MEDIA TABLE -->
        <h3 class="section-title" style="margin-bottom:14px;">🎬 Recent Media</h3>
        <div style="background:var(--surface); border-radius:12px; overflow:hidden; border:1px solid var(--border); margin-bottom:24px;">
            <table>
                <thead><tr>
                    <th>ID</th><th>Title</th><th>Uploader</th><th>Type</th><th>Views</th><th>Status</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php while($m = $recent_media->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $m['id']; ?></td>
                    <td><a href="watch.php?id=<?php echo $m['id']; ?>" style="color:var(--gold);"><?php echo htmlspecialchars(substr($m['title'],0,30)); ?>...</a></td>
                    <td>@<?php echo htmlspecialchars($m['username']); ?></td>
                    <td><span class="badge badge-<?php echo $m['type']; ?>"><?php echo $m['type']; ?></span></td>
                    <td><?php echo number_format($m['views']); ?></td>
                    <td><span class="badge" style="background:<?php echo $m['status']=='active'?'rgba(0,200,0,0.15)':'rgba(200,0,0,0.15)'; ?>; color:<?php echo $m['status']=='active'?'#6bff6b':'#ff6b6b'; ?>;"><?php echo $m['status']; ?></span></td>
                    <td>
                        <a href="admin_media.php?toggle=<?php echo $m['id']; ?>" class="btn-secondary" style="font-size:0.75rem; padding:4px 10px;">Toggle</a>
                        <a href="admin_media.php?delete=<?php echo $m['id']; ?>" class="btn-danger" onclick="return confirm('Delete?')" style="margin-left:6px;">🗑</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <a href="admin_media.php" class="btn-secondary" style="display:inline-block;">View All Media →</a>
    </main>
</div>

<?php require 'includes/footer.php'; ?>
