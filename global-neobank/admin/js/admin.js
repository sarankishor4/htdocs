function filterTable(inputId, tableBodyId) {
    const input = document.getElementById(inputId).value.toUpperCase();
    const rows = document.getElementById(tableBodyId).getElementsByTagName("tr");
    for (let i = 0; i < rows.length; i++) {
        let textValue = rows[i].textContent || rows[i].innerText;
        rows[i].style.display = textValue.toUpperCase().indexOf(input) > -1 ? "" : "none";
    }
}
function switchTab(tab) {
    document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.admin-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.add('active');
    event.target.classList.add('active');
    
    if(tab === 'overview') { loadStats(); loadChart(); }
    if(tab === 'users') loadUsers();
    if(tab === 'loans') loadLoansAdmin();
    if(tab === 'transactions') loadTransactions();
    if(tab === 'jobs') loadJobsAdmin();
    if(tab === 'kyc') loadKycQueue();
    if(tab === 'fraud') loadFraudAlerts();
    if(tab === 'audit') loadAuditLogs();
    if(tab === 'system') loadSystemSettings();
}

let platformChartInstance = null;
async function loadChart() {
    try {
        const res = await fetch('api/index.php?action=platform_volume');
        const data = await res.json();
        if(data.success) {
            const ctx = document.getElementById('platformChart').getContext('2d');
            if(platformChartInstance) platformChartInstance.destroy();
            platformChartInstance = new Chart(ctx, {
                type: 'line',
                data: data.data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: '#ffffff' } } },
                    scales: {
                        x: { ticks: { color: '#8b949e' }, grid: { color: '#ffffff10' } },
                        y: { ticks: { color: '#8b949e' }, grid: { color: '#ffffff10' } }
                    }
                }
            });
        }
    } catch(e) { console.error(e); }
}

async function loadStats() {
    try {
        const res = await fetch('api/index.php?action=stats');
        const data = await res.json();
        if(data.success) {
            const s = data.data;
            document.getElementById('statsGrid').innerHTML = `
                <div class="admin-stat"><div class="label">Total Users</div><div class="value gold">${s.total_users}</div></div>
                <div class="admin-stat"><div class="label">Verified Users</div><div class="value green">${s.verified_users}</div></div>
                <div class="admin-stat"><div class="label">Total USD Deposits</div><div class="value cyan">$${parseFloat(s.total_deposits).toLocaleString()}</div></div>
                <div class="admin-stat"><div class="label">USD Staked</div><div class="value gold">$${parseFloat(s.total_staked).toLocaleString()}</div></div>
                <div class="admin-stat"><div class="label">Active Loans</div><div class="value red">${s.active_loans}</div></div>
                <div class="admin-stat"><div class="label">Total Loan Amount</div><div class="value red">$${parseFloat(s.total_loan_amount).toLocaleString()}</div></div>
                <div class="admin-stat"><div class="label">Total Repaid</div><div class="value green">$${parseFloat(s.total_repaid).toLocaleString()}</div></div>
                <div class="admin-stat"><div class="label">Total Transactions</div><div class="value cyan">${s.total_transactions}</div></div>
            `;
        }
    } catch(e) { console.error(e); }
}

async function loadUsers() {
    try {
        const res = await fetch('api/index.php?action=users');
        const data = await res.json();
        if(data.success) {
            document.getElementById('usersBody').innerHTML = data.data.map(u => {
                const bal = u.wallets.map(w => `${w.currency}: ${parseFloat(w.balance).toFixed(2)}`).join('<br>') || 'No wallets';
                let statusBadge = '';
                if(u.account_status === 'active') statusBadge = 'badge-green';
                else if(u.account_status === 'frozen') statusBadge = 'badge-gold';
                else if(u.account_status === 'banned') statusBadge = 'badge-red';
                
                const kycBadge = u.kyc_status === 'verified' ? 'badge-green' : (u.kyc_status === 'rejected' ? 'badge-red' : 'badge-gold');
                const adminBadge = u.is_admin ? '<span class="badge badge-gold">Admin</span>' : '<span class="badge badge-blue">User</span>';
                return `<tr>
                    <td>${u.id}</td>
                    <td>${u.first_name} ${u.last_name}</td>
                    <td>${u.email}</td>
                    <td>
                        <select class="mini-select" onchange="updateUser(${u.id}, 'account_status', this.value)">
                            <option value="active" ${u.account_status==='active'?'selected':''}>Active</option>
                            <option value="frozen" ${u.account_status==='frozen'?'selected':''}>Frozen</option>
                            <option value="banned" ${u.account_status==='banned'?'selected':''}>Banned</option>
                        </select>
                    </td>
                    <td>
                        <select class="mini-select" onchange="updateUser(${u.id}, 'kyc_status', this.value)">
                            <option value="pending" ${u.kyc_status==='pending'?'selected':''}>Pending</option>
                            <option value="verified" ${u.kyc_status==='verified'?'selected':''}>Verified</option>
                            <option value="rejected" ${u.kyc_status==='rejected'?'selected':''}>Rejected</option>
                        </select>
                    </td>
                    <td><input type="number" class="mini-select" style="width:60px;" value="${u.ai_credit_score || 0}" onchange="updateUser(${u.id}, 'ai_credit_score', this.value)"></td>
                    <td style="font-size:10px;">${bal}</td>
                    <td>${adminBadge}</td>
                    <td>
                        <button class="mini-btn" onclick="openFundModal(${u.id})">💰 Funds</button>
                        <button class="mini-btn" onclick="impersonate(${u.id})">👁 Login As</button>
                    </td>
                </tr>`;
            }).join('');
        }
    } catch(e) { console.error(e); }
}

