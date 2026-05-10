<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_GET['id'])) { header("Location: home.php"); exit(); }

$id = (int)$_GET['id'];
$conn->query("UPDATE media SET views = views + 1 WHERE id=$id");

$result = $conn->query("SELECT media.*, users.username, users.avatar, users.bio FROM media JOIN users ON media.user_id=users.id WHERE media.id=$id");
if ($result->num_rows == 0) { echo "<div class='container'><h2>Media not found.</h2></div>"; require 'includes/footer.php'; exit(); }
$media = $result->fetch_assoc();

// Check if user liked
$user_liked = false;
if (isset($_SESSION['user_id'])) {
    $lcheck = $conn->query("SELECT id FROM likes WHERE media_id=$id AND user_id=" . $_SESSION['user_id']);
    $user_liked = $lcheck->num_rows > 0;
}

// Handle like
if (isset($_POST['toggle_like']) && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    if ($user_liked) {
        $conn->query("DELETE FROM likes WHERE media_id=$id AND user_id=$uid");
        $conn->query("UPDATE media SET likes = likes - 1 WHERE id=$id");
    } else {
        $conn->query("INSERT INTO likes (media_id, user_id) VALUES ($id, $uid)");
        $conn->query("UPDATE media SET likes = likes + 1 WHERE id=$id");
    }
    header("Location: watch.php?id=$id");
    exit();
}

// Handle comment
if (isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
    $cmt = $conn->real_escape_string(trim($_POST['comment']));
    if ($cmt) {
        $conn->query("INSERT INTO comments (media_id, user_id, comment) VALUES ($id, " . $_SESSION['user_id'] . ", '$cmt')");
        header("Location: watch.php?id=$id");
        exit();
    }
}

// Refresh media data after actions
$media = $conn->query("SELECT media.*, users.username, users.avatar FROM media JOIN users ON media.user_id=users.id WHERE media.id=$id")->fetch_assoc();
$user_liked = isset($_SESSION['user_id']) && $conn->query("SELECT id FROM likes WHERE media_id=$id AND user_id=" . $_SESSION['user_id'])->num_rows > 0;

// Comments
$comments = $conn->query("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id=users.id WHERE comments.media_id=$id ORDER BY comments.created_at DESC");

// Related
$related = $conn->query("SELECT media.*, users.username FROM media JOIN users ON media.user_id=users.id WHERE media.id != $id AND media.status='active' ORDER BY RAND() LIMIT 8");
?>

