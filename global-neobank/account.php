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
<title>Manage Account — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
<style>
.tab-row { display:flex; gap:16px; border-bottom:1px solid var(--border); margin-bottom:24px; overflow-x:auto; }
.tab-item { padding:12px 0; font-size:11px; text-transform:uppercase; letter-spacing:1px; cursor:pointer; color:var(--muted); border-bottom:2px solid transparent; transition:all .2s; }
.tab-item:hover { color:var(--text); }
.tab-item.active { color:var(--gold); border-bottom-color:var(--gold); }
.tab-content { display:none; animation:fadeUp .3s ease both; }
.tab-content.active { display:block; }
.toggle-switch { width:40px; height:20px; background:var(--border); border-radius:10px; position:relative; cursor:pointer; transition:all .3s; }
.toggle-switch::after { content:''; position:absolute; top:2px; left:2px; width:16px; height:16px; background:#fff; border-radius:50%; transition:all .3s; }
.toggle-switch.active { background:var(--green); }
.toggle-switch.active::after { left:22px; }
.setting-row { display:flex; justify-content:space-between; align-items:center; padding:16px; background:var(--surface); border:1px solid var(--border); border-radius:4px; margin-bottom:12px; }
.bank-card { padding:16px; border:1px solid var(--border); background:var(--surface); border-radius:8px; display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
.bank-card strong { color:var(--text); display:block; font-size:14px; }
.bank-card span { color:var(--muted); font-size:11px; }
.statement-btn { display:flex; align-items:center; justify-content:center; gap:8px; padding:12px; border:1px dashed var(--gold); color:var(--gold); border-radius:4px; cursor:pointer; font-size:12px; font-weight:600; letter-spacing:1px; text-transform:uppercase; }
.statement-btn:hover { background:var(--gold); color:#000; }
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
    <div class="user-pill active">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>

<div class="wrap">
  <div class="welcome">
    <div class="welcome-left">
      <h1>Account <span>Manager</span></h1>
      <p>Control your identity, security, and linked external accounts.</p>
    </div>
  </div>

  <div class="tab-row">
    <div class="tab-item active" onclick="switchTab('kyc', this)">KYC Verification</div>
    <div class="tab-item" onclick="switchTab('security', this)">Security Center</div>
    <div class="tab-item" onclick="switchTab('banks', this)">Linked Banks</div>
    <div class="tab-item" onclick="switchTab('preferences', this)">Preferences</div>
    <div class="tab-item" onclick="switchTab('statements', this)">Statements</div>
  </div>

  <!-- KYC TAB -->
  <div id="tab-kyc" class="tab-content active">
    <div class="grid-2">
      <div class="fcard" style="--accent:var(--blue)">
        <div class="fcard-top">
          <div class="fcard-title">IDENTITY VERIFICATION</div>
          <span class="badge" id="kycBadge" style="padding:4px 8px; font-size:10px; border-radius:4px; text-transform:uppercase;">LOADING...</span>
        </div>
        <p style="font-size:12px; color:var(--muted); margin-top:16px;">KYC verification is optional but highly recommended to unlock maximum withdrawal and transfer limits on the platform.</p>
        
        <form id="kycForm" onsubmit="event.preventDefault(); submitKyc();" style="margin-top:24px;">
            <div style="margin-bottom:16px;">
                <label style="font-size:10px;color:var(--muted);">Select Document Type</label>
                <select id="kycType" style="width:100%;padding:10px;background:var(--surface);border:1px solid var(--border);color:var(--text);margin-top:4px;">
                    <option value="passport">Passport</option>
                    <option value="driver_license">Driver's License</option>
                    <option value="national_id">National ID</option>
                </select>
            </div>
            <div style="margin-bottom:16px; padding:24px; border:1px dashed var(--border); text-align:center; background:#ffffff05; cursor:pointer;" onclick="document.getElementById('kycFile').click()">
                <div style="font-size:24px; margin-bottom:8px;">📄</div>
                <div style="font-size:12px; font-weight:600; color:var(--text);">Click to select document image</div>
                <div style="font-size:10px; color:var(--muted); margin-top:4px;">JPG, PNG, PDF (Max 5MB)</div>
                <input type="file" id="kycFile" style="display:none;" accept="image/*,.pdf" onchange="document.getElementById('fileName').innerText=this.files[0]?this.files[0].name:'No file selected'">
                <div id="fileName" style="font-size:11px; color:var(--gold); margin-top:8px;"></div>
            </div>
            <button type="submit" class="act-btn fill" style="width:100%;">SUBMIT FOR REVIEW</button>
        </form>
      </div>
    </div>
  </div>

  <!-- SECURITY TAB -->
  <div id="tab-security" class="tab-content">
    <div class="grid-2">
      <div class="fcard" style="--accent:var(--red)">
        <div class="fcard-top">
          <div class="fcard-title">CHANGE PASSWORD</div>
        </div>
        <form onsubmit="event.preventDefault(); changePwd();" style="margin-top:16px;">
          <div style="margin-bottom:12px;">
            <input type="password" id="curPwd" required placeholder="Current Password" style="width:100%;padding:10px;background:var(--surface);border:1px solid var(--border);color:var(--text);">
          </div>
          <div style="margin-bottom:12px;">
            <input type="password" id="newPwd" required placeholder="New Password" style="width:100%;padding:10px;background:var(--surface);border:1px solid var(--border);color:var(--text);">
          </div>
          <div style="margin-bottom:16px;">
            <input type="password" id="conPwd" required placeholder="Confirm New Password" style="width:100%;padding:10px;background:var(--surface);border:1px solid var(--border);color:var(--text);">
          </div>
          <button type="submit" class="act-btn fill" style="width:100%;">UPDATE PASSWORD</button>
        </form>
      </div>

      <div class="fcard" style="--accent:var(--green)">
        <div class="fcard-top">
          <div class="fcard-title">AUTHENTICATION</div>
        </div>
        <div style="margin-top:16px;">
            <div class="setting-row">
                <div>
                    <div style="font-weight:600; font-size:13px; color:var(--text);">Two-Factor Authentication</div>
                    <div style="font-size:11px; color:var(--muted); margin-top:4px;">Require an OTP code for logins and withdrawals.</div>
                </div>
                <div class="toggle-switch" id="tfaToggle" onclick="toggleTFA()"></div>
            </div>
        </div>
      </div>
    </div>
  </div>

  <!-- BANKS TAB -->
  <div id="tab-banks" class="tab-content">
    <div class="grid-2">
      <div class="fcard" style="--accent:var(--gold)">
        <div class="fcard-top">
          <div class="fcard-title">LINKED EXTERNAL ACCOUNTS</div>
        </div>
        <div id="bankList" style="margin-top:16px; margin-bottom:24px;"></div>
        
        <div style="padding-top:16px; border-top:1px solid var(--border);">
            <div style="font-size:11px; font-weight:600; color:var(--muted); margin-bottom:12px; text-transform:uppercase;">Connect New Bank</div>
            <div style="display:flex; gap:8px;">
                <input type="text" id="bankName" placeholder="Bank Name (e.g. Chase)" style="flex:2; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text);">
                <input type="text" id="accLast4" placeholder="Last 4 Digits" maxlength="4" style="flex:1; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text);">
            </div>
            <button class="act-btn fill" style="width:100%; margin-top:12px;" onclick="linkBank()">LINK ACCOUNT</button>
        </div>
      </div>
    </div>
  </div>

  <!-- PREFERENCES TAB -->
  <div id="tab-preferences" class="tab-content">
    <div class="grid-2">
      <div class="fcard" style="--accent:var(--purple)">
        <div class="fcard-top">
          <div class="fcard-title">USER PREFERENCES</div>
        </div>
        <div style="margin-top:16px;">
            <div class="setting-row">
                <div>
                    <div style="font-weight:600; font-size:13px; color:var(--text);">Dark Mode</div>
                    <div style="font-size:11px; color:var(--muted); margin-top:4px;">Toggle between dark and light themes.</div>
                </div>
                <div class="toggle-switch active" id="themeToggle" onclick="togglePref('theme')"></div>
            </div>
            <div class="setting-row">
                <div>
                    <div style="font-weight:600; font-size:13px; color:var(--text);">Email Alerts</div>
                    <div style="font-size:11px; color:var(--muted); margin-top:4px;">Receive security and transaction alerts.</div>
                </div>
                <div class="toggle-switch active" id="emailToggle" onclick="togglePref('email_alerts')"></div>
            </div>
            <div class="setting-row">
                <div>
                    <div style="font-weight:600; font-size:13px; color:var(--text);">Trade Updates</div>
                    <div style="font-size:11px; color:var(--muted); margin-top:4px;">Get real-time price change notifications.</div>
                </div>
                <div class="toggle-switch" id="tradeToggle" onclick="togglePref('trade_updates')"></div>
            </div>
        </div>
      </div>
    </div>
  </div>

  <!-- STATEMENTS TAB -->
  <div id="tab-statements" class="tab-content">
    <div class="grid-2">
      <div class="fcard" style="--accent:var(--cyan)">
        <div class="fcard-top">
          <div class="fcard-title">MONTHLY STATEMENTS</div>
        </div>
        <p style="font-size:12px; color:var(--muted); margin-top:16px; margin-bottom:24px;">Download official PDF records of your transaction history and account balances for tax and compliance purposes.</p>
        
        <div style="display:flex; flex-direction:column; gap:12px;">
            <div class="statement-btn" onclick="downloadStatement('<?= date('Y-m', strtotime('-1 month')) ?>')">
                <span>📄 Download Statement (<?= date('F Y', strtotime('-1 month')) ?>)</span>
            </div>
            <div class="statement-btn" onclick="downloadStatement('<?= date('Y-m', strtotime('-2 month')) ?>')">
                <span>📄 Download Statement (<?= date('F Y', strtotime('-2 month')) ?>)</span>
            </div>
            <div class="statement-btn" onclick="downloadStatement('<?= date('Y-m', strtotime('-3 month')) ?>')">
                <span>📄 Download Statement (<?= date('F Y', strtotime('-3 month')) ?>)</span>
            </div>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
function switchTab(tabId, el) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');
    el.classList.add('active');
}

async function loadAccountData() {
    try {
        const res = await fetch('api/account_manager.php?action=get_data');
        const data = await res.json();
        if(data.success) {
            // KYC
            const kbadge = document.getElementById('kycBadge');
            if(data.data.kyc_status === 'verified') { kbadge.innerText = 'VERIFIED'; kbadge.style.background = '#00e87a20'; kbadge.style.color = 'var(--green)'; }
            else if(data.data.kyc_status === 'pending') { kbadge.innerText = 'PENDING REVIEW'; kbadge.style.background = '#f5c84220'; kbadge.style.color = 'var(--gold)'; }
            else { kbadge.innerText = 'UNVERIFIED'; kbadge.style.background = '#ff456020'; kbadge.style.color = 'var(--red)'; }

            // Security
            const tfa = document.getElementById('tfaToggle');
            if(data.data.two_factor_enabled) tfa.classList.add('active');
            else tfa.classList.remove('active');

            // Preferences
            const theme = document.getElementById('themeToggle');
            if(data.data.prefs.theme === 'dark') theme.classList.add('active');
            else theme.classList.remove('active');

            const email = document.getElementById('emailToggle');
            if(data.data.prefs.email_alerts) email.classList.add('active');
            else email.classList.remove('active');

            const trade = document.getElementById('tradeToggle');
            if(data.data.prefs.trade_updates) trade.classList.add('active');
            else trade.classList.remove('active');

            // Banks
            const blist = document.getElementById('bankList');
            if(data.data.banks.length === 0) {
                blist.innerHTML = '<div style="font-size:12px; color:var(--muted);">No linked banks found.</div>';
            } else {
                blist.innerHTML = data.data.banks.map(b => `
                    <div class="bank-card">
                        <div>
                            <strong>${b.bank_name}</strong>
                            <span>Account ending in •••• ${b.account_last_four}</span>
                        </div>
                        <div style="font-size:10px; color:var(--green); font-weight:600;">ACTIVE</div>
                    </div>
                `).join('');
            }
        }
    } catch(e) { console.error(e); }
}

async function submitKyc() {
    const file = document.getElementById('kycFile').files[0];
    if(!file) return alert('Please select a file.');
    
    // Simulating KYC submission since we don't handle real file uploads here securely
    const fd = new FormData();
    fd.append('kyc_submit', '1');
    try {
        const res = await fetch('api/account_manager.php?action=submit_kyc', { method:'POST', body:fd });
        const data = await res.json();
        if(data.success) { alert('KYC Documents submitted for review!'); loadAccountData(); }
        else alert(data.error);
    } catch(e) { alert('Network error'); }
}

async function changePwd(){
  const cur=document.getElementById('curPwd').value;
  const np=document.getElementById('newPwd').value;
  const cp=document.getElementById('conPwd').value;
  if(np!==cp){alert('Passwords do not match.');return;}
  const fd=new FormData();fd.append('current_password',cur);fd.append('new_password',np);
  try{const r=await fetch('api/account_manager.php?action=change_password',{method:'POST',body:fd});const d=await r.json();
    if(d.success){alert('Password updated!');document.getElementById('curPwd').value='';document.getElementById('newPwd').value='';document.getElementById('conPwd').value='';}
    else{alert(d.error);}
  }catch(e){alert('Network error.');}
}

async function toggleTFA() {
    const toggle = document.getElementById('tfaToggle');
    const newState = toggle.classList.contains('active') ? 0 : 1;
    
    const fd = new FormData(); fd.append('state', newState);
    try {
        const res = await fetch('api/account_manager.php?action=toggle_tfa', { method:'POST', body:fd });
        const data = await res.json();
        if(data.success) {
            toggle.classList.toggle('active');
        } else alert(data.error);
    } catch(e) { alert('Network error'); }
}

async function togglePref(pref) {
    const el = document.getElementById(pref.replace('_alerts','').replace('_updates','') + 'Toggle');
    const newState = el.classList.contains('active') ? 0 : 1;
    
    const fd = new FormData(); fd.append('pref', pref); fd.append('state', newState);
    try {
        const res = await fetch('api/account_manager.php?action=update_prefs', { method:'POST', body:fd });
        const data = await res.json();
        if(data.success) {
            el.classList.toggle('active');
            if(pref === 'theme') {
                alert('Theme changed! (Visual update coming in next release)');
            }
        } else alert(data.error);
    } catch(e) { alert('Network error'); }
}

async function linkBank() {
    const bName = document.getElementById('bankName').value;
    const bLast4 = document.getElementById('accLast4').value;
    if(!bName || bLast4.length !== 4) return alert('Enter valid bank name and 4 digit account number.');
    
    const fd = new FormData();
    fd.append('bank_name', bName);
    fd.append('last_four', bLast4);
    try {
        const res = await fetch('api/account_manager.php?action=link_bank', { method:'POST', body:fd });
        const data = await res.json();
        if(data.success) {
            document.getElementById('bankName').value = '';
            document.getElementById('accLast4').value = '';
            loadAccountData();
        } else alert(data.error);
    } catch(e) { alert('Network error'); }
}

function downloadStatement(month) {
    alert(`Generating PDF statement for ${month}... Please wait.`);
    // Mock statement generator - in real life this hits a TCPDF endpoint
    setTimeout(() => {
        alert(`Statement_${month}.pdf downloaded successfully.`);
    }, 1500);
}

document.addEventListener('DOMContentLoaded', loadAccountData);
</script>
</body>
</html>
