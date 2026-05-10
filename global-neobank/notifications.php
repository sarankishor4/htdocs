<?php
require_once __DIR__ . '/core/includes/auth_guard.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<nav>
  <div class="logo">GLOBAL<em>BANK</em></div>
  <div class="nav-links">
    <div class="nav-link" onclick="window.location='home.php'">Home</div>
    <div class="nav-link" onclick="window.location='trade.php'">Trade</div>
    <div class="nav-link" onclick="window.location='loans.php'">Loans</div>
    <div class="nav-link" onclick="window.location='earn.php'">Earn</div>
    <div class="nav-link" onclick="window.location='jobs.php'">Jobs</div>
  </div>
  <div class="nav-right">
    <div class="user-pill" onclick="window.location='profile.php'">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>

<div class="wrap">
  <div class="welcome">
    <div class="welcome-left">
      <h1>Your <span>Notifications</span></h1>
      <p>Stay updated with your account activity.</p>
    </div>
    <div class="welcome-right">
      <div class="quick-btn primary" onclick="markAllRead()">✓ Mark All Read</div>
    </div>
  </div>

  <div class="fcard" style="--accent:var(--cyan)">
    <div id="notifList" style="display:flex; flex-direction:column; gap:8px;"></div>
  </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
async function loadNotifications() {
    try {
        const res = await fetch('api/notifications.php?action=list');
        const data = await res.json();
        if(data.success) {
            const container = document.getElementById('notifList');
            if(data.data.length === 0) {
                container.innerHTML = '<p style="text-align:center; color:var(--muted); padding:32px;">No notifications yet.</p>';
                return;
            }
            container.innerHTML = data.data.map(n => {
                const unread = !n.is_read;
                return `
                <div style="padding:16px; background:${unread ? '#ffffff08' : 'transparent'}; border:1px solid var(--border); border-radius:4px; ${unread ? 'border-left:3px solid var(--gold);' : ''}">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div style="font-size:13px; font-weight:${unread ? '600' : '400'}; color:var(--text);">${n.title}</div>
                        <div style="font-size:9px; color:var(--muted);">${n.created_at}</div>
                    </div>
                    <div style="font-size:11px; color:var(--muted); margin-top:6px;">${n.message}</div>
                </div>`;
            }).join('');
        }
    } catch(e) {}
}

async function markAllRead() {
    await fetch('api/notifications.php?action=read_all', { method:'POST' });
    loadNotifications();
}

document.addEventListener('DOMContentLoaded', loadNotifications);
</script>
</body>
</html>
