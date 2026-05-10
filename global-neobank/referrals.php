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
<title>Referrals — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
<nav>
  <div class="logo">GLOBAL<em>BANK</em></div>
  <div class="nav-links">
    <div class="nav-link" onclick="window.location='home.php'">Home</div>
    <div class="nav-link" onclick="window.location='cards.php'">Cards</div>
    <div class="nav-link" onclick="window.location='savings.php'">Savings</div>
    <div class="nav-link" onclick="window.location='referrals.php'" style="color:var(--green); border-bottom-color:var(--green);">Referrals</div>
  </div>
  <div class="nav-right">
    <div class="user-pill" onclick="window.location='account.php'">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>

<div class="wrap">
  <div class="welcome">
    <div class="welcome-left">
      <h1>Invite & <span>Earn</span></h1>
      <p>Invite friends and earn $25.00 for every verified user.</p>
    </div>
  </div>

  <div class="grid-2">
    <div class="fcard" style="--accent:var(--gold)">
      <div class="fcard-top">
        <div class="fcard-title">SEND INVITE</div>
      </div>
      <p style="font-size:12px; color:var(--muted); margin-bottom:16px;">Enter your friend's email address to send them a unique referral link.</p>
      <div style="display:flex;gap:10px;">
          <input type="email" id="refEmail" placeholder="Friend's Email" style="flex:1;padding:12px;background:var(--surface);border:1px solid var(--border);color:var(--text);">
          <button class="act-btn fill" onclick="sendInvite()">INVITE</button>
      </div>
    </div>

    <div class="fcard" style="--accent:var(--blue)">
      <div class="fcard-top">
        <div class="fcard-title">MY REFERRALS</div>
      </div>
      <div id="refList" style="margin-top:16px;">Loading...</div>
    </div>
  </div>
</div>

<script>
async function loadRefs() {
    const res = await fetch('api/referrals.php?action=list');
    const data = await res.json();
    const container = document.getElementById('refList');
    if(data.success && data.data.length > 0) {
        container.innerHTML = data.data.map(r => `
            <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border);">
                <div>
                    <div style="font-size:13px;color:var(--text);">${r.referred_email}</div>
                    <div style="font-size:10px;color:var(--muted);">${r.created_at}</div>
                </div>
                <div>
                    <span class="status-pill" style="color: ${r.status==='rewarded'?'var(--green)':'var(--gold)'}">${r.status}</span>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = '<div style="color:var(--muted); font-size:12px;">No referrals yet. Start inviting!</div>';
    }
}

async function sendInvite() {
    const email = document.getElementById('refEmail').value;
    if(!email) return alert("Enter an email");
    
    const fd = new FormData(); fd.append('email', email);
    const res = await fetch('api/referrals.php?action=invite', {method:'POST', body:fd});
    const data = await res.json();
    if(data.success) {
        alert('Invite sent successfully!');
        document.getElementById('refEmail').value = '';
        loadRefs();
    } else alert(data.error);
}

document.addEventListener('DOMContentLoaded', loadRefs);
</script>
</body>
</html>
