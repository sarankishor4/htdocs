let currentUser = null;
let currentCoins = [];
let portfolio = [];
let trades = [];

// Charts
let portfolioChart = null;
let pnlChart = null;

document.addEventListener('DOMContentLoaded', () => {
    initNavigation();
    initProfileSettings();
    initAdvancedFeatures();
    loadDashboardData();
    
    document.getElementById('logout-btn').addEventListener('click', async () => {
        await fetch('api/logout.php');
        window.location.href = 'login.php';
    });
});

function showToast(msg, type = 'success') {
    const c = document.getElementById('toast-container');
    const t = document.createElement('div');
    t.className = `toast toast-${type}`;
    t.innerHTML = `<span>${type === 'success' ? '✓' : '⚠'}</span> ${msg}`;
    c.appendChild(t);
    setTimeout(() => {
        t.style.opacity = '0';
        setTimeout(() => t.remove(), 300);
    }, 3000);
}

function navigateTo(pageId) {
    const btn = document.querySelector(`.sidebar-btn[data-page="${pageId}"]`);
    if(btn) btn.click();
}

function initNavigation() {
    const btns = document.querySelectorAll('.sidebar-btn[data-page]');
    const pages = document.querySelectorAll('.page');
    
    btns.forEach(btn => {
        btn.addEventListener('click', () => {
            btns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const p = btn.getAttribute('data-page');
            pages.forEach(pg => {
                pg.classList.remove('active');
                if(pg.id === `page-${p}`) pg.classList.add('active');
            });
            
            if(window.innerWidth <= 768) {
                document.getElementById('sidebar').classList.remove('open');
            }
            
            if(p === 'dashboard') loadDashboardData();
            if(p === 'markets') loadMarkets();
            if(p === 'portfolio') loadPortfolioTab();
            if(p === 'ai-analysis') loadAIAnalysis();
            if(p === 'trades') loadTradesTab();
            if(p === 'roadmap') loadRoadmap();
            if(p === 'leaderboard') loadLeaderboard();
            if(p === 'bot') loadBotSettings();
        });
    });
}

async function loadDashboardData() {
    try {
        const [profRes, coinsRes, portRes, tradesRes] = await Promise.all([
            fetch('api/profile.php').then(r => r.json()),
            fetch('api/get_coins.php').then(r => r.json()),
            fetch('api/get_portfolio.php').then(r => r.json()),
            fetch('api/get_trades.php').then(r => r.json())
        ]);
        
        if(profRes.status === 'success') {
            currentUser = profRes.data;
            updateProfileUI();
        }
        
        if(coinsRes.status === 'success') currentCoins = coinsRes.data;
        if(portRes.status === 'success') portfolio = portRes;
        if(tradesRes.status === 'success') trades = tradesRes;

        renderDashboardStats();
        renderDashboardCharts();
        renderDashboardWatchlist();
        renderDashboardTrades();
        
    } catch (e) {
        console.error("Dashboard Load Error:", e);
    }
}

function updateProfileUI() {
    if(!currentUser) return;
    document.getElementById('nav-balance').textContent = `$${currentUser.balance.toLocaleString(undefined,{minimumFractionDigits:2})}`;
    const initials = currentUser.avatar_initials || currentUser.username.substring(0,2).toUpperCase();
    document.getElementById('nav-initials').textContent = initials;
    document.getElementById('nav-avatar').style.backgroundColor = currentUser.avatar_color || '#00ff88';
    
    const h = new Date().getHours();
    const g = h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
    document.getElementById('dash-greeting').textContent = `${g}, ${currentUser.full_name || currentUser.username}`;

    // Profile Tab
    document.getElementById('profile-avatar').textContent = initials;
    document.getElementById('profile-avatar').style.backgroundColor = currentUser.avatar_color || '#00ff88';
    document.getElementById('profile-name').textContent = currentUser.full_name || currentUser.username;
    document.getElementById('profile-username').textContent = `@${currentUser.username}`;
    document.getElementById('profile-joined').textContent = `Member since ${new Date(currentUser.created_at).toLocaleDateString()}`;
    
    document.getElementById('ps-trades').textContent = currentUser.total_trades;
    document.getElementById('ps-analyses').textContent = currentUser.total_analyses;
    const pnl = currentUser.total_pnl;
    document.getElementById('ps-pnl').textContent = `${pnl >= 0 ? '+' : ''}$${pnl.toLocaleString()}`;
    document.getElementById('ps-pnl').style.color = pnl >= 0 ? '#00ff88' : '#ff4466';
    document.getElementById('ps-winrate').textContent = `${currentUser.win_rate}%`;
    document.getElementById('ps-days').textContent = currentUser.member_days;
    
    document.getElementById('edit-name').value = currentUser.full_name || '';
    document.getElementById('edit-email').value = currentUser.email || '';
    document.getElementById('edit-bio').value = currentUser.bio || '';
}