async function loadLoansAdmin() {
    try {
        const res = await fetch('api/index.php?action=loans');
        const data = await res.json();
        if(data.success) {
            document.getElementById('loansBody').innerHTML = data.data.map(l => {
                const statusBadge = l.status === 'active' ? 'badge-blue' : (l.status === 'paid' ? 'badge-green' : (l.status === 'defaulted' ? 'badge-red' : 'badge-gold'));
                return `<tr>
                    <td>${l.id}</td>
                    <td>${l.first_name} ${l.last_name} (${l.email})</td>
                    <td>$${parseFloat(l.amount).toFixed(2)}</td>
                    <td>$${parseFloat(l.repaid_amount).toFixed(2)}</td>
                    <td>${l.interest_rate}%</td>
                    <td><span class="badge ${statusBadge}">${l.status}</span></td>
                    <td>
                        <select class="mini-select" onchange="updateLoan(${l.id}, this.value)">
                            <option value="pending" ${l.status==='pending'?'selected':''}>Pending</option>
                            <option value="active" ${l.status==='active'?'selected':''}>Active</option>
                            <option value="paid" ${l.status==='paid'?'selected':''}>Paid</option>
                            <option value="defaulted" ${l.status==='defaulted'?'selected':''}>Defaulted</option>
                        </select>
                    </td>
                </tr>`;
            }).join('');
        }
    } catch(e) { console.error(e); }
}

async function loadTransactions() {
    try {
        const res = await fetch('api/index.php?action=transactions');
        const data = await res.json();
        if(data.success) {
            document.getElementById('txnBody').innerHTML = data.data.map(t => {
                const col = parseFloat(t.amount) > 0 ? 'var(--green)' : 'var(--red)';
                return `<tr>
                    <td>${t.id}</td>
                    <td>${t.first_name} ${t.last_name}</td>
                    <td><span class="badge badge-blue">${t.type}</span></td>
                    <td style="color:${col}">$${parseFloat(t.amount).toFixed(2)}</td>
                    <td>${t.currency}</td>
                    <td>${t.description || '—'}</td>
                    <td style="font-size:10px;">${t.created_at}</td>
                </tr>`;
            }).join('');
        }
    } catch(e) { console.error(e); }
}

async function updateUser(uid, field, value) {
    const fd = new FormData();
    fd.append('user_id', uid);
    fd.append('field', field);
    fd.append('value', value);
    try {
        const res = await fetch('api/index.php?action=update_user', { method:'POST', body:fd });
        const data = await res.json();
        if(data.success) { loadUsers(); }
        else { alert(data.error); }
    } catch(e) { alert('Error'); }
}

async function updateLoan(lid, status) {
    const fd = new FormData();
    fd.append('loan_id', lid);
    fd.append('status', status);
    try {
        const res = await fetch('api/index.php?action=update_loan', { method:'POST', body:fd });
        const data = await res.json();
        if(data.success) { loadLoansAdmin(); }
        else { alert(data.error); }
    } catch(e) { alert('Error'); }
}

async function deleteUser(uid) {
    if(!confirm('Are you sure you want to delete this user? This cannot be undone.')) return;
    const fd = new FormData();
    fd.append('user_id', uid);
    try {
        const res = await fetch('api/index.php?action=delete_user', { method:'POST', body:fd });
        const data = await res.json();
        if(data.success) { loadUsers(); loadStats(); }
        else { alert(data.error); }
    } catch(e) { alert('Error'); }
}

