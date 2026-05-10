<?php require_once __DIR__ . '/../api/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LUMINARR — Wear Your Light</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <div class="cursor" id="cursor"></div>
  <div class="cursor-ring" id="cursorRing"></div>

  <!-- NAV -->
  <nav>
    <a href="<?php echo BASE_URL; ?>index.php" class="nav-logo">LUMINARR</a>
    <ul class="nav-links">
      <li><a href="<?php echo BASE_URL; ?>index.php#collection">Collection</a></li>
      <li><a href="<?php echo BASE_URL; ?>shop.php">Shop All</a></li>
      <li><a href="<?php echo BASE_URL; ?>index.php#about">About</a></li>
      <li><a href="<?php echo BASE_URL; ?>index.php#philosophy">Philosophy</a></li>
    </ul>
    <div style="display: flex; gap: 1rem; align-items: center;">
      <form action="<?php echo BASE_URL; ?>shop.php" method="GET" style="display:flex; border-bottom: 1px solid var(--muted); padding-bottom: 2px; margin-right: 15px;">
         <input type="text" name="search" placeholder="Search..." style="background:transparent; border:none; color:var(--white); outline:none; font-family:var(--font-body); font-size:0.75rem; width: 100px;">
         <button type="submit" style="background:transparent; border:none; color:var(--muted); cursor:none;"><i class="fas fa-search"></i></button>
      </form>
      <?php if (isset($_SESSION['user_id'])): ?>
          <div class="dropdown">
              <a href="#" style="color: var(--white); text-decoration: none; font-size: 0.75rem; letter-spacing: 0.2em; text-transform: uppercase;">
                 <i class="fas fa-user" style="margin-right: 5px;"></i> <?php echo explode(' ', $_SESSION['user_name'])[0]; ?>
              </a>
              <div class="dropdown-content">
                  <?php if ($_SESSION['user_role'] === 'admin'): ?>
                      <a href="<?php echo BASE_URL; ?>admin/index.php">Admin Dash</a>
                  <?php endif; ?>
                  <a href="<?php echo BASE_URL; ?>profile.php">Profile</a>
                  <a href="<?php echo BASE_URL; ?>orders.php">Orders</a>
                  <a href="<?php echo BASE_URL; ?>api/auth.php?action=logout">Logout</a>
              </div>
          </div>
      <?php else: ?>
          <a href="<?php echo BASE_URL; ?>login.php" style="color: var(--white); text-decoration: none; font-size: 0.75rem; letter-spacing: 0.2em; text-transform: uppercase;">Login</a>
      <?php endif; ?>
      <a href="<?php echo BASE_URL; ?>cart.php" class="nav-cta">
        Basket (<span id="cart-count" style="display:inline;">0</span>)
      </a>
    </div>
  </nav>

  <main>