function renderDashboardStats() {
    if(!portfolio || !trades) return;
    
    const s = portfolio.summary;
    document.getElementById('stat-portfolio').textContent = `$${s.total_value.toLocaleString()}`;
    const pChange = document.getElementById('stat-portfolio-change');
    pChange.textContent = `${s.total_pnl_pct >= 0 ? '+' : ''}${s.total_pnl_pct}%`;
    pChange.className = `stat-change ${s.total_pnl_pct >= 0 ? 'positive' : 'negative'}`;
    
    document.getElementById('stat-balance').textContent = `$${currentUser.balance.toLocaleString()}`;
    
    const ts = trades.stats;
    document.getElementById('stat-pnl').textContent = `$${ts.total_pnl.toLocaleString()}`;
    const tChange = document.getElementById('stat-pnl-change');
    tChange.textContent = ts.total_pnl >= 0 ? 'Profitable' : 'Loss';
    tChange.className = `stat-change ${ts.total_pnl >= 0 ? 'positive' : 'negative'}`;
    
    document.getElementById('stat-winrate').textContent = `${ts.win_rate}%`;
    document.getElementById('stat-trades-count').textContent = `${ts.total} trades total`;
}

function renderDashboardCharts() {
    if(!portfolio || !portfolio.data) return;
    
    const ctxPort = document.getElementById('portfolio-chart');
    if(portfolioChart) portfolioChart.destroy();
    
    const labels = portfolio.data.map(d => d.coin_symbol);
    const data = portfolio.data.map(d => d.value);
    const colors = portfolio.data.map(d => d.coin_color);
    
    if(data.length === 0) {
        labels.push('Cash');
        data.push(currentUser.balance);
        colors.push('rgba(255,255,255,0.1)');
    }

    portfolioChart = new Chart(ctxPort, {
        type: 'doughnut',
        data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0 }] },
        options: { cutout: '75%', plugins: { legend: { position: 'right', labels: { color: 'rgba(255,255,255,0.7)', font: { family: 'Space Mono' } } } } }
    });
    
    const ctxPnl = document.getElementById('pnl-chart');
    if(pnlChart) pnlChart.destroy();
    
    const pnlLabels = portfolio.data.map(d => d.coin_symbol);
    const pnlData = portfolio.data.map(d => d.pnl);
    const pnlColors = pnlData.map(v => v >= 0 ? '#00ff88' : '#ff4466');
    
    if(pnlData.length === 0) {
        pnlLabels.push('No Data'); pnlData.push(0); pnlColors.push('rgba(255,255,255,0.1)');
    }

    pnlChart = new Chart(ctxPnl, {
        type: 'bar',
        data: { labels: pnlLabels, datasets: [{ data: pnlData, backgroundColor: pnlColors, borderRadius: 4 }] },
        options: { scales: { y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: 'rgba(255,255,255,0.5)' } }, x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.5)', font: { family: 'Space Mono' } } } }, plugins: { legend: { display: false } } }
    });
}

function renderDashboardWatchlist() {
    const grid = document.getElementById('watchlist-grid');
    const watched = currentCoins.filter(c => c.watched);
    if(watched.length === 0) {
        grid.innerHTML = '<div class="empty-state-small">No coins in watchlist</div>';
        return;
    }
    
    grid.innerHTML = watched.map(c => {
        const pos = c.price_change >= 0;
        return `
        <div class="watch-card" onclick="navigateTo('markets')">
            <div class="watch-head">
                <span class="watch-sym" style="color:${c.color}">${c.symbol}</span>
                <span class="watch-change ${pos?'positive':'negative'}">${pos?'+':''}${c.price_change}%</span>
            </div>
            <div class="watch-price">$${c.price.toLocaleString()}</div>
        </div>
        `;
    }).join('');
}

