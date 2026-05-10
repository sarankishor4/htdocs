<?php 
require_once 'includes/header.php'; 
$db = getDB();
$featured = [];
try {
    $featured = $db->query("SELECT * FROM products WHERE featured = 1 AND status = 'active' LIMIT 4")->fetchAll();
} catch (PDOException $e) {
    // Database tables might not exist yet
}
?>

  <!-- HERO -->
  <section class="hero">
    <div class="hero-left">
      <p class="hero-eyebrow">New Collection — SS 2025</p>
      <h1 class="hero-title">WEAR<br/><span>YOUR</span><br/>LIGHT</h1>
      <p class="hero-subtitle">Style that fits every chapter of your life.</p>
      <div class="hero-actions">
        <a href="#collection" class="btn-primary">Explore Collection</a>
        <a href="#about" class="btn-ghost">Our Story</a>
      </div>
    </div>
    <div class="hero-right">
      <div class="hero-img-wrap">
        <div class="hero-model">
          <svg viewBox="0 0 200 400" fill="none" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="100" cy="45" rx="28" ry="30" fill="#e8c97a"/>
            <path d="M60 90 Q100 80 140 90 L155 200 L145 320 L100 340 L55 320 L45 200 Z" fill="#e8c97a"/>
            <path d="M60 90 L30 180 L50 185 L70 130" fill="#e8c97a"/>
            <path d="M140 90 L170 180 L150 185 L130 130" fill="#e8c97a"/>
            <path d="M65 200 L55 340 L75 342 L100 260 L125 342 L145 340 L135 200 Z" fill="#e8c97a"/>
          </svg>
        </div>
        <div class="hero-badge">
          <p>Est.</p>
          <span>2025</span>
        </div>
      </div>
    </div>
    <div class="scroll-hint">
      <div class="scroll-line"></div>
      <span>Scroll</span>
    </div>
  </section>

  <!-- MARQUEE -->
  <div class="marquee-wrap">
    <div class="marquee-track">
      <span>Smart Casuals</span><span class="marquee-dot">✦</span>
      <span>Everyday Luxury</span><span class="marquee-dot">✦</span>
      <span>Wear Your Light</span><span class="marquee-dot">✦</span>
      <span>Clean Aesthetics</span><span class="marquee-dot">✦</span>
      <span>Timeless Fit</span><span class="marquee-dot">✦</span>
      <span>Digital First</span><span class="marquee-dot">✦</span>
      <span>Smart Casuals</span><span class="marquee-dot">✦</span>
      <span>Everyday Luxury</span><span class="marquee-dot">✦</span>
      <span>Wear Your Light</span><span class="marquee-dot">✦</span>
      <span>Clean Aesthetics</span><span class="marquee-dot">✦</span>
      <span>Timeless Fit</span><span class="marquee-dot">✦</span>
      <span>Digital First</span><span class="marquee-dot">✦</span>
    </div>
  </div>

  <!-- ABOUT -->
  <section class="about" id="about">
    <div>
      <p class="about-label">Who We Are</p>
      <h2 class="about-title">BORN<br/>DIGITAL.<br/>BUILT<br/>DIFFERENT.</h2>
      <p class="about-text">Luminarr is a <em>digital-first clothing brand</em> built for those who move between worlds — the boardroom and the café, the commute and the weekend. We don't make clothes for occasions. We make clothes for people.</p>
      <p class="about-text">Founded in India. Made with intention. Priced so you can <em>actually wear us every day.</em></p>
      <div class="about-stats">
        <div class="stat">
          <h3>3</h3>
          <p>Tiers of Style</p>
        </div>
        <div class="stat">
          <h3>100%</h3>
          <p>Digital Native</p>
        </div>
        <div class="stat">
          <h3>∞</h3>
          <p>Occasions</p>
        </div>
      </div>
    </div>
    <div class="about-visual">
      <div class="about-card main">
        <div class="card-inner-pattern">
          <span>"Style is not about being noticed,<br/>it's about being remembered."</span>
        </div>
      </div>
      <div class="about-card accent-card">
        <p>Since</p>
        <span>2025</span>
        <p>India</p>
      </div>
    </div>
  </section>

  <!-- COLLECTION -->
  <section class="collection" id="collection">
    <div class="section-header">
      <div>
        <p class="section-label">Featured Products</p>
        <h2 class="section-title">THE EDIT</h2>
      </div>
      <a href="shop.php" class="view-all">View All →</a>
    </div>
    <div class="products-grid">

      <?php if (!empty($featured)): ?>
          <?php foreach ($featured as $i => $product): ?>
              <div class="product-card">
                <div class="product-img">
                  <div class="product-img-inner p<?php echo ($i % 4) + 1; ?> p-visual">
                      <?php if($product['image']): ?>
                          <img src="<?php echo UPLOAD_URL . $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="width:100%; height:100%; object-fit:cover; opacity: 0.9;">
                      <?php endif; ?>
                  </div>
                  <?php if($i === 0): ?><div class="product-tag">New</div><?php endif; ?>
                  <?php if($i === 2): ?><div class="product-tag">Best Seller</div><?php endif; ?>
                  <div class="product-overlay"><button class="btn-add-cart" data-id="<?php echo $product['id']; ?>">Quick Add</button></div>
                </div>
                <a href="product.php?id=<?php echo $product['id']; ?>" class="product-name"><?php echo $product['name']; ?></a>
                <div class="product-meta">
                  <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                  <span class="product-category">Luminarr</span>
                </div>
              </div>
          <?php endforeach; ?>
      <?php else: ?>
          <!-- Placeholder Data if DB empty -->
          <div class="product-card">
            <div class="product-img">
              <div class="product-img-inner p1 p-visual">
                <svg class="p-icon" width="80" height="100" viewBox="0 0 80 100" fill="none">
                  <rect x="15" y="0" width="50" height="70" rx="2" stroke="#e8c97a" stroke-width="1.5"/>
                  <line x1="0" y1="20" x2="15" y2="20" stroke="#e8c97a" stroke-width="1.5"/>
                  <line x1="65" y1="20" x2="80" y2="20" stroke="#e8c97a" stroke-width="1.5"/>
                  <line x1="0" y1="20" x2="5" y2="65" stroke="#e8c97a" stroke-width="1.5"/>
                  <line x1="80" y1="20" x2="75" y2="65" stroke="#e8c97a" stroke-width="1.5"/>
                  <rect x="10" y="70" width="60" height="30" rx="2" stroke="#e8c97a" stroke-width="1.5"/>
                </svg>
              </div>
              <div class="product-tag">New</div>
              <div class="product-overlay"><button>Quick Add</button></div>
            </div>
            <p class="product-name">Structured Slim Shirt</p>
            <div class="product-meta">
              <span class="product-price">₹1,299</span>
              <span class="product-category">Smart Casual</span>
            </div>
          </div>
          <!-- Additional placeholders removed for brevity, will rely on DB -->
      <?php endif; ?>

    </div>
  </section>

  <!-- CATEGORIES -->
  <section class="categories" id="categories">
    <div class="section-header">
      <div>
        <p class="section-label">Shop By</p>
        <h2 class="section-title">CATEGORIES</h2>
      </div>
    </div>
    <div class="categories-grid">
      <div class="cat-card tall" onclick="window.location.href='shop.php'">
        <div class="cat-bg cat-bg-1">
          <svg class="cat-pattern" viewBox="0 0 400 800" preserveAspectRatio="none">
            <defs><pattern id="p1" width="40" height="40" patternUnits="userSpaceOnUse"><line x1="0" y1="40" x2="40" y2="0" stroke="#e8c97a" stroke-width="0.5"/></pattern></defs>
            <rect width="400" height="800" fill="url(#p1)"/>
          </svg>
        </div>
        <div class="cat-overlay"></div>
        <div class="cat-content">
          <p class="cat-label">For Him & Her</p>
          <h3 class="cat-title">SMART<br/>CASUALS</h3>
        </div>
      </div>
      <div class="cat-card" onclick="window.location.href='shop.php'">
        <div class="cat-bg cat-bg-2"></div>
        <div class="cat-overlay"></div>
        <div class="cat-content">
          <p class="cat-label">Office Ready</p>
          <h3 class="cat-title">WORK WEAR</h3>
        </div>
      </div>
      <div class="cat-card" onclick="window.location.href='shop.php'">
        <div class="cat-bg cat-bg-3"></div>
        <div class="cat-overlay"></div>
        <div class="cat-content">
          <p class="cat-label">Entry Tier</p>
          <h3 class="cat-title">ESSENTIALS</h3>
        </div>
      </div>
      <div class="cat-card" onclick="window.location.href='shop.php'">
        <div class="cat-bg cat-bg-4"></div>
        <div class="cat-overlay"></div>
        <div class="cat-content">
          <p class="cat-label">Top Shelf</p>
          <h3 class="cat-title">PREMIUM</h3>
        </div>
      </div>
    </div>
  </section>

  <!-- PHILOSOPHY -->
  <section class="philosophy" id="philosophy">
    <div class="philosophy-inner">
      <p class="about-label" style="text-align:center; margin-bottom:1.5rem;">Our Philosophy</p>
      <p class="philosophy-quote">"Clothing shouldn't scream for attention — it should make you feel <em>unstoppable</em> from the inside out."</p>
      <p class="philosophy-author">— The Luminarr Manifesto</p>
    </div>
    <div class="philosophy-pillars">
      <div class="pillar">
        <div class="pillar-num">01</div>
        <h4>Versatility First</h4>
        <p>Every piece is designed to work across contexts — from your 9 AM standup to your 9 PM dinner. No outfit changes required.</p>
      </div>
      <div class="pillar">
        <div class="pillar-num">02</div>
        <h4>Quality You Feel</h4>
        <p>We obsess over fabric weight, stitching, and fit. Premium quality doesn't have to mean premium pricing.</p>
      </div>
      <div class="pillar">
        <div class="pillar-num">03</div>
        <h4>Made in India</h4>
        <p>Proudly sourced and crafted in India. Supporting local textile communities while building a world-class brand.</p>
      </div>
    </div>
  </section>

<?php require_once 'includes/footer.php'; ?>
