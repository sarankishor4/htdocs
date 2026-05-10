<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$message = '';
$user_id = $_SESSION['user_id'];
$sync_dir = 'uploads/sync/';
$media_dir = 'uploads/media/';

// Handle Folder Sync (Moving from sync folder to library)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sync'])) {
    $files = array_diff(scandir($sync_dir), ['.', '..']);
    $count = 0;
    foreach ($files as $file) {
        $source = $sync_dir . $file;
        if (is_file($source)) {
            $clean_file = preg_replace('/[^a-zA-Z0-9._-]/', '', $file);
            $destination = $media_dir . time() . '_' . $clean_file;
            $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
            $type = in_array($ext, ['mp4','webm','mov','avi','mkv']) ? 'video' : 'photo';
            $title = "Synced: " . pathinfo($file, PATHINFO_FILENAME);
            if (rename($source, $destination)) {
                $conn->query("INSERT INTO media (user_id, title, description, file_path, type, category) VALUES ('$user_id', '$title', 'Synced from server folder', '$destination', '$type', 'General')");
                $count++;
            }
        }
    }
    $message = "<div class='alert alert-success'>Synced $count file(s)!</div>";
}

// Handle Smart Re-scan (Finding stray files in media folder)
$stray_files = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rescan'])) {
    $all_files = array_diff(scandir($media_dir), ['.', '..']);
    $db_files_res = $conn->query("SELECT file_path FROM media");
    $db_files = [];
    while($r = $db_files_res->fetch_assoc()) {
        $db_files[] = ltrim($r['file_path'], '/');
    }

    $count = 0;
    foreach($all_files as $f) {
        $path = $media_dir . $f;
        // Check if file is in DB
        if (!in_array($path, $db_files)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4','webm','mov','avi','mkv','jpg','png','jpeg','gif'])) {
                $type = in_array($ext, ['mp4','webm','mov','avi','mkv']) ? 'video' : 'photo';
                $title = "Recovered: " . pathinfo($f, PATHINFO_FILENAME);
                $conn->query("INSERT INTO media (user_id, title, description, file_path, type, category) VALUES ('$user_id', '$title', 'Automatically recovered from server', '$path', '$type', 'General')");
                $count++;
            }
        }
    }
    $message = "<div class='alert alert-success'>Recovered $count stray file(s) into your library!</div>";
}

$pending = array_diff(scandir($sync_dir), ['.', '..']);
?>

<div style="max-width:700px; margin:40px auto; color:white;">
    <div style="text-align:center; margin-bottom:40px;">
        <h1 style="color:var(--gold); font-size:2.8rem; margin:0;">📁 Intelligent Library Sync</h1>
        <p style="color:var(--muted);">Manage stray files and manual server uploads.</p>
    </div>

    <?php echo $message; ?>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
        <!-- FOLDER SYNC -->
        <div class="glass-card" style="padding:30px; border-radius:20px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1);">
            <h3 style="color:var(--gold);">🚀 Folder Sync</h3>
            <p style="font-size:0.85rem; color:var(--muted); margin-bottom:20px;">Move files from <code style="color:var(--gold);">uploads/sync/</code> to your library.</p>
            <div style="background:rgba(0,0,0,0.2); padding:15px; border-radius:10px; min-height:100px; margin-bottom:20px; font-size:0.85rem;">
                <strong><?php echo count($pending); ?> Files Waiting:</strong>
                <ul style="list-style:none; padding:0; margin-top:10px;">
                    <?php foreach(array_slice($pending, 0, 5) as $pf): ?>
                        <li style="color:var(--muted); margin-bottom:4px;">📄 <?php echo htmlspecialchars($pf); ?></li>
                    <?php endforeach; ?>
                    <?php if(count($pending) > 5) echo "<li style='color:var(--gold);'>... and ".(count($pending)-5)." more</li>"; ?>
                </ul>
            </div>
            <form method="POST">
                <button type="submit" name="sync" class="btn-primary" style="width:100%;" <?php echo count($pending)==0?'disabled':''; ?>>Sync Now</button>
            </form>
        </div>

        <!-- STRAY FILE RE-SCAN -->
        <div class="glass-card" style="padding:30px; border-radius:20px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1);">
            <h3 style="color:var(--gold);">🧠 Smart Re-scan</h3>
            <p style="font-size:0.85rem; color:var(--muted); margin-bottom:20px;">Find files in <code style="color:var(--gold);">uploads/media/</code> that aren't in your library.</p>
            <div style="background:rgba(0,0,0,0.2); padding:15px; border-radius:10px; min-height:100px; margin-bottom:20px; display:flex; align-items:center; justify-content:center;">
                <p style="text-align:center; font-size:0.85rem; color:var(--muted);">Use this to restore missing files or fix database errors.</p>
            </div>
            <form method="POST">
                <button type="submit" name="rescan" class="btn-secondary" style="width:100%;">Run Deep Re-scan</button>
            </form>
        </div>
    </div>

    <p style="margin-top:30px; text-align:center;"><a href="dashboard.php" style="color:var(--muted); font-size:0.85rem;">← Back to Dashboard</a></p>
</div>

<?php require 'includes/footer.php'; ?>
