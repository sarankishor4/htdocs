<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$pid = (int)($_GET['id'] ?? 0);
$profile = $conn->query("SELECT * FROM profiles WHERE id=$pid")->fetch_assoc();

if (!$profile) {
    echo "<div class='container' style='padding-top:100px;text-align:center;'><h2>Profile Not Found</h2><a href='people.php' class='btn-primary'>Back to People</a></div>";
    require 'includes/footer.php';
    exit();
}

// Fetch all media where this person appears
$media_res = $conn->query("
    SELECT m.*, u.username 
    FROM media m 
    JOIN media_faces mf ON m.id = mf.media_id 
    JOIN users u ON m.user_id = u.id
    WHERE mf.profile_id = $pid AND m.status = 'active'
    ORDER BY m.created_at DESC
");
?>

<div class="profile-view-container" style="max-width:1100px; margin:40px auto; color:white;">
    <!-- PROFILE HEADER -->
    <div class="glass-card" style="padding:40px; border-radius:30px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; gap:30px; margin-bottom:40px;">
        <div style="width:150px; height:150px; border-radius:50%; overflow:hidden; border:4px solid var(--gold); box-shadow: 0 0 20px rgba(255,215,0,0.3);">
            <img src="<?php echo $profile['thumbnail'] ?: 'assets/no-avatar.jpg'; ?>" style="width:100%; height:100%; object-fit:cover;">
        </div>
        <div>
            <h1 style="color:var(--gold); font-size:2.8rem; margin:0;"><?php echo htmlspecialchars($profile['name']); ?></h1>
            <p style="color:var(--muted); font-size:1.1rem; margin-top:5px;">Appears in <?php echo $media_res->num_rows; ?> media items</p>
            <?php if($_SESSION['role'] == 'admin'): ?>
                <a href="admin_people.php" class="btn-secondary" style="margin-top:15px; display:inline-block; font-size:0.85rem;">⚙️ Manage Profile</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- APPEARANCES GRID -->
    <h2 style="margin-bottom:20px; font-size:1.5rem; color:var(--gold);">🎬 Appearances</h2>
    <div class="media-grid">
        <?php while($row = $media_res->fetch_assoc()): ?>
            <div class="media-card">
                <a href="watch.php?id=<?php echo $row['id']; ?>">
                    <div class="media-card-thumb">
                        <?php if($row['thumbnail']): ?>
                            <img src="<?php echo $row['thumbnail']; ?>" alt="thumb">
                        <?php else: ?>
                            <video src="<?php echo $row['file_path']; ?>" muted></video>
                        <?php endif; ?>
                        <span class="media-card-type"><?php echo $row['type']; ?></span>
                    </div>
                    <div class="media-card-info">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="meta">by @<?php echo htmlspecialchars($row['username']); ?> • <?php echo number_format($row['views']); ?> views</p>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
        <?php if($media_res->num_rows == 0): ?>
            <div style="grid-column:1/-1; text-align:center; padding:50px; color:var(--muted);">
                <p>No media linked to this profile yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
