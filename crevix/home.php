<?php
require 'db.php';
require 'includes/header.php';

// Fetch featured (most viewed)
$featured = $conn->query("SELECT media.*, users.username FROM media JOIN users ON media.user_id=users.id WHERE media.status='active' ORDER BY views DESC LIMIT 1");
$feat = $featured->fetch_assoc();

// Fetch recent
$recent = $conn->query("SELECT media.*, users.username FROM media JOIN users ON media.user_id=users.id WHERE media.status='active' ORDER BY created_at DESC LIMIT 8");

// Fetch popular
$popular = $conn->query("SELECT media.*, users.username FROM media JOIN users ON media.user_id=users.id WHERE media.status='active' ORDER BY views DESC LIMIT 8");
?>

<!-- HERO BANNER -->
<section class="hero-banner">
    <?php if($feat): ?>
        <?php if($feat['type'] == 'video'): ?>
            <?php $feat_url = ($feat['is_offloaded'] ? "stream.php?id=".$feat['id'] : $feat['file_path']); ?>
            <video src="<?php echo $feat_url; ?>" autoplay muted loop playsinline></video>
        <?php endif; ?>
    <?php endif; ?>
    <div class="overlay"></div>
    <div class="hero-content">
        <h1>Welcome to <span>Crevix</span></h1>
        <p>Create, upload, and stream your media in a cinematic, secure environment. Your content, your stage.</p>
        <div class="hero-actions">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="upload.php" class="btn-primary">⬆️ Upload Now</a>
                <a href="explore.php" class="btn-secondary">🔥 Explore</a>
            <?php else: ?>
                <a href="register.php" class="btn-primary">Get Started Free</a>
                <a href="explore.php" class="btn-secondary">Browse Content</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- RECENT UPLOADS -->
<section class="explore-section">
    <h2 class="section-title">🕐 Recently Uploaded</h2>
    <div class="media-grid">
        <?php while($row = $recent->fetch_assoc()): ?>
        <a href="watch.php?id=<?php echo $row['id']; ?>" class="media-card">
            <div class="media-card-thumb">
                <?php 
                $card_url = ($row['is_offloaded'] ? "stream.php?id=".$row['id'] : ($row['thumbnail'] ?: $row['file_path'])); 
                if($row['type'] == 'video'): ?>
                    <video src="<?php echo $card_url; ?>" muted preload="metadata"></video>
                <?php else: ?>
                    <img src="<?php echo $card_url; ?>" alt="media">
                <?php endif; ?>
                <span class="media-card-type"><?php echo $row['type']; ?></span>
            </div>
            <div class="media-card-info">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p class="meta">@<?php echo $row['username']; ?> • <?php echo $row['views']; ?> views • <?php echo date("M j", strtotime($row['created_at'])); ?></p>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
</section>

<!-- POPULAR -->
<section class="explore-section" style="background: var(--surface);">
    <h2 class="section-title">🔥 Trending Now</h2>
    <div class="media-grid">
        <?php while($row = $popular->fetch_assoc()): ?>
        <a href="watch.php?id=<?php echo $row['id']; ?>" class="media-card">
            <div class="media-card-thumb">
                <?php 
                $card_url = ($row['is_offloaded'] ? "stream.php?id=".$row['id'] : ($row['thumbnail'] ?: $row['file_path'])); 
                if($row['type'] == 'video'): ?>
                    <video src="<?php echo $card_url; ?>" muted preload="metadata"></video>
                <?php else: ?>
                    <img src="<?php echo $card_url; ?>" alt="media">
                <?php endif; ?>
                <span class="media-card-type"><?php echo $row['type']; ?></span>
            </div>
            <div class="media-card-info">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p class="meta">@<?php echo $row['username']; ?> • <?php echo $row['views']; ?> views</p>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
