<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$message = '';
$uid = $_SESSION['user_id'];

function getMimeExtension($mime) {
    $mime_map = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogg',
        'video/x-matroska' => 'mkv',
        'video/quicktime' => 'mov',
        'video/x-msvideo' => 'avi',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
    ];
    foreach ($mime_map as $m => $ext) {
        if (stripos($mime, $m) !== false) return $ext;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['url'])) {
    $url = trim($_POST['url']);
    $title = trim($_POST['title']) ?: 'Fetched Media';
    $desc = $conn->real_escape_string(trim($_POST['description']));
    $category = $conn->real_escape_string($_POST['category'] ?? 'General');
    $method = $_POST['fetch_method'] ?? 'smart';

    $download_ok = false;
    $target = '';

    if ($method == 'pro') {
        // --- PRO METHOD: yt-dlp ---
        $target_dir = 'uploads/media/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $python_path = "python";
        $test_python = shell_exec("python --version 2>&1");
        if (stripos($test_python, 'Python') === false) $python_path = "py";

        $cmd = "$python_path python/ytdl_bridge.py " . escapeshellarg($url) . " " . escapeshellarg($target_dir);
        $output = shell_exec($cmd . " 2>&1");

        // Parse metadata (title + thumbnail) from bridge output
        $auto_title = '';
        $auto_thumb_url = '';
        foreach (explode("\n", $output) as $line) {
            if (strpos($line, 'META_JSON:') === 0) {
                $meta = json_decode(trim(substr($line, 10)), true);
                if ($meta) {
                    $auto_title = $meta['title'] ?? '';
                    $auto_thumb_url = $meta['thumbnail'] ?? '';
                }
                break;
            }
        }
        // Use auto title if user didn't provide one
        if (empty($title) || $title === 'Fetched Media') $title = $auto_title;

        $files = glob($target_dir . "ytdl_*");
        if ($files) {
            array_multisort(array_map('filemtime', $files), SORT_DESC, $files);
            $raw_file = $files[0];
            $ext = pathinfo($raw_file, PATHINFO_EXTENSION);
            
            // SANITIZE: Remove spaces and special chars which break web players
            $clean_name = 'ytdl_fixed_' . time() . '.' . $ext;
            $target = $target_dir . $clean_name;
            rename($raw_file, $target);

            // VERIFY: Check if the file is actually a valid MP4 (not a TS stream)
            $fh = fopen($target, 'rb');
            $header = fread($fh, 8);
            fclose($fh);
            
            $is_real_mp4 = (strpos($header, 'ftyp') !== false);
            $is_ts_stream = (ord($header[0]) === 0x47); // TS files start with 0x47
            
            if ($ext === 'mp4' && !$is_real_mp4 && $is_ts_stream) {
                // It's a TS stream saved as .mp4 — try ffmpeg remux
                $ffmpeg_bin = 'C:\Users\kisho\AppData\Local\Microsoft\WinGet\Packages\Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe\ffmpeg-8.1.1-full_build\bin\ffmpeg.exe';
                $remuxed = $target_dir . 'remux_' . time() . '.mp4';
                $ffmpeg_cmd = "\"$ffmpeg_bin\" -i " . escapeshellarg($target) . " -c copy -movflags +faststart " . escapeshellarg($remuxed) . " 2>&1";
                $ffmpeg_out = shell_exec($ffmpeg_cmd);
                
                if (file_exists($remuxed) && filesize($remuxed) > 0) {
                    unlink($target);
                    $target = $remuxed;
                    $message .= "<div style='font-size:0.7rem; color:var(--gold);'>ℹ️ File was TS stream — remuxed to proper MP4.</div>";
                } else {
                    // ffmpeg not available — rename to .ts and let browser try
                    $ts_name = str_replace('.mp4', '.ts', $target);
                    rename($target, $ts_name);
                    $target = $ts_name;
                    $ext = 'ts';
                    $message .= "<div style='font-size:0.7rem; color:orange;'>⚠️ File is TS format. Install <b>ffmpeg</b> for auto-conversion to MP4.</div>";
                }
            }

            $type = in_array($ext, ['jpg','png','webp']) ? 'photo' : 'video';
            $download_ok = true;
            
            // Auto-download thumbnail from yt-dlp metadata if no user-provided thumbnail
            if (empty($_POST['thumbnail_data']) && !empty($auto_thumb_url)) {
                $thumb_data = @file_get_contents($auto_thumb_url);
                if ($thumb_data) {
                    $auto_thumb_path = $target_dir . 'thumb_' . time() . '.jpg';
                    file_put_contents($auto_thumb_path, $thumb_data);
                    $_POST['thumbnail_data'] = ''; // Ensure user thumb doesn't override
                    // Pass via a variable — used below
                    $fetched_thumb_path = $auto_thumb_path;
                }
            }
        } else {
            $message = "<div class='alert alert-error'>❌ Pro Fetch failed. Ensure <b>yt-dlp</b> is installed.<br><pre style='font-size:0.7rem;'>$output</pre></div>";
        }
    } else {
        // --- SMART METHOD: PHP + Python fallback ---
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15');
        curl_exec($ch);
        $info = curl_getinfo($ch);
        $content_type = $info['content_type'];
        curl_close($ch);

        $real_media_url = $url;
        $is_html = stripos($content_type, 'text/html') !== false;

        if ($is_html) {
            $headers = "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15\r\n";
            $html = @file_get_contents($url, false, stream_context_create(["http" => ["header" => $headers]]));
            
            if ($html) {
                $found_url = '';
                $patterns = [
                    '/<meta property="og:video:url" content="([^"]+)"/',
                    '/<meta property="og:video" content="([^"]+)"/',
                    '/<meta name="twitter:player:stream" content="([^"]+)"/',
                    '/<video[^>]+src="([^"]+)"/',
                    '/<source[^>]+src="([^"]+)"/',
                    '/"([^"]+\.(mp4|webm|mkv|mov|avi))"/'
                ];
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $html, $matches)) { $found_url = $matches[1]; break; }
                }
                if (!$found_url) {
                    if (preg_match('/<meta property="og:image" content="([^"]+)"/', $html, $matches)) $found_url = $matches[1];
                }
                if ($found_url) {
                    $real_media_url = htmlspecialchars_decode($found_url);
                    if (strpos($real_media_url, 'http') !== 0) {
                        $base = parse_url($url);
                        $real_media_url = ($base['scheme']??'http') . '://' . ($base['host']??'') . '/' . ltrim($real_media_url, '/');
                    }
                }
            }
        }

        $ext = getMimeExtension($content_type);
        if (!$ext) $ext = strtolower(pathinfo(parse_url($real_media_url, PHP_URL_PATH), PATHINFO_EXTENSION));

        $video_exts = ['mp4','webm','ogg','mov','avi','mkv'];
        $image_exts = ['jpg','jpeg','png','gif','webp','svg'];
        if (in_array($ext, array_merge($video_exts, $image_exts))) {
            $type = in_array($ext, $video_exts) ? 'video' : 'photo';
            $target_dir = 'uploads/media/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            // SANITIZE: Ensure no spaces or special chars in filename
            $safe_url_name = preg_replace('/[^a-zA-Z0-9._-]/', '', pathinfo(parse_url($real_media_url, PHP_URL_PATH), PATHINFO_FILENAME));
            $target = $target_dir . 'fetch_' . time() . '_' . substr($safe_url_name, 0, 30) . '.' . $ext;

            // Try PHP Download
            $ch = curl_init($real_media_url);
            $fp = fopen($target, 'wb');
            $referer = $url;
            if (stripos($url, 'xhamster') !== false) $referer = 'https://xhamster.com/';
            
            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 300, CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
                CURLOPT_REFERER => $referer, CURLOPT_ENCODING => '',
                CURLOPT_HTTPHEADER => ['Accept: */*', 'Connection: keep-alive']
            ]);
            $success = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            fclose($fp);
            curl_close($ch);

            if ($success && $http_code == 200 && filesize($target) > 0) {
                $download_ok = true;
            } else {
                // PHP Failed -> Try Python
                if (file_exists($target)) unlink($target);
                $python_path = shell_exec("python --version") ? "python" : "py";
                $cmd = "$python_path python/processor.py " . escapeshellarg($real_media_url) . " " . escapeshellarg($target) . " " . escapeshellarg($referer);
                shell_exec($cmd);
                if (file_exists($target) && filesize($target) > 0) $download_ok = true;
            }
        }
    }

    if ($download_ok) {
        $title_safe = $conn->real_escape_string($title ?: 'Fetched Media');
        $size_mb = round(filesize($target) / 1024 / 1024, 2);
        
        // Thumbnail priority: user canvas capture > yt-dlp auto-downloaded > empty
        $thumb_path = $fetched_thumb_path ?? '';
        if (!empty($_POST['thumbnail_data'])) {
            $thumb_data = str_replace(['data:image/png;base64,', ' '], ['', '+'], $_POST['thumbnail_data']);
            $user_thumb = 'uploads/media/thumb_' . time() . '.png';
            file_put_contents($user_thumb, base64_decode($thumb_data));
            $thumb_path = $user_thumb; // User thumb overrides auto-thumb
        }

        $sql = "INSERT INTO media (user_id, title, description, file_path, thumbnail, type, category) 
                VALUES ('$uid', '$title_safe', '$desc', '$target', '$thumb_path', '$type', '$category')";
        if ($conn->query($sql)) {
            $new_id = $conn->insert_id;
            $message = "<div class='alert alert-success'>✅ Successfully fetched! ($size_mb MB). <a href='watch.php?id=$new_id' style='color:var(--gold);'>▶ View Now</a></div>";
        }
    }
}
?>

