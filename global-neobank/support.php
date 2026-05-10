<?php
require_once __DIR__ . '/core/includes/auth_guard.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Support Center — GlobalBank</title>
<link rel="stylesheet" href="assets/css/dashboard.css">
<style>
.ticket-card{background:var(--card);border:1px solid var(--border);border-radius:8px;padding:20px;margin-bottom:12px;transition:border-color .2s}
.ticket-card:hover{border-color:var(--blue)}
.ticket-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.ticket-subject{font-size:15px;font-weight:600;color:var(--text)}
.ticket-id{font-size:10px;color:var(--muted);letter-spacing:1px}
.ticket-body{font-size:12px;color:var(--muted);line-height:1.7;margin-bottom:12px}
.ticket-meta{display:flex;gap:12px;font-size:10px;color:var(--muted)}
.priority-badge{padding:3px 8px;border-radius:3px;font-size:9px;letter-spacing:1px;text-transform:uppercase;font-weight:700}
.priority-badge.low{background:#00e87a20;color:var(--green)}
.priority-badge.medium{background:#2196f320;color:var(--blue)}
.priority-badge.high{background:#f5c84220;color:var(--gold)}
.priority-badge.urgent{background:#ff456020;color:var(--red)}
.status-badge{padding:3px 8px;border-radius:3px;font-size:9px;letter-spacing:1px;text-transform:uppercase;font-weight:700}
.status-badge.open{background:#2196f320;color:var(--blue)}
.status-badge.in_progress{background:#f5c84220;color:var(--gold)}
.status-badge.resolved{background:#00e87a20;color:var(--green)}
.admin-reply-box{margin-top:12px;padding:14px;background:var(--surface);border-left:3px solid var(--green);border-radius:0 4px 4px 0}
.admin-reply-box .label{font-size:9px;color:var(--green);letter-spacing:2px;text-transform:uppercase;margin-bottom:6px}
.admin-reply-box p{font-size:12px;color:var(--text);line-height:1.7}
</style>
</head>
<body>
<nav>
  <div class="logo">GLOBAL<em>BANK</em></div>
  <div class="nav-links">
    <div class="nav-link" onclick="window.location='home.php'">Home</div>
    <div class="nav-link" onclick="window.location='analytics.php'">Analytics</div>
    <div class="nav-link" onclick="window.location='cards.php'">Cards</div>
    <div class="nav-link active">Support</div>
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
      <h1>Support <span>Center</span></h1>
      <p>Get help from our team. All support is completely free for every user.</p>
    </div>
    <div class="welcome-right">
      <button class="quick-btn" onclick="document.getElementById('newTicketModal').style.display='flex'" style="border-color:var(--green);color:var(--green)">+ NEW TICKET</button>
    </div>
  </div>

  <div id="ticketsList">Loading tickets...</div>
</div>

<!-- NEW TICKET MODAL -->
<div id="newTicketModal" style="display:none;position:fixed;inset:0;background:#000000e0;z-index:999;align-items:center;justify-content:center;padding:20px;">
  <div class="fcard" style="width:100%;max-width:480px;--accent:var(--blue)">
    <div class="fcard-title">Submit a Ticket</div>
    <p class="fcard-desc" style="margin-bottom:20px;">Describe your issue and our team will respond within 24 hours.</p>
    <input type="text" id="tickSubject" placeholder="Subject" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);color:var(--text);margin-bottom:12px;">
    <select id="tickPriority" style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);color:var(--text);margin-bottom:12px;">
      <option value="low">Low Priority</option>
      <option value="medium" selected>Medium Priority</option>
      <option value="high">High Priority</option>
      <option value="urgent">Urgent</option>
    </select>
    <textarea id="tickMessage" rows="5" placeholder="Describe your issue in detail..." style="width:100%;padding:12px;background:var(--surface);border:1px solid var(--border);color:var(--text);margin-bottom:16px;resize:vertical;font-family:var(--font)"></textarea>
    <div style="display:flex;gap:10px;">
      <button class="act-btn fill" style="flex:1" onclick="submitTicket()">SUBMIT</button>
      <button class="act-btn" style="flex:1" onclick="document.getElementById('newTicketModal').style.display='none'">CANCEL</button>
    </div>
  </div>
</div>

<div class="bottom-nav">
  <div class="bn-item" onclick="window.location='home.php'"><div class="bn-icon">🏠</div>Home</div>
  <div class="bn-item" onclick="window.location='analytics.php'"><div class="bn-icon">📊</div>Analytics</div>
  <div class="bn-item" onclick="window.location='cards.php'"><div class="bn-icon">💳</div>Cards</div>
  <div class="bn-item active"><div class="bn-icon">🎧</div>Support</div>
  <div class="bn-item" onclick="window.location='account.php'"><div class="bn-icon">⚙️</div>Account</div>
</div>

<script>
async function loadTickets() {
  const res = await fetch('api/support.php?action=list');
  const data = await res.json();
  const container = document.getElementById('ticketsList');
  if(data.success && data.data.length > 0) {
    container.innerHTML = data.data.map(t => `
      <div class="ticket-card">
        <div class="ticket-header">
          <div>
            <div class="ticket-subject">${t.subject}</div>
            <div class="ticket-id">#TK-${String(t.id).padStart(5,'0')}</div>
          </div>
          <div style="display:flex;gap:8px;">
            <span class="priority-badge ${t.priority}">${t.priority}</span>
            <span class="status-badge ${t.status}">${t.status.replace('_',' ')}</span>
          </div>
        </div>
        <div class="ticket-body">${t.message}</div>
        ${t.admin_reply ? `<div class="admin-reply-box"><div class="label">Admin Response</div><p>${t.admin_reply}</p></div>` : ''}
        <div class="ticket-meta">
          <span>Created: ${t.created_at}</span>
          <span>Updated: ${t.updated_at}</span>
        </div>
      </div>
    `).join('');
  } else {
    container.innerHTML = '<div style="color:var(--muted);font-size:12px;padding:40px 0;text-align:center;">No tickets yet. Submit one if you need help!</div>';
  }
}

async function submitTicket() {
  const subject = document.getElementById('tickSubject').value;
  const priority = document.getElementById('tickPriority').value;
  const message = document.getElementById('tickMessage').value;
  if(!subject || !message) return alert('Fill in subject and message.');
  const fd = new FormData();
  fd.append('subject', subject); fd.append('priority', priority); fd.append('message', message);
  const res = await fetch('api/support.php?action=create', {method:'POST', body:fd});
  const data = await res.json();
  if(data.success) {
    document.getElementById('newTicketModal').style.display='none';
    document.getElementById('tickSubject').value='';
    document.getElementById('tickMessage').value='';
    loadTickets();
  } else alert(data.error);
}

document.addEventListener('DOMContentLoaded', loadTickets);
</script>
</body>
</html>
