<?php
require_once __DIR__ . '/core/includes/auth_guard.php';
require_once __DIR__ . '/core/includes/db.php';

requireLogin();
$user = currentUser();
$pdo = getDB();
$isAdmin = false;

try {
    $stmtAdmin = $pdo->prepare('SELECT is_admin FROM users WHERE id = ?');
    $stmtAdmin->execute([$user['id']]);
    $isAdmin = (bool)$stmtAdmin->fetchColumn();
} catch (PDOException $e) {
    $isAdmin = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GlobalBank - Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<nav>
  <div class="logo">GLOBAL<em>BANK</em></div>
  <div class="nav-links">
    <div class="nav-link active">Home</div>
    <div class="nav-link" onclick="window.location='trade.php'">Trade</div>
    <div class="nav-link" onclick="window.location='analytics.php'">Analytics</div>
    <div class="nav-link" onclick="window.location='cards.php'">Cards</div>
    <div class="nav-link" onclick="window.location='savings.php'">Savings</div>
    <div class="nav-link" onclick="window.location='tiers.php'" style="color:var(--gold)">Upgrade</div>
    <?php if($isAdmin): ?><div class="nav-link" onclick="window.location='admin/'" style="color:var(--green)">Admin</div><?php endif; ?>
  </div>
  <div class="nav-right">
    <div class="notif-btn" onclick="window.location='notifications.php'">Alerts<div class="notif-badge" id="notifBadge">0</div></div>
    <div class="user-pill" onclick="window.location='profile.php'">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>

<div class="ticker">
  <div class="ticker-track" id="ticker"></div>
</div>

<div class="wrap">

  <?php if (!$user['is_verified']): ?>
  <div class="verify-banner">
      <div>Please verify your email address. Check your inbox for the verification link.</div>
  </div>
  <?php endif; ?>

  <div class="welcome">
    <div class="welcome-left">
      <h1>Good Morning, <span><?= htmlspecialchars($user['first_name']) ?></span></h1>
      <p><span id="currentDate"></span> &nbsp;-&nbsp; Your financial world, in one place</p>
    </div>
    <div class="welcome-right">
      <div class="quick-btn" onclick="window.location='send.php'">Add Money</div>
      <div class="quick-btn" onclick="window.location='send.php'">Send</div>
      <div class="quick-btn primary" onclick="window.location='trade.php'">Quick Trade</div>
    </div>
  </div>

  <div class="balance-card">
    <div>
      <div class="balance-label">Total Portfolio Value</div>
      <div class="balance-amount">$<span id="balNum">0</span></div>
      <div class="balance-sub">
        <div class="balance-sub-item">
          <div class="bsi-label">Fiat Balance</div>
          <div class="bsi-val" id="fiatBal">$0.00</div>
        </div>
        <div class="balance-sub-item">
          <div class="bsi-label">Market Holdings</div>
          <div class="bsi-val" id="cryptoBal">$0.00</div>
        </div>
        <div class="balance-sub-item">
          <div class="bsi-label">AI Credit Score</div>
          <div class="bsi-val up" id="creditScore">0 / 1000</div>
        </div>
      </div>
    </div>
    <div class="balance-actions">
      <div class="act-btn fill" onclick="window.location='send.php'">Deposit</div>
      <div class="act-btn" onclick="window.location='send.php'">Withdraw</div>
      <div class="act-btn" onclick="window.location='trade.php'">Exchange</div>
      <div class="act-btn" onclick="window.location='history.php'">History</div>
    </div>
    <canvas class="balance-chart" id="balChart"></canvas>
  </div>

  <div class="ops-grid" style="animation:fadeUp .6s .18s ease both">
    <section class="ops-panel risk-panel">
      <div class="panel-kicker">AI Risk Engine</div>
      <div class="risk-meter">
        <div>
          <div class="risk-score medium" id="riskLevel">MEDIUM</div>
          <div class="risk-copy" id="riskCopy">Market exposure is balanced, with room to tune limits and repayments.</div>
        </div>
        <div class="ring" id="exposureRing"><span id="exposurePct">38.2%</span></div>
      </div>
      <div class="insight-list" id="insightList">
        <div class="insight-item">
          <div>
            <div class="insight-title">Portfolio risk</div>
            <div class="insight-detail">38.2% of portfolio is in market-linked assets.</div>
          </div>
          <div class="insight-value">MEDIUM</div>
        </div>
        <div class="insight-item">
          <div>
            <div class="insight-title">Trust level</div>
            <div class="insight-detail">Profile is ready for higher transfer limits.</div>
          </div>
          <div class="insight-value">VERIFIED</div>
        </div>
      </div>
    </section>

    <section class="ops-panel">
      <div class="panel-kicker">Cashflow Command</div>
      <div class="cashflow-row">
        <div><span>Inflow</span><strong id="inflowAmt">$4,200.00</strong></div>
        <div><span>Outflow</span><strong id="outflowAmt">$1,840.75</strong></div>
        <div><span>Net</span><strong id="netAmt">$2,359.25</strong></div>
      </div>
      <div class="mini-bars" id="budgetBars">
        <div class="budget-row">
          <div class="row-head"><span>Transfers</span><span>$250.00 / $2,000.00</span></div>
          <div class="bar-track"><div class="bar-fill" style="width:12.5%"></div></div>
        </div>
        <div class="budget-row">
          <div class="row-head"><span>Loan Repayments</span><span>$420.00 / $600.00</span></div>
          <div class="bar-track"><div class="bar-fill" style="width:70%"></div></div>
        </div>
      </div>
    </section>

    <section class="ops-panel">
      <div class="panel-kicker">Wallet Matrix</div>
      <div class="wallet-list" id="walletList">
        <div class="wallet-row">
          <div class="row-head"><strong>USD</strong><span>$12,480.50</span></div>
          <div class="insight-detail">12,480.50 USD</div>
        </div>
        <div class="wallet-row">
          <div class="row-head"><strong>BTC</strong><span>$9,447.00</span></div>
          <div class="insight-detail">0.141 BTC</div>
        </div>
        <div class="wallet-row">
          <div class="row-head"><strong>ETH</strong><span>$9,100.00</span></div>
          <div class="insight-detail">2.6 ETH</div>
        </div>
      </div>
    </section>
  </div>

  <div class="ops-grid secondary" style="animation:fadeUp .6s .24s ease both">
    <section class="ops-panel">
      <div class="panel-kicker">Virtual Cards</div>
      <div class="card-stack" id="cardStack">
        <div class="card-mini active">
          <div class="card-mini-top"><span>Online Spending</span><span class="status-pill active">active</span></div>
          <div class="card-mini-number">**** 4821</div>
          <div class="card-mini-foot"><span>Visa</span><span>$640.50 / $2,500.00</span></div>
        </div>
      </div>
    </section>
    <section class="ops-panel">
      <div class="panel-kicker">Trusted Payees</div>
      <div class="payee-list" id="payeeList">
        <div class="payee-row">
          <div class="row-head"><strong>Jane Doe</strong><span>low</span></div>
          <div class="insight-detail">jane@example.com - limit $1,500.00</div>
        </div>
        <div class="payee-row">
          <div class="row-head"><strong>Vendor Payouts</strong><span>medium</span></div>
          <div class="insight-detail">vendor@example.com - limit $500.00</div>
        </div>
      </div>
    </section>
    <section class="ops-panel">
      <div class="panel-kicker">Loan Watch</div>
      <div class="loan-watch" id="loanWatch">
        <div class="loan-row">
          <div class="row-head"><strong>active</strong><span>$420.00 repaid</span></div>
          <div class="insight-detail">$1,200.00 principal - due 2026-06-01</div>
        </div>
        <div class="loan-row">
          <div class="row-head"><strong>pending</strong><span>$0.00 repaid</span></div>
          <div class="insight-detail">$500.00 principal - due not scheduled</div>
        </div>
      </div>
    </section>
  </div>

  <div class="ops-grid command" style="animation:fadeUp .6s .3s ease both">
    <section class="ops-panel">
      <div class="panel-kicker">Security Center</div>
      <div class="security-list" id="securityList">
        <div class="security-row">
          <div class="row-head"><strong>KYC status</strong><span>verified</span></div>
          <div class="insight-detail">Identity review controls account limits.</div>
        </div>
        <div class="security-row">
          <div class="row-head"><strong>Card utilization</strong><span>9.9%</span></div>
          <div class="insight-detail">Virtual card spend this month.</div>
        </div>
      </div>
    </section>
    <section class="ops-panel">
      <div class="panel-kicker">Treasury Forecast</div>
      <div class="forecast-box">
        <div class="forecast-value" id="forecastValue">$14,777.35</div>
        <div class="forecast-label">Projected 30-day balance after scheduled movement.</div>
      </div>
      <div class="mini-bars" id="forecastBars">
        <div class="budget-row">
          <div class="row-head"><span>Card utilization</span><span>9.9%</span></div>
          <div class="bar-track"><div class="bar-fill" style="width:9.9%"></div></div>
        </div>
        <div class="budget-row">
          <div class="row-head"><span>Budget utilization</span><span>32.8%</span></div>
          <div class="bar-track"><div class="bar-fill" style="width:32.8%"></div></div>
        </div>
      </div>
    </section>
    <section class="ops-panel">
      <div class="panel-kicker">Next Best Actions</div>
      <div class="action-list" id="actionList">
        <button class="action-row" type="button" onclick="window.location='trade.php'">
          <span>Review risk mix</span>
          <small>Market exposure is moderate and worth checking weekly.</small>
        </button>
        <button class="action-row" type="button" onclick="window.location='loans.php'">
          <span>Schedule repayment</span>
          <small>Active loan has a June payment window.</small>
        </button>
      </div>
    </section>
  </div>

  <div class="grid-4" style="animation:fadeUp .6s .34s ease both">
    <div class="stat-card" style="--accent:var(--green)">
      <div class="stat-lbl">AI Score</div>
      <div class="stat-num" id="kpiScore">812</div>
      <div class="stat-trend up">Credit profile strength</div>
    </div>
    <div class="stat-card">
      <div class="stat-lbl">30-Day Net</div>
      <div class="stat-num" id="kpiNet">$2.3<span>K</span></div>
      <div class="stat-trend up">Cashflow movement</div>
    </div>
    <div class="stat-card">
      <div class="stat-lbl">Budget Used</div>
      <div class="stat-num" id="kpiBudget">32<span>%</span></div>
      <div class="stat-trend up">Monthly utilization</div>
    </div>
    <div class="stat-card">
      <div class="stat-lbl">Alerts</div>
      <div class="stat-num" id="kpiAlerts">3</div>
      <div class="stat-trend up">Unread notifications</div>
    </div>
  </div>

  <div class="ops-grid advanced" style="animation:fadeUp .6s .38s ease both">
    <section class="ops-panel">
      <div class="panel-kicker">Fraud Monitor</div>
      <div class="fraud-score">
        <div>
          <span>Risk pulse</span>
          <strong id="fraudPulse">24</strong>
        </div>
        <div class="fraud-status" id="fraudStatus">NORMAL</div>
      </div>
      <div class="monitor-list" id="fraudList">
        <div class="monitor-row">
          <div class="row-head"><strong>Velocity check</strong><span>clear</span></div>
          <div class="insight-detail">Transaction pace is within normal account behavior.</div>
        </div>
        <div class="monitor-row">
          <div class="row-head"><strong>Market exposure</strong><span>watch</span></div>
          <div class="insight-detail">Moderate exposure requires weekly review.</div>
        </div>
      </div>
    </section>

    <section class="ops-panel">
      <div class="panel-kicker">Smart Limits</div>
      <div class="limit-grid" id="limitGrid">
        <div class="limit-card">
          <span>Daily transfer</span>
          <strong>$5,000</strong>
          <small>Based on verified profile</small>
        </div>
        <div class="limit-card">
          <span>Trade limit</span>
          <strong>$2,500</strong>
          <small>Adjusted by exposure risk</small>
        </div>
      </div>
    </section>

    <section class="ops-panel">
      <div class="panel-kicker">Compliance Timeline</div>
      <div class="timeline-list" id="complianceTimeline">
        <div class="timeline-row">
          <div class="timeline-dot"></div>
          <div>
            <strong>KYC verified</strong>
            <span>Profile limits are active.</span>
          </div>
        </div>
        <div class="timeline-row">
          <div class="timeline-dot"></div>
          <div>
            <strong>Monitoring enabled</strong>
            <span>Transfers and trades are scored automatically.</span>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div class="grid-2" style="animation:fadeUp .6s .4s ease both">
    <div class="fcard" style="--accent:var(--gold)">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--gold)">BTC</div>
        <div class="fcard-badge" style="--accent:var(--gold)">LIVE PRICES</div>
      </div>
      <div class="fcard-title">CRYPTO</div>
      <div class="fcard-desc">Buy, sell and hold market assets with instant fiat conversion.</div>
      <div class="crypto-list" id="cryptoList"></div>
    </div>

    <div class="fcard" style="--accent:var(--blue)">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--blue)">FX</div>
        <div class="fcard-badge" style="--accent:var(--blue)">GLOBAL</div>
      </div>
      <div class="fcard-title">SEND MONEY</div>
      <div class="fcard-desc">Send to trusted payees with limits, risk scoring and notifications.</div>
      <div class="send-contacts" id="sendContacts"></div>
      <form id="sendForm" class="send-input-row" onsubmit="event.preventDefault(); sendMoney();">
        <input class="send-input" type="number" id="sendAmount" placeholder="Amount (USD)" required min="1">
        <button type="submit" class="send-go">SEND</button>
      </form>
    </div>
  </div>

  <div class="fcard" style="--accent:var(--green);animation:fadeUp .6s .46s ease both">
    <div class="fcard-top">
      <div class="fcard-title">RECENT ACTIVITY</div>
      <div class="act-btn" style="padding:6px 12px;font-size:9px" onclick="window.location='history.php'">See All</div>
    </div>
    <div class="activity" id="activity"></div>
  </div>

</div>

<div class="bottom-nav">
  <div class="bn-item active"><div class="bn-icon">🏠</div>Home</div>
  <div class="bn-item" onclick="window.location='trade.php'"><div class="bn-icon">📈</div>Trade</div>
  <div class="bn-item" onclick="window.location='analytics.php'"><div class="bn-icon">📊</div>Analytics</div>
  <div class="bn-item" onclick="window.location='cards.php'"><div class="bn-icon">💳</div>Cards</div>
  <div class="bn-item" onclick="window.location='savings.php'"><div class="bn-icon">🎯</div>Savings</div>
  <div class="bn-item" onclick="window.location='account.php'"><div class="bn-icon">⚙️</div>Account</div>
</div>

<script src="assets/js/ticker.js"></script>
<script src="assets/js/charts.js"></script>
<script src="assets/js/dashboard.js"></script>
<script>
  document.getElementById('currentDate').innerText = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' });
</script>
</body>
</html>
