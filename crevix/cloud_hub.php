<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$uid = $_SESSION['user_id'];
$accounts = $conn->query("SELECT * FROM cloud_accounts WHERE user_id=$uid ORDER BY created_at DESC");

// Get total local storage usage
$upload_dir = 'uploads/';
$total_size = 0;
if (file_exists($upload_dir)) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload_dir));
    foreach($it as $file){
        if ($file->isFile()) $total_size += $file->getSize();
    }
}
$formatted_size = round($total_size / (1024 * 1024 * 1024), 2) . ' GB';
?>

<div class="cloud-container" style="max-width:1200px; margin:40px auto; color:white;">
    
    <!-- SYNC OVERLAY -->
    <div id="syncProgress" class="glass-card" style="display:none; margin-bottom:30px; padding:30px; border:2px solid var(--gold); background:rgba(0,0,0,0.5); position:relative; overflow:hidden;">
        <div id="progressFill" style="position:absolute; top:0; left:0; bottom:0; width:0%; background:rgba(212,175,55,0.1); transition: width 0.4s ease;"></div>
        <div style="position:relative; z-index:2; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="margin:0; color:var(--gold);">🚀 SYNC IN PROGRESS</h3>
                <p id="syncStatus" style="font-size:0.8rem; margin:5px 0 0; color:var(--muted);">Starting backup sequence...</p>
            </div>
            <div id="syncStats" style="text-align:right; font-family:monospace; font-weight:700;">
                <span id="syncCount">0</span> / <span id="syncTotal">?</span>
            </div>
        </div>
    </div>
    
    <!-- HEADER SECTION -->
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:40px;">
        <div>
            <h1 style="color:var(--gold); font-size:2.8rem; margin:0; letter-spacing:-1px;">☁️ Crevix Cloud Hub</h1>
            <p style="color:var(--muted);">Offload and backup your 1000+ posts to multiple cloud accounts.</p>
        </div>
        <div class="glass-card" style="padding:15px 25px; border-radius:15px; background:rgba(212,175,55,0.1); border:1px solid var(--gold);">
            <div style="font-size:0.7rem; text-transform:uppercase; font-weight:700; color:var(--gold);">Local Storage Usage</div>
            <div style="font-size:1.8rem; font-weight:800;"><?php echo $formatted_size; ?></div>
        </div>
    </div>

    <div style="grid-template-columns: 2fr 1fr; display:grid; gap:30px;">
        
        <!-- ACCOUNTS LIST -->
        <div>
            <h2 style="font-size:1.4rem; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                Linked Accounts <span style="background:var(--gold); color:black; font-size:0.7rem; padding:2px 8px; border-radius:10px;"><?php echo $accounts->num_rows; ?></span>
            </h2>

            <?php if($accounts->num_rows == 0): ?>
                <div class="glass-card" style="padding:60px; text-align:center; border-radius:20px; border:1px dashed rgba(255,255,255,0.2);">
                    <div style="font-size:4rem; margin-bottom:20px; opacity:0.3;">📂</div>
                    <h3>No cloud accounts linked yet</h3>
                    <p style="color:var(--muted);">Add a Google Drive or Dropbox account to start offloading your media.</p>
                    <button onclick="showAddModal()" class="btn-primary" style="margin-top:20px;">+ Link New Account</button>
                </div>
            <?php else: ?>
                <div style="display:grid; gap:15px;">
                    <?php while($acc = $accounts->fetch_assoc()): ?>
                        <div class="account-card">
                            <div style="display:flex; align-items:center; gap:20px;">
                                <div class="service-icon google_drive">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/1/12/Google_Drive_icon_%282020%29.svg" width="30">
                                </div>
                                <div style="flex:1;">
                                    <h3 style="margin:0; font-size:1.1rem;"><?php echo htmlspecialchars($acc['account_email']); ?></h3>
                                    <div style="font-size:0.8rem; color:var(--muted); margin-top:4px;">
                                        Service: <strong><?php echo ucfirst($acc['service_name']); ?></strong> • Connected since <?php echo date('M d, Y', strtotime($acc['created_at'])); ?>
                                    </div>
                                </div>
                                <div style="display:flex; gap:10px;">
                                    <button class="btn-primary" onclick="startBackup(<?php echo $acc['id']; ?>)" style="padding:8px 20px; font-size:0.8rem; background:var(--gold); color:black;">🚀 Sync Now</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <button onclick="showAddModal()" style="background:none; border:1px dashed rgba(255,255,255,0.2); color:var(--muted); padding:15px; border-radius:15px; cursor:pointer; width:100%;">+ Add Another Account</button>
                </div>
            <?php endif; ?>
        </div>

        <!-- CLOUD STATS / TIPS -->
        <div>
            <div class="glass-card" style="padding:25px; border-radius:20px; margin-bottom:30px;">
                <h3 style="color:var(--gold); margin-top:0;">⚡ Multi-Cloud Magic</h3>
                <p style="font-size:0.85rem; color:var(--muted); line-height:1.6;">
                    Linking multiple accounts allows you to bypass the **750GB daily upload limit** of individual Google accounts. 
                    Crevix can intelligently distribute your 1000+ posts across your linked drives.
                </p>
                <div style="margin-top:20px; padding:15px; background:rgba(0,255,0,0.05); border-left:3px solid #00ff00; font-size:0.8rem;">
                    <strong>Tip:</strong> Offloaded media stays visible in your gallery but loads directly from the cloud!
                </div>
            </div>

            <div class="glass-card" style="padding:25px; border-radius:20px;">
                <h3 style="margin-top:0; font-size:1rem;">🛠️ Automation Status</h3>
                <div class="status-row">
                    <span>Auto-Sync</span>
                    <span class="status-tag off">Disabled</span>
                </div>
                <div class="status-row">
                    <span>Local Cleanup</span>
                    <span class="status-tag on">Active</span>
                </div>
                <div class="status-row">
                    <span>Direct Cloud Playback</span>
                    <span class="status-tag on">Active</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ADD ACCOUNT MODAL -->
