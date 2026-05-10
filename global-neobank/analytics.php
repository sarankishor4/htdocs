<?php
require_once __DIR__ . '/core/includes/auth_guard.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analytics — GlobalBank</title>
<meta name="description" content="Advanced personal financial analytics for GlobalBank users.">
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.kpi-box{padding:20px;background:var(--card);border:1px solid var(--border);border-radius:8px;border-left:3px solid var(--accent,var(--green))}
.kpi-label{font-size:9px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:8px}
.kpi-value{font-family:var(--display);font-size:32px;color:#fff;line-height:1}
.kpi-sub{font-size:11px;color:var(--muted);margin-top:6px}
.chart-box{background:var(--card);border:1px solid var(--border);border-radius:8px;padding:24px;margin-bottom:16px}
.chart-title{font-family:var(--display);font-size:18px;letter-spacing:1px;color:#fff;margin-bottom:20px}
.heatmap{display:grid;grid-template-columns:repeat(7,1fr);gap:4px}
.heat-cell{aspect-ratio:1;border-radius:3px;background:var(--surface);transition:background 0.3s}
.heat-cell:hover{transform:scale(1.2)}
.insight-feed{display:flex;flex-direction:column;gap:12px}
.ai-insight{padding:14px;background:var(--surface);border-left:3px solid var(--purple);border-radius:0 4px 4px 0}
.ai-insight .icon{font-size:18px;margin-bottom:6px}
.ai-insight p{font-size:12px;color:var(--muted);line-height:1.6}
@media(max-width:768px){.kpi-row{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<nav>
  <div class="logo">GLOBAL<em>BANK</em></div>
  <div class="nav-links">
    <div class="nav-link" onclick="window.location='home.php'">Home</div>
    <div class="nav-link active">Analytics</div>
    <div class="nav-link" onclick="window.location='trade.php'">Trade</div>
    <div class="nav-link" onclick="window.location='tiers.php'" style="color:var(--gold)">Upgrade</div>
  </div>
  <div class="nav-right">
    <div class="notif-btn" onclick="window.location='notifications.php'">Alerts<div class="notif-badge" id="notifBadge">0</div></div>
    <div class="user-pill" onclick="window.location='account.php'">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>

<div style="background:var(--surface);border-bottom:1px solid var(--border);padding:0 32px;display:flex;gap:24px;height:42px;align-items:flex-end;position:relative;z-index:2">
  <button class="tab-switch active" onclick="switchView('overview',this)">Overview</button>
  <button class="tab-switch" onclick="switchView('cashflow',this)">Cashflow</button>
  <button class="tab-switch" onclick="switchView('portfolio',this)">Portfolio</button>
  <button class="tab-switch" onclick="switchView('ai',this)">AI Insights</button>
  <style>.tab-switch{background:none;border:none;border-bottom:2px solid transparent;color:var(--muted);font-size:11px;letter-spacing:1px;text-transform:uppercase;padding:0 2px 10px;cursor:pointer;font-family:var(--font);transition:all .2s}.tab-switch.active{color:var(--green);border-bottom-color:var(--green)}</style>
</div>

<div class="wrap">
  <div class="welcome">
    <div class="welcome-left">
      <h1>Financial <span>Analytics</span></h1>
      <p id="analyticsSubtitle">Loading your personalised financial intelligence...</p>
    </div>
    <div id="dateRange" style="font-size:11px;color:var(--muted);letter-spacing:1px"></div>
  </div>

  <!-- KPI ROW -->
  <div class="kpi-row">
    <div class="kpi-box" style="--accent:var(--green)"><div class="kpi-label">Net Cash Flow</div><div class="kpi-value" id="kpi-net">—</div><div class="kpi-sub">Last 30 days</div></div>
    <div class="kpi-box" style="--accent:var(--gold)"><div class="kpi-label">Total Inflow</div><div class="kpi-value" id="kpi-in">—</div><div class="kpi-sub">Last 30 days</div></div>
    <div class="kpi-box" style="--accent:var(--red)"><div class="kpi-label">Total Outflow</div><div class="kpi-value" id="kpi-out">—</div><div class="kpi-sub">Last 30 days</div></div>
    <div class="kpi-box" style="--accent:var(--purple)"><div class="kpi-label">Savings Rate</div><div class="kpi-value" id="kpi-sr">—</div><div class="kpi-sub">% of income saved</div></div>
  </div>

  <!-- OVERVIEW VIEW -->
  <div id="view-overview">
    <div class="grid-2">
      <div class="chart-box"><div class="chart-title">30-Day Cash Flow</div><canvas id="cashflowChart" height="220"></canvas></div>
      <div class="chart-box"><div class="chart-title">Spending Breakdown</div><canvas id="spendingChart" height="220"></canvas></div>
    </div>
    <div class="chart-box">
      <div class="chart-title">Spending Heatmap — Last 3 Months</div>
      <div style="display:flex;gap:6px;align-items:flex-start;margin-bottom:12px;flex-wrap:wrap">
        <span style="font-size:10px;color:var(--muted)">Mon</span>
      </div>
      <div class="heatmap" id="heatmap"></div>
      <div style="display:flex;align-items:center;gap:8px;margin-top:12px;font-size:10px;color:var(--muted)">
        <span>Less</span>
        <div style="width:14px;height:14px;border-radius:2px;background:#0d2b1b"></div>
        <div style="width:14px;height:14px;border-radius:2px;background:#00683a"></div>
        <div style="width:14px;height:14px;border-radius:2px;background:#00e87a"></div>
        <span>More</span>
      </div>
    </div>
  </div>

  <!-- CASHFLOW VIEW -->
  <div id="view-cashflow" style="display:none">
    <div class="chart-box"><div class="chart-title">Monthly Income vs Expenses (6 Months)</div><canvas id="monthlyChart" height="260"></canvas></div>
    <div class="chart-box"><div class="chart-title">Cumulative Savings Growth</div><canvas id="savingsChart" height="200"></canvas></div>
  </div>

  <!-- PORTFOLIO VIEW -->
  <div id="view-portfolio" style="display:none">
    <div class="chart-box"><div class="chart-title">Asset Allocation</div><canvas id="allocationChart" height="280"></canvas></div>
  </div>

  <!-- AI INSIGHTS VIEW -->
  <div id="view-ai" style="display:none">
    <div class="chart-box">
      <div class="chart-title">🧠 AI-Powered Financial Insights</div>
      <div class="insight-feed" id="insightFeed">Generating insights...</div>
    </div>
  </div>
</div>

<div class="bottom-nav">
  <div class="bn-item" onclick="window.location='home.php'"><div class="bn-icon">🏠</div>Home</div>
  <div class="bn-item" onclick="window.location='trade.php'"><div class="bn-icon">📈</div>Trade</div>
  <div class="bn-item active"><div class="bn-icon">📊</div>Analytics</div>
  <div class="bn-item" onclick="window.location='account.php'"><div class="bn-icon">⚙️</div>Account</div>
  <div class="bn-item" onclick="window.location='profile.php'"><div class="bn-icon">👤</div>Profile</div>
</div>

<script>
let charts = {};
function switchView(name, el) {
  document.querySelectorAll('.tab-switch').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  document.querySelectorAll('[id^="view-"]').forEach(v => v.style.display = 'none');
  document.getElementById('view-' + name).style.display = 'block';
  if(name === 'cashflow' && !charts.monthly) buildCashflowCharts();
  if(name === 'portfolio' && !charts.allocation) buildPortfolioCharts();
  if(name === 'ai' && !charts.ai) buildAIInsights();
}

function destroyChart(id) { if(charts[id]) { charts[id].destroy(); delete charts[id]; } }

async function loadAnalytics() {
  const res = await fetch('api/analytics.php?action=summary');
  const d = await res.json();
  if(!d.success) return;

  document.getElementById('kpi-net').innerText = '$' + fmt(d.net_flow);
  document.getElementById('kpi-in').innerText  = '$' + fmt(d.inflow);
  document.getElementById('kpi-out').innerText = '$' + fmt(d.outflow);
  document.getElementById('kpi-sr').innerText  = d.savings_rate + '%';
  document.getElementById('analyticsSubtitle').innerText = 'Showing data for the last 30 days · ' + d.transaction_count + ' transactions analysed.';
  document.getElementById('dateRange').innerText = d.date_range;

  // Cashflow 30-day chart
  const labels = d.daily.map(x => x.day);
  const inData  = d.daily.map(x => x.inflow);
  const outData = d.daily.map(x => x.outflow);
  const ctx = document.getElementById('cashflowChart').getContext('2d');
  charts.cashflow = new Chart(ctx, {
    type:'bar',
    data:{ labels, datasets:[
      { label:'Inflow', data:inData, backgroundColor:'#00e87a66', borderColor:'#00e87a', borderWidth:1 },
      { label:'Outflow', data:outData, backgroundColor:'#ff456066', borderColor:'#ff4560', borderWidth:1 }
    ]},
    options:{ responsive:true, plugins:{legend:{labels:{color:'#5a7080'}}}, scales:{ x:{ticks:{color:'#5a7080'},grid:{color:'#131d2a'}}, y:{ticks:{color:'#5a7080'},grid:{color:'#131d2a'}} } }
  });

  // Spending donut
  const sCtx = document.getElementById('spendingChart').getContext('2d');
  const cats = d.categories || [];
  charts.spending = new Chart(sCtx, {
    type:'doughnut',
    data:{ labels:cats.map(c=>c.category||'Other'), datasets:[{ data:cats.map(c=>c.total), backgroundColor:['#00e87a','#f5c842','#2196f3','#a855f7','#ff4560','#00d4d4'], borderWidth:0 }] },
    options:{ responsive:true, plugins:{legend:{labels:{color:'#5a7080'}}} }
  });

  // Heatmap — random demo data
  const hm = document.getElementById('heatmap');
  hm.innerHTML = '';
  for(let i=0; i<84; i++) {
    const v = Math.random();
    const cell = document.createElement('div');
    cell.className = 'heat-cell';
    cell.style.background = v < 0.4 ? '#0d2b1b' : v < 0.7 ? '#00683a' : '#00e87a';
    cell.title = '$' + (v * 200).toFixed(2);
    hm.appendChild(cell);
  }
}

function buildCashflowCharts() {
  const months = ['Nov','Dec','Jan','Feb','Mar','Apr'];
  const income = [3200,4100,3800,4400,3900,5100];
  const expenses = [2400,3200,2800,3100,2600,3400];
  const savings = income.map((v,i) => income.slice(0,i+1).reduce((a,b)=>a+b,0) - expenses.slice(0,i+1).reduce((a,b)=>a+b,0));

  const mCtx = document.getElementById('monthlyChart').getContext('2d');
  charts.monthly = new Chart(mCtx, {
    type:'bar',
    data:{ labels:months, datasets:[
      { label:'Income', data:income, backgroundColor:'#00e87a55', borderColor:'#00e87a', borderWidth:2, borderRadius:4 },
      { label:'Expenses', data:expenses, backgroundColor:'#ff456055', borderColor:'#ff4560', borderWidth:2, borderRadius:4 }
    ]},
    options:{ responsive:true, plugins:{legend:{labels:{color:'#5a7080'}}}, scales:{ x:{ticks:{color:'#5a7080'},grid:{display:false}}, y:{ticks:{color:'#5a7080'},grid:{color:'#131d2a'}} } }
  });

  const sCtx = document.getElementById('savingsChart').getContext('2d');
  charts.savingsLine = new Chart(sCtx, {
    type:'line',
    data:{ labels:months, datasets:[{ label:'Cumulative Savings', data:savings, borderColor:'#a855f7', backgroundColor:'#a855f720', fill:true, tension:0.4 }] },
    options:{ responsive:true, plugins:{legend:{labels:{color:'#5a7080'}}}, scales:{ x:{ticks:{color:'#5a7080'},grid:{color:'#131d2a'}}, y:{ticks:{color:'#5a7080'},grid:{color:'#131d2a'}} } }
  });
}

async function buildPortfolioCharts() {
  const res = await fetch('api/analytics.php?action=portfolio');
  const d = await res.json();
  if(!d.success) return;
  const aCtx = document.getElementById('allocationChart').getContext('2d');
  charts.allocation = new Chart(aCtx, {
    type:'doughnut',
    data:{ labels:d.data.map(w=>w.currency), datasets:[{ data:d.data.map(w=>w.usd_value), backgroundColor:['#f5c842','#00e87a','#a855f7','#2196f3','#ff4560'], borderWidth:0 }] },
    options:{ responsive:true, plugins:{legend:{labels:{color:'#5a7080'},position:'bottom'}} }
  });
}

function buildAIInsights() {
  charts.ai = true;
  const insights = [
    { icon:'📉', title:'Spending Velocity', text:'Your outflow increased by 23% compared to last month. The largest category was transfers. Consider reviewing recurring subscriptions.' },
    { icon:'💡', title:'Savings Opportunity', text:'Based on your income trend, you could increase savings by $340/month if you reduce discretionary spending to the prior quarter average.' },
    { icon:'🎯', title:'Goal Projection', text:'At your current savings rate, your active savings goals will be reached in approximately 4.2 months.' },
    { icon:'⚠️', title:'Risk Flag', text:'Your crypto allocation at current market prices exceeds 40% of total portfolio, increasing your exposure to volatility.' },
    { icon:'🏆', title:'Credit Score Trend', text:'Your AI credit score has improved this month based on consistent repayment behaviour. Keep it up!' },
  ];
  document.getElementById('insightFeed').innerHTML = insights.map(i => `
    <div class="ai-insight">
      <div class="icon">${i.icon} <strong style="color:var(--text);font-size:13px;">${i.title}</strong></div>
      <p>${i.text}</p>
    </div>`).join('');
}

function fmt(n) { return parseFloat(n||0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); }

document.addEventListener('DOMContentLoaded', loadAnalytics);
</script>
</body>
</html>
