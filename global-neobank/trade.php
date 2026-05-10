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
<title>Trade — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<!-- NAV -->
<nav>
  <div class="logo">GLOBAL<em>BANK</em></div>
  <div class="nav-links">
    <div class="nav-link" onclick="window.location='home.php'">Home</div>
    <div class="nav-link active">Trade</div>
    <div class="nav-link" onclick="window.location='loans.php'">Loans</div>
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
      <h1>Global <span>Markets</span></h1>
      <p>Trade crypto, stocks, and forex with zero fees.</p>
    </div>
  </div>

  <div class="grid-2">
    <!-- CHART CARD -->
    <div class="fcard" style="--accent:var(--gold); grid-column: span 2;">
      <div class="fcard-top" style="margin-bottom: 16px;">
        <div class="fcard-icon" style="--accent:var(--gold)">📊</div>
        <div class="fcard-title" style="flex:1;">MARKET CHART</div>
        
        <div style="display:flex; gap:8px;">
          <select id="chartAsset" onchange="loadChartData()" style="padding:4px 8px; font-size:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); outline:none;">
              <option value="BTC">BTC</option>
              <option value="ETH">ETH</option>
              <option value="SOL">SOL</option>
              <option value="AAPL">AAPL</option>
              <option value="NVDA">NVDA</option>
          </select>
          <button onclick="setChartType('candlestick')" class="act-btn" style="padding:4px 8px; font-size:10px;">Candles</button>
          <button onclick="setChartType('line')" class="act-btn" style="padding:4px 8px; font-size:10px;">Line</button>
        </div>
      </div>
      <div id="tvChart" style="width:100%; height:350px;"></div>
    </div>
    <!-- TRADING CARD -->
    <div class="fcard" style="--accent:var(--cyan)">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--cyan)">📈</div>
        <div class="fcard-title">MARKETS</div>
      </div>
      <div id="marketsList" style="margin-top:16px; display:flex; flex-direction:column; gap:12px;"></div>
    </div>
    
    <!-- EXECUTE TRADE -->
    <div class="fcard" style="--accent:var(--green)">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--green)">⚡</div>
        <div class="fcard-title">QUICK TRADE</div>
      </div>

      <div style="margin-bottom:16px; background:#ffffff05; padding:12px; border-radius:4px;">
        <div style="font-size:10px; color:var(--muted); text-transform:uppercase;">Your Balances</div>
        <div id="walletBalances" style="margin-top:8px; display:flex; gap:12px; flex-wrap:wrap; font-size:12px;">
            Loading balances...
        </div>
      </div>

      <form id="tradeForm" onsubmit="event.preventDefault();">
          <div style="margin-bottom:12px;">
              <select id="tradeAsset" required style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); outline:none;">
                  <option value="BTC">BTC (Bitcoin)</option>
                  <option value="ETH">ETH (Ethereum)</option>
                  <option value="SOL">SOL (Solana)</option>
                  <option value="AAPL">AAPL (Apple)</option>
                  <option value="NVDA">NVDA (Nvidia)</option>
              </select>
          </div>
          <div style="margin-bottom:12px;">
              <input type="number" id="tradeAmount" placeholder="Amount USD" required min="1" step="0.01" style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); outline:none;">
          </div>
          <p id="tradeMsg" style="font-size:11px; margin-bottom:12px;"></p>
          <div style="display:flex; gap:10px;">
              <button type="button" onclick="executeTrade('BUY')" class="act-btn fill" style="flex:1;">BUY</button>
              <button type="button" onclick="executeTrade('SELL')" class="act-btn" style="flex:1; border-color:var(--red); color:var(--red);">SELL</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://unpkg.com/lightweight-charts@4.1.1/dist/lightweight-charts.standalone.production.js"></script>
<script src="assets/js/dashboard.js"></script>
<script>
let chart;
let candlestickSeries;
let lineSeries;
let currentChartType = 'candlestick';