<div style="max-width:700px; margin:40px auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="color:var(--gold); margin:0;">🌐 Universal Media Fetcher</h2>
        <a href="bulk_fetch.php" class="btn-secondary" style="font-size:0.85rem; padding:8px 15px;">📦 Switch to Bulk Profile Fetch</a>
    </div>
    <p style="color:var(--muted); margin-bottom:30px;">Choose a method to fetch media from any site on the web.</p>

    <?php echo $message; ?>

    <form method="POST" id="fetchForm">
        <div class="input-group">
            <label>Fetch Method</label>
            <select name="fetch_method" id="fetchMethod" style="background:var(--surface2); border:1px solid var(--gold); color:white;">
                <option value="pro">🔥 Pro Fetch (Supports 1000+ Video Sites)</option>
                <option value="smart">🚀 Smart Fetch (Fast - Webpages/Direct Links)</option>
            </select>
            <small id="methodNote" style="color:var(--gold); margin-top:5px; display:block;">🔥 Pro mode: Powerful download using yt-dlp.</small>
        </div>

        <div class="input-group">
            <label>URL</label>
            <input type="url" name="url" id="fetchUrl" required placeholder="https://example.com/video" style="font-size:1rem;">
        </div>

        <!-- Meta Preview Card (Auto-fetched for Pro mode) -->
        <div id="metaPreview" style="display:none; background:var(--surface2); border-radius:12px; padding:16px; margin-bottom:16px; display:flex; align-items:center; gap:16px;">
            <img id="metaThumb" src="" alt="Thumbnail" style="width:120px; height:80px; object-fit:cover; border-radius:8px; flex-shrink:0;">
            <div>
                <div id="metaTitle" style="color:white; font-weight:600; margin-bottom:4px;"></div>
                <div id="metaStatus" style="color:var(--muted); font-size:0.8rem;">✅ Metadata fetched via yt-dlp</div>
            </div>
        </div>

        <div id="urlPreview" style="display:none; background:#000; border-radius:10px; overflow:hidden; margin-bottom:20px; text-align:center;">
            <video id="previewVideo" controls muted style="max-width:100%; max-height:300px; display:none;"></video>
            <img id="previewImage" style="max-width:100%; max-height:300px; display:none;" alt="preview">
            <p id="previewInfo" style="padding:10px; color:var(--muted); font-size:0.85rem;"></p>
        </div>
        <canvas id="thumbCanvas" style="display:none;"></canvas>
        <input type="hidden" name="thumbnail_data" id="thumbnailData">

        <div style="margin-bottom:15px; font-size:0.9rem;">
            <a href="fetch.php" style="color:var(--gold); margin-right:15px;"><span>🔗</span> Pro Fetch</a>
            <a href="bulk_fetch.php" style="color:var(--gold); margin-right:15px;"><span>📦</span> Bulk Fetch</a>
            <a href="sync.php" style="color:var(--gold);"><span>📁</span> Folder Sync</a>
        </div>

        <div class="input-group">
            <label>Title <span style="color:var(--muted); font-size:0.8rem;">(leave blank for auto-detect)</span></label>
            <input type="text" name="title" id="titleInput" placeholder="Auto-detected from video...">
        </div>
        <div class="input-group">
            <label>Description</label>
            <textarea name="description" rows="3" placeholder="Enter a description..."></textarea>
        </div>
        <div class="input-group">
            <label>Category</label>
            <select name="category">
                <option value="General">General</option>
                <option value="Music">Music</option>
                <option value="Gaming">Gaming</option>
                <option value="Vlog">Vlog</option>
                <option value="Film">Film</option>
            </select>
        </div>
        <button type="submit" id="fetchBtn" class="btn-primary" style="width:100%; padding:14px; font-size:1rem;">⬇️ Start Fetching</button>
    </form>
