<?php
require_once 'core/Config.php';
require_once 'core/Database.php';
require_once 'core/Auth.php';

use AI\Core\Config;
use AI\Core\Database;
use AI\Core\Auth;

Auth::init();
if (!Auth::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = Auth::user();
$page_title = Config::APP_NAME . " | Business OS";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <style>
        .phaze-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px;
            opacity: 0;
            transform: translateY(20px);
        }

        .phaze-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 50px;
        }

        .phaze-logo {
            width: 38px;
            height: 38px;
            background: var(--accent-ceo);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: white;
            box-shadow: 0 0 15px rgba(255, 71, 87, 0.3);
        }

        .phaze-brand {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: -0.5px;
        }

        .phaze-brand span {
            font-weight: 400;
            color: var(--text-dim);
            font-size: 0.85rem;
            margin-left: 6px;
        }

        /* CEO AGENT SECTION */
        .ceo-hero {
            background: rgba(255, 71, 87, 0.05);
            border: 1px solid rgba(255, 71, 87, 0.15);
            border-radius: 24px;
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .ceo-hero:hover {
            background: rgba(255, 71, 87, 0.08);
            border-color: rgba(255, 71, 87, 0.3);
            transform: scale(1.01);
        }

        .ceo-avatar {
            width: 70px;
            height: 70px;
            background: var(--accent-ceo);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            box-shadow: 0 0 25px rgba(255, 71, 87, 0.3);
        }

        .ceo-content h2 {
            margin: 0 0 8px 0;
            font-family: 'Montserrat', sans-serif;
            font-size: 1.3rem;
            color: white;
        }

        .ceo-content p {
            margin: 0;
            color: var(--text-dim);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* GRID */
        .section-label {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 0.7rem;
            letter-spacing: 1.5px;
            color: var(--text-dim);
            text-transform: uppercase;
            margin-bottom: 25px;
            display: block;
        }

        .dept-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .dept-card {
            background: #151518;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 14px;
            padding: 18px;
            transition: all 0.2s ease;
            position: relative;
        }

        .dept-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
            background: #1c1c20;
        }

        .dept-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .dept-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            box-shadow: inset 0 0 10px rgba(255,255,255,0.05);
        }

        .dept-status {
            font-size: 0.6rem;
            font-weight: 800;
            color: var(--text-dim);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-dot { width: 5px; height: 5px; border-radius: 50%; background: #444; }
        .status-active .status-dot { background: var(--accent-sales); box-shadow: 0 0 8px var(--accent-sales); }

        .dept-info h3 { font-size: 0.9rem; margin-bottom: 4px; color: #fff; }
        .dept-info p { font-size: 0.75rem; color: var(--text-dim); margin-bottom: 15px; line-height: 1.4; }

        .dept-actions {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            flex: 1;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #fff;
            padding: 8px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-action:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .btn-chat {
            width: 60px;
            font-size: 0.7rem;
        }

        /* WORKSPACE OVERLAY */
        #workspace-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: var(--bg-main);
            z-index: 1000;
            display: none;
            overflow-y: auto;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="aurora-bg">
        <div class="aurora-orb" style="top: -10%; left: -10%; background: var(--accent-ceo);"></div>
        <div class="aurora-orb" style="bottom: -10%; right: -10%; background: var(--accent-primary);"></div>
    </div>

    <div class="phaze-container" id="main-content">
        <!-- Header -->
        <header class="phaze-header">
            <div class="phaze-logo"><i class="fas fa-bolt"></i></div>
            <div class="phaze-brand">Phaze AI <span>Business OS</span></div>
            <div style="margin-left: auto; display: flex; align-items: center; gap: 20px;">
                <span style="font-size: 0.75rem; color: var(--text-dim); font-weight: 600;"><?php echo strtoupper($user['username']); ?></span>
                <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer;" onclick="window.location.href='profile.php'">
                    <i class="fas fa-user" style="font-size: 0.8rem; color: var(--text-dim);"></i>
                </div>
            </div>
        </header>

        <!-- CEO Hero -->
        <div class="ceo-hero glass-card" onclick="openAgent('ceo')">
            <div class="ceo-avatar"><i class="fas fa-brain"></i></div>
            <div class="ceo-content">
                <h2>CEO Agent</h2>
                <p>Analyzes all business data · Proposes tasks · You approve everything</p>
                <div style="margin-top: 15px; font-size: 0.8rem; color: var(--text-dim); opacity: 0.7;">
                    Click "Run Morning Analysis" to scan nodes, or chat with the matrix...
                </div>
            </div>
        </div>

        <!-- Task Queue -->
        <span class="section-label">Task Queue</span>
        <div class="glass-card" style="padding: 40px; text-align: center; color: var(--text-dim); font-size: 0.85rem; margin-bottom: 50px;">
            <i class="fas fa-inbox" style="font-size: 1.5rem; margin-bottom: 15px; opacity: 0.3;"></i>
            <p>No active tasks in the queue. Run departmental analysis to generate directives.</p>
        </div>

        <!-- Dept Agents Dynamic Grid -->
        <span class="section-label">Department Agents</span>
        <div class="dept-grid">
            <?php foreach (Config::DEPARTMENTS as $id => $data): ?>
            <?php if ($id === 'ceo') continue; // CEO is already shown in the Hero section ?>
            <div class="dept-card" onclick="openAgent('<?php echo $id; ?>')" style="border-left: 3px solid <?php echo $data['color']; ?>;">
                <div class="dept-header">
                    <div class="dept-icon" style="background: <?php echo $data['color']; ?>22; color: <?php echo $data['color']; ?>;"><i class="fas fa-<?php echo $data['icon']; ?>"></i></div>
                    <div class="dept-status"><div class="status-dot"></div> IDLE</div>
                </div>
                <div class="dept-info">
                    <h3><?php echo $data['name']; ?></h3>
                    <p>Advanced AI operative specializing in <?php echo $id; ?> operations.</p>
                </div>
                <div class="dept-actions">
                    <div class="btn-action">Open →</div>
                    <div class="btn-action btn-chat" onclick="event.stopPropagation(); openAgent('<?php echo $id; ?>', true)">Chat</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Workspace Overlay -->
    <div id="workspace-overlay">
        <div id="workspace-content"></div>
    </div>

    <script>
        window.onload = () => {
            anime({
                targets: '.phaze-container',
                opacity: [0, 1],
                translateY: [20, 0],
                duration: 800,
                easing: 'easeOutExpo'
            });
        };

        async function openAgent(id, focusChat = false) {
            const overlay = document.getElementById('workspace-overlay');
            const content = document.getElementById('workspace-content');
            
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';

            try {
                const response = await fetch(`agents/${id}/Dashboard.php`);
                const html = await response.text();
                content.innerHTML = html;
                
                if (focusChat) {
                    setTimeout(() => {
                        const input = content.querySelector('.chat-input');
                        if (input) input.focus();
                    }, 400);
                }
                
                anime({
                    targets: content,
                    opacity: [0, 1],
                    translateY: [30, 0],
                    duration: 600,
                    easing: 'easeOutExpo'
                });
            } catch (err) {
                console.error("Failed to load agent matrix:", err);
                overlay.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        function closeWorkspace() {
            const overlay = document.getElementById('workspace-overlay');
            anime({
                targets: '#workspace-content',
                opacity: [1, 0],
                translateY: [0, 20],
                duration: 300,
                easing: 'easeInQuad',
                complete: () => {
                    overlay.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }
    </script>
</body>
</html>
