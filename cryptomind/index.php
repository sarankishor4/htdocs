<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoMind AI — Intelligent Trading</title>
    <meta name="description" content="Next generation AI-powered cryptocurrency trading simulation platform.">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="landing-page">

    <div class="auth-bg">
        <div class="bg-orb orb-1"></div>
        <div class="bg-orb orb-2"></div>
        <div class="bg-orb orb-3"></div>
        <div class="grid-overlay"></div>
    </div>

    <nav class="top-nav landing-nav">
        <div class="nav-inner" style="max-width: 1400px; margin: 0 auto; width: 100%;">
            <div class="nav-brand">
                <span class="nav-logo">CRYPTOMIND<span class="brand-dot">.</span>AI</span>
            </div>
            <div class="nav-links desktop-only">
                <a href="#features">Features</a>
                <a href="#how-it-works">How it Works</a>
                <a href="#performance">Performance</a>
            </div>
            <div class="nav-right">
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php" class="btn btn-primary btn-sm">Go to Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-ghost btn-sm">Sign In</a>
                    <a href="login.php" class="btn btn-primary btn-sm">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Live Market Ticker -->
    <div class="ticker-wrap">
        <div class="ticker-move" id="hero-ticker">
            <div class="ticker-item"><span class="ti-sym">BTC</span><span class="ti-price">$64,230.50</span><span class="ti-change positive">+2.4%</span></div>
            <div class="ticker-item"><span class="ti-sym">ETH</span><span class="ti-price">$3,120.80</span><span class="ti-change negative">-1.2%</span></div>
            <div class="ticker-item"><span class="ti-sym">SOL</span><span class="ti-price">$148.90</span><span class="ti-change positive">+5.7%</span></div>
            <div class="ticker-item"><span class="ti-sym">BNB</span><span class="ti-price">$580.20</span><span class="ti-change positive">+0.8%</span></div>
            <div class="ticker-item"><span class="ti-sym">ADA</span><span class="ti-price">$0.45</span><span class="ti-change negative">-0.4%</span></div>
            <div class="ticker-item"><span class="ti-sym">XRP</span><span class="ti-price">$0.62</span><span class="ti-change positive">+1.1%</span></div>
            <!-- Duplicate for infinite scroll effect -->
            <div class="ticker-item"><span class="ti-sym">BTC</span><span class="ti-price">$64,230.50</span><span class="ti-change positive">+2.4%</span></div>
            <div class="ticker-item"><span class="ti-sym">ETH</span><span class="ti-price">$3,120.80</span><span class="ti-change negative">-1.2%</span></div>
            <div class="ticker-item"><span class="ti-sym">SOL</span><span class="ti-price">$148.90</span><span class="ti-change positive">+5.7%</span></div>
            <div class="ticker-item"><span class="ti-sym">BNB</span><span class="ti-price">$580.20</span><span class="ti-change positive">+0.8%</span></div>
            <div class="ticker-item"><span class="ti-sym">ADA</span><span class="ti-price">$0.45</span><span class="ti-change negative">-0.4%</span></div>
            <div class="ticker-item"><span class="ti-sym">XRP</span><span class="ti-price">$0.62</span><span class="ti-change positive">+1.1%</span></div>
        </div>
    </div>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="hero-content reveal">
            <div class="badge">CryptoMind Engine v2.0 Live</div>
            <h1 class="hero-title">Master the Crypto Markets with <span class="accent">AI Intelligence</span></h1>
            <p class="hero-desc">Simulate trades, analyze real-time market data, and build your automated trading bot strategy without risking real capital. The ultimate sandbox for modern traders.</p>
            <div class="hero-actions">
                <?php if ($isLoggedIn): ?>
                    <a href="dashboard.php" class="btn btn-primary btn-lg">Access Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-lg">Start Trading Free &rarr;</a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Interactive UI Preview -->
        <div class="hero-preview reveal delay-1">
            <div class="mock-browser">
                <div class="mock-header">
                    <div class="mock-dots"><span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span></div>
                    <div class="mock-url">cryptomind.ai/dashboard</div>
                </div>
                <div class="mock-body">
                    <div class="mock-sidebar">
                        <div class="ms-item active"></div><div class="ms-item"></div><div class="ms-item"></div><div class="ms-item"></div>
                    </div>
                    <div class="mock-main">
                        <div class="mock-stats">
                            <div class="ms-card"><div class="ms-label">Portfolio</div><div class="ms-val">$14,250.00</div><div class="ms-change positive">+42.5%</div></div>
                            <div class="ms-card"><div class="ms-label">AI Confidence</div><div class="ms-val">88%</div><div class="ms-change positive">Bullish</div></div>
                            <div class="ms-card"><div class="ms-label">Active Trades</div><div class="ms-val">12</div></div>
                        </div>
                        <div class="mock-chart">
                            <div class="mc-line"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Stats Banner -->
    <section class="stats-banner reveal" id="performance">
        <div class="stat-block">
            <div class="sb-num">$10K</div>
            <div class="sb-label">Starting Virtual Capital</div>
        </div>
        <div class="stat-block">
            <div class="sb-num">6+</div>
            <div class="sb-label">Major Assets Tracked</div>
        </div>
        <div class="stat-block">
            <div class="sb-num">~200ms</div>
            <div class="sb-label">Market Sync Latency</div>
        </div>
        <div class="stat-block">
            <div class="sb-num">24/7</div>
            <div class="sb-label">AI Analysis Available</div>
        </div>
    </section>

    <!-- Features -->
    <section class="container" id="features">
        <div class="section-title-wrap reveal">
            <h2>Institutional-Grade Tools, <br>Built for Everyone.</h2>
            <p>Everything you need to analyze, learn, and execute.</p>
        </div>
        
        <div class="hero-features reveal delay-1">
            <div class="feature-card glass-card">
                <div class="feature-icon" style="color: #00ff88;">🧠</div>
                <h3>Predictive AI</h3>
                <p>Get instant technical analysis, RSI, MACD indicators, and confidence scoring powered by our custom Python AI engine.</p>
            </div>
            <div class="feature-card glass-card">
                <div class="feature-icon" style="color: #627EEA;">⚡</div>
                <h3>Live Webhooks</h3>
                <p>Real-time price tracking from the Binance API. See the market move tick-by-tick with Interactive TradingView charts.</p>
            </div>
            <div class="feature-card glass-card">
                <div class="feature-icon" style="color: #F7931A;">💼</div>
                <h3>Zero-Risk Sandbox</h3>
                <p>Practice trading with a $10,000 virtual portfolio. Test your theories and track your Win Rate without risking a dime.</p>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="container step-section" id="how-it-works">
        <div class="section-title-wrap reveal">
            <h2>From Novice to Quant in 3 Steps</h2>
        </div>
        
        <div class="step-grid">
            <div class="step-card reveal">
                <div class="step-num">01</div>
                <h3>Analyze the Market</h3>
                <p>Select an asset and let our AI engine scan the charts. Review momentum scores, volatility, and key support/resistance levels.</p>
            </div>
            <div class="step-card reveal delay-1">
                <div class="step-num">02</div>
                <h3>Execute the Trade</h3>
                <p>Use your virtual $10,000 balance to buy or sell. The system immediately calculates your entry price and updates your portfolio.</p>
            </div>
            <div class="step-card reveal delay-2">
                <div class="step-num">03</div>
                <h3>Track & Optimize</h3>
                <p>Watch your P&L grow in real-time. Export your bot's trade logs to CSV to analyze your performance and refine your strategy.</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-banner reveal">
        <div class="cta-content">
            <h2>Ready to dominate the market?</h2>
            <p>Join thousands of traders using CryptoMind to build their edge.</p>
            <a href="login.php" class="btn btn-primary btn-lg mt-4">Create Free Account</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container footer-inner">
            <div class="footer-brand">
                <span class="nav-logo">CRYPTOMIND<span class="brand-dot">.</span>AI</span>
                <p>The ultimate AI-powered cryptocurrency trading sandbox. Learn to trade securely.</p>
            </div>
            <div class="footer-links">
                <div class="fl-col">
                    <h4>Platform</h4>
                    <a href="login.php">Dashboard</a>
                    <a href="#features">Features</a>
                    <a href="#performance">Live Data</a>
                </div>
                <div class="fl-col">
                    <h4>Resources</h4>
                    <a href="#">Trading Guides</a>
                    <a href="#">API Documentation</a>
                    <a href="#">System Status</a>
                </div>
                <div class="fl-col">
                    <h4>Legal</h4>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Risk Disclaimer</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom container">
            <p>&copy; <?php echo date('Y'); ?> CryptoMind AI. All rights reserved. <span>Not financial advice. Demo platform only.</span></p>
        </div>
    </footer>

    <script>
        // Simple reveal animation observer
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
    </script>
</body>
</html>
