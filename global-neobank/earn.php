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
<title>Earn — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<!-- NAV -->
<nav>
  <div class="logo">GLOBAL<em>BANK</em></div>
  <div class="nav-links">
    <div class="nav-link" onclick="window.location='home.php'">Home</div>
    <div class="nav-link" onclick="window.location='trade.php'">Trade</div>
    <div class="nav-link" onclick="window.location='loans.php'">Loans</div>
    <div class="nav-link active">Earn</div>
    <div class="nav-link" onclick="window.location='jobs.php'">Jobs</div>
  </div>
  <div class="nav-right">
    <div class="notif-btn">🔔<div class="notif-badge">3</div></div>
    <div class="user-pill" onclick="window.location='profile.php'">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>

<div class="wrap">
  <div class="welcome">
    <div class="welcome-left">
      <h1>High-Yield <span>Savings</span></h1>
      <p>Stake your USD to earn daily interest globally.</p>
    </div>
  </div>

  <div class="grid-2">
    <!-- STAKING STATUS -->
    <div class="fcard" style="--accent:var(--gold)">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--gold)">📈</div>
        <div class="fcard-title">YOUR VAULT</div>
      </div>
      
      <div style="margin-top:24px; text-align:center;">
          <div style="font-size:12px; color:var(--muted); text-transform:uppercase;">Total Staked</div>
          <div id="stakedBal" style="font-size:48px; font-weight:700; color:var(--gold); font-family:var(--display);">$0.00</div>
          
          <div style="margin-top:24px; display:inline-block; padding:12px 24px; background:#f5c84215; border:1px solid var(--gold); border-radius:4px;">
              <div style="font-size:10px; color:var(--gold); text-transform:uppercase;">Current Yield (APY)</div>
              <div id="apyVal" style="font-size:24px; font-weight:600; color:var(--text);">--%</div>
          </div>
      </div>
      
      <div style="margin-top:32px; padding-top:16px; border-top:1px solid var(--border); display:flex; justify-content:space-between;">
          <span style="font-size:12px; color:var(--muted);">Available to Stake:</span>
          <span id="usdBal" style="font-size:14px; font-weight:600;">$0.00</span>
      </div>
    </div>

    <!-- STAKE ACTION -->
    <div class="fcard" style="--accent:var(--green)">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--green)">🔒</div>
        <div class="fcard-title">MANAGE STAKE</div>
      </div>

      <form id="earnForm" style="margin-top:16px;" onsubmit="event.preventDefault();">
          <div style="margin-bottom:12px;">
              <input type="number" id="earnAmount" placeholder="Amount USD" required min="1" step="0.01" style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); outline:none;">
          </div>
          <p id="earnMsg" style="font-size:11px; margin-bottom:12px;"></p>
          <div style="display:flex; gap:10px;">
              <button type="button" onclick="executeEarn('stake')" class="act-btn fill" style="flex:1;">STAKE</button>
              <button type="button" onclick="executeEarn('unstake')" class="act-btn" style="flex:1; border-color:var(--text);">UNSTAKE</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
async function loadEarnData() {
    try {
        const res = await fetch('api/earn.php?action=status');
        const data = await res.json();
        
        if(data.success) {
            document.getElementById('stakedBal').innerText = '$' + parseFloat(data.staked_balance).toFixed(2);
            document.getElementById('usdBal').innerText = '$' + parseFloat(data.usd_balance).toFixed(2);
            document.getElementById('apyVal').innerText = data.apy + '%';
        }
    } catch(e) {}
}

async function executeEarn(action) {
    const amount = document.getElementById('earnAmount').value;
    const msg = document.getElementById('earnMsg');

    if(!amount || amount <= 0) return;

    const formData = new FormData();
    formData.append('amount', amount);

    try {
        const res = await fetch(`api/earn.php?action=${action}`, { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            msg.style.color = 'var(--green)';
            msg.innerText = data.message;
            document.getElementById('earnAmount').value = '';
            loadEarnData();
        } else {
            msg.style.color = 'var(--red)';
            msg.innerText = data.error;
        }
    } catch(err) {
        msg.style.color = 'var(--red)';
        msg.innerText = 'Network Error';
    }
}

document.addEventListener('DOMContentLoaded', loadEarnData);
</script>
</body>
</html>
