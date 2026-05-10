<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$status = '';
?>

<div class="bulk-container" style="max-width:1200px; margin:40px auto; color:white;">
    <div style="text-align:center; margin-bottom:40px;">
        <h1 style="color:var(--gold); font-size:2.8rem; margin:0; letter-spacing:-1px;">📦 Bulk Profile Fetch</h1>
        <p style="color:var(--muted);">Import entire playlists or channels with a single click.</p>
    </div>

    <!-- URL INPUT -->
    <div class="glass-card" style="max-width:800px; margin:0 auto 40px; padding:30px; border-radius:20px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1);">
        <div class="input-group">
            <label>Profile or Playlist URL</label>
            <div style="display:flex; gap:10px;">
                <input type="url" id="bulkUrl" placeholder="https://www.youtube.com/@channel/videos" style="flex:1; font-size:1.1rem;">
                <button onclick="scanProfile()" id="scanBtn" class="btn-primary" style="padding:0 30px;">🔍 Scan Profile</button>
            </div>
        </div>
        <p style="font-size:0.85rem; color:var(--muted); margin-top:10px;">Supports YouTube, Instagram, xHamster, playlists and many other social profiles.</p>
    </div>

    <!-- LIVE SCAN STATUS -->
    <div id="scanStatus" style="display:none; text-align:center; margin-bottom:30px; padding:25px; background:rgba(212,175,55,0.08); border:1px solid rgba(212,175,55,0.3); border-radius:16px; max-width:800px; margin:0 auto 30px;">
        <div style="font-size:2.5rem; margin-bottom:12px;" id="scanIcon">🔍</div>
        <div id="scanStage" style="font-size:1.2rem; font-weight:600; color:var(--gold);">Connecting...</div>
        <div id="scanSub" style="font-size:0.85rem; color:var(--muted); margin-top:6px;">Please wait, this may take 15-30 seconds for Instagram profiles.</div>
        <div style="margin-top:16px; height:4px; background:rgba(255,255,255,0.1); border-radius:4px; overflow:hidden;">
            <div id="scanBar" style="height:100%; background:var(--gold); width:0%; transition:width 0.8s ease; border-radius:4px;"></div>
        </div>
    </div>

    <!-- RESULTS AREA -->
    <div id="resultsArea" style="display:none;">
        <!-- FILTER BAR -->
        <div class="glass-card" style="padding:15px 25px; margin-bottom:25px; display:flex; gap:20px; align-items:center; background:rgba(255,255,255,0.02); border-radius:15px; border:1px solid rgba(255,255,255,0.1);">
            <div style="flex:1; position:relative;">
                <input type="text" id="gridSearch" placeholder="🔍 Filter by title..." onkeyup="applyFilters()" style="width:100%; padding:10px 15px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:10px; color:white;">
            </div>
            <select id="typeFilter" onchange="applyFilters()" style="padding:10px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:10px; color:white;">
                <option value="all">All Types</option>
                <option value="video">🎬 Videos Only</option>
                <option value="photo">🖼️ Photos Only</option>
                <option value="carousel">📷 Carousels Only</option>
            </select>
            <select id="concurrencyLimit" style="padding:10px; background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.1); border-radius:10px; color:var(--gold); font-weight:600;">
                <option value="5">🚀 Balanced: 5 at once</option>
                <option value="20">🔥 Extreme: 20 at once</option>
                <option value="50">⚡ Turbo: 50 at once</option>
                <option value="100" selected>👑 God Mode: 100 at once</option>
            </select>
            <div style="display:flex; gap:10px;">
                <button onclick="toggleSelectAll()" class="btn-secondary" style="padding:10px 15px; font-size:0.85rem;">Check/Uncheck All</button>
                <button onclick="startBulkDownload()" id="downloadBtn" class="btn-primary" style="background:var(--gold); color:black; padding:10px 25px; font-weight:600;">🚀 Download Selected</button>
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h2 id="foundCount" style="margin:0; font-size:1.5rem;">Scanning...</h2>
                <div class="glass-card" style="margin-bottom:25px; padding:20px; border:1px solid rgba(212,175,55,0.3); background:rgba(212,175,55,0.05); display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h4 style="margin:0; color:var(--gold);">☁️ Cloud-Only Ingestion (Zero-Disk)</h4>
                        <p style="margin:5px 0 0; font-size:0.75rem; color:var(--muted);">Media will go directly to your linked Drives without using local space.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="cloudOnlyToggle">
                        <span class="slider round"></span>
                    </label>
                </div>

                <div id="fetch-controls" style="display:none;">
                    <div id="scanningPulse" style="display:none; font-size:0.8rem; color:var(--gold); margin-top:4px;">⚡ Live scanning in background...</div>
                </div>
            </div>
        </div>

        <div id="videoGrid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
            <!-- Dynamic Content -->
        </div>
    </div>

    <!-- LOGGING / PROGRESS -->
    <div id="progressArea" style="display:none; margin-top:40px; padding:20px; background:rgba(0,0,0,0.3); border-radius:15px; border:1px solid var(--surface2);">
        <h3 style="margin-top:0;">⏳ Download Progress</h3>
        <div id="progressLog" style="height:200px; overflow-y:auto; font-family:monospace; font-size:0.85rem; color:#00ff00;">
            <!-- Progress lines -->
        </div>
    </div>
