<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php"); exit();
}

// Toggle status
if (isset($_GET['toggle'])) {
    $mid = (int)$_GET['toggle'];
    $current = $conn->query("SELECT status FROM media WHERE id=$mid")->fetch_assoc()['status'];
    $new_status = ($current == 'active') ? 'hidden' : 'active';
    $conn->query("UPDATE media SET status='$new_status' WHERE id=$mid");
    header("Location: admin_media.php"); exit();
}

// Delete
if (isset($_GET['delete'])) {
    $mid = (int)$_GET['delete'];
    $f = $conn->query("SELECT file_path, thumbnail FROM media WHERE id=$mid")->fetch_assoc();
    if ($f) {
        if (file_exists($f['file_path'])) unlink($f['file_path']);
        if ($f['thumbnail'] && file_exists($f['thumbnail'])) unlink($f['thumbnail']);
    }
    $conn->query("DELETE FROM media WHERE id=$mid");
    header("Location: admin_media.php"); exit();
}

$media = $conn->query("SELECT media.*, users.username FROM media JOIN users ON media.user_id=users.id ORDER BY media.created_at DESC");
?>

<div class="dash-layout">
    <aside class="sidebar">
        <p class="sidebar-title">Admin Panel</p>
        <a href="admin.php"><span>⚡</span> Overview</a>
        <a href="admin_users.php"><span>👥</span> All Users</a>
        <a href="admin_media.php" class="active"><span>🎬</span> All Media</a>
        <a href="sync.php"><span>📁</span> Folder Sync</a>
        <p class="sidebar-title">User</p>
        <a href="dashboard.php"><span>📊</span> My Dashboard</a>
        <a href="logout.php"><span>🚪</span> Logout</a>
    </aside>
    <main class="dash-main">
        <div class="admin-header">
            <h2>🎬 Manage All Media</h2>
        </div>
        <div style="background:var(--surface); border-radius:12px; overflow:hidden; border:1px solid var(--border);">
            <table>
                <thead><tr>
                    <th>ID</th><th>Thumbnail</th><th>Title</th><th>Uploader</th><th>Type</th><th>Category</th><th>Views</th><th>Likes</th><th>Status</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php while($m = $media->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $m['id']; ?></td>
                    <td>
                        <?php if($m['thumbnail']): ?>
                            <img src="<?php echo $m['thumbnail']; ?>" style="width:70px;height:45px;object-fit:cover;border-radius:6px;">
                        <?php elseif($m['type']=='video'): ?>
                            <video src="<?php echo $m['file_path']; ?>" style="width:70px;height:45px;object-fit:cover;border-radius:6px;" muted preload="metadata"></video>
                        <?php else: ?>
                            <img src="<?php echo $m['file_path']; ?>" style="width:70px;height:45px;object-fit:cover;border-radius:6px;">
                        <?php endif; ?>
                    </td>
                    <td><a href="watch.php?id=<?php echo $m['id']; ?>" style="color:var(--gold);"><?php echo htmlspecialchars(substr($m['title'],0,25)); ?>...</a></td>
                    <td>@<?php echo htmlspecialchars($m['username']); ?></td>
                    <td><span class="badge badge-<?php echo $m['type']; ?>"><?php echo $m['type']; ?></span></td>
                    <td><?php echo htmlspecialchars($m['category']); ?></td>
                    <td><?php echo number_format($m['views']); ?></td>
                    <td><?php echo $m['likes']; ?></td>
                    <td><span class="badge" style="background:<?php echo $m['status']=='active'?'rgba(0,200,0,0.15)':'rgba(200,0,0,0.15)'; ?>; color:<?php echo $m['status']=='active'?'#6bff6b':'#ff6b6b'; ?>;"><?php echo $m['status']; ?></span></td>
                    <td style="display:flex; gap:6px; flex-wrap:wrap;">
                        <a href="admin_media.php?toggle=<?php echo $m['id']; ?>" class="btn-secondary" style="font-size:0.75rem; padding:4px 10px;">Toggle</a>
                        <a href="admin_media.php?delete=<?php echo $m['id']; ?>" class="btn-danger" onclick="return confirm('Permanently delete?')">🗑</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require 'includes/footer.php'; ?>
