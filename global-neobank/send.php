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
<title>Send Money — GlobalBank</title>
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
    <div class="notif-btn" onclick="window.location='notifications.php'">🔔<div class="notif-badge" id="notifCount">0</div></div>
    <div class="user-pill" onclick="window.location='profile.php'">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>

<div class="wrap">
  <div class="welcome">
    <div class="welcome-left">
      <h1>Send <span>Money</span></h1>
      <p>Transfer funds instantly to any GlobalBank user worldwide.</p>
    </div>
  </div>

  <div class="grid-2">
    <!-- P2P SEND -->
    <div class="fcard" style="--accent:var(--blue)">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--blue)">↗</div>
        <div class="fcard-title">SEND TO USER</div>
      </div>
      <form onsubmit="event.preventDefault(); sendToUser();" style="margin-top:16px;">
          <div style="margin-bottom:12px;">
              <label style="font-size:10px; color:var(--muted);">Recipient Email</label>
              <input type="email" id="sendEmail" placeholder="user@example.com" required style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); margin-top:4px;">
          </div>
          <div style="margin-bottom:12px;">
              <label style="font-size:10px; color:var(--muted);">Amount (USD)</label>
              <input type="number" id="sendAmt" placeholder="100.00" required min="1" step="0.01" style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); margin-top:4px;">
          </div>
          <div style="margin-bottom:16px;">
              <label style="font-size:10px; color:var(--muted);">Note (optional)</label>
              <input type="text" id="sendNote" placeholder="Lunch money" style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); margin-top:4px;">
          </div>
          <button type="submit" class="act-btn fill" style="width:100%;">SEND NOW</button>
          <p id="sendMsg" style="font-size:11px; margin-top:8px;"></p>
      </form>
    </div>

    <!-- DEPOSIT / WITHDRAW -->
    <div class="fcard" style="--accent:var(--green)">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--green)">🏦</div>
        <div class="fcard-title">DEPOSIT & WITHDRAW</div>
      </div>

      <div style="margin:16px 0; padding:16px; background:#ffffff05; border-radius:4px; text-align:center;">
          <div style="font-size:10px; color:var(--muted);">AVAILABLE BALANCE</div>
          <div id="availBal" style="font-size:32px; font-weight:700; color:var(--green); font-family:var(--display);">$0.00</div>
      </div>

      <form onsubmit="event.preventDefault();" style="margin-top:16px;">
          <div style="margin-bottom:12px;">
              <input type="number" id="bankAmt" placeholder="Amount (USD)" required min="1" step="0.01" style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text);">
          </div>
          <div style="display:flex; gap:10px;">
              <button type="button" onclick="bankAction('deposit')" class="act-btn fill" style="flex:1;">↓ DEPOSIT</button>
              <button type="button" onclick="bankAction('withdraw')" class="act-btn" style="flex:1; border-color:var(--red); color:var(--red);">↑ WITHDRAW</button>
          </div>
          <p id="bankMsg" style="font-size:11px; margin-top:8px;"></p>
      </form>
    </div>
  </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
async function loadBalance() {
    try {
        const res = await fetch('api/account.php');
        const data = await res.json();
        if(data.success) {
            document.getElementById('availBal').innerText = '$' + data.fiat_balance.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
        }
    } catch(e) {}
}

async function sendToUser() {
    const email = document.getElementById('sendEmail').value;
    const amount = document.getElementById('sendAmt').value;
    const note = document.getElementById('sendNote').value || 'P2P Transfer';
    const msg = document.getElementById('sendMsg');

    const fd = new FormData();
    fd.append('email', email);
    fd.append('amount', amount);
    fd.append('note', note);

    try {
        const res = await fetch('api/transfer.php?action=send', { method:'POST', body:fd });
        const data = await res.json();
        if(data.success) {
            msg.style.color = 'var(--green)';
            msg.innerText = data.message;
            document.getElementById('sendAmt').value = '';
            document.getElementById('sendEmail').value = '';
            document.getElementById('sendNote').value = '';
            loadBalance();
        } else {
            msg.style.color = 'var(--red)';
            msg.innerText = data.error;
        }
    } catch(e) { msg.style.color='var(--red)'; msg.innerText='Network error.'; }
}

async function bankAction(action) {
    const amount = document.getElementById('bankAmt').value;
    const msg = document.getElementById('bankMsg');
    if(!amount || amount <= 0) return;

    const fd = new FormData();
    fd.append('amount', amount);

    try {
        const res = await fetch(`api/transfer.php?action=${action}`, { method:'POST', body:fd });
        const data = await res.json();
        if(data.success) {
            msg.style.color = 'var(--green)';
            msg.innerText = data.message;
            document.getElementById('bankAmt').value = '';
            loadBalance();
        } else {
            msg.style.color = 'var(--red)';
            msg.innerText = data.error;
        }
    } catch(e) { msg.style.color='var(--red)'; msg.innerText='Network error.'; }
}

document.addEventListener('DOMContentLoaded', loadBalance);
</script>
</body>
</html>
