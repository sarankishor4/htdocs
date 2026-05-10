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
<title>Virtual Cards — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
<style>
.card-wrapper { position:relative; perspective:1000px; margin-bottom:24px; cursor:pointer; }
.vcard { width:100%; height:200px; background:linear-gradient(135deg, #2196f3, #0b1118); border-radius:12px; padding:24px; display:flex; flex-direction:column; justify-content:space-between; box-shadow:0 10px 30px rgba(0,0,0,0.5); transition:transform 0.6s; transform-style:preserve-3d; border:1px solid #ffffff30; }
.vcard.frozen { filter:grayscale(100%); opacity:0.6; }
.card-top { display:flex; justify-content:space-between; align-items:center; }
.card-chip { width:40px; height:30px; background:linear-gradient(135deg, #d4af37, #f3e5ab); border-radius:4px; }
.card-network { font-family:var(--display); font-size:24px; font-style:italic; }
.card-number { font-family:monospace; font-size:22px; letter-spacing:4px; text-shadow:0 2px 4px rgba(0,0,0,0.5); }
.card-bottom { display:flex; justify-content:space-between; align-items:flex-end; font-size:12px; text-transform:uppercase; letter-spacing:2px; }
</style>
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
    <div class="user-pill" onclick="window.location='account.php'">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>

<div class="wrap">
  <div class="welcome">
    <div class="welcome-left">
      <h1>Virtual <span>Cards</span></h1>
      <p>Generate secure virtual cards for online spending.</p>
    </div>
    <div class="welcome-right">
      <div class="quick-btn primary" onclick="document.getElementById('newCardModal').style.display='flex'">+ NEW CARD</div>
    </div>
  </div>

  <div class="grid-2" id="cardsList">
      <div style="color:var(--muted); font-size:12px;">Loading your cards...</div>
  </div>
</div>

<div id="newCardModal" style="display:none;position:fixed;inset:0;background:#000000e0;z-index:999;align-items:center;justify-content:center;padding:20px;">
    <div class="fcard" style="width:100%;max-width:400px;--accent:var(--blue)">
        <div class="fcard-title">Create Virtual Card</div>
        <p class="fcard-desc" style="margin-bottom:20px;">Instantly generate a virtual card tied to your USD balance.</p>
        <input type="text" id="newCardName" placeholder="Card Label (e.g., Online Subs)" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);color:var(--text);margin-bottom:12px;">
        <input type="number" id="newCardLimit" placeholder="Monthly Limit ($)" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);color:var(--text);margin-bottom:16px;">
        <div style="display:flex;gap:10px;">
            <button class="act-btn fill" style="flex:1" onclick="createCard()">GENERATE</button>
            <button class="act-btn" style="flex:1" onclick="document.getElementById('newCardModal').style.display='none'">CANCEL</button>
        </div>
    </div>
</div>

<script>
async function loadCards() {
    try {
        const res = await fetch('api/cards.php?action=list');
        const data = await res.json();
        const container = document.getElementById('cardsList');
        if(data.success && data.data.length > 0) {
            container.innerHTML = data.data.map(c => `
                <div class="card-wrapper">
                    <div class="vcard ${c.status === 'frozen' ? 'frozen' : ''}" style="background:linear-gradient(135deg, var(--${c.color || 'blue'}), #0b1118)">
                        <div class="card-top">
                            <div class="card-chip"></div>
                            <div class="card-network">${c.network}</div>
                        </div>
                        <div class="card-number">**** **** **** ${c.last_four}</div>
                        <div class="card-bottom">
                            <div>
                                <div style="font-size:8px;color:#fff8;">NAME</div>
                                <div>${c.card_name}</div>
                            </div>
                            <div style="text-align:right">
                                <div style="font-size:8px;color:#fff8;">SPENT</div>
                                <div>$${parseFloat(c.spent_this_month).toFixed(2)} / $${parseFloat(c.monthly_limit).toFixed(0)}</div>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <button class="act-btn" style="flex:1;padding:8px" onclick="toggleCard(${c.id}, '${c.status}')">${c.status === 'active' ? 'FREEZE' : 'UNFREEZE'}</button>
                        <button class="act-btn" style="padding:8px;color:var(--red);border-color:var(--red);" onclick="deleteCard(${c.id})">DELETE</button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div style="color:var(--muted); font-size:12px;">No virtual cards found. Create one above!</div>';
        }
    } catch(e) {}
}

async function createCard() {
    const name = document.getElementById('newCardName').value;
    const limit = document.getElementById('newCardLimit').value;
    if(!name || !limit) return alert("Fill all fields");
    
    const fd = new FormData();
    fd.append('name', name);
    fd.append('limit', limit);
    
    const res = await fetch('api/cards.php?action=create', {method:'POST', body:fd});
    const data = await res.json();
    if(data.success) {
        document.getElementById('newCardModal').style.display='none';
        document.getElementById('newCardName').value='';
        document.getElementById('newCardLimit').value='';
        loadCards();
    } else alert(data.error);
}

async function toggleCard(id, currentStatus) {
    const fd = new FormData(); fd.append('id', id); fd.append('status', currentStatus === 'active' ? 'frozen' : 'active');
    const res = await fetch('api/cards.php?action=toggle', {method:'POST', body:fd});
    loadCards();
}

async function deleteCard(id) {
    if(!confirm("Delete this card permanently?")) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('api/cards.php?action=delete', {method:'POST', body:fd});
    loadCards();
}

document.addEventListener('DOMContentLoaded', loadCards);
</script>
</body>
</html>
