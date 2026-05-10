<?php
require_once __DIR__.'/../includes/auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoMind — Admin Console</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-page { background: #05080f; }
        .admin-nav { background: #0a0f1a; border-bottom: 1px solid #1a2333; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; }
        .admin-brand { font-family: var(--font-mono); font-weight: 700; color: #ff4466; display: flex; align-items: center; gap: 12px; }
        .admin-container { padding: 40px; max-width: 1400px; margin: 0 auto; }
        .admin-stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 40px; }
        .admin-stat-card { background: #0d1421; border: 1px solid #1a2333; border-radius: 12px; padding: 24px; }
        .admin-panel { background: #0d1421; border: 1px solid #1a2333; border-radius: 12px; padding: 24px; }
        .table-wrap { overflow-x: auto; margin-top: 24px; }
        .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
        .admin-table th { padding: 12px 16px; border-bottom: 1px solid #1a2333; font-family: var(--font-mono); font-size: 11px; color: rgba(255,255,255,0.4); text-transform: uppercase; }
        .admin-table td { padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.02); font-size: 14px; }
        .action-btn { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; transition: 0.2s; }
        .action-btn:hover { background: rgba(255,255,255,0.1); }
        .action-btn.danger { color: #ff4466; border-color: rgba(255,68,102,0.3); }
        .action-btn.danger:hover { background: rgba(255,68,102,0.1); }
    </style>
</head>
<body class="admin-page">
    <nav class="admin-nav">
        <div class="admin-brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            CryptoMind Admin Console
        </div>
        <div>
            <a href="../dashboard.php" class="btn btn-ghost btn-sm">Exit to Dashboard</a>
        </div>
    </nav>

    <div class="admin-container">
        <div class="page-header">
            <h2 class="page-title">System Overview</h2>
        </div>

        <div class="admin-stat-row" id="admin-stats">
            <div class="admin-stat-card"><div class="stat-label">Total Users</div><div class="stat-value" id="as-users">...</div></div>
            <div class="admin-stat-card"><div class="stat-label">Total Trades</div><div class="stat-value" id="as-trades">...</div></div>
            <div class="admin-stat-card"><div class="stat-label">System Volume</div><div class="stat-value" id="as-vol">...</div></div>
            <div class="admin-stat-card"><div class="stat-label">Total AI Queries</div><div class="stat-value" id="as-ai">...</div></div>
        </div>

        <div class="admin-panel">
            <div class="section-head">
                <span class="section-title">User Management</span>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Balance</th>
                            <th>Trades</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="admin-users-tbody">
                        <tr><td colspan="8">Loading users...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <script>
        function showToast(msg, type = 'success') {
            const c = document.getElementById('toast-container');
            const t = document.createElement('div');
            t.className = `toast toast-${type}`;
            t.innerHTML = `<span>${type === 'success' ? '✓' : '⚠'}</span> ${msg}`;
            c.appendChild(t);
            setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3000);
        }

        async function loadAdminData() {
            try {
                const statsRes = await fetch('api/get_stats.php').then(r=>r.json());
                if(statsRes.status === 'success') {
                    document.getElementById('as-users').textContent = statsRes.data.users;
                    document.getElementById('as-trades').textContent = statsRes.data.trades;
                    document.getElementById('as-vol').textContent = '$' + parseFloat(statsRes.data.volume).toLocaleString();
                    document.getElementById('as-ai').textContent = statsRes.data.ai_queries;
                }

                const usersRes = await fetch('api/get_users.php').then(r=>r.json());
                if(usersRes.status === 'success') {
                    const tb = document.getElementById('admin-users-tbody');
                    tb.innerHTML = usersRes.data.map(u => `
                        <tr>
                            <td>#${u.id}</td>
                            <td class="mono-bold">${u.username}</td>
                            <td>${u.email}</td>
                            <td><span class="badge ${u.role==='admin'?'type-sell':''}">${u.role}</span></td>
                            <td class="mono-bold">$${parseFloat(u.balance).toLocaleString()}</td>
                            <td>${u.trade_count}</td>
                            <td>${new Date(u.created_at).toLocaleDateString()}</td>
                            <td style="display:flex;gap:8px;">
                                <button class="action-btn" onclick="resetBalance(${u.id})">Reset $10k</button>
                                ${u.role !== 'admin' ? `<button class="action-btn danger" onclick="deleteUser(${u.id})">Ban</button>` : ''}
                            </td>
                        </tr>
                    `).join('');
                }
            } catch(e) { console.error(e); }
        }

        async function resetBalance(uid) {
            if(!confirm('Reset this user\'s balance to $10,000?')) return;
            const res = await fetch('api/manage_user.php', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({action:'reset_balance', user_id:uid})
            }).then(r=>r.json());
            if(res.status==='success') { showToast('Balance reset'); loadAdminData(); }
            else showToast(res.message, 'error');
        }

        async function deleteUser(uid) {
            if(!confirm('Are you absolutely sure you want to ban/delete this user?')) return;
            const res = await fetch('api/manage_user.php', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({action:'delete_user', user_id:uid})
            }).then(r=>r.json());
            if(res.status==='success') { showToast('User deleted'); loadAdminData(); }
            else showToast(res.message, 'error');
        }

        document.addEventListener('DOMContentLoaded', loadAdminData);
    </script>
</body>
</html>
