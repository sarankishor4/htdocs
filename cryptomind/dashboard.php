<?php
require_once __DIR__.'/includes/auth.php';
requireAuth(); // Guard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoMind AI — Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lightweight-charts/dist/lightweight-charts.standalone.production.js"></script>
</head>
<body class="dashboard-page">

<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>

<!-- Top Navigation -->
<nav class="top-nav">
    <div class="nav-inner">
        <div class="nav-brand">
            <button class="mobile-menu-btn" id="mobile-menu-btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <span class="nav-logo">CRYPTOMIND<span class="brand-dot">.</span>AI</span>
            <span class="nav-live-badge" id="live-badge">● LIVE</span>
        </div>
        <div class="nav-right">
            <span class="nav-balance" id="nav-balance">$0.00</span>
            <div class="nav-actions">
                <button class="nav-icon-btn" title="Notifications">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    <span class="nav-badge">3</span>
                </button>
            </div>
            <div class="nav-avatar" id="nav-avatar" title="Profile">
                <span id="nav-initials">--</span>
            </div>
        </div>
    </div>
</nav>

<!-- Sidebar Navigation -->
<div class="sidebar" id="sidebar">
    <button class="sidebar-btn active" data-page="dashboard" title="Dashboard">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        <span>Dashboard</span>
    </button>
    <button class="sidebar-btn" data-page="markets" title="Markets">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
        <span>Markets</span>
    </button>
    <button class="sidebar-btn" data-page="portfolio" title="Portfolio">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12V7H5a2 2 0 010-4h14v4"/><path d="M3 5v14a2 2 0 002 2h16v-5"/><circle cx="18" cy="16" r="2"/></svg>
        <span>Portfolio</span>
    </button>
    <button class="sidebar-btn" data-page="ai-analysis" title="AI Analysis">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
        <span>AI Analysis</span>
    </button>
    <button class="sidebar-btn" data-page="trades" title="Trade History">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
        <span>Trades</span>
    </button>
    <button class="sidebar-btn" data-page="roadmap" title="Roadmap">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V7.5L14.5 2z"/><polyline points="14,2 14,8 20,8"/></svg>
        <span>Roadmap</span>
    </button>
    <button class="sidebar-btn" data-page="bot" title="Bot Config">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="5" r="2"/><path d="M12 7v4"/><line x1="8" y1="16" x2="8" y2="16"/><line x1="16" y1="16" x2="16" y2="16"/></svg>
        <span>Bot Config</span>
    </button>
    <button class="sidebar-btn" data-page="leaderboard" title="Leaderboard">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 20h.01"/><path d="M7 20v-4"/><path d="M12 20v-8"/><path d="M17 20V8"/><path d="M22 4v16"/></svg>
        <span>Leaderboard</span>
    </button>
    <button class="sidebar-btn" data-page="profile" title="Profile">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Profile</span>
    </button>
    <div class="sidebar-spacer"></div>
    <button class="sidebar-btn sidebar-logout" id="logout-btn" title="Logout">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Logout</span>
    </button>
</div>

