<?php
require 'db.php';
require 'includes/header.php';

// Group by original_url to create Instagram-style carousels
$res = $conn->query("
    SELECT media.*, users.username 
    FROM media 
    JOIN users ON media.user_id = users.id 
    WHERE media.status='active'
    ORDER BY RAND()
");

$posts = [];
$posts = [];
while ($row = $res->fetch_assoc()) {
    $key = $row['original_url'] ?: 'single_' . $row['id'];
    if (!isset($posts[$key])) {
        $posts[$key] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'username' => $row['username'],
            'created_at' => $row['created_at'],
            'original_url' => $row['original_url'],
            'items' => []
        ];
    }
    if ($row['type'] === 'video') continue; // STRICT PHOTO ONLY POLICY
    
    $is_cloud = (bool)$row['is_offloaded'];
    $stream_url = $is_cloud ? "stream.php?id=" . $row['id'] : $row['file_path'];

    $posts[$key]['items'][] = [
        'id' => $row['id'],
        'path' => $stream_url,
        'type' => $row['type'],
        'thumb' => $stream_url // For cloud, use the same high-res stream for thumb
    ];
}

// Handle Filtering and Standalone Video/Empty Post Suppression
$posts = array_filter($posts, function($p) {
    // Hide posts that have no photo items (either originally videos or now empty)
    if (empty($p['items'])) {
        return false;
    }
    return true;
});

$posts = array_values($posts);

$filter = $_GET['filter'] ?? 'all';
if ($filter === 'single') {
    $posts = array_filter($posts, function ($p) {
        return count($p['items']) === 1;
    });
} elseif ($filter === 'carousel') {
    $posts = array_filter($posts, function ($p) {
        return count($p['items']) > 1;
    });
}
$posts = array_values($posts);
?>

<div class="photos-layout">
    <div style="margin-bottom:50px; text-align:center;">
        <h1 style="color:var(--gold); font-size:3.5rem; margin:0; font-weight:800; letter-spacing:-2px;">✨ Creative Gallery</h1>
        <p style="color:var(--muted); font-size:1.1rem; margin-bottom:30px;">A curated selection of premium visuals and multi-part stories.</p>

        <div class="filter-bar">
            <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All Media</a>
            <a href="?filter=single" class="filter-btn <?php echo $filter === 'single' ? 'active' : ''; ?>">Single Photos</a>
            <a href="?filter=carousel" class="filter-btn <?php echo $filter === 'carousel' ? 'active' : ''; ?>">Carousels</a>
        </div>
    </div>

    <div class="masonry-grid">
        <?php if (empty($posts)): ?>
            <div style="grid-column:1/-1; text-align:center; padding:100px;">
                <p style="font-size:4rem;">🖼️</p>
                <h2 style="color:var(--muted);">No photos found in the gallery.</h2>
            </div>
        <?php endif; ?>

        <?php $idx = 0;
        foreach ($posts as $post): 
            $count = count($post['items']);
            $is_large = ($count > 10);
            $gallery_url = $post['items'][0]['original_url'] ?? '';
            $onclick = $is_large ? "window.location='gallery_view.php?url=".urlencode($post['original_url'])."'" : "openLightbox(".($idx++).")";
        ?>
            <div class="masonry-item" onclick='<?php echo $onclick; ?>'>
                <div class="card-inner <?php echo ($count > 1) ? 'is-carousel' : ''; ?>">
                    <img src="<?php echo $post['items'][0]['thumb']; ?>" loading="lazy" alt="gallery">

                    <?php if (count($post['items']) > 1): ?>
                        <div class="carousel-stack-effect"></div>
                        <div class="carousel-badge">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="white">
                                <path d="M19 15V5c0-1.1-.9-2-2-2H7c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2zm-2 0H7V5h10v10zm4-12v14c0 1.1-.9 2-2 2H5c-1.1 0-2-.9-2-2V7c0-1.1.9-2 2-2h1V3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V3h-2z" />
                            </svg>
                            <span class="count"><?php echo $is_large ? "View All ($count)" : $count; ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="masonry-overlay">
                        <div class="masonry-info">
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p>by @<?php echo htmlspecialchars($post['username']); ?></p>
                        </div>
                        <div class="quick-actions">
                            <button class="action-btn">❤️</button>
                            <button class="action-btn">🔖</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- PREMIUM INSTAGRAM SPLIT-VIEW LIGHTBOX -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="close-lightbox" onclick="closeLightbox()">&times;</span>

    <div class="lightbox-container" onclick="event.stopPropagation()">
        <!-- Media Side -->
        <div class="lightbox-main">
            <span id="slide-index" class="slide-indicator">1 / 1</span>
            <button class="nav-btn prev" onclick="changeSlide(-1)">&#10094;</button>
            <div id="slide-container" class="slide-container">
                <!-- Images injected here -->
            </div>
            <button class="nav-btn next" onclick="changeSlide(1)">&#10095;</button>
        </div>

        <!-- Info Sidebar (Instagram Style) -->
        <div class="lightbox-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-avatar" id="sidebar-avatar">U</div>
                <h3 id="sidebar-username">username</h3>
                <button style="margin-left:auto; background:none; border:none; color:var(--gold); font-weight:700; cursor:pointer;">Follow</button>
            </div>

            <div class="sidebar-content">
                <h2 id="lightbox-title" style="margin-bottom:10px;"></h2>
                <p id="lightbox-description" style="color:#ccc; font-size:0.9rem;"></p>

                <div class="thumb-tray" id="thumb-tray" style="margin-top:30px;">
                    <!-- Thumbs injected here -->
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="action-icons">
                    <span class="action-icon">❤️</span>
                    <span class="action-icon">💬</span>
                    <span class="action-icon">✈️</span>
                    <span class="action-icon" style="margin-left:auto;">🔖</span>
                </div>
                <div class="likes-count">Highly engaging post</div>
                <div class="post-date" id="lightbox-date">Just now</div>
            </div>
        </div>
    </div>
