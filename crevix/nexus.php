<?php
include 'db.php';
session_start();

// Simplified auth check for demo
if (!isset($_SESSION['user_id'])) {
    // header('Location: login.php');
    // exit();
}

$page_title = "Crevix Nexus | AI Workforce";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/nexus.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="nexus-body">

    <div class="nexus-container">
        <!-- Sidebar -->
        <aside class="nexus-sidebar">
            <div class="nexus-logo">
                <i class="fas fa-microchip"></i>
                <span>Nexus</span>
            </div>

            <nav class="agent-nav">
                <div class="agent-item active">
                    <i class="fas fa-th-large"></i>
                    <span>Command Center</span>
                </div>
                <div class="agent-item">
                    <i class="fas fa-crown"></i>
                    <span>CEO Agent</span>
                    <div class="agent-status status-online"></div>
                </div>
                <div class="agent-item">
                    <i class="fas fa-code"></i>
                    <span>Developer</span>
                    <div class="agent-status status-busy"></div>
                </div>
                <div class="agent-item">
                    <i class="fas fa-bullhorn"></i>
                    <span>Marketing</span>
                    <div class="agent-status status-online"></div>
                </div>
                <div class="agent-item">
                    <i class="fas fa-users-cog"></i>
                    <span>HR Operations</span>
                    <div class="agent-status status-offline"></div>
                </div>
                <div class="agent-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Sales Dept</span>
                    <div class="agent-status status-online"></div>
                </div>
                <div class="agent-item">
                    <i class="fas fa-wallet"></i>
                    <span>Finance</span>
                    <div class="agent-status status-online"></div>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="agent-item">
                    <i class="fas fa-cog"></i>
                    <span>Nexus Settings</span>
                </div>
                <div class="agent-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Exit Nexus</span>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="nexus-main">
            <header class="nexus-header">
                <div class="user-welcome">
                    <h1>Crevix <span style="color:var(--nexus-primary)">Nexus</span></h1>
                    <p>Welcome back, Administrator. Your AI workforce is active.</p>
                </div>
                <div class="nexus-actions">
                    <button class="btn-secondary" style="border-radius: 12px; padding: 12px 20px;">
                        <i class="fas fa-sync"></i> Refresh Matrix
                    </button>
                </div>
            </header>

            <div class="agent-grid">
                <!-- CEO Agent -->
                <div class="agent-card">
                    <div class="agent-card-icon" style="color: #ffd700;"><i class="fas fa-crown"></i></div>
                    <h3>CEO Oracle</h3>
                    <p>Strategic orchestration and high-level decision support. Summarizes cross-departmental data.</p>
                    <div class="agent-card-stats">
                        <div class="stat-item">
                            <span class="stat-val">Active</span>
                            <span class="stat-label">Status</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-val">98%</span>
                            <span class="stat-label">Efficiency</span>
                        </div>
                    </div>
                    <button class="btn-summon">Summon CEO</button>
                </div>

                <!-- Dev Agent -->
                <div class="agent-card">
                    <div class="agent-card-icon" style="color: #00d2ff;"><i class="fas fa-terminal"></i></div>
                    <h3>Dev Matrix</h3>
                    <p>Full-stack automation. Handles PHP, JS, and Database architecture. Auto-fixes bugs and deploys code.</p>
                    <div class="agent-card-stats">
                        <div class="stat-item">
                            <span class="stat-val">Compiling</span>
                            <span class="stat-label">Task</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-val">12</span>
                            <span class="stat-label">Commits</span>
                        </div>
                    </div>
                    <button class="btn-summon">Summon Developer</button>
                </div>

                <!-- Marketing Agent -->
                <div class="agent-card">
                    <div class="agent-card-icon" style="color: #ff00ff;"><i class="fas fa-rocket"></i></div>
                    <h3>Growth Engine</h3>
                    <p>Social media management and marketing automation. SEO analysis and campaign generation.</p>
                    <div class="agent-card-stats">
                        <div class="stat-item">
                            <span class="stat-val">Posting</span>
                            <span class="stat-label">Activity</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-val">+12%</span>
                            <span class="stat-label">Growth</span>
                        </div>
                    </div>
                    <button class="btn-summon">Summon Marketer</button>
                </div>

                <!-- Sales Agent -->
                <div class="agent-card">
                    <div class="agent-card-icon" style="color: #00ff88;"><i class="fas fa-funnel-dollar"></i></div>
                    <h3>Revenue Flow</h3>
                    <p>Sales optimization and lead generation. CRM management and conversion rate monitoring.</p>
                    <div class="agent-card-stats">
                        <div class="stat-item">
                            <span class="stat-val">$14.2k</span>
                            <span class="stat-label">Revenue</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-val">84</span>
                            <span class="stat-label">Leads</span>
                        </div>
                    </div>
                    <button class="btn-summon">Summon Sales</button>
                </div>

                <!-- HR Agent -->
                <div class="agent-card">
                    <div class="agent-card-icon" style="color: #ffa500;"><i class="fas fa-heart"></i></div>
                    <h3>Ops Pulse</h3>
                    <p>Human Resources and internal operations. Talent acquisition and employee engagement tracking.</p>
                    <div class="agent-card-stats">
                        <div class="stat-item">
                            <span class="stat-val">Optimal</span>
                            <span class="stat-label">Culture</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-val">4</span>
                            <span class="stat-label">Tickets</span>
                        </div>
                    </div>
                    <button class="btn-summon">Summon HR</button>
                </div>

                <!-- Finance Agent -->
                <div class="agent-card">
                    <div class="agent-card-icon" style="color: #4facfe;"><i class="fas fa-vault"></i></div>
                    <h3>Ledger AI</h3>
                    <p>Financial accounting and fiscal forecasting. Budget allocation and expense auditing.</p>
                    <div class="agent-card-stats">
                        <div class="stat-item">
                            <span class="stat-val">Audit</span>
                            <span class="stat-label">Mode</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-val">$2k</span>
                            <span class="stat-label">Savings</span>
                        </div>
                    </div>
                    <button class="btn-summon">Summon Finance</button>
                </div>
            </div>

            <!-- Global Command Bar -->
            <div class="command-bar-wrap">
                <div class="command-bar">
                    <i class="fas fa-bolt"></i>
                    <input type="text" placeholder="Type a command for the CEO or delegate to a department...">
                    <div class="k-hint" style="color: #444; font-size: 0.8rem; font-weight: 700;">
                        PRESS <span style="background: #333; padding: 2px 6px; border-radius: 4px;">ENTER</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Future: Add real-time interaction logic here
        document.querySelectorAll('.agent-item').forEach(item => {
            item.addEventListener('click', () => {
                document.querySelectorAll('.agent-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');
            });
        });
    </script>
</body>
</html>