<div id="addModal" class="modal-overlay" style="display:none;">
    <div class="modal-content glass-card" style="max-width:500px; padding:40px; border-radius:25px; position:relative;">
        <button onclick="hideAddModal()" style="position:absolute; top:20px; right:20px; background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;">&times;</button>
        <h2 style="color:var(--gold); margin-top:0;">Link Cloud Storage</h2>
        <p style="color:var(--muted); font-size:0.9rem;">Choose a service to connect with Crevix.</p>
        
        <div style="display:grid; gap:15px; margin-top:30px;">
            <button class="connector-btn google" onclick="linkGoogle()">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" width="20">
                Google Drive
            </button>
            <button class="connector-btn dropbox">
                <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Dropbox_Icon.svg" width="20">
                Dropbox (Coming Soon)
            </button>
            <button class="connector-btn s3">
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/bc/Amazon-S3-Logo.svg" width="20">
                AWS S3 / R2
            </button>
        </div>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}
function hideAddModal() {
    document.getElementById('addModal').style.display = 'none';
}
function linkGoogle() {
    // Redirect to a Google OAuth page (Mock for now)
    window.location.href = 'google_link.php';
}
async function startBackup(id) {
    const progressEl = document.getElementById('syncProgress');
    const statusEl = document.getElementById('syncStatus');
    const countEl = document.getElementById('syncCount');
    const fillEl = document.getElementById('progressFill');
    
    progressEl.style.display = 'block';
    let processed = 0;
    let total = 0; // We'll estimate or get this later
    
    async function runBatch() {
        statusEl.innerText = "Processing batch of files...";
        try {
            const res = await fetch(`cloud_sync.php?account_id=${id}`);
            const data = await res.json();
            
            if (data.error) {
                statusEl.innerText = "❌ Error: " + data.error;
                return;
            }
            
            const batchCount = data.results.length;
            if (batchCount === 0) {
                statusEl.innerText = "✅ ALL FILES SYNCED! Your library is backed up.";
                fillEl.style.width = '100%';
                setTimeout(() => { progressEl.style.display = 'none'; location.reload(); }, 3000);
                return;
            }
            
            processed += batchCount;
            countEl.innerText = processed;
            fillEl.style.width = (processed % 100) + '%'; // Animation effect
            
            // Recurse for next batch
            runBatch();
        } catch (e) {
            statusEl.innerText = "❌ Sync Interrupted. Retrying...";
            setTimeout(runBatch, 5000);
        }
    }
    
    runBatch();
}
</script>

<style>
.account-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 20px;
    border-radius: 20px;
    transition: all 0.3s;
}
.account-card:hover {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,215,0,0.3);
    transform: translateY(-2px);
}
.service-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    font-weight: 800;
}
.service-icon.google_drive { background: #fff; border: 1px solid #ddd; }

.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.85);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.connector-btn {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 25px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.05);
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-align: left;
}
.connector-btn:hover {
    background: rgba(255,255,255,0.1);
    transform: translateX(5px);
}
.connector-btn img { filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2)); }

.status-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.status-tag {
    font-size: 0.7rem;
    padding: 2px 8px;
    border-radius: 5px;
    font-weight: 700;
    text-transform: uppercase;
}
.status-tag.on { background: rgba(0,255,0,0.1); color: #00ff00; }
.status-tag.off { background: rgba(255,0,0,0.1); color: #ff0000; }
</style>

<?php require 'includes/footer.php'; ?>