</div>

<script>
let foundVideos = [];

let currentEventSource = null;

function scanProfile() {
    const url = document.getElementById('bulkUrl').value.trim();
    if (!url) return;

    // Close any previous stream
    if (currentEventSource) currentEventSource.close();

    const scanBtn = document.getElementById('scanBtn');
    scanBtn.disabled = true;
    scanBtn.textContent = '⏳ Connecting...';
    document.getElementById('resultsArea').style.display = 'block';
    document.getElementById('scanStatus').style.display = 'none';
    document.getElementById('foundCount').textContent = 'Scanning... 0 found';
    document.getElementById('scanningPulse').style.display = 'block';
    document.getElementById('videoGrid').innerHTML = '';
    foundVideos = [];

    // AUTO-DETECTION OF PLATFORM
    let scraperFile = 'insta_scraper.py';
    if (url.includes('pinterest.com')) {
        scraperFile = 'pinterest_native.py';
        addLog(`📌 Pinterest URL detected. Using Pinterest Engine...`);
    } else if (url.includes('xhamster')) {
        scraperFile = 'xhamster_scraper.py';
        addLog(`🔞 xHamster URL detected. Using Video Engine...`);
    } else {
        addLog(`📸 Instagram URL detected. Using Instagram Engine...`);
    }

    // Launch scan process
    fetch(`api_scan.php?url=${encodeURIComponent(url)}&scraper=${scraperFile}`);

    const es = new EventSource('bulk_stream.php?url=' + encodeURIComponent(url));
    currentEventSource = es;

    es.onmessage = function(event) {
        const data = JSON.parse(event.data);

        if (data.type === 'error') {
            alert(data.message || 'Scan failed.');
            es.close();
            scanDone(scanBtn);
            return;
        }

        if (data.type === 'profile') {
            // Show profile picture
            if (data.profile_pic) {
                const header = document.querySelector('.bulk-container > div:first-child');
                let pImg = document.getElementById('resProfilePic');
                if (!pImg) {
                    pImg = document.createElement('img');
                    pImg.id = 'resProfilePic';
                    pImg.style = 'width:80px;height:80px;border-radius:50%;border:3px solid var(--gold);margin-top:16px;object-fit:cover;';
                    header.appendChild(pImg);
                }
                pImg.src = 'thumb_proxy.php?url=' + encodeURIComponent(data.profile_pic);
            }
            return;
        }

        if (data.type === 'post') {
            const index = foundVideos.length;
            foundVideos.push(data);
            document.getElementById('foundCount').textContent = `Scanning... ${foundVideos.length} found`;
            addCardToGrid(data, index);
            return;
        }

        if (data.type === 'done') {
            es.close();
            document.getElementById('foundCount').textContent = `✅ Found ${foundVideos.length} items`;
            scanDone(scanBtn);
        }
    };

    es.onerror = function() {
        es.close();
        document.getElementById('foundCount').textContent = `✅ Found ${foundVideos.length} items`;
        scanDone(scanBtn);
    };
}

