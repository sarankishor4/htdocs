<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$status = '';
if (isset($_POST['scan_all'])) {
    $python = 'python';
    $test = shell_exec('python --version 2>&1');
    if (stripos($test, 'Python') === false) $python = 'py';

    $media_res = $conn->query("SELECT id, file_path FROM media WHERE status='active'");
    $count = 0;
    while($m = $media_res->fetch_assoc()) {
        $path = __DIR__ . '/' . ltrim($m['file_path'], '/');
        if (file_exists($path)) {
            $cmd = "$python python/ai_face_engine.py {$m['id']} " . escapeshellarg($path);
            shell_exec($cmd);
            $count++;
        }
    }
    $status = "✅ AI Scan Complete! Processed $count items and updated People library.";
}
?>

<div class="scan-container" style="max-width:700px; margin:60px auto; text-align:center;">
    <div style="font-size:4rem; margin-bottom:20px;">🧠</div>
    <h1 style="color:var(--gold); margin:0;">AI Library Scanner</h1>
    <p style="color:var(--muted); margin-top:10px;">Crevix Intelligence will scan your videos and photos to identify people and organize them into profiles.</p>

    <?php if($status): ?>
        <div class="alert alert-success" style="margin:30px 0;"><?php echo $status; ?></div>
        <a href="people.php" class="btn-primary" style="display:inline-block; padding:15px 40px; border-radius:30px;">📂 View People Library</a>
    <?php else: ?>
        <div class="glass-card" style="margin-top:40px; padding:40px; border-radius:20px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1);">
            <form method="POST">
                <button type="submit" name="scan_all" class="btn-primary" style="width:100%; padding:20px; font-size:1.2rem; font-weight:700; border-radius:15px; box-shadow: 0 10px 20px rgba(0,0,0,0.3);">
                    🚀 Start Intelligent Scan
                </button>
            </form>
            <p style="margin-top:20px; font-size:0.85rem; color:var(--muted);">
                <b>Warning:</b> Scans can take a few minutes depending on library size.<br>
                For best results, ensure videos have clear lighting.
            </p>
        </div>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>
