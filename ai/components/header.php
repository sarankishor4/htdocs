<?php
require_once __DIR__ . '/../core/Auth.php';
$currentUser = \AI\Core\Auth::user();
?>
<header class="nexus-header">
    <div class="user-welcome">
        <h1>AI NEXUS CORE</h1>
        <p>ENTERPRISE MULTI-AGENT WORKFORCE | SYSTEM ONLINE</p>
    </div>
    
    <div class="header-tools" style="display: flex; gap: 15px; align-items: center;">
        <button onclick="location.reload()" class="btn-secondary" style="background: var(--glass-white); border: 1px solid var(--glass-border); color: #fff; padding: 8px 15px; border-radius: 8px; font-size: 0.7rem; cursor: pointer; transition: 0.3s;">
            <i class="fas fa-sync-alt" style="margin-right: 8px;"></i> REFRESH MATRIX
        </button>
        
        <div class="system-stats" style="display: flex; gap: 20px; font-size: 0.7rem; color: var(--text-dim);">
            <div><span style="color: var(--nexus-primary);">LATENCY:</span> 14ms</div>
            <div><span style="color: var(--nexus-primary);">NODES:</span> 1130</div>
        </div>
        
        <?php if ($currentUser): ?>
        <div class="user-profile" style="display: flex; align-items: center; gap: 15px; border-left: 1px solid var(--glass-border); padding-left: 20px;">
            <div style="text-align: right;">
                <div style="font-weight: 700; font-size: 0.8rem; cursor: pointer;" onclick="window.location.href='profile.php'"><?php echo strtoupper($currentUser['username']); ?></div>
                <div style="font-size: 0.6rem; color: var(--nexus-primary); cursor: pointer;" onclick="logout()">DISCONNECT MATRIX</div>
            </div>
            <div onclick="window.location.href='profile.php'" style="width: 32px; height: 32px; background: var(--nexus-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #000; cursor: pointer;">
                <?php echo substr($currentUser['username'], 0, 1); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</header>

<script>
async function logout() {
    const response = await fetch('api/auth.php?action=logout');
    const data = await response.json();
    if (data.status === 'success') {
        window.location.href = 'login.php';
    }
}
</script>