</div>

<style>
    .photos-layout {
        max-width: 1400px;
        margin: 0 auto;
        padding: 60px 20px;
    }

    .filter-bar {
        display: inline-flex;
        background: rgba(255, 255, 255, 0.05);
        padding: 6px;
        border-radius: 40px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        margin-bottom: 20px;
    }

    .filter-btn {
        padding: 10px 25px;
        border-radius: 30px;
        color: #888;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    .filter-btn:hover {
        color: #fff;
    }

    .filter-btn.active {
        background: var(--gold);
        color: #000;
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
    }

    /* Pinterest Masonry */
    .masonry-grid {
        columns: 8 180px;
        /* Denser, smaller items like Pinterest */
        column-gap: 12px;
    }

    .masonry-item {
        margin-bottom: 15px;
        break-inside: avoid;
        cursor: zoom-in;
    }

    .card-inner {
        position: relative;
        border-radius: 16px;
        background: var(--surface2);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        /* Removed overflow:hidden to allow stack to show */
    }

    .card-inner img {
        width: 100%;
        display: block;
        height: auto;
        border-radius: 16px;
        /* Moved radius here */
        position: relative;
        z-index: 1;
    }

    .card-inner.is-carousel::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 5%;
        width: 90%;
        height: 100%;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        z-index: -1;
        transition: all 0.3s;
    }

    .carousel-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        color: white;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.7rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
        z-index: 5;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .masonry-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 60%, rgba(0, 0, 0, 0.9) 100%);
        opacity: 0;
        transition: opacity 0.3s;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        padding: 15px;
    }

    .masonry-item:hover .masonry-overlay {
        opacity: 1;
    }

    .masonry-info h3 {
        font-size: 0.9rem;
        margin: 0;
        color: white;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }

    .masonry-info p {
        font-size: 0.75rem;
        color: var(--gold);
        margin: 2px 0 0;
    }

    .quick-actions {
        display: flex;
        gap: 6px;
    }

    .action-btn {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        backdrop-filter: blur(5px);
        transition: background 0.2s;
    }

    .action-btn:hover {
        background: var(--gold);
        color: black;
    }

    /* Premium Instagram Split-View Lightbox */
    .lightbox {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 10000;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(40px);
    }

    .lightbox-container {
        display: flex;
        width: 90%;
        max-width: 1100px;
        height: 85vh;
        background: #000;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 50px 100px rgba(0, 0, 0, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Left side: Media */
    .lightbox-main {
        flex: 1;
        position: relative;
        background: #000;
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
        /* CRITICAL: Prevents second image from showing next to the first */
    }

    .slide-container {
        display: flex;
        transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        width: 100%;
        height: 100%;
    }

    .slide-container img,
    .slide-container video {
        flex-shrink: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #000;
    }

    .nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: all 0.3s;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .nav-btn:hover {
        background: var(--gold);
        color: black;
        transform: translateY(-50%) scale(1.1);
    }

    .nav-btn.prev {
        left: 20px;
    }

    .nav-btn.next {
        right: 20px;
    }

    .slide-indicator {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.6);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        z-index: 10;
        font-weight: 600;
        backdrop-filter: blur(5px);
    }

    /* Right side: Info */
    .lightbox-sidebar {
        width: 380px;
        background: #111;
        display: flex;
        flex-direction: column;
        border-left: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sidebar-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--gold);
        color: black;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.8rem;
    }

    .sidebar-header h3 {
        font-size: 0.9rem;
        margin: 0;
        color: white;
    }

    .sidebar-content {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        scrollbar-width: none;
    }

    .sidebar-content::-webkit-scrollbar {
        display: none;
    }

    .sidebar-content h2 {
        color: white;
        font-size: 1.1rem;
        margin: 0 0 10px;
        font-weight: 700;
    }

    .sidebar-content p {
        color: #aaa;
        font-size: 0.9rem;
        line-height: 1.6;
        margin: 0;
    }

    .tray-item {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        opacity: 0.4;
        transition: all 0.2s;
        border: 2px solid transparent;
    }

    .tray-item.active {
        opacity: 1;
        border-color: var(--gold);
        transform: scale(1.1);
    }

    .thumb-tray {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .sidebar-footer {
        padding: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .action-icons {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
        font-size: 1.4rem;
    }

    .action-icon {
        cursor: pointer;
        transition: transform 0.1s;
    }

    .action-icon:hover {
        transform: scale(1.1);
        color: var(--gold);
    }

    .likes-count {
        color: white;
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .post-date {
        color: #666;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    /* Overlays inside main */
    .slide-indicator {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.5);
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        color: white;
        z-index: 5;
    }

    .nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        backdrop-filter: blur(5px);
    }

    .nav-btn.prev {
        left: 15px;
    }

    .nav-btn.next {
        right: 15px;
    }

    @media (max-width: 900px) {
        .lightbox-container {
            flex-direction: column;
            height: 95vh;
        }

        .lightbox-sidebar {
            width: 100%;
            height: auto;
            border-left: none;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
    }
</style>

<script>
    let currentPostIndex = 0;
    let currentSlide = 0;
    let galleryPosts = <?php echo json_encode(array_values($posts)); ?>;

    function openLightbox(index) {
        currentPostIndex = index;
        currentSlide = 0;
        loadPostData();
        document.getElementById('lightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function loadPostData() {
        const post = galleryPosts[currentPostIndex];
        const items = post.items;

        const container = document.getElementById('slide-container');
        const tray = document.getElementById('thumb-tray');

        container.innerHTML = items.map(item => {
            if (item.type === 'video') {
                return `<video src="${item.path}" controls autoplay muted style="min-width:100%; height:100%; object-fit:contain;"></video>`;
            } else {
                return `<img src="${item.path}">`;
            }
        }).join('');

        tray.innerHTML = items.map((item, i) => {
            const thumbSrc = item.thumb || item.path;
            return `<img src="${thumbSrc}" class="tray-item ${i === currentSlide ? 'active' : ''}" onclick="goToSlide(${i})">`;
        }).join('');

        document.getElementById('lightbox-title').innerText = post.title;
        document.getElementById('sidebar-username').innerText = post.username;
        document.getElementById('sidebar-avatar').innerText = post.username.charAt(0).toUpperCase();
        document.getElementById('lightbox-description').innerText = post.title;
        document.getElementById('lightbox-date').innerText = new Date(post.created_at).toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });

        updateSlider();
    }

    function changeSlide(dir) {
        const post = galleryPosts[currentPostIndex];
        const totalSlides = post.items.length;

        if (dir === 1 && currentSlide === totalSlides - 1) {
            // Go to next post
            if (currentPostIndex < galleryPosts.length - 1) {
                currentPostIndex++;
                currentSlide = 0;
                loadPostData();
            }
        } else if (dir === -1 && currentSlide === 0) {
            // Go to prev post
            if (currentPostIndex > 0) {
                currentPostIndex--;
                currentSlide = galleryPosts[currentPostIndex].items.length - 1;
                loadPostData();
            }
        } else {
            currentSlide = (currentSlide + dir + totalSlides) % totalSlides;
            updateSlider();
        }
    }

    function updateSlider() {
        const container = document.getElementById('slide-container');
        container.style.transform = `translateX(-${currentSlide * 100}%)`;

        // Update indicator
        const post = galleryPosts[currentPostIndex];
        const items = post.items;

        document.getElementById('slide-index').innerText = `${currentSlide + 1} / ${items.length}`;
        document.getElementById('slide-index').style.display = items.length > 1 ? 'block' : 'none';

        // Update active thumb
        const trayItems = document.querySelectorAll('.tray-item');
        trayItems.forEach((img, i) => {
            img.classList.toggle('active', i === currentSlide);
        });

        // Nav buttons opacity
        const hasNext = (currentPostIndex < galleryPosts.length - 1 || currentSlide < items.length - 1);
        const hasPrev = (currentPostIndex > 0 || currentSlide > 0);

        document.querySelector('.nav-btn.next').style.opacity = hasNext ? '1' : '0.2';
        document.querySelector('.nav-btn.prev').style.opacity = hasPrev ? '1' : '0.2';

        // Pause all videos and play current one if applicable
        container.querySelectorAll('video').forEach(v => {
            v.pause();
            v.currentTime = 0;
        });
        const currentMedia = container.children[currentSlide];
        if (currentMedia && currentMedia.tagName === 'VIDEO') {
            currentMedia.play();
        }
    }


    function goToSlide(index) {
        currentSlide = index;
        updateSlider();
    }

    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', (e) => {
        if (document.getElementById('lightbox').style.display === 'flex') {
            if (e.key === 'ArrowLeft') changeSlide(-1);
            if (e.key === 'ArrowRight') changeSlide(1);
            if (e.key === 'Escape') closeLightbox();
        }
    });
</script>

<?php require 'includes/footer.php'; ?>