</div>

<script>
const fetchUrl = document.getElementById('fetchUrl');
const previewDiv = document.getElementById('urlPreview');
const previewVideo = document.getElementById('previewVideo');
const previewImage = document.getElementById('previewImage');
const previewInfo = document.getElementById('previewInfo');
const thumbCanvas = document.getElementById('thumbCanvas');
const thumbnailData = document.getElementById('thumbnailData');

let debounceTimer;
fetchUrl.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => previewUrl(this.value.trim()), 600);
});

function previewUrl(url) {
    if (!url) { previewDiv.style.display = 'none'; return; }
    let path = url.split('?')[0].split('#')[0];
    let ext = path.split('.').pop().toLowerCase();
    let videoExts = ['mp4','webm','ogg','mov','avi','mkv'];
    let imageExts = ['jpg','jpeg','png','gif','webp','svg'];

    previewDiv.style.display = 'block';
    previewVideo.style.display = 'none';
    previewImage.style.display = 'none';
    previewInfo.textContent = '🔍 Scanning URL...';

    if (videoExts.includes(ext)) {
        previewVideo.src = url;
        previewVideo.style.display = 'block';
        previewVideo.onloadeddata = () => previewVideo.currentTime = 1;
        previewVideo.onseeked = () => {
            thumbCanvas.width = previewVideo.videoWidth;
            thumbCanvas.height = previewVideo.videoHeight;
            thumbCanvas.getContext('2d').drawImage(previewVideo, 0, 0);
            thumbnailData.value = thumbCanvas.toDataURL('image/png');
        };
    } else if (imageExts.includes(ext)) {
        previewImage.src = url;
        previewImage.style.display = 'block';
    }
}

