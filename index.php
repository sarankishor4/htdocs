<?php
// ─── CONFIGURE YOUR FOLDERS HERE ───────────────────────────────────────────
$folders = [
    [
        "label"       => "web",
        "path"        => "/luminarr",          // URL path to redirect to
        "icon"        => "⚙️",
        "description" => "Manage users & settings",
        "color"       => "#e74c3c",
    ],
    [
        "label"       => "Bank",
        "path"        => "global-neobank",
        "icon"        => "🎓",
        "description" => "Access courses & grades",
        "color"       => "#3498db",
    ],
    [
        "label"       => "crypto",
        "path"        => "cryptomind",
        "icon"        => "📋",
        "description" => "Manage classes & assignments",
        "color"       => "#2ecc71",
    ],
    [
        "label"       => "Hub",
        "path"        => "crypto-bank-hub",
        "icon"        => "📚",
        "description" => "Browse books & resources",
        "color"       => "#f39c12",
    ],
    [
        "label"       => "crevix",
        "path"        => "/crevix/",
        "icon"        => "📊",
        "description" => "View analytics & reports",
        "color"       => "#9b59b6",
    ],
    [
        "label"       => "ai",
        "path"        => "/ai/",
        "icon"        => "🛠️",
        "description" => "Help & troubleshooting",
        "color"       => "#1abc9c",
    ],
];
// ────────────────────────────────────────────────────────────────────────────

// Handle redirect if a folder is selected via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folder'])) {
    $selected = $_POST['folder'];
    // Validate against known paths (security check)
    $validPaths = array_column($folders, 'path');
    if (in_array($selected, $validPaths)) {
        header("Location: " . $selected);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal — Select Destination</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #0d0f14;
            --surface: #161920;
            --border: #252930;
            --text: #e8eaf0;
            --muted: #6b7280;
            --accent: #5b6af0;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            overflow-x: hidden;
        }

        /* Subtle grid background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(91, 106, 240, .04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(91, 106, 240, .04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 780px;
        }

        header {
            text-align: center;
            margin-bottom: 3rem;
        }

        header .badge {
            display: inline-block;
            font-family: 'Syne', sans-serif;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .15em;
            text-transform: uppercase;
            color: var(--accent);
            border: 1px solid rgba(91, 106, 240, .4);
            border-radius: 100px;
            padding: .3em 1em;
            margin-bottom: 1.2rem;
        }

        header h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -.02em;
            margin-bottom: .8rem;
        }

        header p {
            color: var(--muted);
            font-size: 1rem;
            font-weight: 300;
        }

        /* ── Grid ── */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        /* ── Card / label ── */
        .card-label {
            display: block;
            cursor: pointer;
            position: relative;
        }

        .card-label input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: 14px;
            padding: 1.4rem 1.2rem;
            transition: border-color .2s, transform .2s, box-shadow .2s;
            display: flex;
            flex-direction: column;
            gap: .5rem;
            user-select: none;
        }

        .card-label:hover .card {
            border-color: rgba(255, 255, 255, .15);
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, .35);
        }

        /* Selected state */
        .card-label input:checked+.card {
            border-color: var(--card-color, var(--accent));
            box-shadow: 0 0 0 1px var(--card-color, var(--accent)),
                0 8px 32px rgba(0, 0, 0, .4);
            transform: translateY(-3px);
        }

        .card-label input:checked+.card .check {
            opacity: 1;
            transform: scale(1);
        }

        .icon-wrap {
            font-size: 1.8rem;
            line-height: 1;
            margin-bottom: .25rem;
        }

        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 1rem;
            font-weight: 700;
        }

        .card-desc {
            font-size: .8rem;
            color: var(--muted);
            line-height: 1.4;
            flex: 1;
        }

        .card-path {
            font-size: .7rem;
            color: var(--muted);
            font-family: monospace;
            border-top: 1px solid var(--border);
            padding-top: .5rem;
            margin-top: .25rem;
        }

        .check {
            position: absolute;
            top: .8rem;
            right: .8rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--card-color, var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .65rem;
            color: #fff;
            opacity: 0;
            transform: scale(.6);
            transition: opacity .2s, transform .2s;
        }

        /* ── CTA ── */
        .cta-wrap {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }

        button[type="submit"] {
            font-family: 'Syne', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .03em;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .85rem 2.5rem;
            cursor: pointer;
            transition: background .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 20px rgba(91, 106, 240, .3);
        }

        button[type="submit"]:hover {
            background: #6e7cf5;
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(91, 106, 240, .45);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        /* ── Alert ── */
        .alert {
            background: rgba(231, 76, 60, .12);
            border: 1px solid rgba(231, 76, 60, .35);
            color: #e74c3c;
            border-radius: 8px;
            padding: .7rem 1rem;
            font-size: .85rem;
            text-align: center;
            margin-bottom: 1.2rem;
            display: none;
        }

        .alert.show {
            display: block;
        }
    </style>
</head>

<body>
    <div class="container">

        <header>
            <span class="badge">Navigation Portal</span>
            <h1>Where would you<br>like to go?</h1>
            <p>Select a section below and click <strong>Go</strong> to continue.</p>
        </header>

        <p class="alert" id="alert">⚠️ Please select a destination first.</p>

        <form method="POST" action="" id="portalForm">
            <div class="grid">
                <?php foreach ($folders as $i => $f): ?>
                    <label class="card-label" style="--card-color: <?= htmlspecialchars($f['color']) ?>">
                        <input type="radio" name="folder" value="<?= htmlspecialchars($f['path']) ?>" id="folder<?= $i ?>">
                        <div class="card">
                            <span class="check">✓</span>
                            <div class="icon-wrap"><?= $f['icon'] ?></div>
                            <div class="card-title"><?= htmlspecialchars($f['label']) ?></div>
                            <div class="card-desc"><?= htmlspecialchars($f['description']) ?></div>
                            <div class="card-path"><?= htmlspecialchars($f['path']) ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="cta-wrap">
                <button type="submit">Go →</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('portalForm').addEventListener('submit', function(e) {
            const selected = document.querySelector('input[name="folder"]:checked');
            if (!selected) {
                e.preventDefault();
                const alert = document.getElementById('alert');
                alert.classList.add('show');
                setTimeout(() => alert.classList.remove('show'), 3000);
            }
        });
    </script>
</body>

</html>