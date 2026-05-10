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
<title>Savings Goals — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
<style>
.goal-card { padding:24px; background:var(--card); border:1px solid var(--border); border-radius:8px; margin-bottom:16px; position:relative; }
.goal-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
.goal-icon { font-size:32px; background:var(--surface); width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
.goal-info h3 { font-size:18px; color:var(--text); font-family:var(--display); letter-spacing:1px; }
.goal-info p { font-size:11px; color:var(--muted); }
.goal-progress-bar { width:100%; height:8px; background:var(--surface); border-radius:4px; overflow:hidden; margin:16px 0; }
.goal-progress-fill { height:100%; background:linear-gradient(90deg, var(--green), var(--gold)); border-radius:4px; transition:width 0.5s ease; }
</style>
</head>
<body>
<nav>
  <div class="logo">GLOBAL<em>BANK</em></div>
  <div class="nav-links">
    <div class="nav-link" onclick="window.location='home.php'">Home</div>
    <div class="nav-link" onclick="window.location='cards.php'">Cards</div>
    <div class="nav-link" onclick="window.location='savings.php'" style="color:var(--green); border-bottom-color:var(--green);">Savings</div>
    <div class="nav-link" onclick="window.location='trade.php'">Trade</div>
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
      <h1>Savings <span>Vaults</span></h1>
      <p>Set financial goals and lock your funds automatically.</p>
    </div>
    <div class="welcome-right">
      <button class="quick-btn primary" onclick="document.getElementById('newGoalModal').style.display='flex'">+ NEW GOAL</button>
    </div>
  </div>

  <div class="grid-2" id="goalsList">Loading goals...</div>
</div>

<div id="newGoalModal" style="display:none;position:fixed;inset:0;background:#000000e0;z-index:999;align-items:center;justify-content:center;padding:20px;">
    <div class="fcard" style="width:100%;max-width:400px;--accent:var(--green)">
        <div class="fcard-title">Create Savings Goal</div>
        <input type="text" id="gName" placeholder="Goal Name (e.g., Vacation)" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);color:var(--text);margin-top:16px;">
        <input type="number" id="gTarget" placeholder="Target Amount ($)" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);color:var(--text);margin-top:12px;">
        <input type="text" id="gEmoji" placeholder="Emoji (e.g., ✈️)" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);color:var(--text);margin-top:12px;">
        <div style="display:flex;gap:10px;margin-top:16px;">
            <button class="act-btn fill" style="flex:1" onclick="createGoal()">CREATE</button>
            <button class="act-btn" style="flex:1" onclick="document.getElementById('newGoalModal').style.display='none'">CANCEL</button>
        </div>
    </div>
</div>

<script>
async function loadGoals() {
    const res = await fetch('api/savings.php?action=list');
    const data = await res.json();
    const container = document.getElementById('goalsList');
    if(data.success && data.data.length > 0) {
        container.innerHTML = data.data.map(g => {
            const pct = Math.min(100, (g.current_amount / g.target_amount) * 100);
            return `
            <div class="goal-card">
                <div class="goal-top">
                    <div class="goal-icon">${g.emoji}</div>
                    <div style="text-align:right">
                        <div style="font-family:var(--display);font-size:24px;color:var(--green)">$${parseFloat(g.current_amount).toFixed(2)}</div>
                        <div style="font-size:11px;color:var(--muted)">of $${parseFloat(g.target_amount).toFixed(2)}</div>
                    </div>
                </div>
                <div class="goal-info">
                    <h3>${g.name}</h3>
                </div>
                <div class="goal-progress-bar">
                    <div class="goal-progress-fill" style="width:${pct}%"></div>
                </div>
                <div style="display:flex;gap:8px;margin-top:16px;">
                    <button class="act-btn fill" style="flex:1;padding:8px" onclick="fundGoal(${g.id})">+ ADD FUNDS</button>
                    <button class="act-btn" style="padding:8px;color:var(--red);border-color:var(--red);" onclick="deleteGoal(${g.id})">DELETE</button>
                </div>
            </div>`;
        }).join('');
    } else {
        container.innerHTML = '<div style="color:var(--muted); font-size:12px;">No savings goals found. Create one above!</div>';
    }
}

async function createGoal() {
    const fd = new FormData();
    fd.append('name', document.getElementById('gName').value);
    fd.append('target', document.getElementById('gTarget').value);
    fd.append('emoji', document.getElementById('gEmoji').value || '💰');
    const res = await fetch('api/savings.php?action=create', {method:'POST', body:fd});
    document.getElementById('newGoalModal').style.display='none';
    loadGoals();
}

async function fundGoal(id) {
    const amt = prompt("How much USD to add?");
    if(!amt || amt <= 0) return;
    const fd = new FormData(); fd.append('id', id); fd.append('amount', amt);
    const res = await fetch('api/savings.php?action=fund', {method:'POST', body:fd});
    const data = await res.json();
    if(!data.success) alert(data.error);
    loadGoals();
}

async function deleteGoal(id) {
    if(!confirm("Delete goal and return funds to main balance?")) return;
    const fd = new FormData(); fd.append('id', id);
    await fetch('api/savings.php?action=delete', {method:'POST', body:fd});
    loadGoals();
}

document.addEventListener('DOMContentLoaded', loadGoals);
</script>
</body>
</html>