function renderDashboardTrades() {
    const list = document.getElementById('recent-trades-list');
    if(!trades || trades.data.length === 0) {
        list.innerHTML = '<div class="empty-state-small">No recent trades</div>';
        return;
    }
    
    list.innerHTML = trades.data.slice(0,5).map(t => {
        const d = new Date(t.trade_date).toLocaleDateString(undefined, {month:'short', day:'numeric'});
        const typeCol = t.trade_type === 'BUY' ? '#00aaff' : '#ffaa00';
        return `
        <div class="trade-item">
            <div class="trade-info">
                <div class="trade-type" style="color:${typeCol}">${t.trade_type}</div>
                <div class="trade-desc">${t.amount} ${t.coin_symbol} @ $${t.price.toLocaleString()}</div>
            </div>
            <div class="trade-date">${d}</div>
        </div>
        `;
    }).join('');
}

// Markets logic
async function loadMarkets() {
    const grid = document.getElementById('markets-grid');
    grid.innerHTML = '<div class="empty-state-small">Loading markets...</div>';
    
    const res = await fetch('api/get_coins.php').then(r=>r.json());
    if(res.status === 'success') {
        currentCoins = res.data;
        grid.innerHTML = currentCoins.map(c => {
            const pos = c.price_change >= 0;
            return `
            <div class="market-card">
                <div class="mc-head">
                    <div class="mc-id">
                        <div class="mc-icon" style="color:${c.color}">${c.icon}</div>
                        <div>
                            <div class="mc-name">${c.name}</div>
                            <div class="mc-sym">${c.symbol}</div>
                        </div>
                    </div>
                    <button class="btn btn-ghost btn-sm btn-icon" onclick="toggleWatchlist('${c.id}')" title="Watchlist">
                        ${c.watched ? '⭐' : '☆'}
                    </button>
                </div>
                <div class="mc-price-row">
                    <div class="mc-price">$${c.price.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:4})}</div>
                    <div class="mc-change ${pos?'positive':'negative'}">${pos?'+':''}${c.price_change}%</div>
                </div>
                <div class="mc-stats">
                    <div><span class="lbl">Vol 24h</span><span class="val">${c.volume}</span></div>
                    <div><span class="lbl">High</span><span class="val">$${c.high_24h.toLocaleString()}</span></div>
                </div>
                <div class="mc-actions">
                    <button class="btn btn-primary btn-sm btn-full" onclick="openTradeModal('${c.id}')">Trade</button>
                    <button class="btn btn-ghost btn-sm btn-full" onclick="openAnalysis('${c.id}')">AI Analysis</button>
                </div>
            </div>
            `;
        }).join('');
    }
}

async function toggleWatchlist(id) {
    const res = await fetch('api/watchlist.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({coin_id:id}) }).then(r=>r.json());
    if(res.status === 'success') {
        showToast(res.message);
        loadMarkets();
        loadDashboardData();
    }
}

let activeTradeCoin = null;
let activeTradeType = 'BUY';

function openTradeModal(id) {
    activeTradeCoin = currentCoins.find(c => c.id === id);
    if(!activeTradeCoin) return;
    
    document.getElementById('trade-modal-title').textContent = `Trade ${activeTradeCoin.name}`;
    document.getElementById('trade-symbol').textContent = activeTradeCoin.symbol;
    document.getElementById('trade-price').textContent = `$${activeTradeCoin.price.toLocaleString()}`;
    document.getElementById('trade-amount').value = '';
    updateTradeTotal();
    
    document.getElementById('trade-modal').style.display = 'flex';
}

document.getElementById('trade-modal-close').addEventListener('click', () => {
    document.getElementById('trade-modal').style.display = 'none';
});

document.querySelectorAll('.trade-type-btn').forEach(b => {
    b.addEventListener('click', () => {
        document.querySelectorAll('.trade-type-btn').forEach(btn => btn.classList.remove('active'));
        b.classList.add('active');
        activeTradeType = b.getAttribute('data-type');
    });
});

document.getElementById('trade-amount').addEventListener('input', updateTradeTotal);

function updateTradeTotal() {
    const amt = parseFloat(document.getElementById('trade-amount').value) || 0;
    const tot = amt * (activeTradeCoin ? activeTradeCoin.price : 0);
    document.getElementById('trade-total').textContent = `$${tot.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2})}`;
}

document.getElementById('execute-trade-btn').addEventListener('click', async () => {
    const amt = parseFloat(document.getElementById('trade-amount').value);
    const err = document.getElementById('trade-error');
    err.style.display = 'none';
    
    if(!amt || amt <= 0) {
        err.textContent = 'Enter a valid amount';
        err.style.display = 'block';
        return;
    }
    
    const btn = document.getElementById('execute-trade-btn');
    btn.classList.add('loading');
    
    try {
        const res = await fetch('api/trade.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ coin_id: activeTradeCoin.id, trade_type: activeTradeType, amount: amt })
        });
        const data = await res.json();
        
        if(data.status === 'success') {
            showToast(data.message);
            document.getElementById('trade-modal').style.display = 'none';
            loadDashboardData();
        } else {
            err.textContent = data.message;
            err.style.display = 'block';
        }
    } catch(e) {
        err.textContent = 'Connection error';
        err.style.display = 'block';
    }
    btn.classList.remove('loading');
});