<div class="watch-layout">
    <!-- LEFT: PLAYER + INFO -->
    <div>
        <div class="player-wrap" oncontextmenu="return false;">
            <?php if($media['type'] == 'video'): 
                $ext = pathinfo($media['file_path'], PATHINFO_EXTENSION);
                $mime_map = [
                    'mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg',
                    'ts' => 'video/mp2t', 'm3u8' => 'application/x-mpegURL',
                    'mov' => 'video/quicktime', 'avi' => 'video/x-msvideo', 'mkv' => 'video/x-matroska'
                ];
                $mime = $mime_map[$ext] ?? 'video/mp4';
                // If it's a Live Link (starts with http), default to mp4 as stream.php will redirect to the best mp4 stream
                if (strpos($media['file_path'], 'http') === 0) $mime = 'video/mp4';
            ?>
                <video controls controlsList="nodownload" autoplay id="mainPlayer" style="width:100%; border-radius:12px;">
                    <source src="stream.php?id=<?php echo $id; ?>" type="<?php echo $mime; ?>">
                    Your browser does not support the video tag.
                </video>
                <?php if($ext == 'm3u8'): ?>
                    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
                    <script>
                        var video = document.getElementById('mainPlayer');
                        var videoSrc = 'stream.php?id=<?php echo $id; ?>';
                        if (Hls.isSupported()) {
                            var hls = new Hls();
                            hls.loadSource(videoSrc);
                            hls.attachMedia(video);
                        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                            video.src = videoSrc;
                        }
                    </script>
                <?php endif; ?>
            <?php else: 
                $img_url = ($media['is_offloaded'] ? "stream.php?id=".$id : $media['file_path']);
            ?>
                <img src="<?php echo $img_url; ?>" alt="<?php echo htmlspecialchars($media['title']); ?>" draggable="false" style="width:100%; border-radius:12px;">
            <?php endif; ?>
        </div>

        <h1 class="video-title"><?php echo htmlspecialchars($media['title']); ?></h1>

        <div class="video-meta">
            <div class="left">
                <?php echo number_format($media['views']); ?> views • <?php echo date("F j, Y", strtotime($media['created_at'])); ?>
                • <span class="badge badge-<?php echo $media['type']; ?>"><?php echo $media['type']; ?></span>
                • <?php echo htmlspecialchars($media['category']); ?>
            </div>
            <div class="video-actions">
                <button class="action-btn" id="theaterBtn" title="Theater Mode">🎭 Theater</button>
                <button class="action-btn" id="castBtn" title="Cast to TV/Device">📺 Cast</button>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="toggle_like" class="action-btn" style="<?php echo $user_liked ? 'color:var(--gold);border-color:var(--gold);' : ''; ?>">
                        <?php echo $user_liked ? '❤️' : '🤍'; ?> <?php echo $media['likes']; ?>
                    </button>
                </form>
                <button class="action-btn" onclick="navigator.share && navigator.share({title:'<?php echo addslashes($media['title']); ?>', url:window.location.href})">
                    📤 Share
                </button>
                <?php if (isset($_SESSION['user_id']) && ($media['user_id'] == $_SESSION['user_id'] || $_SESSION['role'] == 'admin')): ?>
                    <a href="studio.php?id=<?php echo $id; ?>" class="action-btn" style="text-decoration:none; color:#00f2fe; border-color:#00f2fe;">🚀 Studio Pro</a>
                    <a href="edit_media.php?id=<?php echo $id; ?>" class="action-btn" style="text-decoration:none; color:var(--gold); border-color:var(--gold);">✏️ Edit</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- CHANNEL -->
        <div class="channel-box">
            <div class="channel-avatar" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;">👤</div>
            <div class="channel-info">
                <h4>@<?php echo htmlspecialchars($media['username']); ?></h4>
                <p>Creator on Crevix</p>
            </div>
        </div>

        <!-- DESCRIPTION -->
        <div class="desc-box">
            <?php echo nl2br(htmlspecialchars($media['description'] ?: 'No description provided.')); ?>
        </div>

        <!-- COMMENTS -->
        <div class="comments-section" style="margin-top:30px;">
            <h3><?php echo $comments->num_rows; ?> Comments</h3>

            <?php if(isset($_SESSION['user_id'])): ?>
            <form method="POST" class="comment-form" style="margin:16px 0;">
                <textarea name="comment" rows="3" placeholder="Add a comment..." required></textarea>
                <button type="submit" name="add_comment" class="btn-primary">Post Comment</button>
            </form>
            <?php else: ?>
                <p style="color:var(--muted); margin:14px 0;"><a href="login.php" style="color:var(--gold);">Login</a> to comment.</p>
            <?php endif; ?>

            <?php while($cmt = $comments->fetch_assoc()): ?>
            <div class="comment">
                <div class="comment-avatar" style="display:flex;align-items:center;justify-content:center;font-size:1rem;">👤</div>
                <div class="comment-body">
                    <span class="author">@<?php echo $cmt['username']; ?></span>
                    <span class="time"> • <?php echo date("M j, g:ia", strtotime($cmt['created_at'])); ?></span>
                    <p><?php echo nl2br(htmlspecialchars($cmt['comment'])); ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- RIGHT SIDEBAR: RELATED -->
    <aside class="sidebar-related">
        <h3>Up Next</h3>
        <?php while($r = $related->fetch_assoc()): ?>
        <a href="watch.php?id=<?php echo $r['id']; ?>" class="related-card">
            <div class="related-thumb">
                <?php 
                $rel_url = ($r['is_offloaded'] ? "stream.php?id=".$r['id'] : ($r['thumbnail'] ?: $r['file_path'])); 
                if($r['type'] == 'video'): ?>
                    <video src="<?php echo $rel_url; ?>" muted preload="metadata"></video>
                <?php else: ?>
                    <img src="<?php echo $rel_url; ?>" alt="media">
                <?php endif; ?>
            </div>
            <div class="related-info">
                <h4><?php echo htmlspecialchars($r['title']); ?></h4>
                <p>@<?php echo $r['username']; ?> • <?php echo $r['views']; ?> views</p>
            </div>
        </a>
        <?php endwhile; ?>
    </aside>
</div>

<script>
// Theater Mode
const theaterBtn = document.getElementById('theaterBtn');
const watchLayout = document.querySelector('.watch-layout');
theaterBtn.addEventListener('click', () => {
    watchLayout.classList.toggle('theater-mode');
    theaterBtn.textContent = watchLayout.classList.contains('theater-mode') ? '📺 Default' : '🎭 Theater';
});

// Casting Feature
const castBtn = document.getElementById('castBtn');
const video = document.getElementById('mainPlayer');

if (video && video.remote) {
    video.remote.addEventListener('connecting', () => { castBtn.textContent = '⏳ Connecting...'; });
    video.remote.addEventListener('connect', () => { castBtn.textContent = '✅ Casting'; });
    video.remote.addEventListener('disconnect', () => { castBtn.textContent = '📺 Cast'; });
}

castBtn.addEventListener('click', () => {
    if (!video) return;
    
    // 1. Try Native Remote Playback API (Chrome/Safari)
    if (video.remote && video.remote.prompt) {
        video.remote.prompt().catch(err => {
            console.log('Remote Playback error:', err);
            fallbackCast();
        });
    } 
    // 2. Try Webkit (Safari older)
    else if (video.webkitShowPlaybackTargetPicker) {
        video.webkitShowPlaybackTargetPicker();
    }
    // 3. Fallback
    else {
        fallbackCast();
    }
});

function fallbackCast() {
    alert("Casting not natively supported in this browser. \n\nTip: You can use the 'Share' button to send this link to your TV or use Chrome's built-in 'Cast...' menu (Right Click > Cast).");
}
</script>

<style>
.theater-mode {
    display: block !important;
}
.theater-mode .player-wrap {
    margin-bottom: 20px;
    width: 100vw;
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
    border-radius: 0;
}
.theater-mode .player-wrap video {
    border-radius: 0 !important;
    max-height: 80vh;
    background: #000;
}
</style>

<?php require 'includes/footer.php'; ?>
