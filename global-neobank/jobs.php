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
<title>Jobs — GlobalBank</title>
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
    <div class="nav-link" onclick="window.location='earn.php'">Earn</div>
    <div class="nav-link active">Jobs</div>
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
      <h1>Human Capital <span>Jobs</span></h1>
      <p>Complete verified micro-tasks to earn money or repay active loans.</p>
    </div>
  </div>

  <div class="grid-2">
    <!-- JOBS SECTION -->
    <div class="fcard" style="--accent:var(--purple); grid-column: span 2;">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--purple)">💼</div>
        <div class="fcard-title">AVAILABLE TASKS</div>
      </div>
      <div id="jobsList" style="display:flex; flex-direction:column; gap:12px; margin-top:16px;"></div>
    </div>
  </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
async function loadJobs() {
    try {
        const res = await fetch('api/jobs.php?action=list');
        const data = await res.json();
        if(data.success) {
            const container = document.getElementById('jobsList');
            container.innerHTML = data.data.map(j => `
                <div style="background:var(--surface); padding:16px; border:1px solid var(--border); border-radius:4px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-size:13px; font-weight:500; color:var(--text);">${j.title}</div>
                        <div style="font-size:11px; color:var(--muted); margin-top:4px;">⏱ ${j.time} &nbsp;·&nbsp; 🎓 ${j.skill}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:14px; font-weight:600; color:var(--green); margin-bottom:6px;">+$${j.reward}</div>
                        <button class="act-btn" onclick="completeJob(${j.id}, ${j.reward})" style="padding:6px 12px; font-size:10px;">DO TASK</button>
                    </div>
                </div>
            `).join('');
        }
    } catch(e) { console.error('Failed to load jobs'); }
}

async function completeJob(id, reward) {
    if(!confirm('Complete this task for $' + reward + '?')) return;
    
    const formData = new FormData();
    formData.append('job_id', id);
    formData.append('reward', reward);
    
    try {
        const res = await fetch('api/jobs.php?action=complete', { method: 'POST', body: formData });
        const data = await res.json();
        
        if(data.success) {
            alert(data.message);
        } else {
            alert('Error: ' + data.error);
        }
    } catch(e) { alert('Network error.'); }
}

document.addEventListener('DOMContentLoaded', loadJobs);
</script>
</body>
</html>