// Profile Settings Logic
function initProfileSettings() {
    document.getElementById('save-profile-btn').addEventListener('click', async () => {
        const n = document.getElementById('edit-name').value;
        const e = document.getElementById('edit-email').value;
        const b = document.getElementById('edit-bio').value;
        
        const res = await fetch('api/profile.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body:JSON.stringify({action:'update', full_name:n, email:e, bio:b})
        }).then(r=>r.json());
        
        if(res.status==='success') { showToast('Profile saved'); loadDashboardData(); }
        else showToast(res.message, 'error');
    });

    document.getElementById('change-pw-btn').addEventListener('click', async () => {
        const c = document.getElementById('pw-current').value;
        const n = document.getElementById('pw-new').value;
        
        const res = await fetch('api/profile.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body:JSON.stringify({action:'change_password', current_password:c, new_password:n})
        }).then(r=>r.json());
        
        if(res.status==='success') { showToast('Password updated'); document.getElementById('pw-current').value=''; document.getElementById('pw-new').value=''; }
        else showToast(res.message, 'error');
    });

    const delBtn = document.getElementById('delete-account-btn');
    if(delBtn) delBtn.addEventListener('click', async () => {
        const p = document.getElementById('delete-pw').value;
        if(!p) { showToast('Enter password to confirm deletion', 'error'); return; }
        if(confirm("Are you absolutely sure you want to delete your account? This is permanent.")) {
            const res = await fetch('api/profile.php', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body:JSON.stringify({action:'delete_account', password:p})
            }).then(r=>r.json());
            if(res.status==='success') window.location.href = 'index.php';
            else showToast(res.message, 'error');
        }
    });
}

function openAnalysis(id) {
    navigateTo('ai-analysis');
    setTimeout(() => {
        const btn = document.querySelector(`.ai-coin-card[data-id="${id}"]`);
        if(btn) btn.click();
    }, 500);
}

// Portfolio Tab
async function loadPortfolioTab() {
    const hold = document.getElementById('portfolio-holdings');
    const res = await fetch('api/get_portfolio.php').then(r=>r.json());
    
    if(res.status === 'success') {
        const s = res.summary;
        document.getElementById('portfolio-summary-bar').innerHTML = `
            <div class="psb-item"><span>Total Value</span><span class="psb-val">$${s.total_value.toLocaleString()}</span></div>
            <div class="psb-item"><span>Total Cost</span><span class="psb-val">$${s.total_cost.toLocaleString()}</span></div>
            <div class="psb-item"><span>Unrealized P&L</span><span class="psb-val ${s.total_pnl>=0?'positive':'negative'}">${s.total_pnl>=0?'+':''}$${s.total_pnl.toLocaleString()} (${s.total_pnl_pct}%)</span></div>
        `;
        
        if(res.data.length === 0) {
            hold.innerHTML = '<div class="empty-state">No holdings yet. Go to Markets to make a trade.</div>';
            return;
        }
        
        hold.innerHTML = res.data.map(h => {
            const pos = h.pnl >= 0;
            return `
            <div class="port-card">
                <div class="port-head">
                    <div class="port-sym" style="color:${h.coin_color}">${h.coin_symbol}</div>
                    <div class="port-val">$${h.value.toLocaleString()}</div>
                </div>
                <div class="port-details">
                    <div>Amt: ${h.amount}</div>
                    <div>Avg: $${h.avg_buy_price.toLocaleString()}</div>
                    <div>Cur: $${h.current_price.toLocaleString()}</div>
                </div>
                <div class="port-pnl ${pos?'positive':'negative'}">
                    P&L: ${pos?'+':''}$${h.pnl.toLocaleString()} (${h.pnl_pct}%)
                </div>
                <button class="btn btn-ghost btn-sm btn-full mt-2" onclick="openTradeModal('${h.coin_id}')">Trade</button>
            </div>
            `;
        }).join('');
    }
}