let existingUrls = [];

function applyFilters() {
    const q = document.getElementById('gridSearch').value.toLowerCase();
    const type = document.getElementById('typeFilter').value;
    const cards = document.querySelectorAll('.bulk-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const v = foundVideos[card.dataset.vindex];
        const title = (v.title || '').toLowerCase();
        
        let match = title.includes(q);
        if (type !== 'all') {
            if (type === 'video') {
                // Show if it's a video OR a carousel containing a video
                const hasVideo = v.media_type === 'video' || (v.is_carousel && v.carousel_items.some(ci => ci.media_type === 'video'));
                if (!hasVideo) match = false;
            }
            if (type === 'photo') {
                // Show if it's a photo OR a carousel containing a photo
                const hasPhoto = v.media_type === 'photo' || (v.is_carousel && v.carousel_items.some(ci => ci.media_type === 'photo'));
                if (!hasPhoto) match = false;
            }
            if (type === 'carousel' && !v.is_carousel) match = false;
        }
        
        card.style.display = match ? 'block' : 'none';
        if (match) visibleCount++;
    });
    
    document.getElementById('foundCount').textContent = `✅ Showing ${visibleCount} items (${type})`;
}

function checkDuplicates() {
    const urls = foundVideos.map(v => v.url);
    if (urls.length === 0) return;

    fetch('bulk_api.php?action=check_duplicates', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({urls})
    })
    .then(r => r.json())
    .then(existing => {
        existingUrls = existing;
        const cards = document.querySelectorAll('.bulk-card');
        cards.forEach(card => {
            const v = foundVideos[card.dataset.vindex];
            if (existing.includes(v.url)) {
                card.classList.add('duplicate');
                const check = card.querySelector('.video-check');
                check.checked = false;
                if (!card.querySelector('.dup-badge')) {
                    const badge = document.createElement('div');
                    badge.className = 'dup-badge';
                    badge.innerHTML = '✅ In Library';
                    card.querySelector('.thumb-wrap').appendChild(badge);
                }
            }
        });
    });
}

function scanDone(scanBtn) {
    scanBtn.disabled = false;
    scanBtn.textContent = '🔍 Scan Profile';
    document.getElementById('scanningPulse').style.display = 'none';
    checkDuplicates();
}

