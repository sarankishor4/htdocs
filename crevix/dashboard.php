<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$uid = $_SESSION['user_id'];

// Combined Stats Query for Performance
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM media WHERE user_id=$uid) as total_media,
        (SELECT IFNULL(SUM(views),0) FROM media WHERE user_id=$uid) as total_views,
        (SELECT COUNT(*) FROM likes JOIN media ON likes.media_id=media.id WHERE media.user_id=$uid) as total_likes,
        (SELECT COUNT(*) FROM comments JOIN media ON comments.media_id=media.id WHERE media.user_id=$uid) as total_comments,
        (SELECT COUNT(*) FROM profiles) as total_people
")->fetch_assoc();

$total_media = $stats['total_media'];
$total_views = $stats['total_views'];
$total_likes = $stats['total_likes'];
$total_comments = $stats['total_comments'];
$total_people = $stats['total_people'];

// User media
$my_media = $conn->query("SELECT * FROM media WHERE user_id=$uid ORDER BY created_at DESC");
?>

<div class="dash-layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="width:70px;height:70px;border-radius:50%;background:var(--surface2);margin:auto;display:flex;align-items:center;justify-content:center;font-size:2rem;">👤</div>
            <h3 style="margin-top:10px;font-size:1rem;"><?php echo $_SESSION['username']; ?></h3>
            <span class="badge <?php echo ($_SESSION['role']=='admin') ? 'badge-admin' : 'badge-user'; ?>"><?php echo $_SESSION['role']; ?></span>
        </div>

        <p class="sidebar-title">Navigation</p>
        <a href="home.php"><span>🏠</span> Home</a>
        <a href="dashboard.php" class="active"><span>📊</span> Dashboard</a>
        <a href="fetch.php"><span>🔗</span> Pro Fetch</a>
        <a href="bulk_fetch.php"><span>📦</span> Bulk Fetch</a>
        <a href="sync.php"><span>📁</span> Folder Sync</a>
        <a href="people.php"><span>👥</span> People Intelligence</a>
        <a href="ai_scan.php"><span>🧠</span> AI Scanning</a>
        <a href="explore.php"><span>🔥</span> Explore Videos</a>
        <a href="reels.php"><span>🎥</span> Reels Mode</a>
        <a href="photos.php"><span>🖼️</span> Photo Gallery</a>
        <a href="cloud_hub.php" style="border:1px solid rgba(212,175,55,0.2); background:rgba(212,175,55,0.05); margin:5px 0;"><span>☁️</span> Cloud Vault</a>
        <a href="profile.php"><span>⚙️</span> My Profile</a>
        <?php if($_SESSION['role'] == 'admin'): ?>
            <p class="sidebar-title">Admin</p>
            <a href="admin.php"><span>⚡</span> Admin Panel</a>
            <a href="admin_users.php"><span>👥</span> Manage Users</a>
            <a href="admin_media.php"><span>🎬</span> Manage Media</a>
        <?php endif; ?>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="dash-main">
        <h2 style="margin-bottom:24px;">Welcome back, <span style="color:var(--gold);"><?php echo $_SESSION['username']; ?></span> 👋</h2>

        <!-- STAT CARDS -->
        <div class="stat-cards">
            <div class="stat-card">
                <div class="num"><?php echo $total_media; ?></div>
                <div class="label">Total Uploads</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo number_format($total_views); ?></div>
                <div class="label">Total Views</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $total_likes; ?></div>
                <div class="label">Total Likes</div>
            </div>
            <div class="stat-card">
                <div class="num"><?php echo $total_comments; ?></div>
                <div class="label">Comments</div>
            </div>
            <div class="stat-card" style="border-color:var(--gold);">
                <div class="num"><?php echo $total_people; ?></div>
                <div class="label" style="color:var(--gold);">People Identified</div>
            </div>
        </div>

        <!-- MY MEDIA -->
        <h3 class="section-title">📁 My Uploads</h3>
        <div class="media-grid">
            <?php while($row = $my_media->fetch_assoc()): ?>
            <div class="media-card">
                <a href="watch.php?id=<?php echo $row['id']; ?>">
                    <div class="media-card-thumb">
                        <?php if($row['thumbnail']): ?>
                            <img src="<?php echo $row['thumbnail']; ?>" alt="thumb">
                        <?php elseif($row['type'] == 'video'): ?>
                            <video src="<?php echo $row['file_path']; ?>" muted preload="metadata"></video>
                        <?php else: ?>
                            <img src="<?php echo $row['file_path']; ?>" alt="media">
                        <?php endif; ?>
                        <span class="media-card-type"><?php echo $row['type']; ?></span>
                    </div>
                    <div class="media-card-info">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="meta"><?php echo $row['views']; ?> views • <?php echo $row['category']; ?> • <?php echo date("M j", strtotime($row['created_at'])); ?></p>
                    </div>
                </a>
                <div style="padding:0 14px 14px; display:flex; gap:8px;">
                    <a href="edit_media.php?id=<?php echo $row['id']; ?>" class="btn-secondary" style="font-size:0.75rem; padding:5px 12px;">✏️ Edit</a>
                    <a href="delete_media.php?id=<?php echo $row['id']; ?>" class="btn-danger" onclick="return confirm('Delete this?')">🗑️ Delete</a>
                </div>
            </div>
            <?php endwhile; ?>
            <?php if($total_media == 0): ?>
                <div style="grid-column:1/-1; text-align:center; padding:60px;">
                    <p style="font-size:3rem; margin-bottom:14px;">🎬</p>
                    <p style="color:var(--muted);">You haven't uploaded anything yet.</p>
                    <a href="upload.php" class="btn-primary" style="margin-top:16px;">Upload Your First Media</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require 'includes/footer.php'; ?>