function initChart() {
    try {
        const container = document.getElementById('tvChart');
        if (!container) throw new Error("tvChart container not found");
        
        chart = LightweightCharts.createChart(container, {
            layout: {
                background: { type: 'solid', color: 'transparent' },
                textColor: '#8392a5',
            },
            grid: {
                vertLines: { color: 'rgba(255, 255, 255, 0.05)' },
                horzLines: { color: 'rgba(255, 255, 255, 0.05)' },
            },
            crosshair: {
                mode: LightweightCharts.CrosshairMode.Normal,
            },
            rightPriceScale: {
                borderColor: 'rgba(255, 255, 255, 0.1)',
            },
            timeScale: {
                borderColor: 'rgba(255, 255, 255, 0.1)',
            },
        });

        candlestickSeries = chart.addCandlestickSeries({
            upColor: '#00e87a', downColor: '#ff4560', borderDownColor: '#ff4560', borderUpColor: '#00e87a', wickDownColor: '#ff4560', wickUpColor: '#00e87a',
        });
        
        lineSeries = chart.addLineSeries({
            color: '#f5c842', lineWidth: 2,
        });
        
        // Use color: transparent instead of visible: false just in case
        // lightweight-charts sometimes complains about visible: false if not set right
        // Wait, visible is a valid option.
        lineSeries.applyOptions({ visible: false });

        new ResizeObserver(entries => {
            if (entries.length === 0 || entries[0].target !== container) return;
            const newRect = entries[0].contentRect;
            chart.applyOptions({ height: newRect.height, width: newRect.width });
        }).observe(container);
        
        loadChartData();
    } catch(err) {
        console.error(err);
        alert("Chart Init Error: " + err.message);
    }
}

async function loadChartData() {
    const asset = document.getElementById('chartAsset').value;
    try {
        const res = await fetch(`api/trading.php?action=chart&asset=${asset}`);
        const data = await res.json();
        if(data.success) {
            // Ensure data is sorted
            data.data.sort((a,b) => new Date(a.time) - new Date(b.time));
            
            const cData = data.data.map(d => ({
                time: d.time,
                open: Number(d.open),
                high: Number(Math.max(d.high, d.open, d.close)),
                low: Number(Math.min(d.low, d.open, d.close)),
                close: Number(d.close)
            }));
            
            const lineData = cData.map(d => ({ time: d.time, value: d.close }));
            
            candlestickSeries.setData(cData);
            lineSeries.setData(lineData);
            chart.timeScale().fitContent();
            
            document.getElementById('tradeAsset').value = asset;
        } else {
            alert("API Error: " + data.error);
        }
    } catch(e) { 
        console.error(e);
        alert('Failed to load chart data: ' + e.message); 
    }
}

function setChartType(type) {
    currentChartType = type;
    if(type === 'candlestick') {
        candlestickSeries.applyOptions({ visible: true });
        lineSeries.applyOptions({ visible: false });
    } else {
        candlestickSeries.applyOptions({ visible: false });
        lineSeries.applyOptions({ visible: true });
    }
}
async function loadMarketsAndBalances() {
    try {
        const [marketRes, balRes] = await Promise.all([
            fetch('api/trading.php?action=pairs'),
            fetch('api/trading.php?action=balances')
        ]);
        const marketData = await marketRes.json();
        const balData = await balRes.json();
        
        let html = '';
        if(marketData.success) {
            marketData.data.forEach(c => {
                const col = c.change > 0 ? 'var(--green)' : 'var(--red)';
                html += `<div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border);">
                    <span>${c.name} (${c.symbol})</span>
                    <span style="color:${col}">$${c.price} (${c.change}%)</span>
                </div>`;
            });
            document.getElementById('marketsList').innerHTML = html;
        }
        
        if(balData.success) {
            document.getElementById('walletBalances').innerHTML = balData.data.map(w => {
                return `<div><strong style="color:var(--gold)">${w.currency}:</strong> ${parseFloat(w.balance).toFixed(4)}</div>`;
            }).join('');
        }

    } catch(e) { console.error('Failed to load data'); }
}

async function executeTrade(type) {
    const asset = document.getElementById('tradeAsset').value;
    const amount = document.getElementById('tradeAmount').value;
    const msg = document.getElementById('tradeMsg');

    if(!amount || amount <= 0) return;

    const formData = new FormData();
    formData.append('asset', asset);
    formData.append('type', type);
    formData.append('amount_usd', amount);

    try {
        const res = await fetch('api/trading.php?action=execute', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            msg.style.color = 'var(--green)';
            msg.innerText = data.message;
            document.getElementById('tradeAmount').value = '';
            loadMarketsAndBalances(); // Refresh balances
        } else {
            msg.style.color = 'var(--red)';
            msg.innerText = data.error;
        }
    } catch(err) {
        msg.style.color = 'var(--red)';
        msg.innerText = 'Network Error';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadMarketsAndBalances();
    initChart();
    
    // Automatically update chart when Quick Trade asset changes
    document.getElementById('tradeAsset').addEventListener('change', function() {
        document.getElementById('chartAsset').value = this.value;
        loadChartData();
    });
});
</script>
</body>
</html>
