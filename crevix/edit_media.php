<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$uid = $_SESSION['user_id'];
$mid = (int)($_GET['id'] ?? 0);

// Fetch media with owner check
$media = $conn->query("SELECT * FROM media WHERE id=$mid AND (user_id=$uid OR '{$_SESSION['role']}' = 'admin')")->fetch_assoc();

if (!$media) { 
    echo "<div class='container' style='padding-top:100px;text-align:center;'><h2 style='color:var(--gold);'>❌ Access Denied</h2><p>You don't have permission to edit this media.</p><br><a href='dashboard.php' class='btn-primary'>Go to Dashboard</a></div>"; 
    require 'includes/footer.php'; 
    exit(); 
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string(trim($_POST['title']));
    $desc = $conn->real_escape_string(trim($_POST['description']));
    $category = $conn->real_escape_string($_POST['category']);
    $status = $conn->real_escape_string($_POST['status'] ?? 'active'); // active/private

    // Handle Thumbnail Update
    $thumb_sql = "";
    if (isset($_FILES['new_thumbnail']) && $_FILES['new_thumbnail']['error'] == 0) {
        $target_dir = "uploads/media/";
        $ext = pathinfo($_FILES["new_thumbnail"]["name"], PATHINFO_EXTENSION);
        $new_thumb = $target_dir . "thumb_update_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES["new_thumbnail"]["tmp_name"], $new_thumb)) {
            $thumb_sql = ", thumbnail='$new_thumb'";
        }
    }

    $sql = "UPDATE media SET title='$title', description='$desc', category='$category', status='$status' $thumb_sql WHERE id=$mid";
    if ($conn->query($sql)) {
        $message = "<div class='alert alert-success'>✨ Changes saved successfully!</div>";
        $media = $conn->query("SELECT * FROM media WHERE id=$mid")->fetch_assoc();
    } else {
        $message = "<div class='alert alert-error'>❌ Error updating: " . $conn->error . "</div>";
    }
}
?>

<div class="edit-container" style="max-width:800px; margin:40px auto; padding:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <div>
            <h1 style="color:var(--gold); font-size:2rem; margin:0;">✏️ Edit Media</h1>
            <p style="color:var(--muted); margin:5px 0 0;">Modify your content's visibility and details.</p>
        </div>
        <a href="dashboard.php" class="btn-secondary" style="padding:10px 20px;">← Dashboard</a>
    </div>

    <?php echo $message; ?>

    <div class="glass-card" style="padding:30px; border-radius:20px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); backdrop-filter:blur(10px);">
        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
                <!-- LEFT COL -->
                <div>
                    <div class="input-group">
                        <label style="color:var(--gold);">Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($media['title']); ?>" required style="background:rgba(0,0,0,0.2); border-color:var(--surface2);">
                    </div>

                    <div class="input-group">
                        <label style="color:var(--gold);">Visibility</label>
                        <select name="status" style="background:rgba(0,0,0,0.2); border-color:var(--surface2);">
                            <option value="active" <?php echo ($media['status']=='active')?'selected':''; ?>>🌎 Public (Everyone can see)</option>
                            <option value="private" <?php echo ($media['status']=='private')?'selected':''; ?>>🔒 Private (Only you can see)</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label style="color:var(--gold);">Category</label>
                        <select name="category" style="background:rgba(0,0,0,0.2); border-color:var(--surface2);">
                            <?php foreach(['General','Music','Gaming','Education','Vlog','Art','Tech','Photography','Film'] as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo ($media['category']==$cat)?'selected':''; ?>><?php echo $cat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- RIGHT COL (Thumbnail) -->
                <div>
                    <label style="color:var(--gold); display:block; margin-bottom:10px;">Thumbnail</label>
                    <div style="position:relative; border-radius:12px; overflow:hidden; aspect-ratio:16/9; background:#000; border:1px solid var(--surface2);">
                        <img src="<?php echo $media['thumbnail'] ?: 'assets/no-thumb.jpg'; ?>" id="thumbPreview" style="width:100%; height:100%; object-fit:cover;">
                        <div style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.7); padding:10px; text-align:center;">
                            <label for="thumbInput" style="cursor:pointer; color:var(--gold); font-size:0.85rem;">📸 Change Thumbnail</label>
                            <input type="file" id="thumbInput" name="new_thumbnail" accept="image/*" style="display:none;" onchange="previewFile(this)">
                        </div>
                    </div>
                    <p style="font-size:0.75rem; color:var(--muted); margin-top:8px;">Recommended: 1280x720px (16:9 ratio)</p>
                </div>
            </div>

            <div class="input-group" style="margin-top:20px;">
                <label style="color:var(--gold);">Description</label>
                <textarea name="description" rows="5" style="background:rgba(0,0,0,0.2); border-color:var(--surface2);"><?php echo htmlspecialchars($media['description']); ?></textarea>
            </div>

            <div style="display:flex; gap:15px; margin-top:30px;">
                <button type="submit" class="btn-primary" style="flex:2; padding:15px; font-weight:600;">💾 Save All Changes</button>
                <a href="watch.php?id=<?php echo $mid; ?>" class="btn-secondary" style="flex:1; display:flex; align-items:center; justify-content:center;">👁 View</a>
            </div>
        </form>
    </div>

    <div style="margin-top:30px; text-align:center; padding:20px; border-radius:15px; background:rgba(255,0,0,0.05); border:1px solid rgba(255,0,0,0.1);">
        <p style="color:#ff6b6b; font-size:0.9rem; margin-bottom:10px;">Dangerous Actions</p>
        <button onclick="confirmDelete()" style="background:none; border:1px solid #ff6b6b; color:#ff6b6b; padding:8px 20px; border-radius:8px; cursor:pointer; font-size:0.85rem;">🗑 Delete Permanently</button>
    </div>
</div>

<script>
function previewFile(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('thumbPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function confirmDelete() {
    if (confirm("🚨 Are you absolutely sure? This will delete the video file and all data permanently!")) {
        window.location.href = "delete_media.php?id=<?php echo $mid; ?>";
    }
}
</script>

<?php require 'includes/footer.php'; ?>
