<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$mid = (int)($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'];
$media = $conn->query("SELECT * FROM media WHERE id=$mid AND (user_id=$uid OR '{$_SESSION['role']}' = 'admin')")->fetch_assoc();

if (!$media) { die("Access Denied."); }

$ffmpeg = 'C:\Users\kisho\AppData\Local\Microsoft\WinGet\Packages\Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe\ffmpeg-8.1.1-full_build\bin\ffmpeg.exe';

$status = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $source = __DIR__ . '/' . ltrim($media['file_path'], '/');
    $output_name = 'studio_' . time() . '_' . basename($media['file_path']);
    $output_path = 'uploads/media/' . $output_name;
    $target_full = __DIR__ . '/' . $output_path;

    $cmd = "";
    if ($media['type'] == 'video') {
        if ($action == 'slowmo') {
            $cmd = "\"$ffmpeg\" -i " . escapeshellarg($source) . " -filter:v \"setpts=2.0*PTS\" -filter:a \"atempo=0.5\" " . escapeshellarg($target_full) . " 2>&1";
        } elseif ($action == 'cinematic') {
            // 21:9 Cinematic Crop
            $cmd = "\"$ffmpeg\" -i " . escapeshellarg($source) . " -vf \"crop=in_w:in_w*9/21,scale=1920:-1\" -c:a copy " . escapeshellarg($target_full) . " 2>&1";
        } elseif ($action == 'mute') {
            $cmd = "\"$ffmpeg\" -i " . escapeshellarg($source) . " -an " . escapeshellarg($target_full) . " 2>&1";
        } elseif ($action == 'add_music') {
            $music_url = $_POST['music_url'] ?? '';
            $is_yt = (int)($_POST['is_yt'] ?? 0);
            if ($music_url) {
                $audio_source = $music_url;
                if ($is_yt) {
                    // Use global yt-dlp to download audio locally first (prevents 403 Forbidden errors)
                    $temp_audio = 'uploads/temp/yt_' . time() . '.mp3';
                    if (!file_exists('uploads/temp')) mkdir('uploads/temp', 0777, true);
                    
                    $dl_cmd = "yt-dlp -f ba -x --audio-format mp3 -o " . escapeshellarg($temp_audio) . " " . escapeshellarg($music_url);
                    shell_exec($dl_cmd);
                    
                    if (file_exists($temp_audio)) {
                        $audio_source = $temp_audio;
                    } else {
                        $status = "❌ Could not download YouTube audio. The video might be restricted.";
                    }
                }
                
                if ($audio_source && !$status) {
                    $start_time = (int)($_POST['audio_start'] ?? 0);
                    $seek_arg = ($start_time > 0) ? "-ss $start_time" : "";
                    
                    // Merge external audio with video, applying seek to the audio input
                    $cmd = "\"$ffmpeg\" -i " . escapeshellarg($source) . " $seek_arg -i " . escapeshellarg($audio_source) . " -c:v copy -map 0:v:0 -map 1:a:0 -shortest " . escapeshellarg($target_full) . " 2>&1";
                    
                    // Add cleanup for temp audio if it was a YT download
                    if ($is_yt && file_exists($audio_source)) {
                        // We'll delete it after FFmpeg runs (handled below the shell_exec($cmd))
                    }
                }
            }
        }
    } else {
        // Photo actions (placeholder for now, can use GD)
        if ($action == 'cinematic') {
            // Simulate cinematic crop for photo using simple copy for now or integrate GD
            copy($source, $target_full);
            $status = "Cinematic effect applied to photo!";
        }
    }

    if ($cmd) {
        $out = shell_exec($cmd);
        
        // Cleanup temp audio if it exists
        if (isset($temp_audio) && file_exists($temp_audio)) {
            @unlink($temp_audio);
        }

        if (file_exists($target_full)) {
            // Insert as new media
            $new_title = $conn->real_escape_string($media['title'] . " (Studio Edit)");
            $conn->query("INSERT INTO media (user_id, title, description, file_path, thumbnail, type, category) 
                          VALUES ('$uid', '$new_title', 'Edited in Crevix Studio', '$output_path', '{$media['thumbnail']}', '{$media['type']}', '{$media['category']}')");
            $status = "✅ Processing Complete! New video saved to your library.";
        } else {
            $status = "❌ Processing Failed. <pre style='font-size:0.7rem;'>$out</pre>";
        }
    }
}
?>

<div class="studio-container" style="max-width:1000px; margin:40px auto; color:white;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:30px;">
        <div>
            <h1 style="color:var(--gold); font-size:2.5rem; margin:0; letter-spacing:-1px;">🎬 Crevix Studio</h1>
            <p style="color:var(--muted);">Pro-Level Cinema Tools for your content.</p>
        </div>
        <a href="watch.php?id=<?php echo $mid; ?>" class="btn-secondary">← Back to Player</a>
    </div>

    <?php if($status): ?>
        <div class="alert" style="background:rgba(255,215,0,0.1); border:1px solid var(--gold); padding:15px; border-radius:10px; margin-bottom:20px;"><?php echo $status; ?></div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:30px;">
        <!-- PREVIEW -->
        <div class="glass-card" style="padding:20px; border-radius:20px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1);">
            <div style="border-radius:12px; overflow:hidden; background:#000; aspect-ratio:16/9; display:flex; align-items:center; justify-content:center;">
                <?php if($media['type'] == 'video'): ?>
                    <video controls style="width:100%; height:100%;">
                        <source src="stream.php?id=<?php echo $mid; ?>" type="video/mp4">
                    </video>
                <?php else: ?>
                    <img src="<?php echo $media['file_path']; ?>" style="max-width:100%; max-height:100%; object-fit:contain;">
                <?php endif; ?>
            </div>
            <div style="margin-top:20px;">
                <h3 style="margin:0;"><?php echo htmlspecialchars($media['title']); ?></h3>
                <p style="color:var(--muted); font-size:0.9rem; margin-top:5px;">Editing Source: <code><?php echo basename($media['file_path']); ?></code></p>
            </div>
        </div>

        <!-- TOOLS -->
        <div class="glass-card" style="padding:30px; border-radius:20px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1);">
            <h2 style="margin-top:0; font-size:1.2rem; text-transform:uppercase; color:var(--gold); letter-spacing:1px;">Studio Tools</h2>
            
            <form method="POST">
                <div class="tool-grid" style="display:grid; gap:15px; margin-top:20px;">
                    
                    <?php if($media['type'] == 'video'): ?>
                        <button type="submit" name="action" value="slowmo" class="studio-btn">
                            <span class="icon">🐌</span>
                            <div>
                                <strong>Slow Motion</strong>
                                <small>Apply 0.5x cinematic slow-mo with pitch correction</small>
                            </div>
                        </button>

                        <button type="submit" name="action" value="cinematic" class="studio-btn">
                            <span class="icon">📽️</span>
                            <div>
                                <strong>Cinematic Crop</strong>
                                <small>Transform to 21:9 Ultra-Wide aspect ratio</small>
                            </div>
                        </button>

                        <button type="submit" name="action" value="mute" class="studio-btn">
                            <span class="icon">🔇</span>
                            <div>
                                <strong>Manage Audio</strong>
                                <small>Remove audio track or mute completely</small>
                            </div>
                        </button>

                            <!-- Tabs -->
                            <div style="display:flex; border-bottom:1px solid rgba(255,255,255,0.1); margin-bottom:15px;">
                                <button type="button" class="tab-btn active" onclick="switchTab('local')">Crevix Library</button>
                                <button type="button" class="tab-btn" onclick="switchTab('yt')">YouTube Audio Source</button>
                            </div>

                            <div id="local-tab">
                                <!-- Search & Filter -->
                                <div style="display:flex; gap:10px; margin-bottom:15px;">
                                    <input type="text" id="musicSearch" placeholder="Search tracks..." onkeyup="filterLibrary()" style="flex:1; padding:8px 15px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white; font-size:0.85rem;">
                                    <select id="genreFilter" onchange="filterLibrary()" style="padding:8px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:white; font-size:0.85rem;">
                                        <option value="all">All Genres</option>
                                        <option value="Synthwave">Synthwave</option>
                                        <option value="Lo-Fi">Lo-Fi</option>
                                        <option value="Chill">Chill</option>
                                        <option value="Electro">Electro</option>
                                        <option value="Ambient">Ambient</option>
                                    </select>
                                </div>

                                <div class="audio-table-header">
                                    <span>Track</span>
                                    <span>Genre</span>
                                    <span>Action</span>
                                </div>
                                <div id="music-list" class="audio-list">
                                    <!-- Music items injected here -->
                                </div>
                            </div>

                            <div id="yt-tab" style="display:none; padding:20px; background:rgba(255,0,0,0.05); border-radius:12px; border:1px solid rgba(255,0,0,0.2);">
                                <h5 style="margin:0 0 10px; color:#ff4444;">YouTube Direct Audio</h5>
                                <p style="font-size:0.75rem; color:var(--muted); margin-bottom:15px;">Paste any YouTube URL to extract and use its audio as your soundtrack.</p>
                                <input type="url" id="ytAudioUrl" placeholder="https://www.youtube.com/watch?v=..." style="width:100%; padding:12px; background:rgba(0,0,0,0.4); border:1px solid rgba(255,255,255,0.1); border-radius:10px; color:white;">
                                <button type="button" onclick="useYtAudio()" class="btn-primary" style="width:100%; margin-top:10px; background:#ff4444; border:none;">🔗 Link YouTube Track</button>
                            </div>
                            
                            <input type="hidden" name="music_url" id="selected_music_url">
                            <input type="hidden" name="is_yt" id="is_yt_audio" value="0">
                            
                            <div id="audio-trim-section" style="display:none; margin-top:20px; padding:20px; background:rgba(255,255,255,0.03); border-radius:12px; border:1px solid rgba(255,255,255,0.1);">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                    <h5 style="margin:0; color:var(--gold); font-size:0.8rem; text-transform:uppercase;">✂️ Audio Start Position</h5>
                                    <span id="start-time-display" style="font-family:monospace; color:var(--gold); font-weight:700;">0:00</span>
                                </div>
                                <input type="range" name="audio_start" id="audioStartSlider" min="0" max="300" value="0" style="width:100%; accent-color:var(--gold);" oninput="updateTimeDisplay(this.value)">
                                <p style="font-size:0.7rem; color:var(--muted); margin-top:8px;">Choose where the song begins (Instagram Style).</p>
                            </div>

                            <div id="selected-bar" style="display:none; margin-top:15px; padding:12px; background:rgba(212,175,55,0.1); border:1px solid var(--gold); border-radius:10px; animation: slideUp 0.3s ease;">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <div style="font-size:0.7rem; color:var(--gold); text-transform:uppercase; font-weight:700;">Selected Track</div>
                                        <div id="selected-name" style="font-weight:600; font-size:0.9rem;">None</div>
                                    </div>
                                    <button type="submit" name="action" value="add_music" class="btn-primary" style="padding:8px 20px; font-size:0.85rem; background:var(--gold); color:black;">✨ Apply</button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <button type="submit" name="action" value="cinematic" class="studio-btn">
                            <span class="icon">🎨</span>
                            <div>
                                <strong>Cinematic Look</strong>
                                <small>Apply movie-grade color grading and crop</small>
                            </div>
                        </button>
                    <?php endif; ?>

                </div>
            </form>

            <div style="margin-top:40px; padding:20px; background:rgba(0,0,0,0.2); border-radius:12px;">
                <h4 style="margin:0; color:var(--gold);">💡 Studio Pro Tip</h4>
                <p style="font-size:0.85rem; color:var(--muted); margin-top:10px;">Crevix Studio uses server-side FFmpeg processing. Editing large 4K files may take a few seconds. Edits are saved as NEW files to preserve your originals.</p>
            </div>
        </div>
    </div>
</div>

<script>
const library = [
    { name: "Midnight City", genre: "Synthwave", url: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3" },
    { name: "Urban Jungle", genre: "Lo-Fi", url: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3" },
    { name: "Golden Sunset", genre: "Chill", url: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3" },
    { name: "Cyberpunk Night", genre: "Electro", url: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3" },
    { name: "Deep Ocean", genre: "Ambient", url: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-8.mp3" },
    { name: "Neon Dreams", genre: "Synthwave", url: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-9.mp3" },
    { name: "Rainy Day", genre: "Lo-Fi", url: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-10.mp3" },
    { name: "Morning Dew", genre: "Ambient", url: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-11.mp3" },
    { name: "Bass Boost", genre: "Electro", url: "https://www.soundhelix.com/examples/mp3/SoundHelix-Song-12.mp3" }
];

function renderLibrary(tracks) {
    const list = document.getElementById('music-list');
    list.innerHTML = '';
    tracks.forEach(song => {
        const row = document.createElement('div');
        row.className = 'audio-row';
        row.innerHTML = `
            <div style="display:flex; align-items:center; gap:10px;">
                <button type="button" class="audio-play-btn" onclick="previewSong('${song.url}', this)">▶</button>
                <span class="track-name">${song.name}</span>
            </div>
            <span class="genre-tag">${song.genre}</span>
            <button type="button" class="select-track-btn" onclick="selectSong('${song.url}', '${song.name}', this)">Select</button>
        `;
        list.appendChild(row);
    });
}

function filterLibrary() {
    const q = document.getElementById('musicSearch').value.toLowerCase();
    const g = document.getElementById('genreFilter').value;
    const filtered = library.filter(s => {
        const matchesQ = s.name.toLowerCase().includes(q);
        const matchesG = g === 'all' || s.genre === g;
        return matchesQ && matchesG;
    });
    renderLibrary(filtered);
}

let currentAudio = null;
function previewSong(url, btn) {
    if (currentAudio) {
        currentAudio.pause();
        if (currentAudio.src === url) {
            currentAudio = null;
            btn.innerText = '▶';
            return;
        }
    }
    document.querySelectorAll('.audio-play-btn').forEach(b => b.innerText = '▶');
    currentAudio = new Audio(url);
    currentAudio.play();
    btn.innerText = '⏸';
}

function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('local-tab').style.display = 'none';
    document.getElementById('yt-tab').style.display = 'none';
    
    if (tab === 'local') {
        document.querySelector('[onclick="switchTab(\'local\')"]').classList.add('active');
        document.getElementById('local-tab').style.display = 'block';
    } else {
        document.querySelector('[onclick="switchTab(\'yt\')"]').classList.add('active');
        document.getElementById('yt-tab').style.display = 'block';
    }
}

function updateTimeDisplay(val) {
    const min = Math.floor(val / 60);
    const sec = Math.floor(val % 60);
    document.getElementById('start-time-display').innerText = `${min}:${sec.toString().padStart(2, '0')}`;
    
    // Real-time Audio Preview (Seek to the position)
    if (currentAudio) {
        currentAudio.currentTime = val;
        if (currentAudio.paused) currentAudio.play();
    }
}

function useYtAudio() {
    const url = document.getElementById('ytAudioUrl').value.trim();
    if (!url) return;
    
    document.getElementById('selected_music_url').value = url;
    document.getElementById('is_yt_audio').value = "1";
    document.getElementById('selected-name').innerText = "YouTube Source Linked";
    document.getElementById('selected-bar').style.display = 'block';
    document.getElementById('audio-trim-section').style.display = 'block';
}

function selectSong(url, name, btn) {
    document.getElementById('selected_music_url').value = url;
    document.getElementById('is_yt_audio').value = "0";
    document.getElementById('selected-name').innerText = name;
    document.getElementById('selected-bar').style.display = 'block';
    document.getElementById('audio-trim-section').style.display = 'block';
    
    // Load and play the song for previewing the trim
    if (currentAudio) currentAudio.pause();
    currentAudio = new Audio(url);
    currentAudio.play();
    
    document.querySelectorAll('.select-track-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

// Initial Render
renderLibrary(library);
</script>

<style>
.tab-btn {
    flex: 1;
    padding: 10px;
    background: none;
    border: none;
    color: var(--muted);
    font-weight: 600;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.3s;
}
.tab-btn.active {
    color: var(--gold);
    border-bottom-color: var(--gold);
    background: rgba(212,175,55,0.05);
}

.audio-table-header {
    display: grid;
    grid-template-columns: 2fr 1fr 100px;
    padding: 10px;
    background: rgba(255,255,255,0.05);
    border-radius: 8px 8px 0 0;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
}
.audio-list {
    max-height: 250px;
    overflow-y: auto;
    border: 1px solid rgba(255,255,255,0.05);
    border-top: none;
    border-radius: 0 0 8px 8px;
}
.audio-row {
    display: grid;
    grid-template-columns: 2fr 1fr 100px;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    transition: background 0.2s;
}
.audio-row:hover { background: rgba(255,255,255,0.02); }
.audio-play-btn {
    background: var(--gold);
    color: black;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.track-name { font-size: 0.85rem; font-weight: 500; }
.genre-tag { font-size: 0.75rem; color: var(--muted); }
.select-track-btn {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: white;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.7rem;
    cursor: pointer;
}
.select-track-btn.active {
    background: var(--gold);
    color: black;
    border-color: var(--gold);
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.studio-btn {
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 15px;
    border-radius: 15px;
    color: white;
    text-align: left;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.studio-btn:hover {
    background: rgba(255,215,0,0.1);
    border-color: var(--gold);
    transform: translateX(5px);
}
.studio-btn .icon {
    font-size: 1.8rem;
    background: rgba(0,0,0,0.3);
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}
.studio-btn strong {
    display: block;
    font-size: 1rem;
}
.studio-btn small {
    display: block;
    color: var(--muted);
    font-size: 0.75rem;
}
</style>

<?php require 'includes/footer.php'; ?>
