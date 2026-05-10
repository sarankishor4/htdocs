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
<title>My Profile — GlobalBank</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Epilogue:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<!-- NAV -->
<nav>
  <div class="logo">GLOBAL<em>BANK</em></div>
  <div class="nav-links">
    <div class="nav-link" onclick="window.location='home.php'">Home</div>
    <div class="nav-link" onclick="window.location='trade.php'">Trade</div>
    <div class="nav-link" onclick="window.location='loans.php'">Loans</div>
    <div class="nav-link">Send <span class="dot"></span></div>
    <div class="nav-link" onclick="window.location='earn.php'">Earn</div>
    <div class="nav-link" onclick="window.location='jobs.php'">Jobs</div>
  </div>
  <div class="nav-right">
    <div class="notif-btn">🔔<div class="notif-badge">3</div></div>
    <div class="user-pill active" onclick="window.location='profile.php'">
      <div class="user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
    </div>
  </div>
</nav>

<div class="wrap">
  <div class="welcome">
    <div class="welcome-left">
      <h1>My <span>Profile</span></h1>
      <p>Manage your personal details and settings.</p>
    </div>
    <div class="welcome-right">
      <div class="quick-btn primary" onclick="logout()" style="border-color:var(--red); color:var(--red); background:transparent;">Logout</div>
    </div>
  </div>

  <div class="grid-2">
    <!-- PROFILE DETAILS -->
    <div class="fcard" style="--accent:var(--blue)">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--blue)">👤</div>
        <div class="fcard-title">PERSONAL INFO</div>
      </div>
      
      <div id="msg" style="display:none; padding:10px; border-radius:4px; font-size:12px; margin-bottom:16px;"></div>

      <form id="profileForm" onsubmit="event.preventDefault(); updateProfile();">
        <div style="display:flex; gap:16px; margin-bottom:12px;">
            <div style="flex:1;">
                <label style="font-size:10px; color:var(--muted);">First Name</label>
                <input type="text" id="first_name" required style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); margin-top:4px;">
            </div>
            <div style="flex:1;">
                <label style="font-size:10px; color:var(--muted);">Last Name</label>
                <input type="text" id="last_name" required style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); margin-top:4px;">
            </div>
        </div>
        
        <div style="margin-bottom:12px;">
            <label style="font-size:10px; color:var(--muted);">Phone Number</label>
            <input type="text" id="phone_number" style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); margin-top:4px;">
        </div>
        
        <div style="margin-bottom:16px;">
            <label style="font-size:10px; color:var(--muted);">Address</label>
            <textarea id="address" rows="3" style="width:100%; padding:10px; background:var(--surface); border:1px solid var(--border); color:var(--text); margin-top:4px; resize:vertical;"></textarea>
        </div>

        <button type="submit" class="act-btn fill" style="width:100%;">SAVE CHANGES</button>
      </form>
    </div>

    <!-- READ ONLY METRICS -->
    <div class="fcard" style="--accent:var(--green)">
      <div class="fcard-top">
        <div class="fcard-icon" style="--accent:var(--green)">🛡️</div>
        <div class="fcard-title">ACCOUNT STATUS</div>
      </div>
      
      <div style="margin-top:16px;">
          <div style="margin-bottom:16px; padding-bottom:16px; border-bottom:1px solid var(--border);">
              <div style="font-size:10px; color:var(--muted); text-transform:uppercase; letter-spacing:1px;">Email Address</div>
              <div id="email_display" style="font-size:16px; font-weight:500; margin-top:4px;"></div>
              <div id="verified_badge" style="display:inline-block; margin-top:8px; padding:2px 8px; font-size:9px; border-radius:12px;"></div>
          </div>

          <div style="margin-bottom:16px; padding-bottom:16px; border-bottom:1px solid var(--border);">
              <div style="font-size:10px; color:var(--muted); text-transform:uppercase; letter-spacing:1px;">KYC Status</div>
              <div id="kyc_display" style="font-size:16px; font-weight:500; margin-top:4px; text-transform:capitalize;"></div>
          </div>

          <div>
              <div style="font-size:10px; color:var(--muted); text-transform:uppercase; letter-spacing:1px;">AI Credit Score</div>
              <div id="ai_score_display" style="font-size:24px; font-weight:600; color:var(--green); margin-top:4px;"></div>
          </div>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
async function loadProfile() {
    try {
        const res = await fetch('api/profile.php?action=get');
        const data = await res.json();
        if(data.success) {
            document.getElementById('first_name').value = data.data.first_name || '';
            document.getElementById('last_name').value = data.data.last_name || '';
            document.getElementById('phone_number').value = data.data.phone_number || '';
            document.getElementById('address').value = data.data.address || '';
            
            document.getElementById('email_display').innerText = data.data.email;
            document.getElementById('kyc_display').innerText = data.data.kyc_status;
            document.getElementById('ai_score_display').innerText = data.data.ai_credit_score || 'N/A';
            
            const badge = document.getElementById('verified_badge');
            if(data.data.is_verified) {
                badge.innerText = 'VERIFIED';
                badge.style.background = 'var(--green)';
                badge.style.color = '#000';
            } else {
                badge.innerText = 'UNVERIFIED';
                badge.style.background = 'var(--gold)';
                badge.style.color = '#000';
            }
        }
    } catch(e) {}
}

async function updateProfile() {
    const msg = document.getElementById('msg');
    const formData = new FormData();
    formData.append('first_name', document.getElementById('first_name').value);
    formData.append('last_name', document.getElementById('last_name').value);
    formData.append('phone_number', document.getElementById('phone_number').value);
    formData.append('address', document.getElementById('address').value);

    try {
        const res = await fetch('api/profile.php?action=update', { method: 'POST', body: formData });
        const data = await res.json();
        
        msg.style.display = 'block';
        if(data.success) {
            msg.style.background = '#00e87a15';
            msg.style.color = 'var(--green)';
            msg.innerText = 'Profile updated successfully!';
            
            // Update name in nav
            document.querySelector('.user-name').innerText = document.getElementById('first_name').value + ' ' + document.getElementById('last_name').value;
        } else {
            msg.style.background = '#ff456015';
            msg.style.color = 'var(--red)';
            msg.innerText = data.error;
        }
    } catch(e) {
        msg.style.display = 'block';
        msg.innerText = 'Network error.';
    }
}

document.addEventListener('DOMContentLoaded', loadProfile);
</script>
</body>
</html>
