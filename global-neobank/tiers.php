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
<title>Subscription Tiers — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
<style>
.tier-card { padding:32px 24px; background:var(--card); border:1px solid var(--border); border-radius:12px; display:flex; flex-direction:column; position:relative; overflow:hidden; transition:transform 0.3s; }
.tier-card:hover { transform:translateY(-5px); }
.tier-card.metal { background:linear-gradient(135deg, #1a1a1a, #000); border-color:#333; }
.tier-card.metal::before { content:''; position:absolute; top:0; left:0; width:100%; height:100%; background:linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.05) 50%, transparent 100%); pointer-events:none; }
.tier-title { font-family:var(--display); font-size:28px; letter-spacing:2px; margin-bottom:8px; }
.tier-price { font-size:32px; font-weight:700; color:var(--text); margin-bottom:24px; }
.tier-price span { font-size:14px; color:var(--muted); font-weight:400; }
.tier-feature { display:flex; align-items:center; gap:12px; margin-bottom:16px; font-size:13px; color:var(--text); }
.tier-feature i { color:var(--green); font-style:normal; }
.tier-btn { margin-top:auto; padding:16px; text-align:center; background:var(--surface); border:1px solid var(--border); color:var(--text); font-weight:600; letter-spacing:2px; text-transform:uppercase; cursor:pointer; border-radius:4px; transition:all 0.3s; }
.tier-btn:hover { background:var(--gold); color:#000; border-color:var(--gold); }
.tier-btn.active { background:var(--green); color:#000; border-color:var(--green); pointer-events:none; }
.metal-badge { position:absolute; top:24px; right:24px; background:linear-gradient(135deg, #d4af37, #f3e5ab); color:#000; padding:4px 8px; font-size:10px; font-weight:700; border-radius:4px; letter-spacing:1px; }
</style>
</head>
<body>
<nav>
  <div class="logo">GLOBAL<em>BANK</em></div>
  <div class="nav-links">
    <div class="nav-link" onclick="window.location='home.php'">Home</div>
    <div class="nav-link" onclick="window.location='cards.php'">Cards</div>
    <div class="nav-link" onclick="window.location='developers.php'">Developers</div>
    <div class="nav-link active" onclick="window.location='tiers.php'">Upgrade</div>
  </div>
  <div class="nav-right">
    <div class="user-pill" onclick="window.location='account.php'">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>

<div class="wrap">
  <div class="welcome" style="margin-bottom:48px;">
    <div class="welcome-left">
      <h1>Platform <span>Tiers</span></h1>
      <p>Upgrade to unlock higher limits, exclusive metal cards, and priority API access.</p>
    </div>
  </div>

  <div class="grid-4" id="tiersGrid" style="grid-template-columns:repeat(3, 1fr);">
    <!-- Standard -->
    <div class="tier-card" id="tier-standard">
      <div class="tier-title" style="color:var(--blue)">STANDARD</div>
      <div class="tier-price">$0 <span>/ month</span></div>
      <div class="tier-feature"><i>✓</i> Standard withdrawal limits</div>
      <div class="tier-feature"><i>✓</i> Up to 2 virtual cards</div>
      <div class="tier-feature"><i>✓</i> Standard support</div>
      <div class="tier-btn" onclick="upgradeTier('standard')">Current Plan</div>
    </div>

    <!-- Premium -->
    <div class="tier-card" id="tier-premium" style="border-color:var(--blue)">
      <div class="tier-title" style="color:var(--gold)">PREMIUM</div>
      <div class="tier-price">$9.99 <span>/ month</span></div>
      <div class="tier-feature"><i>✓</i> 2x Withdrawal limits</div>
      <div class="tier-feature"><i>✓</i> Unlimited virtual cards</div>
      <div class="tier-feature"><i>✓</i> Lower trading fees</div>
      <div class="tier-feature"><i>✓</i> Priority 24/7 support</div>
      <div class="tier-btn" onclick="upgradeTier('premium')">Upgrade</div>
    </div>

    <!-- Metal -->
    <div class="tier-card metal" id="tier-metal">
      <div class="metal-badge">EXCLUSIVE</div>
      <div class="tier-title" style="color:#f3e5ab">METAL</div>
      <div class="tier-price">$24.99 <span>/ month</span></div>
      <div class="tier-feature"><i>✓</i> 10x Withdrawal limits</div>
      <div class="tier-feature"><i>✓</i> 18g Solid Gold/Steel Physical Card</div>
      <div class="tier-feature"><i>✓</i> Zero trading fees</div>
      <div class="tier-feature"><i>✓</i> Developer API Access</div>
      <div class="tier-feature"><i>✓</i> Dedicated Account Manager</div>
      <div class="tier-btn" onclick="upgradeTier('metal')" style="background:linear-gradient(135deg, #d4af37, #f3e5ab); color:#000; border:none;">Upgrade</div>
    </div>
  </div>
</div>

<script>
async function loadTier() {
    try {
        const res = await fetch('api/developer.php?action=get_tier');
        const data = await res.json();
        if(data.success) {
            const current = data.tier;
            document.querySelectorAll('.tier-btn').forEach(b => {
                b.innerText = 'Upgrade';
                b.classList.remove('active');
            });
            const activeBtn = document.querySelector(`#tier-${current} .tier-btn`);
            if(activeBtn) {
                activeBtn.innerText = 'Current Plan';
                activeBtn.classList.add('active');
            }
        }
    } catch(e) {}
}

async function upgradeTier(tier) {
    if(!confirm(`Are you sure you want to upgrade to the ${tier.toUpperCase()} plan?`)) return;
    const fd = new FormData(); fd.append('tier', tier);
    const res = await fetch('api/developer.php?action=set_tier', {method:'POST', body:fd});
    const data = await res.json();
    if(data.success) {
        alert(`Successfully upgraded to ${tier.toUpperCase()}!`);
        loadTier();
    } else alert(data.error);
}

document.addEventListener('DOMContentLoaded', loadTier);
</script>
</body>
</html>
