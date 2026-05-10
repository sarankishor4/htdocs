<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_GET['url'])) { header("Location: photos.php"); exit(); }

$url = $_GET['url'];
$res = $conn->query("
    SELECT media.*, users.username 
    FROM media 
    JOIN users ON media.user_id = users.id 
    WHERE media.original_url = '" . $conn->real_escape_string($url) . "' 
    AND media.status='active'
    ORDER BY media.id ASC
");

$items = [];
$title = "Gallery Collection";
$username = "System";
while ($row = $res->fetch_assoc()) {
    $title = $row['title']; // Take title from any item
    $username = $row['username'];
    
    $is_cloud = (bool)$row['is_offloaded'];
    $stream_url = $is_cloud ? "stream.php?id=" . $row['id'] : $row['file_path'];
    
    $items[] = [
        'id' => $row['id'],
        'path' => $stream_url,
        'type' => $row['type'],
        'thumb' => $stream_url,
        'created_at' => $row['created_at']
    ];
}

if (empty($items)) { header("Location: photos.php"); exit(); }
?>

<div class="gallery-view-container">
    <div class="gallery-header">
        <a href="photos.php" class="back-link">← Back to Gallery</a>
        <h1><?php echo htmlspecialchars($title); ?></h1>
        <div class="gallery-info">
            <span class="badge">📸 <?php echo count($items); ?> Photos</span>
            <span class="author">by @<?php echo htmlspecialchars($username); ?></span>
        </div>
    </div>

    <div class="gallery-grid">
        <?php foreach ($items as $idx => $item): ?>
            <div class="gallery-tile" onclick="openLightbox(<?php echo $idx; ?>)">
                <img src="<?php echo $item['thumb']; ?>" loading="lazy" alt="item">
                <div class="tile-overlay">
                    <span class="zoom-icon">🔍</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- SHARED LIGHTBOX -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="close-lightbox" onclick="closeLightbox()">&times;</span>
    <div class="lightbox-container" onclick="event.stopPropagation()">
        <div class="lightbox-main">
            <span id="slide-index" class="slide-indicator">1 / 1</span>
            <button class="nav-btn prev" onclick="changeSlide(-1)">&#10094;</button>
            <div id="slide-container" class="slide-container"></div>
            <button class="nav-btn next" onclick="changeSlide(1)">&#10095;</button>
        </div>
        <div class="lightbox-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
                <h3><?php echo htmlspecialchars($username); ?></h3>
            </div>
            <div class="sidebar-content">
                <h2><?php echo htmlspecialchars($title); ?></h2>
                <div class="thumb-tray" id="thumb-tray"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .gallery-view-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 80px 20px;
    }
    .gallery-header {
        margin-bottom: 50px;
    }
    .back-link {
        color: var(--gold);
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 20px;
        display: block;
    }
    .gallery-header h1 {
        font-size: 3rem;
        margin: 0 0 15px;
        color: white;
        letter-spacing: -1px;
    }
    .gallery-info {
        display: flex;
        gap: 20px;
        align-items: center;
    }
    .badge {
        background: rgba(212, 175, 55, 0.1);
        color: var(--gold);
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 700;
        border: 1px solid rgba(212, 175, 55, 0.2);
    }
    .author {
        color: #888;
        font-weight: 600;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
    }
    .gallery-tile {
        position: relative;
        aspect-ratio: 1/1;
        border-radius: 12px;
        overflow: hidden;
        background: #111;
        cursor: pointer;
    }
    .gallery-tile img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s;
    }
    .gallery-tile:hover img {
        transform: scale(1.1);
    }
    .tile-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .gallery-tile:hover .tile-overlay {
        opacity: 1;
    }
    .zoom-icon {
        font-size: 1.5rem;
        color: white;
    }

    /* Lightbox Styles (Copied/Shared for consistency) */
    .lightbox { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; justify-content: center; align-items: center; backdrop-filter: blur(40px); }
    .lightbox-container { display: flex; width: 90%; max-width: 1100px; height: 85vh; background: #000; border-radius: 20px; overflow: hidden; box-shadow: 0 50px 100px rgba(0,0,0,0.8); border: 1px solid rgba(255,255,255,0.1); }
    .lightbox-main { flex: 1; position: relative; background: #000; display: flex; flex-direction: column; justify-content: center; overflow: hidden; }
    .slide-container { display: flex; transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); width: 100%; height: 100%; }
    .slide-container img { flex-shrink: 0; width: 100%; height: 100%; object-fit: contain; }
    .nav-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
    .nav-btn.prev { left: 20px; }
    .nav-btn.next { right: 20px; }
    .lightbox-sidebar { width: 380px; background: #111; display: flex; flex-direction: column; border-left: 1px solid rgba(255,255,255,0.1); }
    .sidebar-header { padding: 20px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .sidebar-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--gold); color: black; display: flex; align-items: center; justify-content: center; font-weight: 800; }
    .sidebar-content { flex: 1; padding: 20px; overflow-y: auto; }
    .thumb-tray { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }
    .tray-item { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer; opacity: 0.4; border: 2px solid transparent; transition: 0.2s; }
    .tray-item.active { opacity: 1; border-color: var(--gold); }
    .slide-indicator { position: absolute; top: 20px; right: 20px; background: rgba(0,0,0,0.5); padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; color: white; z-index: 10; }
    .close-lightbox { position: absolute; top: 20px; right: 420px; color: white; font-size: 2.5rem; cursor: pointer; z-index: 10001; }
</style>

<script>
    let currentSlide = 0;
    const items = <?php echo json_encode($items); ?>;

    function openLightbox(index) {
        currentSlide = index;
        const container = document.getElementById('slide-container');
        const tray = document.getElementById('thumb-tray');

        container.innerHTML = items.map(item => `<img src="${item.path}">`).join('');
        tray.innerHTML = items.map((item, i) => `<img src="${item.thumb}" class="tray-item ${i === currentSlide ? 'active' : ''}" onclick="goToSlide(${i})">`).join('');

        document.getElementById('lightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        updateSlider();
    }

    function changeSlide(dir) {
        currentSlide = (currentSlide + dir + items.length) % items.length;
        updateSlider();
    }

    function goToSlide(index) {
        currentSlide = index;
        updateSlider();
    }

    function updateSlider() {
        document.getElementById('slide-container').style.transform = `translateX(-${currentSlide * 100}%)`;
        document.getElementById('slide-index').innerText = `${currentSlide + 1} / ${items.length}`;
        
        document.querySelectorAll('.tray-item').forEach((img, i) => {
            img.classList.toggle('active', i === currentSlide);
        });
    }

    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
</script>

<?php require 'includes/footer.php'; ?>
