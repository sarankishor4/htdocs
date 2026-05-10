<?php
require 'db.php';
require 'includes/header.php';

$category = isset($_GET['cat']) ? $conn->real_escape_string($_GET['cat']) : '';
$search = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';

$where = "WHERE media.status='active' AND media.type='video' AND (media.duration > 60 OR media.duration = 0 OR media.duration IS NULL) AND media.category != 'Reels'";
if ($category) $where .= " AND media.category='$category'";
if ($search) $where .= " AND (media.title LIKE '%$search%' OR media.description LIKE '%$search%' OR users.username LIKE '%$search%')";

$all = $conn->query("
    SELECT media.*, users.username, 
    (SELECT COUNT(*) FROM likes WHERE media_id = media.id) as like_count 
    FROM media 
    JOIN users ON media.user_id = users.id 
    $where 
    ORDER BY created_at DESC
");

$cats = $conn->query("SELECT DISTINCT category FROM media WHERE status='active' ORDER BY category");
?>

<div class="yt-layout">
    <!-- SUB-NAV CATEGORIES -->
    <div class="category-pill-bar">
        <a href="explore.php" class="pill <?php echo (!$category) ? 'active' : ''; ?>">All</a>
        <a href="reels.php" class="pill">🎥 Reels</a>
        <a href="photos.php" class="pill">🖼️ Photos</a>
        <div class="pill-divider"></div>
        <?php while($c = $cats->fetch_assoc()): ?>
            <a href="explore.php?cat=<?php echo urlencode($c['category']); ?>" class="pill <?php echo ($category == $c['category']) ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($c['category']); ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- MAIN GRID -->
    <div class="yt-grid">
        <?php if($all->num_rows == 0): ?>
            <div style="grid-column:1/-1; text-align:center; padding:100px;">
                <p style="font-size:4rem;">🔭</p>
                <h2 style="color:var(--muted);">No videos found in this category.</h2>
                <a href="explore.php" class="btn-primary" style="margin-top:20px; display:inline-block;">Explore All Content</a>
            </div>
        <?php endif; ?>

        <?php while($row = $all->fetch_assoc()): ?>
            <div class="yt-card">
                <a href="watch.php?id=<?php echo $row['id']; ?>" class="yt-thumb-link">
                    <div class="yt-thumb">
                        <?php if($row['thumbnail']): ?>
                            <img src="<?php echo $row['thumbnail']; ?>" loading="lazy">
                        <?php else: ?>
                            <video src="<?php echo $row['file_path']; ?>" muted preload="metadata"></video>
                        <?php endif; ?>
                        <span class="duration">4:20</span>
                    </div>
                </a>
                <div class="yt-info">
                    <div class="yt-avatar">
                        <div class="avatar-circle"><?php echo strtoupper(substr($row['username'], 0, 1)); ?></div>
                    </div>
                    <div class="yt-text">
                        <h3 class="yt-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="yt-meta">@<?php echo htmlspecialchars($row['username']); ?></p>
                        <p class="yt-meta"><?php echo number_format($row['views']); ?> views • <?php echo date("M j, Y", strtotime($row['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<style>
.yt-layout {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* Category Pills */
.category-pill-bar {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding: 10px 0 25px;
    scrollbar-width: none;
    position: sticky;
    top: 0;
    z-index: 10;
    background: var(--background);
}
.category-pill-bar::-webkit-scrollbar { display: none; }
.pill {
    background: var(--surface2);
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    white-space: nowrap;
    border: 1px solid rgba(255,255,255,0.05);
    transition: all 0.2s;
}
.pill:hover { background: var(--border); }
.pill.active { background: white; color: black; }
.pill-divider { width: 1px; height: 30px; background: rgba(255,255,255,0.1); margin: 0 5px; }

/* YouTube Grid */
.yt-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 40px 20px;
}
.yt-card {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.yt-thumb {
    position: relative;
    aspect-ratio: 16 / 9;
    background: #000;
    border-radius: 12px;
    overflow: hidden;
}
.yt-thumb img, .yt-thumb video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s cubic-bezier(0.2, 0, 0, 1);
}
.yt-card:hover .yt-thumb img { transform: scale(1.05); }
.yt-thumb .duration {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.yt-info {
    display: flex;
    gap: 12px;
}
.yt-avatar {
    flex-shrink: 0;
}
.avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--gold);
    color: black;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9rem;
}
.yt-text {
    flex: 1;
}
.yt-title {
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.4;
    margin: 0 0 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    color: #f1f1f1;
}
.yt-meta {
    font-size: 0.85rem;
    color: #aaaaaa;
    margin: 0;
}
.yt-card:hover .yt-title { color: var(--gold); }
</style>

<?php require 'includes/footer.php'; ?>