// Trades Tab
async function loadTradesTab() {
    const tbody = document.getElementById('trades-tbody');
    const res = await fetch('api/get_trades.php').then(r=>r.json());
    
    if(res.status === 'success') {
        const d = res.data;
        if(d.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center">No trades yet</td></tr>';
            return;
        }
        
        tbody.innerHTML = d.map(t => {
            const pnl = t.profit_loss !== null ? `<span class="${t.profit_loss>=0?'positive':'negative'}">${t.profit_loss>=0?'+':''}$${t.profit_loss}</span>` : '-';
            const typeClass = t.trade_type === 'BUY' ? 'type-buy' : 'type-sell';
            return `
            <tr>
                <td>${new Date(t.trade_date).toLocaleString()}</td>
                <td class="mono-bold">${t.coin_symbol}</td>
                <td><span class="badge ${typeClass}">${t.trade_type}</span></td>
                <td>${t.amount}</td>
                <td>$${t.price.toLocaleString()}</td>
                <td>$${t.total_value.toLocaleString()}</td>
                <td>${pnl}</td>
                <td>${t.signal_confidence? t.signal_confidence+'%' : '-'}</td>
            </tr>
            `;
        }).join('');
    }
}

// AI Analysis Tab
async function loadAIAnalysis() {
    const grid = document.getElementById('ai-coins-grid');
    const res = await fetch('api/get_coins.php').then(r=>r.json());
    
    if(res.status === 'success') {
        currentCoins = res.data;
        grid.innerHTML = currentCoins.map(c => `
            <div class="ai-coin-card" data-id="${c.id}" onclick="runAIAnalysis('${c.id}')" style="border-color:${c.color}44">
                <span style="color:${c.color}">${c.symbol}</span>
            </div>
        `).join('');
    }
    loadAnalysisHistory();
}

async function runAIAnalysis(id) {
    const coin = currentCoins.find(c => c.id === id);
    if(!coin) return;
    
    document.querySelectorAll('.ai-coin-card').forEach(c => c.classList.remove('selected'));
    document.querySelector(`.ai-coin-card[data-id="${id}"]`).classList.add('selected');
    
    const panel = document.getElementById('analysis-panel');
    panel.innerHTML = `
        <div class="loader-container">
            <div class="loader-text" style="color: ${coin.color}">AI ANALYZING ${coin.symbol}...</div>
            <div class="loader-bars">
                ${[0,1,2,3,4].map(i => `<div class="loader-bar" style="background: ${coin.color}; animation-delay: ${i * 0.15}s"></div>`).join('')}
            </div>
        </div>
    `;
    
    try {
        const res = await fetch('api/analyze.php', {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(coin)
        }).then(r=>r.json());
        
        if(res.status === 'success') {
            const a = res.data;
            const sCol = a.signal === 'BUY' ? '#00ff88' : a.signal === 'SELL' ? '#ff4466' : '#ffaa00';
            panel.innerHTML = `
                <div class="analysis-results">
                    <div class="signal-header">
                        <div class="signal-dot" style="background: ${coin.color}"></div>
                        <span class="signal-title" style="color: ${coin.color}">AI SIGNAL &mdash; ${coin.symbol}</span>
                    </div>
                    <div class="rec-box" style="background: linear-gradient(135deg, ${coin.color}22, transparent); border: 1px solid ${coin.color}44;">
                        <div class="rec-label">RECOMMENDATION</div>
                        <div class="rec-value" style="color: ${sCol}">${a.signal}</div>
                        <div class="rec-conf">Confidence: ${a.confidence}%</div>
                    </div>
                    <div class="metrics-grid">
                        <div class="metric-card"><div class="metric-label">RSI</div><div class="metric-val text-white">${a.rsi}</div><div class="metric-note">${a.rsi>70?'Overbought':a.rsi<30?'Oversold':'Neutral'}</div></div>
                        <div class="metric-card"><div class="metric-label">MACD</div><div class="metric-val text-white">${a.macd}</div><div class="metric-note">${parseFloat(a.macd)>0?'Bullish':'Bearish'}</div></div>
                        <div class="metric-card"><div class="metric-label">Support</div><div class="metric-val text-white">$${a.support.toLocaleString()}</div><div class="metric-note">Key Level</div></div>
                        <div class="metric-card"><div class="metric-label">Resistance</div><div class="metric-val text-white">$${a.resistance.toLocaleString()}</div><div class="metric-note">Key Level</div></div>
                    </div>
                    <div class="reasoning-box">
                        <div class="reasoning-label">AI REASONING</div>
                        <div class="reasoning-text">${a.reasoning}</div>
                    </div>
                </div>
            `;
            loadAnalysisHistory();
        } else {
            panel.innerHTML = `<div class="empty-state text-error">${res.message}</div>`;
        }
    } catch(e) {
        panel.innerHTML = `<div class="empty-state text-error">Failed to connect to AI engine.</div>`;
    }
}