function addCardToGrid(v, index) {
    const grid = document.getElementById('videoGrid');
    const proxyThumb = v.thumbnail ? 'thumb_proxy.php?url=' + encodeURIComponent(v.thumbnail) : '';
    const card = document.createElement('div');
    card.className = 'bulk-card';
    card.dataset.vindex = index;
    card.style.animation = 'cardIn 0.3s ease';

    // Type badge
    let typeBadge = '';
    if (v.is_carousel && v.carousel_count) {
        typeBadge = `<span class="card-badge">📷 ${v.carousel_count}</span>`;
    } else if (v.media_type === 'video') {
        typeBadge = `<span class="card-badge">🎬 ${v.duration ? v.duration + 's' : 'Video'}</span>`;
    } else {
        typeBadge = `<span class="card-badge">🖼️</span>`;
    }

    // Likes
    const likes = v.likes ? `❤️ ${v.likes >= 1000 ? (v.likes/1000).toFixed(1)+'K' : v.likes}` : '';

    card.innerHTML = `
        <div class="thumb-wrap">
            ${proxyThumb ? `<img src="${proxyThumb}" alt="thumb" loading="lazy">` : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem;background:#222;">📷</div>`}
            <input type="checkbox" class="video-check" data-index="${index}" checked>
            ${typeBadge}
        </div>
        <div class="info">
            <h3>${v.title || 'Untitled'}</h3>
            <p>@${v.uploader || 'Creator'} ${likes ? ' • ' + likes : ''}</p>
        </div>
    `;
    grid.appendChild(card);
}



function toggleSelectAll() {
    const visibleCards = Array.from(document.querySelectorAll('.bulk-card'))
        .filter(card => card.style.display !== 'none' && !card.classList.contains('duplicate'));
        
    const visibleChecks = visibleCards.map(card => card.querySelector('.video-check'));
    if (visibleChecks.length === 0) return;
        
    const allChecked = visibleChecks.every(c => c.checked);
    visibleChecks.forEach(c => c.checked = !allChecked);
}

let downloadQueue = [];
let activeDownloads = 0;

function startBulkDownload() {
    // Only select checked boxes that are VISIBLE (not filtered out)
    const checks = Array.from(document.querySelectorAll('.bulk-card')).filter(card => {
        return card.style.display !== 'none' && card.querySelector('.video-check').checked;
    }).map(card => card.querySelector('.video-check'));

    if (checks.length === 0) {
        alert("Please select at least one visible item.");
        return;
    }

    const limit = parseInt(document.getElementById('concurrencyLimit').value);
    if (!confirm(`Start download of ${checks.length} items (${limit} at a time)?`)) return;

    const downloadBtn = document.getElementById('downloadBtn');
    downloadBtn.disabled = true;
    downloadBtn.textContent = '📥 Downloading...';
    
    document.getElementById('progressArea').style.display = 'block';
    const log = document.getElementById('progressLog');
    log.innerHTML = '';

    downloadQueue = [];
    checks.forEach(c => {
        const v = foundVideos[c.dataset.index];
        // Send the whole post (including carousel_items) to the API
        downloadQueue.push({ video: v });
    });
    
    activeDownloads = 0;
    runNextBatch(limit);
}

function runNextBatch(limit) {
    while (activeDownloads < limit && downloadQueue.length > 0) {
        const item = downloadQueue.shift();
        downloadItem(item, limit);
    }

    if (activeDownloads === 0 && downloadQueue.length === 0) {
        addLog("✅ ALL DOWNLOADS COMPLETE!");
        document.getElementById('downloadBtn').textContent = '✅ Finished';
    }
}

async function downloadItem(item, limit) {
    activeDownloads++;
    const video = item.video;
    const isCloudOnly = document.getElementById('cloudOnlyToggle').checked;
    const apiEndpoint = isCloudOnly ? 'cloud_bulk_api.php' : 'bulk_api.php';
    
    addLog(`🚀 Starting: ${video.title}...`);

    try {
        const response = await fetch(apiEndpoint, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(video)
        });
        const result = await response.json();
        
        if (result.success || (isCloudOnly && result.status === 'success')) {
            addLog(`   ✅ Success: ${video.title}`);
        } else {
            addLog(`   ❌ Failed: ${video.title} (${result.error || 'Err'})`);
        }
    } catch (e) {
        addLog(`   ❌ Connection Error: ${video.title}`);
    } finally {
        activeDownloads--;
        runNextBatch(limit);
    }
}

function addLog(msg) {
    const log = document.getElementById('progressLog');
    const line = document.createElement('div');
    line.textContent = msg;
    log.appendChild(line);
    log.scrollTop = log.scrollHeight;
}
</script>

<style>
.bulk-card {
    background: rgba(255,255,255,0.03);
    border-radius: 15px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.1);
    transition: transform 0.2s;
}
.bulk-card:hover { transform: scale(1.02); }
.thumb-wrap { position: relative; aspect-ratio: 16/9; background: #111; overflow:hidden; }
.thumb-wrap img { width: 100%; height: 100%; object-fit: cover; }
.video-check {
    position: absolute;
    top: 10px;
    left: 10px;
    width: 25px;
    height: 25px;
    cursor: pointer;
    accent-color: var(--gold);
    z-index: 2;
}
.bulk-card .info { padding: 15px; }
.bulk-card h3 { 
    margin: 0; font-size: 0.95rem; line-height: 1.4; 
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.bulk-card p { margin: 5px 0 0; color: var(--muted); font-size: 0.8rem; }
.card-badge {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0,0,0,0.85);
    color: white;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    backdrop-filter: blur(4px);
}
.dup-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #00ff00;
    color: black;
    padding: 3px 8px;
    border-radius: 5px;
    font-size: 0.7rem;
    font-weight: 800;
    z-index: 3;
}
.bulk-card.duplicate {
    opacity: 0.6;
    border: 1px solid #00ff00;
}
@keyframes cardIn {
    from { opacity: 0; transform: translateY(15px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

<?php require 'includes/footer.php'; ?>
