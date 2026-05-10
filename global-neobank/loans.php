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
<title>Loans — GlobalBank</title>
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
    <div class="nav-link active">Loans</div>
    <div class="nav-link" onclick="window.location='earn.php'">Earn</div>
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
      <h1>Loans & <span>Repay</span></h1>
      <p>Human Capital Model: Borrow against your AI score, repay with cash or verified tasks.</p>
    </div>
  </div>

  <div class="grid-2">
    <!-- LOANS SECTION -->
    <div class="fcard" style="--accent:var(--gold)">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--gold)">💸</div>
        <div class="fcard-title">MY LOANS</div>
      </div>
      
      <div id="loansList" style="margin-top:16px; display:flex; flex-direction:column; gap:12px;"></div>
      
      <div style="margin-top:24px; padding-top:16px; border-top:1px solid #131d2a;">
        <h3 style="font-size:14px; margin-bottom:12px;">Apply for Loan</h3>
        <form id="applyLoanForm" onsubmit="event.preventDefault(); applyLoan();">
            <div style="display:flex; gap:10px;">
                <input type="number" id="loanAmount" placeholder="Amount (USD)" required min="50" style="flex:1; background:var(--surface); border:1px solid var(--border); color:var(--text); padding:10px; border-radius:4px;">
                <button type="submit" class="act-btn fill" style="padding:10px 20px;">APPLY</button>
            </div>
            <p id="loanMsg" style="font-size:11px; margin-top:8px; color:var(--muted);"></p>
        </form>
      </div>
    </div>

    <!-- REPAY SECTION -->
    <div class="fcard" style="--accent:var(--green)">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--green)">💳</div>
        <div class="fcard-title">REPAY LOAN</div>
      </div>
      
      <div style="margin-top:16px;">
          <div style="margin-bottom:16px; padding-bottom:16px; border-bottom:1px solid var(--border);">
              <div style="font-size:10px; color:var(--muted); text-transform:uppercase; letter-spacing:1px;">Available USD Balance</div>
              <div id="usdBalance" style="font-size:24px; font-weight:600; margin-top:4px; color:var(--green);">$0.00</div>
          </div>
          
          <form onsubmit="event.preventDefault(); repayLoan();">
              <div style="margin-bottom:12px;">
                  <label style="font-size:10px; color:var(--muted);">Select Loan</label>
                  <select id="repayLoanId" required style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); margin-top:4px;">
                      <option value="">-- Select active loan --</option>
                  </select>
              </div>
              <div style="margin-bottom:12px;">
                  <label style="font-size:10px; color:var(--muted);">Repayment Amount (USD)</label>
                  <input type="number" id="repayAmount" placeholder="Amount" required min="1" step="0.01" style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); margin-top:4px;">
              </div>
              <button type="submit" class="act-btn fill" style="width:100%;">SEND REPAYMENT</button>
              <p id="repayMsg" style="font-size:11px; margin-top:8px;"></p>
          </form>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
async function loadLoans() {
    try {
        const res = await fetch('api/loans.php?action=list');
        const data = await res.json();
        if(data.success) {
            const container = document.getElementById('loansList');
            const select = document.getElementById('repayLoanId');
            
            // Clear select options except first
            select.innerHTML = '<option value="">-- Select active loan --</option>';
            
            if(data.data.length === 0) {
                container.innerHTML = '<p style="font-size:12px; color:var(--muted);">No loans currently active.</p>';
            } else {
                container.innerHTML = data.data.map(l => {
                    const pct = Math.min(100, (l.repaid_amount / l.amount) * 100);
                    let col = 'var(--green)';
                    if(l.status === 'defaulted') col = 'var(--red)';
                    if(l.status === 'pending') col = 'var(--gold)';
                    if(l.status === 'active') col = 'var(--blue)';
                    
                    // Add active/pending loans to repay dropdown
                    if(l.status === 'active' || l.status === 'pending') {
                        const remaining = (l.amount - l.repaid_amount).toFixed(2);
                        select.innerHTML += `<option value="${l.id}">Loan #${l.id} — $${remaining} remaining</option>`;
                    }
                    
                    return `
                    <div style="background:var(--surface); padding:16px; border:1px solid var(--border); border-radius:4px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="font-weight:600; font-size:14px;">Loan #${l.id} &mdash; $${l.amount}</span>
                            <span style="font-size:10px; padding:2px 6px; border:1px solid ${col}; color:${col}; text-transform:uppercase;">${l.status}</span>
                        </div>
                        <div style="height:4px; background:var(--border); border-radius:2px; overflow:hidden; margin-bottom:8px;">
                            <div style="height:100%; width:${pct}%; background:${col};"></div>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--muted);">
                            <span>$${l.repaid_amount} repaid</span>
                            <span>Interest: ${l.interest_rate}%</span>
                        </div>
                    </div>`;
                }).join('');
            }
        }
    } catch(e) { console.error('Failed to load loans', e); }
}

async function loadBalance() {
    try {
        const res = await fetch('api/account.php');
        const data = await res.json();
        if(data.success) {
            document.getElementById('usdBalance').innerText = '$' + data.fiat_balance.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
        }
    } catch(e) {}
}

async function applyLoan() {
    const amt = document.getElementById('loanAmount').value;
    const msg = document.getElementById('loanMsg');
    
    const formData = new FormData();
    formData.append('amount', amt);
    
    try {
        const res = await fetch('api/loans.php?action=apply', { method: 'POST', body: formData });
        const data = await res.json();
        
        if(data.success) {
            msg.style.color = 'var(--green)';
            msg.innerText = 'Loan approved and deposited to your wallet!';
            document.getElementById('loanAmount').value = '';
            loadLoans();
            loadBalance();
        } else {
            msg.style.color = 'var(--red)';
            msg.innerText = data.error;
        }
    } catch(e) {
        msg.style.color = 'var(--red)';
        msg.innerText = 'Network error.';
    }
}

async function repayLoan() {
    const loanId = document.getElementById('repayLoanId').value;
    const amount = document.getElementById('repayAmount').value;
    const msg = document.getElementById('repayMsg');

    if(!loanId) { msg.style.color='var(--red)'; msg.innerText='Please select a loan.'; return; }
    
    const formData = new FormData();
    formData.append('loan_id', loanId);
    formData.append('amount', amount);
    
    try {
        const res = await fetch('api/loans.php?action=repay', { method: 'POST', body: formData });
        const data = await res.json();
        
        if(data.success) {
            msg.style.color = 'var(--green)';
            msg.innerText = data.message;
            document.getElementById('repayAmount').value = '';
            loadLoans();
            loadBalance();
        } else {
            msg.style.color = 'var(--red)';
            msg.innerText = data.error;
        }
    } catch(e) {
        msg.style.color = 'var(--red)';
        msg.innerText = 'Network error.';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadLoans();
    loadBalance();
});
</script>
</body>
</html>