// === META FETCH (Pro mode only) ===
const metaPreview = document.getElementById('metaPreview');
const metaThumb   = document.getElementById('metaThumb');
const metaTitle   = document.getElementById('metaTitle');
const metaStatus  = document.getElementById('metaStatus');
const titleInput  = document.getElementById('titleInput');

let metaTimer;
fetchUrl.addEventListener('input', function() {
    clearTimeout(metaTimer);
    const method = document.getElementById('fetchMethod').value;
    if (method !== 'pro') return;
    const url = this.value.trim();
    if (!url) { metaPreview.style.display = 'none'; return; }
    metaStatus.textContent = '⏳ Fetching metadata...';
    metaPreview.style.display = 'flex';
    metaThumb.src = '';
    metaTitle.textContent = '';
    metaTimer = setTimeout(() => fetchMeta(url), 800);
});

function fetchMeta(url) {
    fetch('fetch_meta.php?url=' + encodeURIComponent(url))
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                metaStatus.textContent = '⚠️ ' + data.error;
                return;
            }
            if (data.thumbnail) metaThumb.src = data.thumbnail;
            if (data.title) {
                metaTitle.textContent = data.title;
                // Auto-fill title field only if user hasn't typed anything
                if (!titleInput.value) titleInput.value = data.title;
            }
            metaStatus.textContent = '✅ Metadata fetched!';
        })
        .catch(() => metaStatus.textContent = '❌ Could not fetch metadata');
}

// Dynamic method switcher
const fetchMethod = document.getElementById('fetchMethod');
const fetchBtn = document.getElementById('fetchBtn');
const methodNote = document.getElementById('methodNote');
const methodConfig = {
    pro:  { btn: '⬇️ Start Downloading', note: '🔥 Pro mode: Downloads using yt-dlp. Best for permanent archiving.' },
    smart:{ btn: '⬇️ Start Fetching', note: '🚀 Smart mode: PHP-based download. Works for direct media links.' }
};
fetchMethod.addEventListener('change', function() {
    const cfg = methodConfig[this.value];
    fetchBtn.textContent = cfg.btn;
    methodNote.textContent = cfg.note;
});
</script>

<?php require 'includes/footer.php'; ?>
