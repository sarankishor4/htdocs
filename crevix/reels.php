<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

// Optimized query to fetch Reels
$reels = $conn->query("
    SELECT media.*, users.username 
    FROM media 
    JOIN users ON media.user_id = users.id 
    WHERE media.status='active' 
    AND media.type='video' 
    ORDER BY RAND() LIMIT 20
");
?>

<div class="reels-viewport">
    <div class="reels-stack" id="reelsStack">
        <?php while($row = $reels->fetch_assoc()): ?>
            <div class="reel-container" data-id="<?php echo $row['id']; ?>">
                <!-- The Video Player (Using Smart Streamer) -->
                <video 
                    src="stream.php?id=<?php echo $row['id']; ?>" 
                    loop 
                    playsinline
                    class="reel-video" 
                    onclick="handleReelClick(this)"
                    ondblclick="handleReelLike(this, <?php echo $row['id']; ?>)">
                </video>
                
                <!-- GLASSMORPHIC OVERLAY -->
                <div class="reel-interface">
                    <div class="reel-main-info">
                        <div class="reel-author">
                            <div class="author-avatar"><?php echo strtoupper(substr($row['username'], 0, 1)); ?></div>
                            <span class="author-name">@<?php echo htmlspecialchars($row['username']); ?></span>
                            <button class="btn-follow-mini">Follow</button>
                        </div>
                        <h3 class="reel-caption"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <div class="reel-music-tag">
                            <div class="music-icon">🎵</div>
                            <div class="music-scroller">
                                <span>Original Audio - <?php echo htmlspecialchars($row['username']); ?> • Crevix Studio Mix</span>
                            </div>
                        </div>
                    </div>

                    <div class="reel-sidebar">
                        <div class="sidebar-item" onclick="toggleLike(this, <?php echo $row['id']; ?>)">
                            <div class="sidebar-icon">❤️</div>
                            <span><?php echo number_format($row['likes']); ?></span>
                        </div>
                        <div class="sidebar-item">
                            <div class="sidebar-icon">💬</div>
                            <span>452</span>
                        </div>
                        <div class="sidebar-item">
                            <div class="sidebar-icon">✈️</div>
                            <span>Share</span>
                        </div>
                        <div class="sidebar-item">
                            <div class="sidebar-icon">...</div>
                        </div>
                        <div class="sidebar-item music-disk">
                            <div class="disk-inner"><?php echo strtoupper(substr($row['username'], 0, 1)); ?></div>
                        </div>
                    </div>
                </div>

                <!-- HEART POP ANIMATION CONTAINER -->
                <div class="heart-animation-layer"></div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<style>
:root {
    --reel-width: 420px;
    --reel-height: calc(100vh - 80px);
}

.reels-viewport {
    background: #000;
    height: var(--reel-height);
    display: flex;
    justify-content: center;
    overflow: hidden;
}

.reels-stack {
    width: var(--reel-width);
    height: 100%;
    overflow-y: scroll;
    scroll-snap-type: y mandatory;
    scrollbar-width: none;
}
.reels-stack::-webkit-scrollbar { display: none; }

.reel-container {
    height: var(--reel-height);
    width: 100%;
    position: relative;
    scroll-snap-align: start;
    background: #111;
    display: flex;
    align-items: center;
    justify-content: center;
}

.reel-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
}

.reel-interface {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 30%);
    pointer-events: none;
}

.reel-main-info {
    pointer-events: auto;
    color: white;
    width: 80%;
}

.reel-author {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}
.author-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);
    border: 2px solid white;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 0.8rem;
}
.author-name { font-weight: 600; font-size: 0.95rem; }
.btn-follow-mini {
    background: transparent; border: 1px solid white; color: white;
    padding: 3px 10px; border-radius: 5px; font-size: 0.75rem; font-weight: 700;
}

.reel-caption { font-size: 0.9rem; font-weight: 400; margin-bottom: 15px; line-height: 1.4; }

.reel-music-tag {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 6px 12px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    width: fit-content;
    overflow: hidden;
}
.music-scroller {
    white-space: nowrap;
    animation: marquee 10s linear infinite;
    font-size: 0.8rem;
}
@keyframes marquee {
    0% { transform: translateX(20%); }
    100% { transform: translateX(-100%); }
}

.reel-sidebar {
    pointer-events: auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
    align-items: center;
    margin-bottom: 10px;
}
.sidebar-item {
    text-align: center;
    color: white;
    cursor: pointer;
}
.sidebar-icon {
    font-size: 1.8rem;
    margin-bottom: 4px;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));
    transition: transform 0.2s;
}
.sidebar-item:hover .sidebar-icon { transform: scale(1.1); }
.sidebar-item span { font-size: 0.75rem; font-weight: 600; }

.music-disk {
    width: 45px;
    height: 45px;
    background: #333;
    border-radius: 50%;
    border: 10px solid #222;
    animation: spin 3s linear infinite;
    display: flex; align-items: center; justify-content: center;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

.heart-animation-layer {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
    z-index: 100;
}
.floating-heart {
    font-size: 5rem;
    animation: heartPop 0.8s ease-out forwards;
    opacity: 0;
}
@keyframes heartPop {
    0% { transform: scale(0) rotate(-15deg); opacity: 0; }
    50% { transform: scale(1.2) rotate(0deg); opacity: 0.9; }
    100% { transform: scale(1) translateY(-100px); opacity: 0; }
}

/* Mobile Responsiveness */
@media (max-width: 600px) {
    .reels-stack { width: 100%; }
    :root { --reel-height: 100vh; }
}
</style>

<script>
const reelsStack = document.getElementById('reelsStack');
const videos = document.querySelectorAll('.reel-video');

// Auto-play the visible reel
const reelObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        const video = entry.target;
        if (entry.isIntersecting) {
            video.play().catch(() => {});
        } else {
            video.pause();
            video.currentTime = 0;
        }
    });
}, { threshold: 0.7 });

videos.forEach(v => reelObserver.observe(v));

function handleReelClick(v) {
    if (v.paused) v.play();
    else v.pause();
}

function handleReelLike(v, id) {
    // Show heart animation
    const layer = v.parentElement.querySelector('.heart-animation-layer');
    const heart = document.createElement('div');
    heart.className = 'floating-heart';
    heart.innerHTML = '❤️';
    layer.appendChild(heart);
    setTimeout(() => heart.remove(), 800);

    // Call like API
    fetch(`api_like.php?id=${id}`);
}

function toggleLike(btn, id) {
    const icon = btn.querySelector('.sidebar-icon');
    icon.style.color = (icon.style.color === 'red') ? 'white' : 'red';
    fetch(`api_like.php?id=${id}`);
}
</script>

<?php require 'includes/footer.php'; ?>

<?php require 'includes/footer.php'; ?>
