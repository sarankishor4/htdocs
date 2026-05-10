<?php
require_once __DIR__ . '/../core/includes/admin_guard.php';
requireAdmin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.admin-tabs { display:flex; gap:0; border-bottom:1px solid var(--border); margin-bottom:24px; }
.admin-tab { padding:12px 24px; font-size:11px; text-transform:uppercase; letter-spacing:1.5px; cursor:pointer; border-bottom:2px solid transparent; color:var(--muted); transition:all .2s; }
.admin-tab:hover { color:var(--text); }
.admin-tab.active { color:var(--gold); border-bottom-color:var(--gold); }
.admin-panel { display:none; }
.admin-panel.active { display:block; }
.admin-table { width:100%; border-collapse:collapse; font-size:12px; }
.admin-table th { text-align:left; padding:10px 12px; color:var(--gold); text-transform:uppercase; font-size:9px; letter-spacing:1.5px; border-bottom:1px solid var(--border); }
.admin-table td { padding:10px 12px; border-bottom:1px solid #0a0f16; color:var(--text); }
.admin-table tr:hover td { background:#ffffff05; }
.badge { display:inline-block; padding:2px 8px; border-radius:12px; font-size:9px; font-weight:600; text-transform:uppercase; }
.badge-green { background:#00e87a20; color:var(--green); }
.badge-red { background:#ff456020; color:var(--red); }
.badge-gold { background:#f5c84220; color:var(--gold); }
.badge-blue { background:#00bfff20; color:var(--cyan); }
.mini-btn { padding:4px 10px; font-size:9px; border:1px solid var(--border); background:transparent; color:var(--text); cursor:pointer; border-radius:2px; transition:all .2s; }
.mini-btn:hover { border-color:var(--gold); color:var(--gold); }
.mini-btn.danger { border-color:var(--red); color:var(--red); }
.mini-btn.danger:hover { background:var(--red); color:#000; }
.mini-select { padding:4px 8px; font-size:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); cursor:pointer; }
.stat-grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin-bottom:32px; }
.admin-stat { background:var(--card); border:1px solid var(--border); padding:20px; border-radius:4px; }
.admin-stat .label { font-size:10px; color:var(--muted); text-transform:uppercase; letter-spacing:1px; }
.admin-stat .value { font-size:28px; font-weight:700; font-family:var(--display); margin-top:6px; }
.admin-stat .value.gold { color:var(--gold); }
.admin-stat .value.green { color:var(--green); }
.admin-stat .value.cyan { color:var(--cyan); }
.admin-stat .value.red { color:var(--red); }
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="logo">GLOBAL<em>BANK</em> <span style="font-size:9px; color:var(--gold); margin-left:8px; letter-spacing:2px;">ADMIN</span></div>
  <div class="nav-links">
    <div class="nav-link" onclick="window.location='../home.php'">Dashboard</div>
    <div class="nav-link active">Admin</div>
    <div class="nav-link" onclick="window.location='../trade.php'">Trade</div>
    <div class="nav-link" onclick="window.location='../loans.php'">Loans</div>
    <div class="nav-link" onclick="window.location='../earn.php'">Earn</div>
    <div class="nav-link" onclick="window.location='../jobs.php'">Jobs</div>
  </div>
  <div class="nav-right">
    <div class="user-pill" onclick="window.location='../profile.php'">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>

<div class="wrap">
  <div class="welcome">
    <div class="welcome-left">
      <h1>Admin <span>Panel</span></h1>
      <p>Manage users, loans, transactions, and platform metrics.</p>
    </div>
  </div>

  <!-- TABS -->
  <div class="admin-tabs">
    <div class="admin-tab active" onclick="switchTab('overview')">Overview</div>
    <div class="admin-tab" onclick="switchTab('users')">Users</div>
    <div class="admin-tab" onclick="switchTab('loans')">Loans</div>
    <div class="admin-tab" onclick="switchTab('transactions')">Transactions</div>
    <div class="admin-tab" onclick="switchTab('jobs')">Jobs</div>
    <div class="admin-tab" onclick="switchTab('kyc')" style="color:var(--gold)">KYC Queue</div>
    <div class="admin-tab" onclick="switchTab('fraud')" style="color:var(--red)">🚨 Fraud</div>
    <div class="admin-tab" onclick="switchTab('audit')">Audit Logs</div>
    <div class="admin-tab" onclick="switchTab('system')">System</div>
  </div>

  <!-- OVERVIEW -->
  <div class="admin-panel active" id="panel-overview">
    <div class="stat-grid" id="statsGrid">Loading...</div>
    <div class="fcard" style="margin-top:24px;">
        <div class="fcard-title">PLATFORM VOLUME</div>
        <canvas id="platformChart" style="width:100%; height:300px; margin-top:16px;"></canvas>
    </div>
  </div>

  <!-- USERS -->
  <div class="admin-panel" id="panel-users">
    <div class="fcard" style="--accent:var(--blue); overflow-x:auto;">
      <div class="fcard-top">
        <div class="fcard-title">ALL USERS</div>
        <input type="text" id="searchUsers" onkeyup="filterTable('searchUsers', 'usersBody')" placeholder="Search email or ID..." style="padding:4px 8px; font-size:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); outline:none;">
      </div>
      <table class="admin-table" style="margin-top:16px;">
        <thead>
          <tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>KYC</th><th>AI Score</th><th>Balance</th><th>Admin</th><th>Actions</th></tr>
        </thead>
        <tbody id="usersBody"></tbody>
      </table>
    </div>
  </div>

  <!-- LOANS -->
  <div class="admin-panel" id="panel-loans">
    <div class="fcard" style="--accent:var(--gold); overflow-x:auto;">
      <div class="fcard-top">
        <div class="fcard-title">ALL LOANS</div>
      </div>
      <table class="admin-table" style="margin-top:16px;">
        <thead>
          <tr><th>ID</th><th>User</th><th>Amount</th><th>Repaid</th><th>Interest</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody id="loansBody"></tbody>
      </table>
    </div>
  </div>

  <!-- TRANSACTIONS -->
  <div class="admin-panel" id="panel-transactions">
    <div class="fcard" style="--accent:var(--green); overflow-x:auto;">
      <div class="fcard-top">
        <div class="fcard-title">RECENT TRANSACTIONS</div>
        <input type="text" id="searchTxn" onkeyup="filterTable('searchTxn', 'txnBody')" placeholder="Search email or type..." style="padding:4px 8px; font-size:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); outline:none;">
      </div>
      <table class="admin-table" style="margin-top:16px;">
        <thead>
          <tr><th>ID</th><th>User</th><th>Type</th><th>Amount</th><th>Currency</th><th>Description</th><th>Date</th></tr>
        </thead>
        <tbody id="txnBody"></tbody>
      </table>
    </div>
  </div>

  <!-- JOBS -->
  <div class="admin-panel" id="panel-jobs">
    <div class="fcard" style="--accent:var(--blue); overflow-x:auto;">
      <div class="fcard-top">
        <div class="fcard-title">MANAGE JOBS</div>
        <button class="mini-btn" onclick="openJobModal()" style="border-color:var(--gold); color:var(--gold);">+ New Job</button>
      </div>
      <table class="admin-table" style="margin-top:16px;">
        <thead>
          <tr><th>ID</th><th>Title</th><th>Category</th><th>Reward</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody id="jobsBody"></tbody>
      </table>
    </div>
  </div>

  <!-- KYC REVIEW QUEUE -->
  <div class="admin-panel" id="panel-kyc">
    <div class="fcard" style="--accent:var(--gold); overflow-x:auto;">
      <div class="fcard-top">
        <div class="fcard-title">KYC REVIEW QUEUE</div>
        <span id="kycPendingCount" style="font-size:11px;color:var(--gold);letter-spacing:1px">Loading...</span>
      </div>
      <table class="admin-table" style="margin-top:16px;">
        <thead><tr><th>User ID</th><th>Name</th><th>Email</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody id="kycBody"></tbody>
      </table>
    </div>
  </div>

  <!-- FRAUD INTELLIGENCE -->
  <div class="admin-panel" id="panel-fraud">
    <div class="grid-2" style="margin-bottom:24px">
      <div class="fcard" style="--accent:var(--red);">
        <div class="fcard-top"><div class="fcard-title">OPEN ALERTS</div><div id="fraudOpenCount" style="font-family:var(--display);font-size:36px;color:var(--red)">—</div></div>
        <p class="fcard-desc">Transactions flagged by the automated risk engine.</p>
      </div>
      <div class="fcard" style="--accent:var(--green);">
        <div class="fcard-top"><div class="fcard-title">RESOLVED (7D)</div><div id="fraudResolvedCount" style="font-family:var(--display);font-size:36px;color:var(--green)">—</div></div>
        <p class="fcard-desc">Alerts cleared by admin review in the last 7 days.</p>
      </div>
    </div>
    <div class="fcard" style="--accent:var(--red); overflow-x:auto;">
      <div class="fcard-title" style="margin-bottom:16px">FRAUD ALERT FEED</div>
      <table class="admin-table">
        <thead><tr><th>Alert ID</th><th>User</th><th>Risk Score</th><th>Reason</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
        <tbody id="fraudBody"></tbody>
      </table>
    </div>
  </div>

  <!-- AUDIT LOGS -->
  <div class="admin-panel" id="panel-audit">
    <div class="fcard" style="--accent:var(--gold); overflow-x:auto;">
      <div class="fcard-top">
        <div class="fcard-title">ADMIN AUDIT LOGS</div>
        <input type="text" id="searchAudit" onkeyup="filterTable('searchAudit', 'auditBody')" placeholder="Search action or admin..." style="padding:4px 8px; font-size:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); outline:none;">
      </div>
      <table class="admin-table" style="margin-top:16px;">
        <thead>
          <tr><th>Log ID</th><th>Date</th><th>Admin</th><th>Target User</th><th>Action</th><th>Description</th></tr>
        </thead>
        <tbody id="auditBody"></tbody>
      </table>
    </div>
  </div>

  <!-- SYSTEM -->
  <div class="admin-panel" id="panel-system">
    <div class="grid-2">
      <div class="fcard" style="--accent:var(--purple)">
        <div class="fcard-top">
          <div class="fcard-title">PLATFORM FEES (%)</div>
        </div>
        <form id="feesForm" onsubmit="event.preventDefault(); saveSettings();" style="margin-top:16px;">
          <div style="margin-bottom:12px;">
            <label style="font-size:10px;color:var(--muted);">Withdrawal Fee</label>
            <input type="text" id="set_withdrawal_fee_pct" style="width:100%;padding:10px;background:var(--surface);border:1px solid var(--border);color:var(--text);margin-top:4px;">
          </div>
          <div style="margin-bottom:12px;">
            <label style="font-size:10px;color:var(--muted);">Transfer Fee</label>
            <input type="text" id="set_transfer_fee_pct" style="width:100%;padding:10px;background:var(--surface);border:1px solid var(--border);color:var(--text);margin-top:4px;">
          </div>
          <div style="margin-bottom:16px;">
            <label style="font-size:10px;color:var(--muted);">Crypto Trade Fee</label>
            <input type="text" id="set_crypto_trade_fee_pct" style="width:100%;padding:10px;background:var(--surface);border:1px solid var(--border);color:var(--text);margin-top:4px;">
          </div>
          <button type="submit" class="act-btn fill" style="width:100%;">SAVE FEES</button>
        </form>
      </div>

      <div class="fcard" style="--accent:var(--red)">
        <div class="fcard-top">
          <div class="fcard-title">GLOBAL SETTINGS</div>
        </div>
        <div style="margin-top:16px;">
          <div style="display:flex; justify-content:space-between; align-items:center; padding:16px; background:var(--surface); border:1px solid var(--border); border-radius:4px; margin-bottom:12px;">
            <div>
              <div style="font-weight:600; font-size:13px; color:var(--text);">Maintenance Mode</div>
              <div style="font-size:11px; color:var(--muted); margin-top:4px;">Disable logins globally.</div>
            </div>
            <div class="toggle-switch" id="maintenanceToggle" onclick="toggleSetting('maintenance_mode')" style="width:40px; height:20px; background:var(--border); border-radius:10px; position:relative; cursor:pointer;">
               <!-- Added CSS manually in style tag above if needed -->
            </div>
          </div>
          <div style="margin-bottom:16px;">
            <label style="font-size:10px;color:var(--muted);">Referral Bonus ($)</label>
            <div style="display:flex; gap:8px; margin-top:4px;">
              <input type="text" id="set_referral_bonus" style="flex:1;padding:10px;background:var(--surface);border:1px solid var(--border);color:var(--text);">
              <button class="act-btn fill" onclick="saveSettings()">UPDATE</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- FUND MANAGEMENT MODAL -->
<div id="fundModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--card); padding:24px; border-radius:8px; width:400px; border:1px solid var(--border);">
        <h3 style="margin-bottom:16px;">Manage Funds</h3>
        <input type="hidden" id="fundUid">
        <div style="margin-bottom:12px;">
            <select id="fundCurrency" class="mini-select" style="width:100%; padding:8px;">
                <option value="USD">USD</option>
                <option value="BTC">BTC</option>
                <option value="ETH">ETH</option>
                <option value="SOL">SOL</option>
            </select>
        </div>
        <div style="margin-bottom:12px;">
            <select id="fundType" class="mini-select" style="width:100%; padding:8px;">
                <option value="add">Add Funds (+)</option>
                <option value="deduct">Deduct Funds (-)</option>
            </select>
        </div>
        <div style="margin-bottom:16px;">
            <input type="number" id="fundAmount" placeholder="Amount" step="0.01" style="width:100%; padding:8px; background:var(--surface); border:1px solid var(--border); color:var(--text);">
        </div>
        <div style="display:flex; gap:8px;">
            <button onclick="submitFunds()" class="act-btn fill" style="flex:1;">Submit</button>
            <button onclick="document.getElementById('fundModal').style.display='none'" class="act-btn" style="flex:1;">Cancel</button>
        </div>
    </div>
</div>

<!-- JOB MANAGEMENT MODAL -->
<div id="jobModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:999; align-items:center; justify-content:center;">
    <div style="background:var(--card); padding:24px; border-radius:8px; width:400px; border:1px solid var(--border);">
        <h3 style="margin-bottom:16px;">Create New Job</h3>
        <input type="hidden" id="jobId">
        <div style="margin-bottom:12px;">
            <input type="text" id="jobTitle" placeholder="Job Title" style="width:100%; padding:8px; background:var(--surface); border:1px solid var(--border); color:var(--text);">
        </div>
        <div style="margin-bottom:12px;">
            <input type="text" id="jobCategory" placeholder="Category (e.g. Analysis, Translation)" style="width:100%; padding:8px; background:var(--surface); border:1px solid var(--border); color:var(--text);">
        </div>
        <div style="margin-bottom:12px;">
            <input type="number" id="jobReward" placeholder="Reward (USD)" step="0.01" style="width:100%; padding:8px; background:var(--surface); border:1px solid var(--border); color:var(--text);">
        </div>
        <div style="margin-bottom:16px;">
            <textarea id="jobDesc" placeholder="Job Description" rows="4" style="width:100%; padding:8px; background:var(--surface); border:1px solid var(--border); color:var(--text);"></textarea>
        </div>
        <div style="display:flex; gap:8px;">
            <button onclick="submitJob()" class="act-btn fill" style="flex:1;">Submit</button>
            <button onclick="document.getElementById('jobModal').style.display='none'" class="act-btn" style="flex:1;">Cancel</button>
        </div>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>
<script src="js/admin.js"></script>

</body>
</html>
