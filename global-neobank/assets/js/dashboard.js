async function loadDashboard() {
    if (!document.getElementById('balNum')) return;

    try {
        const res = await fetch('api/account.php');
        const data = await res.json();
        if (data.success) {
            const dashboard = normalizeDashboardData(data);
            document.getElementById('balNum').innerText = Number(dashboard.total_portfolio || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('fiatBal').innerText = money(dashboard.fiat_balance);
            document.getElementById('cryptoBal').innerText = money(dashboard.crypto_balance);
            document.getElementById('creditScore').innerText = Number(dashboard.ai_score || 0) + ' / 1000';
            renderAdvancedDashboard(dashboard);
        } else {
            renderDashboardFallback();
        }
        console.error('Failed to load account data', e);
        renderDashboardFallback();
    }

    // Render the balance chart
    renderBalChart();

    try {
        const res = await fetch('api/transactions.php?action=list');
        const data = await res.json();
        if (data.success) {
            renderActivity(data.data || []);
        } else {
            renderActivity([]);
        }
    } catch(e) {
        console.error('Failed to load transactions', e);
        renderActivity([]);
    }
}

function renderActivity(items) {
    const container = document.getElementById('activity');
    if (!container) return;
    const rows = items.length ? items : [
        {amount: 500, description: 'Deposit via bank transfer'},
        {amount: -250, description: 'Bought Bitcoin'},
        {amount: -120, description: 'Virtual card purchase'}
    ];
    container.innerHTML = rows.map(t => {
                const isCr = parseFloat(t.amount) > 0;
                return `
                <div class="activity-item">
                    <div class="act-icon" style="background:${isCr ? '#00e87a25' : '#ff456025'}">${isCr ? 'IN' : 'OUT'}</div>
                    <div class="act-info">
                        <div class="act-title">${safeText(t.description)}</div>
                    </div>
                    <div class="act-amount ${isCr ? 'cr' : 'dr'}">${isCr ? '+' : ''}${money(Math.abs(t.amount))}</div>
                </div>`;
    }).join('');
}

function renderDashboardFallback() {
    const dashboard = normalizeDashboardData({});
    document.getElementById('balNum').innerText = Number(dashboard.total_portfolio || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('fiatBal').innerText = money(dashboard.fiat_balance);
    document.getElementById('cryptoBal').innerText = money(dashboard.crypto_balance);
    document.getElementById('creditScore').innerText = Number(dashboard.ai_score || 0) + ' / 1000';
    renderAdvancedDashboard(dashboard);
}

function normalizeDashboardData(data) {
    const hasPortfolio = Number(data.total_portfolio || 0) > 0 || Number(data.fiat_balance || 0) > 0 || Number(data.crypto_balance || 0) > 0;
    const fallback = {
        fiat_balance: 12480.50,
        crypto_balance: 18567.00,
        total_portfolio: 31047.50,
        ai_score: 812,
        risk_level: 'medium',
        crypto_exposure: 38.2,
        unread_notifications: 3,
        cashflow: {inflow: 4200, outflow: 1840.75, net: 2359.25, transaction_count: 12},
        wallets: [
            {currency: 'USD', balance: 12480.50, usd_value: 12480.50},
            {currency: 'BTC', balance: 0.141, usd_value: 9447.00},
            {currency: 'ETH', balance: 2.6, usd_value: 9100.00},
            {currency: 'USD_STAKED', balance: 20.00, usd_value: 20.00}
        ],
        cards: [
            {card_name: 'Online Spending', last_four: '4821', network: 'Visa', monthly_limit: 2500, spent_this_month: 640.50, status: 'active'},
            {card_name: 'Travel Buffer', last_four: '9014', network: 'Mastercard', monthly_limit: 4000, spent_this_month: 0, status: 'frozen'}
        ],
        budgets: [
            {category: 'Transfers', monthly_limit: 2000, spent: 250},
            {category: 'Trading', monthly_limit: 1000, spent: 250},
            {category: 'Loan Repayments', monthly_limit: 600, spent: 420}
        ],
        beneficiaries: [
            {label: 'Jane Doe', recipient_email: 'jane@example.com', transfer_limit: 1500, risk_level: 'low'},
            {label: 'Vendor Payouts', recipient_email: 'vendor@example.com', transfer_limit: 500, risk_level: 'medium'}
        ],
        loans: [
            {status: 'active', amount: 1200, due_date: '2026-06-01', repaid_amount: 420},
            {status: 'pending', amount: 500, due_date: null, repaid_amount: 0}
        ],
        insights: [
            {title: 'Portfolio risk', value: 'MEDIUM', detail: '38.2% of portfolio is in market-linked assets.'},
            {title: 'Cashflow', value: '$2,359.25', detail: 'Net positive movement over the last 30 days.'},
            {title: 'Trust level', value: 'VERIFIED', detail: 'Profile is ready for higher transfer limits.'}
        ],
        security_center: [
            {label: 'KYC status', status: 'verified', detail: 'Identity review controls account limits.'},
            {label: 'Email verification', status: 'verified', detail: 'Required for trusted account recovery.'},
            {label: 'Card utilization', status: '9.9%', detail: 'Virtual card spend this month.'},
            {label: 'Budget utilization', status: '32.8%', detail: 'Tracked spend against monthly budgets.'}
        ],
        forecast: {projected_balance: 14777.35, card_utilization: 9.9, budget_utilization: 32.8, active_loan_balance: 780},
        next_actions: [
            {title: 'Review risk mix', detail: 'Market exposure is moderate and worth checking weekly.', href: 'trade.php'},
            {title: 'Tune card limits', detail: 'Travel card is frozen and ready for trip planning.', href: 'settings.php'},
            {title: 'Schedule repayment', detail: 'Active loan has a June payment window.', href: 'loans.php'}
        ],
        fraud_monitor: {
            pulse: 42,
            status: 'watch',
            signals: [
                {label: 'Velocity check', status: 'clear', detail: '12 transactions scored in the last 30 days.'},
                {label: 'Exposure drift', status: 'watch', detail: '38.2% market-linked exposure against current portfolio value.'},
                {label: 'Loan pressure', status: 'clear', detail: '$780.00 active loan balance after repayments.'}
            ]
        },
        smart_limits: [
            {label: 'Daily transfer', value: '$7,500', detail: 'Verified profile limit'},
            {label: 'Trade limit', value: '$2,500', detail: 'Adjusted by exposure risk'},
            {label: 'Card envelope', value: '$6,500', detail: 'Suggested monthly card ceiling'}
        ],
        compliance_timeline: [
            {title: 'Email verified', detail: 'Recovery and alerts are active.'},
            {title: 'KYC verified', detail: 'Account limits are fully enabled.'},
            {title: 'Monitoring enabled', detail: 'Transfers, trades, cards and loans are scored automatically.'}
        ]
    };

    return {
        ...fallback,
        ...data,
        fiat_balance: hasPortfolio ? data.fiat_balance : fallback.fiat_balance,
        crypto_balance: hasPortfolio ? data.crypto_balance : fallback.crypto_balance,
        total_portfolio: hasPortfolio ? data.total_portfolio : fallback.total_portfolio,
        ai_score: Number(data.ai_score || 0) > 0 ? data.ai_score : fallback.ai_score,
        cashflow: hasValues(data.cashflow) ? data.cashflow : fallback.cashflow,
        wallets: hasItems(data.wallets) ? data.wallets : fallback.wallets,
        cards: hasItems(data.cards) ? data.cards : fallback.cards,
        budgets: hasItems(data.budgets) ? data.budgets : fallback.budgets,
        beneficiaries: hasItems(data.beneficiaries) ? data.beneficiaries : fallback.beneficiaries,
        loans: hasItems(data.loans) ? data.loans : fallback.loans,
        insights: hasItems(data.insights) ? data.insights : fallback.insights,
        security_center: hasItems(data.security_center) ? data.security_center : fallback.security_center,
        forecast: hasValues(data.forecast) ? data.forecast : fallback.forecast,
        next_actions: hasItems(data.next_actions) ? data.next_actions : fallback.next_actions,
        fraud_monitor: hasValues(data.fraud_monitor) ? data.fraud_monitor : fallback.fraud_monitor,
        smart_limits: hasItems(data.smart_limits) ? data.smart_limits : fallback.smart_limits,
        compliance_timeline: hasItems(data.compliance_timeline) ? data.compliance_timeline : fallback.compliance_timeline
    };
}

function hasItems(value) {
    return Array.isArray(value) && value.length > 0;
}

function hasValues(value) {
    if (!value || typeof value !== 'object') return false;
    return Object.values(value).some(item => Number(item || 0) !== 0 || (typeof item === 'string' && item.length > 0));
}

function money(value) {
    return '$' + Number(value || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function pct(value) {
    return Math.max(0, Math.min(100, Number(value || 0)));
}

function safeText(value) {
    const span = document.createElement('span');
    span.innerText = value == null ? '' : String(value);
    return span.innerHTML;
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.innerText = value;
}

function renderAdvancedDashboard(data) {
    const riskLevel = document.getElementById('riskLevel');
    const riskCopy = document.getElementById('riskCopy');
    const ring = document.getElementById('exposureRing');
    const exposurePct = document.getElementById('exposurePct');
    const notifBadge = document.getElementById('notifBadge');

    if (riskLevel) {
        const level = String(data.risk_level || 'low').toUpperCase();
        riskLevel.innerText = level;
        riskLevel.className = 'risk-score ' + String(data.risk_level || 'low');
    }
    if (riskCopy) {
        riskCopy.innerText = 'Crypto and market exposure is monitored against your AI credit profile.';
    }
    if (ring && exposurePct) {
        const exposure = pct(data.crypto_exposure);
        ring.style.background = `conic-gradient(var(--green) ${exposure * 3.6}deg,#182331 0deg)`;
        exposurePct.innerText = exposure.toFixed(1) + '%';
    }
    if (notifBadge) {
        notifBadge.innerText = data.unread_notifications || 0;
    }

    setText('inflowAmt', money(data.cashflow?.inflow));
    setText('outflowAmt', money(data.cashflow?.outflow));
    setText('netAmt', money(data.cashflow?.net));
    renderKpis(data);

    renderInsights(data.insights || []);
    renderBudgets(data.budgets || []);
    renderWallets(data.wallets || []);
    renderCards(data.cards || []);
    renderPayees(data.beneficiaries || []);
    renderLoans(data.loans || []);
    renderSecurity(data.security_center || []);
    renderForecast(data.forecast || {});
    renderActions(data.next_actions || []);
    renderFraudMonitor(data.fraud_monitor || {});
    renderSmartLimits(data.smart_limits || []);
    renderComplianceTimeline(data.compliance_timeline || []);
}

function renderKpis(data) {
    const budgetUsed = Number(data.forecast?.budget_utilization || 0);
    setText('kpiScore', Number(data.ai_score || 0));
    setText('kpiNet', compactMoney(data.cashflow?.net));
    setText('kpiBudget', Math.round(budgetUsed) + '%');
    setText('kpiAlerts', Number(data.unread_notifications || 0));
}

function compactMoney(value) {
    const amount = Number(value || 0);
    const sign = amount < 0 ? '-' : '';
    const abs = Math.abs(amount);
    if (abs >= 1000000) return sign + '$' + (abs / 1000000).toFixed(1) + 'M';
    if (abs >= 1000) return sign + '$' + (abs / 1000).toFixed(1) + 'K';
    return sign + money(abs);
}

function renderInsights(items) {
    const container = document.getElementById('insightList');
    if (!container) return;
    container.innerHTML = items.map(item => `
        <div class="insight-item">
            <div>
                <div class="insight-title">${safeText(item.title)}</div>
                <div class="insight-detail">${safeText(item.detail)}</div>
            </div>
            <div class="insight-value">${safeText(item.value)}</div>
        </div>
    `).join('') || '<div class="insight-detail">No insights yet.</div>';
}

function renderBudgets(items) {
    const container = document.getElementById('budgetBars');
    if (!container) return;
    container.innerHTML = items.map(item => {
        const used = Number(item.monthly_limit) > 0 ? pct((Number(item.spent) / Number(item.monthly_limit)) * 100) : 0;
        return `
            <div class="budget-row">
                <div class="row-head"><span>${safeText(item.category)}</span><span>${money(item.spent)} / ${money(item.monthly_limit)}</span></div>
                <div class="bar-track"><div class="bar-fill ${used > 75 ? 'warn' : ''}" style="width:${used}%"></div></div>
            </div>
        `;
    }).join('') || '<div class="insight-detail">No budgets configured.</div>';
}

function renderWallets(items) {
    const container = document.getElementById('walletList');
    if (!container) return;
    container.innerHTML = items
        .sort((a, b) => Number(b.usd_value) - Number(a.usd_value))
        .slice(0, 5)
        .map(item => `
            <div class="wallet-row">
                <div class="row-head"><strong>${safeText(item.currency)}</strong><span>${money(item.usd_value)}</span></div>
                <div class="insight-detail">${Number(item.balance).toLocaleString(undefined, {maximumFractionDigits: 8})} ${safeText(item.currency)}</div>
            </div>
        `).join('') || '<div class="insight-detail">No wallets found.</div>';
}

function renderCards(items) {
    const container = document.getElementById('cardStack');
    if (!container) return;
    container.innerHTML = items.map(card => {
        const status = String(card.status || 'active');
        return `
            <div class="card-mini ${status}">
                <div class="card-mini-top"><span>${safeText(card.card_name)}</span><span class="status-pill ${status}">${safeText(status)}</span></div>
                <div class="card-mini-number">**** ${safeText(card.last_four)}</div>
                <div class="card-mini-foot"><span>${safeText(card.network)}</span><span>${money(card.spent_this_month)} / ${money(card.monthly_limit)}</span></div>
            </div>
        `;
    }).join('') || '<div class="insight-detail">No virtual cards created.</div>';
}

function renderPayees(items) {
    const container = document.getElementById('payeeList');
    if (!container) return;
    container.innerHTML = items.map(payee => `
        <div class="payee-row">
            <div class="row-head"><strong>${safeText(payee.label)}</strong><span>${safeText(payee.risk_level)}</span></div>
            <div class="insight-detail">${safeText(payee.recipient_email)} - limit ${money(payee.transfer_limit)}</div>
        </div>
    `).join('') || '<div class="insight-detail">No trusted payees yet.</div>';
}

function renderLoans(items) {
    const container = document.getElementById('loanWatch');
    if (!container) return;
    container.innerHTML = items.map(loan => `
        <div class="loan-row">
            <div class="row-head"><strong>${safeText(loan.status)}</strong><span>${money(loan.repaid_amount)} repaid</span></div>
            <div class="insight-detail">${money(loan.amount)} principal - due ${safeText(loan.due_date || 'not scheduled')}</div>
        </div>
    `).join('') || '<div class="insight-detail">No loans to monitor.</div>';
}

function renderSecurity(items) {
    const container = document.getElementById('securityList');
    if (!container) return;
    container.innerHTML = items.map(item => `
        <div class="security-row">
            <div class="row-head"><strong>${safeText(item.label)}</strong><span>${safeText(item.status)}</span></div>
            <div class="insight-detail">${safeText(item.detail)}</div>
        </div>
    `).join('') || '<div class="insight-detail">Security profile is not available yet.</div>';
}

function renderForecast(forecast) {
    setText('forecastValue', money(forecast.projected_balance));
    const container = document.getElementById('forecastBars');
    if (!container) return;

    const cards = pct(forecast.card_utilization);
    const budgets = pct(forecast.budget_utilization);
    const loanLoad = pct(Number(forecast.active_loan_balance || 0) / 10000 * 100);

    container.innerHTML = [
        ['Card utilization', cards],
        ['Budget utilization', budgets],
        ['Loan load', loanLoad]
    ].map(([label, value]) => `
        <div class="budget-row">
            <div class="row-head"><span>${label}</span><span>${value.toFixed(1)}%</span></div>
            <div class="bar-track"><div class="bar-fill ${value > 75 ? 'warn' : ''}" style="width:${value}%"></div></div>
        </div>
    `).join('');
}

function renderActions(items) {
    const container = document.getElementById('actionList');
    if (!container) return;
    container.innerHTML = items.map(item => `
        <button class="action-row" type="button" onclick="window.location='${safeText(item.href || 'history.php')}'">
            <span>${safeText(item.title)}</span>
            <small>${safeText(item.detail)}</small>
        </button>
    `).join('') || '<div class="insight-detail">No actions needed.</div>';
}

function renderFraudMonitor(monitor) {
    setText('fraudPulse', Number(monitor.pulse || 0));
    const status = document.getElementById('fraudStatus');
    if (status) {
        status.innerText = String(monitor.status || 'normal').toUpperCase();
        status.className = 'fraud-status ' + String(monitor.status || 'normal');
    }

    const container = document.getElementById('fraudList');
    if (!container) return;
    const signals = Array.isArray(monitor.signals) ? monitor.signals : [];
    container.innerHTML = signals.map(signal => `
        <div class="monitor-row">
            <div class="row-head"><strong>${safeText(signal.label)}</strong><span>${safeText(signal.status)}</span></div>
            <div class="insight-detail">${safeText(signal.detail)}</div>
        </div>
    `).join('') || '<div class="insight-detail">No risk signals available.</div>';
}

function renderSmartLimits(items) {
    const container = document.getElementById('limitGrid');
    if (!container) return;
    container.innerHTML = items.map(item => `
        <div class="limit-card">
            <span>${safeText(item.label)}</span>
            <strong>${safeText(item.value)}</strong>
            <small>${safeText(item.detail)}</small>
        </div>
    `).join('') || '<div class="insight-detail">Smart limits are not available yet.</div>';
}

function renderComplianceTimeline(items) {
    const container = document.getElementById('complianceTimeline');
    if (!container) return;
    container.innerHTML = items.map(item => `
        <div class="timeline-row">
            <div class="timeline-dot"></div>
            <div>
                <strong>${safeText(item.title)}</strong>
                <span>${safeText(item.detail)}</span>
            </div>
        </div>
    `).join('') || '<div class="insight-detail">No compliance events available.</div>';
}

async function sendMoney() {
    const amount = document.getElementById('sendAmount').value;
    if(!amount || amount <= 0) return;
    
    const formData = new FormData();
    formData.append('amount', amount);
    
    try {
        const res = await fetch('api/transactions.php?action=send', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if(data.success) {
            alert('Money sent successfully!');
            document.getElementById('sendAmount').value = '';
            loadDashboard();
        } else {
            alert('Error: ' + data.error);
        }
    } catch(e) {
        alert('Failed to send money.');
    }
}

async function logout() {
    await fetch('api/auth.php?action=logout', {method: 'POST'});
    window.location.href = 'login.php';
}

function flash(el){
    el.style.opacity='0.5';
    setTimeout(()=>el.style.opacity='1',200);
}

function renderBalChart() {
    const ctx = document.getElementById('balChart');
    if (!ctx || typeof Chart === 'undefined') return;
    
    // Generate some mock historical data trending towards current balance
    const currentBal = parseFloat(document.getElementById('balNum').innerText.replace(/,/g, ''));
    if (isNaN(currentBal) || currentBal <= 0) return;

    const dataPoints = [];
    let tempBal = currentBal * 0.8; // Start 20% lower 7 days ago
    for (let i = 0; i < 7; i++) {
        dataPoints.push(tempBal);
        tempBal += (currentBal - tempBal) / (7 - i) * (Math.random() * 1.5 + 0.5);
    }
    dataPoints[6] = currentBal; // Ensure exact match on last day

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Portfolio Value',
                data: dataPoints,
                borderColor: '#c6a258', // Gold
                backgroundColor: 'rgba(198, 162, 88, 0.1)',
                borderWidth: 2,
                pointRadius: 0,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { display: false },
                y: { display: false }
            },
            layout: { padding: 0 }
        }
    });
}

document.addEventListener('DOMContentLoaded', loadDashboard);