<!-- Main Content Area -->
<main class="main-area" id="main-area">

    <!-- PAGE: Dashboard -->
    <section id="page-dashboard" class="page active">
        <div class="page-header">
            <h2 class="page-title">Dashboard</h2>
            <span class="page-subtitle" id="dash-greeting">Good evening</span>
        </div>

        <!-- Stats Row -->
        <div class="stats-row" id="stats-row">
            <div class="stat-card">
                <div class="stat-label">Portfolio Value</div>
                <div class="stat-value" id="stat-portfolio">$0.00</div>
                <div class="stat-change" id="stat-portfolio-change">0%</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Available Balance</div>
                <div class="stat-value" id="stat-balance">$0.00</div>
                <div class="stat-sub">Trading power</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total P&L</div>
                <div class="stat-value" id="stat-pnl">$0.00</div>
                <div class="stat-change" id="stat-pnl-change">0%</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Win Rate</div>
                <div class="stat-value" id="stat-winrate">0%</div>
                <div class="stat-sub" id="stat-trades-count">0 trades</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-row">
            <div class="chart-card chart-card-wide">
                <div class="chart-header">
                    <span class="chart-title">Portfolio Allocation</span>
                </div>
                <div class="chart-body">
                    <canvas id="portfolio-chart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <span class="chart-title">P&L Breakdown</span>
                </div>
                <div class="chart-body">
                    <canvas id="pnl-chart"></canvas>
                </div>
            </div>
        </div>

        <div class="split-row">
            <!-- Watchlist -->
            <div class="section-card flex-1">
                <div class="section-head">
                    <span class="section-title">⭐ Watchlist</span>
                </div>
                <div class="watchlist-grid" id="watchlist-grid">
                    <div class="empty-state-small skeleton-loader" style="height:100px;"></div>
                </div>
            </div>

            <!-- Recent Trades -->
            <div class="section-card flex-1">
                <div class="section-head">
                    <span class="section-title">Recent Trades</span>
                </div>
                <div id="recent-trades-list" class="trades-list">
                    <div class="empty-state-small skeleton-loader" style="height:100px;"></div>
                </div>
            </div>
        </div>
        
        <!-- Market News -->
        <div class="section-card">
            <div class="section-head">
                <span class="section-title">📰 Market Intelligence</span>
            </div>
            <div id="market-news-list" class="news-list">
                <div class="empty-state-small skeleton-loader" style="height:80px;"></div>
                <div class="empty-state-small skeleton-loader" style="height:80px;"></div>
            </div>
        </div>
    </section>

    <!-- PAGE: Markets -->
    <section id="page-markets" class="page">
        <div class="page-header">
            <h2 class="page-title">Live Markets</h2>
            <span class="page-subtitle">Real-time prices from Binance</span>
        </div>
        <div class="markets-grid" id="markets-grid">
            <div class="market-card skeleton-loader" style="height:180px;"></div>
            <div class="market-card skeleton-loader" style="height:180px;"></div>
            <div class="market-card skeleton-loader" style="height:180px;"></div>
        </div>

        <!-- Trade Modal -->
        <div class="modal-overlay" id="trade-modal" style="display:none">
            <div class="modal-card">
                <div class="modal-header">
                    <h3 class="modal-title" id="trade-modal-title">Trade BTC</h3>
                    <button class="modal-close" id="trade-modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="trade-toggle">
                        <button class="trade-type-btn active" data-type="BUY" id="trade-buy-btn">BUY</button>
                        <button class="trade-type-btn" data-type="SELL" id="trade-sell-btn">SELL</button>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount (<span id="trade-symbol">BTC</span>)</label>
                        <input type="number" id="trade-amount" class="form-input" placeholder="0.00" step="any" min="0">
                    </div>
                    <div class="trade-summary">
                        <div class="trade-summary-row"><span>Price</span><span id="trade-price">$0.00</span></div>
                        <div class="trade-summary-row"><span>Total</span><span id="trade-total" class="mono-bold">$0.00</span></div>
                    </div>
                    <div id="trade-error" class="auth-error" style="display:none"></div>
                    <button class="btn btn-primary btn-full" id="execute-trade-btn">
                        <span class="btn-text">Execute Trade</span>
                        <span class="btn-loader" style="display:none"></span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- PAGE: Portfolio -->
    <section id="page-portfolio" class="page">
        <div class="page-header">
            <h2 class="page-title">Portfolio</h2>
            <span class="page-subtitle">Your holdings and performance</span>
        </div>
        <div class="portfolio-summary-bar" id="portfolio-summary-bar"></div>
        <div class="portfolio-holdings" id="portfolio-holdings">
            <div class="empty-state-small">Loading portfolio...</div>
        </div>
    </section>

    <!-- PAGE: AI Analysis -->
    <section id="page-ai-analysis" class="page">
        <div class="page-header">
            <h2 class="page-title">AI Analysis</h2>
            <span class="page-subtitle">Select an asset for AI-powered insights</span>
        </div>
        <div class="ai-coins-grid" id="ai-coins-grid"></div>
        <div class="analysis-panel" id="analysis-panel">
            <div class="empty-state">
                <div class="empty-icon">🤖</div>
                <div class="empty-text">Select a coin above to run AI analysis</div>
            </div>
        </div>
        
        <div class="chart-card" id="tv-chart-container" style="display:none; margin-top:20px;">
            <div class="chart-header"><span class="chart-title">Interactive Price Action</span></div>
            <div id="tv-chart" style="height: 400px; width: 100%;"></div>
        </div>

        <!-- Analysis History -->
        <div class="section-card" style="margin-top:20px">
            <div class="section-head">
                <span class="section-title">Analysis History</span>
            </div>
            <div id="analysis-history" class="analysis-history-list">
                <div class="empty-state-small">No analyses yet</div>
            </div>
        </div>
    </section>

    <!-- PAGE: Trades -->
    <section id="page-trades" class="page">
        <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                <h2 class="page-title">Trade History</h2>
                <span class="page-subtitle">Bot execution log</span>
            </div>
            <button id="export-csv-btn" class="btn btn-ghost btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export CSV
            </button>
        </div>
        <div class="trade-stats-row" id="trade-stats-row"></div>
        <div class="trades-table-wrap">
            <table class="data-table" id="trades-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Asset</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>P&L</th>
                        <th>Confidence</th>
                    </tr>
                </thead>
                <tbody id="trades-tbody"></tbody>
            </table>
        </div>
    </section>

    <!-- PAGE: Roadmap -->
    <section id="page-roadmap" class="page">
        <div class="page-header">
            <h2 class="page-title">Learning Roadmap</h2>
            <span class="page-subtitle">Your path from beginner to live trading bot</span>
        </div>
        <div class="roadmap-container" id="roadmap-container"></div>
    </section>

    <!-- PAGE: Bot Config -->
    <section id="page-bot" class="page">
        <div class="page-header">
            <h2 class="page-title">Automated Bot Control</h2>
            <span class="page-subtitle">Configure your personal AI trading agent</span>
        </div>
        <div class="section-card" style="max-width: 600px;">
            <div class="form-row">
                <div class="form-group" style="flex: 2;">
                    <label class="form-label">Master Switch</label>
                    <div class="toggle-wrap" style="display:flex; align-items:center;">
                        <label class="toggle-switch">
                            <input type="checkbox" id="bot-active-toggle">
                            <span class="slider"></span>
                        </label>
                        <span id="bot-status-text" style="font-family:var(--font-mono); font-size:14px; margin-left:12px;">Bot Offline</span>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Risk Tolerance</label>
                    <select id="bot-risk-select" class="form-input" style="appearance: none; background: rgba(0,0,0,0.2) url('data:image/svg+xml;utf8,<svg fill=\"white\" height=\"24\" viewBox=\"0 0 24 24\" width=\"24\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M7 10l5 5 5-5z\"/></svg>') no-repeat right 12px center;">
                        <option value="low">Low Risk (Conservative)</option>
                        <option value="medium">Medium Risk (Balanced)</option>
                        <option value="high">High Risk (Aggressive)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Trade Allocation (%)</label>
                    <input type="number" id="bot-alloc-input" class="form-input" min="1" max="100" value="10">
                </div>
            </div>
            <button class="btn btn-primary mt-2" id="save-bot-btn">Save Configuration</button>
        </div>
    </section>

    <!-- PAGE: Leaderboard -->
    <section id="page-leaderboard" class="page">
        <div class="page-header">
            <h2 class="page-title">Global Leaderboard</h2>
            <span class="page-subtitle">Top simulated traders by ROI</span>
        </div>
        <div class="trades-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Trader</th>
                        <th>Total Assets</th>
                        <th>ROI</th>
                        <th>Total P&L</th>
                        <th>Trades</th>
                    </tr>
                </thead>
                <tbody id="leaderboard-tbody">
                    <tr><td colspan="6" class="text-center">Loading leaderboard...</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- PAGE: Profile -->
    <section id="page-profile" class="page">
        <div class="page-header">
            <h2 class="page-title">Account Settings</h2>
            <span class="page-subtitle">Manage your profile and security</span>
        </div>

        <!-- Profile Card -->
        <div class="profile-hero" id="profile-hero">
            <div class="profile-avatar" id="profile-avatar"><span>CM</span></div>
            <div class="profile-info">
                <h3 id="profile-name">User</h3>
                <span class="profile-username" id="profile-username">@username</span>
                <span class="profile-joined" id="profile-joined">Member since ...</span>
            </div>
            <div class="profile-stats-row">
                <div class="profile-stat"><span class="ps-val" id="ps-trades">0</span><span class="ps-label">Trades</span></div>
                <div class="profile-stat"><span class="ps-val" id="ps-analyses">0</span><span class="ps-label">Analyses</span></div>
                <div class="profile-stat"><span class="ps-val" id="ps-pnl">$0</span><span class="ps-label">Total P&L</span></div>
                <div class="profile-stat"><span class="ps-val" id="ps-winrate">0%</span><span class="ps-label">Win Rate</span></div>
                <div class="profile-stat"><span class="ps-val" id="ps-days">0</span><span class="ps-label">Days Active</span></div>
            </div>
        </div>

        <div class="split-row">
            <!-- Edit Profile -->
            <div class="settings-section flex-1">
                <h4 class="settings-title">Edit Profile</h4>
                <div id="profile-msg" class="auth-success" style="display:none"></div>
                <div id="profile-err" class="auth-error" style="display:none"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Full Name</label><input type="text" id="edit-name" class="form-input"></div>
                    <div class="form-group"><label class="form-label">Email</label><input type="email" id="edit-email" class="form-input"></div>
                </div>
                <div class="form-group"><label class="form-label">Bio</label><textarea id="edit-bio" class="form-input form-textarea" rows="3"></textarea></div>
                <button class="btn btn-primary" id="save-profile-btn">Save Changes</button>
            </div>

            <!-- Change Password -->
            <div class="settings-section flex-1">
                <h4 class="settings-title">Change Password</h4>
                <div id="pw-msg" class="auth-success" style="display:none"></div>
                <div id="pw-err" class="auth-error" style="display:none"></div>
                <div class="form-group"><label class="form-label">Current Password</label><input type="password" id="pw-current" class="form-input"></div>
                <div class="form-group"><label class="form-label">New Password</label><input type="password" id="pw-new" class="form-input"></div>
                <button class="btn btn-primary" id="change-pw-btn">Change Password</button>
            </div>
        </div>
    </section>

</main>

<!-- Toast -->
<div class="toast-container" id="toast-container"></div>

<script src="js/app.js"></script>
</body>
</html>