async function loadAuditLogs() {
    try {
        const res = await fetch('api/index.php?action=audit_logs');
        const data = await res.json();
        if(data.success) {
            document.getElementById('auditBody').innerHTML = data.data.map(a => {
                return `<tr>
                    <td>${a.id}</td>
                    <td style="font-size:10px; color:var(--muted);">${a.created_at}</td>
                    <td>${a.admin_email}</td>
                    <td>${a.target_email || '—'}</td>
                    <td><span class="badge badge-gold">${a.action}</span></td>
                    <td>${a.description}</td>
                </tr>`;
            }).join('');
        }
    } catch(e) { console.error(e); }
}

async function impersonate(uid) {
    if(!confirm("You are about to log in as this user. Your admin session will be overwritten. Continue?")) return;
    const fd = new FormData(); fd.append('user_id', uid);
    const res = await fetch('api/index.php?action=impersonate', { method:'POST', body:fd });
    const data = await res.json();
    if(data.success) window.location.href = '../home.php';
    else alert(data.error);
}

function openFundModal(uid) {
    document.getElementById('fundUid').value = uid;
    document.getElementById('fundModal').style.display = 'flex';
}

async function submitFunds() {
    const uid = document.getElementById('fundUid').value;
    const amount = document.getElementById('fundAmount').value;
    const currency = document.getElementById('fundCurrency').value;
    const type = document.getElementById('fundType').value;
    
    if(!amount || amount <= 0) return alert('Invalid amount');
    
    const fd = new FormData();
    fd.append('user_id', uid); fd.append('amount', amount);
    fd.append('currency', currency); fd.append('type', type);
    
    const res = await fetch('api/index.php?action=manage_funds', { method:'POST', body:fd });
    const data = await res.json();
    if(data.success) {
        document.getElementById('fundModal').style.display = 'none';
        document.getElementById('fundAmount').value = '';
        loadUsers();
    } else alert(data.error);
}

