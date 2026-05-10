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
<title>Developer API — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
<style>
.key-card { padding:16px; background:var(--surface); border:1px solid var(--border); border-left:3px solid var(--cyan); border-radius:4px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; }
.key-name { font-weight:600; font-size:14px; margin-bottom:4px; }
.key-val { font-family:monospace; color:var(--gold); font-size:13px; letter-spacing:1px; background:#000; padding:4px 8px; border-radius:4px; margin-right:12px; }
.lock-overlay { position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); display:flex; flex-direction:column; align-items:center; justify-content:center; z-index:10; border-radius:12px; backdrop-filter:blur(4px); }
.lock-icon { font-size:48px; margin-bottom:16px; color:var(--gold); }
</style>
</head>
<body>
<nav>
  <div class="logo">GLOBAL<em>BANK</em></div>
  <div class="nav-links">
    <div class="nav-link" onclick="window.location='home.php'">Home</div>
    <div class="nav-link" onclick="window.location='cards.php'">Cards</div>
    <div class="nav-link" onclick="window.location='tiers.php'">Upgrade</div>
    <div class="nav-link active" onclick="window.location='developers.php'">Developers</div>
  </div>
  <div class="nav-right">
    <div class="user-pill" onclick="window.location='account.php'">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>

<div class="wrap" style="position:relative;">
  <div class="welcome">
    <div class="welcome-left">
      <h1>Developer <span>API</span></h1>
      <p>Generate API keys to build automated trading bots or integrate NeoBank into your app.</p>
    </div>
  </div>

  <div class="grid-2" style="position:relative;" id="apiContainer">
    <div id="tierLock" class="lock-overlay" style="display:none;">
        <div class="lock-icon">🔒</div>
        <h2 style="font-family:var(--display); letter-spacing:2px;">METAL TIER REQUIRED</h2>
        <p style="color:var(--muted); margin-top:8px; font-size:12px;">API Access is exclusive to Metal tier members.</p>
        <button class="act-btn fill" style="margin-top:16px;" onclick="window.location='tiers.php'">UPGRADE NOW</button>
    </div>

    <div class="fcard" style="--accent:var(--cyan)">
      <div class="fcard-top">
        <div class="fcard-title">GENERATE KEY</div>
      </div>
      <div style="margin-top:16px; display:flex; gap:10px;">
          <input type="text" id="keyName" placeholder="Key Label (e.g. Trading Bot 1)" style="flex:1;padding:12px;background:var(--surface);border:1px solid var(--border);color:var(--text);">
          <button class="act-btn fill" onclick="generateKey()">CREATE</button>
      </div>
    </div>

    <div class="fcard" style="--accent:var(--blue)">
      <div class="fcard-top">
        <div class="fcard-title">ACTIVE KEYS</div>
      </div>
      <div id="keysList" style="margin-top:16px;">Loading...</div>
    </div>
  </div>
</div>

<script>
async function loadKeys() {
    const res = await fetch('api/developer.php?action=list_keys');
    const data = await res.json();
    
    if(!data.success && data.error === 'tier_required') {
        document.getElementById('tierLock').style.display = 'flex';
        return;
    }

    const container = document.getElementById('keysList');
    if(data.success && data.data.length > 0) {
        container.innerHTML = data.data.map(k => `
            <div class="key-card">
                <div>
                    <div class="key-name">${k.key_name}</div>
                    <div style="font-size:10px; color:var(--muted)">Created: ${k.created_at}</div>
                </div>
                <div style="display:flex; align-items:center;">
                    <div class="key-val">${k.api_key}</div>
                    <button class="act-btn" style="padding:4px 8px; color:var(--red); border-color:var(--red);" onclick="revokeKey(${k.id})">REVOKE</button>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = '<div style="color:var(--muted); font-size:12px;">No active API keys.</div>';
    }
}

async function generateKey() {
    const name = document.getElementById('keyName').value;
    if(!name) return alert("Enter a key label.");
    
    const fd = new FormData(); fd.append('name', name);
    const res = await fetch('api/developer.php?action=create_key', {method:'POST', body:fd});
    const data = await res.json();
    if(data.success) {
        document.getElementById('keyName').value = '';
        loadKeys();
    } else alert(data.error);
}

async function revokeKey(id) {
    if(!confirm("Revoke this key? It will immediately stop working.")) return;
    const fd = new FormData(); fd.append('id', id);
    await fetch('api/developer.php?action=revoke_key', {method:'POST', body:fd});
    loadKeys();
}

document.addEventListener('DOMContentLoaded', loadKeys);
</script>
</body>
</html>