async function loadAnalysisHistory() {
    const list = document.getElementById('analysis-history');
    const res = await fetch('api/get_analysis_history.php').then(r=>r.json());
    if(res.status === 'success' && res.data.length > 0) {
        list.innerHTML = res.data.map(h => {
            const sCol = h.signal === 'BUY' ? '#00ff88' : h.signal === 'SELL' ? '#ff4466' : '#ffaa00';
            return `
            <div class="history-card">
                <div class="hc-head">
                    <span class="mono-bold">${h.coin_symbol}</span>
                    <span style="color:${sCol}">${h.signal} (${h.confidence}%)</span>
                </div>
                <div class="hc-date">${new Date(h.created_at).toLocaleString()}</div>
            </div>
            `;
        }).join('');
    }
}

// Roadmap
function loadRoadmap() {
    const steps = [
        { phase: "01", title: "Learn the Basics", duration: "1–2 months", color: "#00ff88", items: ["Python fundamentals", "How crypto exchanges work", "Basic trading concepts", "Git & GitHub"] },
        { phase: "02", title: "Build Your First Bot", duration: "2–3 months", color: "#00aaff", items: ["Connect to a crypto API", "Paper trade", "Code a strategy", "Log trades"] },
        { phase: "03", title: "Add AI & Analysis", duration: "2–4 months", color: "#9945FF", items: ["Machine learning basics", "Train prediction model", "Sentiment analysis", "Connect AI APIs"] },
        { phase: "04", title: "Build the Dashboard", duration: "2–3 months", color: "#F7931A", items: ["Learn frontend frameworks", "Display live prices", "Show bot status", "Build portfolio tracker"] },
        { phase: "05", title: "Go Live (Carefully!)", duration: "Ongoing", color: "#ff4466", items: ["Start with tiny amounts", "Set strict stop-losses", "Monitor performance", "Iterate and improve"] },
    ];
    document.getElementById('roadmap-container').innerHTML = steps.map(s => `
        <div class="roadmap-card" style="border-left: 4px solid ${s.color}">
            <div class="rc-phase" style="color:${s.color}">PHASE ${s.phase} &bull; ${s.duration}</div>
            <div class="rc-title">${s.title}</div>
            <ul class="rc-items">${s.items.map(i => `<li>${i}</li>`).join('')}</ul>
        </div>
    `).join('');
}

/* ========== ADVANCED FEATURES ========== */
let tvChartInstance = null;
let candleSeries = null;