// ── JOBS MANAGEMENT ──
async function loadJobsAdmin() {
    try {
        const res = await fetch('api/index.php?action=jobs');
        const data = await res.json();
        if(data.success) {
            document.getElementById('jobsBody').innerHTML = data.data.map(j => `
                <tr>
                    <td>${j.id}</td>
                    <td>${j.title}</td>
                    <td><span class="badge badge-blue">${j.category}</span></td>
                    <td style="color:var(--green)">$${parseFloat(j.reward).toFixed(2)}</td>
                    <td><span class="badge ${j.status==='active'?'badge-green':'badge-red'}">${j.status}</span></td>
                    <td>
                        <button class="mini-btn danger" onclick="deleteJob(${j.id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        }
    } catch(e) { console.error(e); }
}

function openJobModal() {
    document.getElementById('jobId').value = '';
    document.getElementById('jobTitle').value = '';
    document.getElementById('jobCategory').value = '';
    document.getElementById('jobReward').value = '';
    document.getElementById('jobDesc').value = '';
    document.getElementById('jobModal').style.display = 'flex';
}

async function submitJob() {
    const fd = new FormData();
    fd.append('title', document.getElementById('jobTitle').value);
    fd.append('category', document.getElementById('jobCategory').value);
    fd.append('reward', document.getElementById('jobReward').value);
    fd.append('description', document.getElementById('jobDesc').value);
    
    const res = await fetch('api/index.php?action=job_add', { method:'POST', body:fd });
    const data = await res.json();
    if(data.success) {
        document.getElementById('jobModal').style.display = 'none';
        loadJobsAdmin();
    } else alert(data.error);
}

async function deleteJob(id) {
    if(!confirm("Are you sure you want to delete this job?")) return;
    const fd = new FormData(); fd.append('job_id', id);
    const res = await fetch('api/index.php?action=job_delete', { method:'POST', body:fd });
    const data = await res.json();
    if(data.success) loadJobsAdmin();
    else alert(data.error);
}

document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadChart();
});

// ── KYC QUEUE ──
async function loadKycQueue() {
    try {
        const res = await fetch('api/index.php?action=kyc_queue');
        const data = await res.json();
        if(data.success) {
            document.getElementById('kycPendingCount').innerText = data.data.length + ' pending';
            document.getElementById('kycBody').innerHTML = data.data.map(u => `
                <tr>
                    <td>${u.id}</td>
                    <td>${u.first_name} ${u.last_name}</td>
                    <td>${u.email}</td>
                    <td style="font-size:10px;">${u.kyc_submitted_at || 'N/A'}</td>
                    <td><span class="badge badge-gold">${u.kyc_status}</span></td>
                    <td>
                        <button class="mini-btn" onclick="approveKyc(${u.id}, 'verified')" style="color:var(--green)">✔ Approve</button>
                        <button class="mini-btn" onclick="approveKyc(${u.id}, 'rejected')" style="color:var(--red)">✘ Reject</button>
                    </td>
                </tr>`).join('');
        }
    } catch(e) { console.error(e); }
}

async function approveKyc(uid, status) {
    const fd = new FormData();
    fd.append('user_id', uid); fd.append('field', 'kyc_status'); fd.append('value', status);
    await fetch('api/index.php?action=update_user', { method:'POST', body:fd });
    loadKycQueue();
}

// ── FRAUD ALERTS ──
async function loadFraudAlerts() {
    try {
        const res = await fetch('api/index.php?action=fraud_alerts');
        const data = await res.json();
        if(!data.success) return;
        document.getElementById('fraudOpenCount').innerText = data.open;
        document.getElementById('fraudResolvedCount').innerText = data.resolved;
        document.getElementById('fraudBody').innerHTML = data.data.map(a => {
            const scoreColor = a.risk_score >= 70 ? 'var(--red)' : a.risk_score >= 45 ? 'var(--gold)' : 'var(--green)';
            return `<tr>
                <td>${a.id}</td>
                <td>${a.email || 'User #' + a.user_id}</td>
                <td style="color:${scoreColor};font-weight:700">${a.risk_score}</td>
                <td style="font-size:11px;">${a.alert_reason}</td>
                <td style="font-size:10px;">${a.created_at}</td>
                <td><span class="badge ${a.status==='open'?'badge-red':'badge-green'}">${a.status}</span></td>
                <td>
                    ${a.status === 'open' ? `<button class="mini-btn" onclick="resolveAlert(${a.id},'resolved')">Resolve</button>
                    <button class="mini-btn" onclick="resolveAlert(${a.id},'false_positive')" style="color:var(--muted)">FP</button>` : '—'}
                </td>
            </tr>`;
        }).join('');
    } catch(e) { console.error(e); }
}

async function resolveAlert(id, status) {
    const fd = new FormData(); fd.append('alert_id', id); fd.append('status', status);
    await fetch('api/index.php?action=resolve_fraud', { method:'POST', body:fd });
    loadFraudAlerts();
}

// ── SYSTEM SETTINGS ──
async function loadSystemSettings() {
    try {
        const res = await fetch('api/index.php?action=system_settings');
        const data = await res.json();
        if(data.success) {
            const s = data.data;
            document.getElementById('set_withdrawal_fee_pct').value = s.withdrawal_fee_pct || '';
            document.getElementById('set_transfer_fee_pct').value = s.transfer_fee_pct || '';
            document.getElementById('set_crypto_trade_fee_pct').value = s.crypto_trade_fee_pct || '';
            document.getElementById('set_referral_bonus').value = s.referral_bonus || '';
            const tog = document.getElementById('maintenanceToggle');
            tog.style.background = s.maintenance_mode === '1' ? 'var(--red)' : 'var(--border)';
        }
    } catch(e) { console.error(e); }
}

async function saveSettings() {
    const fd = new FormData();
    fd.append('withdrawal_fee_pct', document.getElementById('set_withdrawal_fee_pct').value);
    fd.append('transfer_fee_pct', document.getElementById('set_transfer_fee_pct').value);
    fd.append('crypto_trade_fee_pct', document.getElementById('set_crypto_trade_fee_pct').value);
    fd.append('referral_bonus', document.getElementById('set_referral_bonus').value);
    const res = await fetch('api/index.php?action=update_settings', {method:'POST', body:fd});
    const data = await res.json();
    if(data.success) alert('Settings saved!');
    else alert(data.error || 'Error saving settings');
}

async function toggleSetting(key) {
    const tog = document.getElementById('maintenanceToggle');
    const isOn = tog.style.background === 'rgb(255, 69, 96)';
    tog.style.background = isOn ? 'var(--border)' : 'var(--red)';
    const fd = new FormData(); fd.append(key, isOn ? '0' : '1');
    await fetch('api/index.php?action=update_settings', {method:'POST', body:fd});
}
