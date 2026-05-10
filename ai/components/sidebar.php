<?php
require_once __DIR__ . '/../core/Config.php';
use AI\Core\Config;
?>
<aside class="nexus-sidebar">
    <div class="nexus-logo">
        <i class="fas fa-microchip"></i>
        <span>AI NEXUS</span>
    </div>

    <nav class="agent-nav">
        <div class="nav-label" style="font-size: 0.6rem; color: #444; letter-spacing: 2px; margin-bottom: 15px; padding-left: 15px;">DEPARTMENTS</div>
        
        <?php foreach (Config::DEPARTMENTS as $id => $data): ?>
        <div class="agent-item" onclick="showDept('<?php echo $id; ?>')">
            <i class="fas fa-<?php echo $data['icon']; ?>"></i>
            <span><?php echo $data['name']; ?></span>
            <div class="agent-status status-online"></div>
        </div>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer" style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--glass-border);">
        <div class="agent-item" style="opacity: 0.5;">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </div>
        <div class="agent-item" style="color: var(--nexus-accent);">
            <i class="fas fa-power-off"></i>
            <span>Disconnect</span>
        </div>
    </div>
</aside>