function initAdvancedFeatures() {
    // 1. Mobile Menu Toggle
    const menuBtn = document.getElementById('mobile-menu-btn');
    if(menuBtn) {
        menuBtn.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('open');
        });
    }

    // close sidebar on click outside in mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            if (!e.target.closest('.sidebar') && !e.target.closest('#mobile-menu-btn')) {
                document.getElementById('sidebar').classList.remove('open');
            }
        }
    });

    // 2. Export CSV
    const exportBtn = document.getElementById('export-csv-btn');
    if(exportBtn) {
        exportBtn.addEventListener('click', () => {
            if(!trades || !trades.data || trades.data.length === 0) return showToast('No trades to export', 'error');
            const headers = ['Date', 'Asset', 'Type', 'Amount', 'Price', 'Total', 'P&L', 'Confidence'];
            const rows = trades.data.map(t => [
                new Date(t.trade_date).toLocaleString().replace(/,/g, ''),
                t.coin_symbol, t.trade_type, t.amount, t.price, t.total_value, t.profit_loss || '', t.signal_confidence || ''
            ]);
            let csvContent = "data:text/csv;charset=utf-8," + headers.join(',') + "\n" + rows.map(r => r.join(',')).join("\n");
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "cryptomind_trades.csv");
            document.body.appendChild(link);
            link.click();
            link.remove();
            showToast('Trades exported successfully');
        });
    }

    // 3. Mock News Fetching
    fetchMockNews();

    // 4. WebSocket Simulation for Prices (Poll every 5s)
    setInterval(simulateLivePrices, 5000);
}

function fetchMockNews() {
    const list = document.getElementById('market-news-list');
    if(!list) return;
    const news = [
        { source: 'CoinDesk', title: 'Bitcoin Breaks Key Resistance, Analysts Target New Highs', time: '10m ago' },
        { source: 'Decrypt', title: 'Ethereum Gas Fees Drop to Yearly Lows Ahead of Upgrade', time: '1h ago' },
        { source: 'Bloomberg', title: 'Institutional Inflows to Crypto Funds Resume After Brief Pause', time: '2h ago' }
    ];
    list.innerHTML = news.map(n => `
        <div class="news-item">
            <div class="news-meta"><span>${n.source}</span><span>${n.time}</span></div>
            <div class="news-title">${n.title}</div>
        </div>
    `).join('');
}

async function simulateLivePrices() {
    const res = await fetch('api/get_coins.php').then(r=>r.json());
    if(res.status === 'success') {
        const newCoins = res.data;
        newCoins.forEach(newC => {
            const oldC = currentCoins.find(c => c.id === newC.id);
            if(oldC && oldC.price !== newC.price) {
                // Update Markets Grid
                const marketCards = document.querySelectorAll('.market-card');
                marketCards.forEach(card => {
                    if(card.innerHTML.includes(newC.name)) {
                        const priceEl = card.querySelector('.mc-price');
                        if(priceEl) {
                            priceEl.textContent = '$' + newC.price.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:4});
                            priceEl.classList.add(newC.price > oldC.price ? 'flash-green' : 'flash-red');
                            setTimeout(() => priceEl.classList.remove('flash-green', 'flash-red'), 1000);
                        }
                    }
                });
            }
        });
        currentCoins = newCoins;
    }
}

function initTradingViewChart(coin) {
    const container = document.getElementById('tv-chart');
    document.getElementById('tv-chart-container').style.display = 'block';
    
    if(tvChartInstance) {
        tvChartInstance.remove();
        tvChartInstance = null;
    }

    tvChartInstance = LightweightCharts.createChart(container, {
        width: container.clientWidth,
        height: 400,
        layout: { backgroundColor: '#080c14', textColor: 'rgba(255, 255, 255, 0.9)' },
        grid: { vertLines: { color: 'rgba(255, 255, 255, 0.05)' }, horzLines: { color: 'rgba(255, 255, 255, 0.05)' } },
        crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
        priceScale: { borderColor: 'rgba(255, 255, 255, 0.1)' },
        timeScale: { borderColor: 'rgba(255, 255, 255, 0.1)' }
    });

    candleSeries = tvChartInstance.addCandlestickSeries({
        upColor: '#00ff88', downColor: '#ff4466', borderDownColor: '#ff4466', borderUpColor: '#00ff88', wickDownColor: '#ff4466', wickUpColor: '#00ff88'
    });

    // Generate realistic mock daily candle data based on current price
    const data = [];
    let curPrice = coin.price * 0.8; // Start 20% lower 60 days ago
    let time = Math.floor(Date.now() / 1000) - (60 * 86400);

    for(let i=0; i<60; i++) {
        const volatility = curPrice * 0.03;
        const open = curPrice;
        const close = open + (Math.random() - 0.45) * volatility;
        const high = Math.max(open, close) + (Math.random() * volatility * 0.5);
        const low = Math.min(open, close) - (Math.random() * volatility * 0.5);
        
        data.push({ time: time, open, high, low, close });
        curPrice = close;
        time += 86400;
    }
    
    // Add today's current price as the last candle
    data.push({
        time: Math.floor(Date.now() / 1000),
        open: curPrice,
        high: Math.max(curPrice, coin.price) * 1.01,
        low: Math.min(curPrice, coin.price) * 0.99,
        close: coin.price
    });

    candleSeries.setData(data);
    tvChartInstance.timeScale().fitContent();
}

