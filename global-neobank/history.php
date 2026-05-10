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
<title>History — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
<style>
.txn-table{width:100%;border-collapse:collapse;font-size:12px}
.txn-table th{text-align:left;padding:10px 12px;color:var(--gold);text-transform:uppercase;font-size:9px;letter-spacing:1.5px;border-bottom:1px solid var(--border)}
.txn-table td{padding:10px 12px;border-bottom:1px solid #0a0f16;color:var(--text)}
.txn-table tr:hover td{background:#ffffff05}
.tbadge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:9px;font-weight:600;text-transform:uppercase}
.fbar{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap}
.fbtn{padding:6px 14px;font-size:10px;border:1px solid var(--border);background:transparent;color:var(--muted);cursor:pointer;border-radius:2px;text-transform:uppercase;letter-spacing:1px}
.fbtn:hover,.fbtn.active{border-color:var(--gold);color:var(--gold)}
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
    <div class="user-pill" onclick="window.location='profile.php'">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>
<div class="wrap">
  <div class="welcome"><div class="welcome-left"><h1>Transaction <span>History</span></h1><p>Your complete financial activity log.</p></div></div>
  <div class="fcard" style="--accent:var(--green);overflow-x:auto">
    <div class="fbar">
      <button class="fbtn active" onclick="filterT('all',this)">All</button>
      <button class="fbtn" onclick="filterT('deposit',this)">Deposits</button>
      <button class="fbtn" onclick="filterT('withdrawal',this)">Withdrawals</button>
      <button class="fbtn" onclick="filterT('transfer',this)">Transfers</button>
      <button class="fbtn" onclick="filterT('trade',this)">Trades</button>
      <button class="fbtn" onclick="filterT('loan_disbursement',this)">Loans</button>
    </div>
    <table class="txn-table"><thead><tr><th>Date</th><th>Type</th><th>Description</th><th>Amount</th><th>Status</th></tr></thead><tbody id="txnBody"></tbody></table>
  </div>
</div>
<script src="assets/js/dashboard.js"></script>
<script>
let allT=[];
async function loadH(){try{const r=await fetch('api/transactions.php?action=list');const d=await r.json();if(d.success){allT=d.data;renderT(allT);}}catch(e){}}
function renderT(t){const b=document.getElementById('txnBody');if(!t.length){b.innerHTML='<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:32px">No transactions.</td></tr>';return;}
b.innerHTML=t.map(x=>{const a=parseFloat(x.amount);const c=a>0?'var(--green)':'var(--red)';return`<tr><td style="font-size:10px;color:var(--muted)">${x.created_at}</td><td><span class="tbadge" style="background:#ffffff10;color:var(--cyan)">${x.type.replace('_',' ')}</span></td><td>${x.description||'—'}</td><td style="color:${c};font-weight:600">${a>0?'+':''}$${Math.abs(a).toFixed(2)}</td><td><span class="tbadge" style="background:#00e87a20;color:var(--green)">${x.status}</span></td></tr>`;}).join('');}
function filterT(type,btn){document.querySelectorAll('.fbtn').forEach(b=>b.classList.remove('active'));btn.classList.add('active');if(type==='all')renderT(allT);else renderT(allT.filter(t=>t.type===type));}
document.addEventListener('DOMContentLoaded',loadH);
</script>
</body>
</html>