// Hook into runAIAnalysis to render chart
const originalRunAIAnalysis = runAIAnalysis;
runAIAnalysis = async function(id) {
    await originalRunAIAnalysis(id);
    const coin = currentCoins.find(c => c.id === id);
    if(coin) initTradingViewChart(coin);
}

/* ========== NEW FUNCTIONS ========== */

async function loadLeaderboard() {
    const res = await fetch('api/leaderboard.php').then(r=>r.json());
    if(res.status === 'success') {
        const tbody = document.getElementById('leaderboard-tbody');
        tbody.innerHTML = res.data.map((l, i) => `
            <tr>
                <td><span class="badge ${i<3 ? 'type-buy' : ''}">#${i+1}</span></td>
                <td><div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:28px;height:28px;border-radius:50%;background:${l.avatar_color};display:flex;align-items:center;justify-content:center;color:#000;font-weight:bold;font-size:10px;">${l.username.substring(0,2).toUpperCase()}</div>
                    <span class="mono-bold">${l.username}</span>
                </div></td>
                <td class="mono-bold">$${parseFloat(l.total_assets).toLocaleString()}</td>
                <td class="${l.roi >= 0 ? 'positive' : 'negative'} mono-bold">${l.roi >= 0 ? '+' : ''}${l.roi}%</td>
                <td class="${l.total_pnl >= 0 ? 'positive' : 'negative'} mono-bold">$${parseFloat(l.total_pnl).toLocaleString()}</td>
                <td>${l.total_trades}</td>
            </tr>
        `).join('');
    }
}

async function loadBotSettings() {
    const res = await fetch('api/bot_settings.php').then(r=>r.json());
    if(res.status === 'success') {
        const toggle = document.getElementById('bot-active-toggle');
        const status = document.getElementById('bot-status-text');
        
        toggle.checked = res.data.active;
        status.textContent = res.data.active ? 'Bot Active' : 'Bot Offline';
        status.style.color = res.data.active ? 'var(--accent)' : 'var(--text-muted)';
        
        document.getElementById('bot-risk-select').value = res.data.risk;
        document.getElementById('bot-alloc-input').value = res.data.allocation;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('bot-active-toggle');
    if(toggle) {
        toggle.addEventListener('change', (e) => {
            const status = document.getElementById('bot-status-text');
            status.textContent = e.target.checked ? 'Bot Active' : 'Bot Offline';
            status.style.color = e.target.checked ? 'var(--accent)' : 'var(--text-muted)';
        });
    }

    const saveBotBtn = document.getElementById('save-bot-btn');
    if(saveBotBtn) {
        saveBotBtn.addEventListener('click', async () => {
            const active = document.getElementById('bot-active-toggle').checked ? 1 : 0;
            const risk = document.getElementById('bot-risk-select').value;
            const allocation = document.getElementById('bot-alloc-input').value;
            
            saveBotBtn.innerHTML = '<span class="btn-loader" style="display:block;"></span>';
            const res = await fetch('api/bot_settings.php', {
                method: 'POST',
                body: JSON.stringify({active, risk, allocation})
            }).then(r=>r.json());
            
            saveBotBtn.innerHTML = 'Save Configuration';
            
            if(res.status === 'success') {
                showToast(res.message);
            } else {
                showToast(res.message || 'Error saving', 'error');
            }
        });
    }
});
