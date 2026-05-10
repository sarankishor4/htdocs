  </main>

  <!-- NEWSLETTER -->
  <section class="newsletter">
    <div>
      <h2>GET EARLY ACCESS</h2>
      <p>Be the first to know about new drops, exclusive offers, and behind-the-scenes at Luminarr. No spam — just style.</p>
    </div>
    <div class="newsletter-form">
      <div class="newsletter-input-wrap">
        <input type="email" id="nl-email" placeholder="your@email.com" />
        <button id="nl-btn">Subscribe</button>
      </div>
      <p class="newsletter-note" id="nl-msg">Join 500+ early members. Unsubscribe anytime.</p>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="footer-top">
      <div class="footer-brand">
        <a href="<?php echo BASE_URL; ?>index.php" class="nav-logo">LUMINARR</a>
        <p>A digital-first clothing brand built for the way you actually live. Clean. Versatile. Unapologetically you.</p>
      </div>
      <div class="footer-col">
        <h5>Shop</h5>
        <a href="<?php echo BASE_URL; ?>shop.php">New Arrivals</a>
        <a href="<?php echo BASE_URL; ?>shop.php?category=1">Smart Casuals</a>
        <a href="<?php echo BASE_URL; ?>shop.php">Workwear</a>
        <a href="<?php echo BASE_URL; ?>shop.php">Essentials</a>
        <a href="<?php echo BASE_URL; ?>shop.php">Premium</a>
      </div>
      <div class="footer-col">
        <h5>Help</h5>
        <a href="#">Size Guide</a>
        <a href="#">Shipping Info</a>
        <a href="#">Returns</a>
        <a href="#">FAQs</a>
        <a href="#">Contact Us</a>
      </div>
      <div class="footer-col">
        <h5>Brand</h5>
        <a href="#">Our Story</a>
        <a href="#">Sustainability</a>
        <a href="#">Careers</a>
        <a href="#">Press</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© <?php echo date('Y'); ?> Luminarr. All rights reserved. Made in India 🇮🇳</p>
      <div class="social-links">
        <a href="#">Instagram</a>
        <a href="#">WhatsApp</a>
        <a href="#">LinkedIn</a>
      </div>
    </div>
  </footer>

  <script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>
  <script>
    // Custom cursor
    const cursor = document.getElementById('cursor');
    const ring = document.getElementById('cursorRing');
    let mx = 0, my = 0, rx = 0, ry = 0;
    let scaleC = 1, scaleR = 1;
    
    // Only apply custom cursor on non-touch devices
    if (window.matchMedia("(pointer: fine)").matches) {
        document.addEventListener('mousemove', e => {
          mx = e.clientX; my = e.clientY;
          if(cursor) {
              cursor.style.transform = `translate3d(${mx - 5}px, ${my - 5}px, 0) scale(${scaleC})`;
          }
        });
        function animateRing() {
          rx += (mx - rx) * 0.12;
          ry += (my - ry) * 0.12;
          if(ring) {
              ring.style.transform = `translate3d(${rx - 18}px, ${ry - 18}px, 0) scale(${scaleR})`;
          }
          requestAnimationFrame(animateRing);
        }
        animateRing();

        // Hover expand cursor on interactive elements
        document.querySelectorAll('a, button, input, select, textarea').forEach(el => {
          el.addEventListener('mouseenter', () => {
            if(cursor && ring) {
                scaleC = 2.5;
                scaleR = 1.5;
                cursor.style.transform = `translate3d(${mx - 5}px, ${my - 5}px, 0) scale(${scaleC})`;
                ring.style.transform = `translate3d(${rx - 18}px, ${ry - 18}px, 0) scale(${scaleR})`;
                ring.style.opacity = '0.3';
            }
          });
          el.addEventListener('mouseleave', () => {
            if(cursor && ring) {
                scaleC = 1;
                scaleR = 1;
                cursor.style.transform = `translate3d(${mx - 5}px, ${my - 5}px, 0) scale(${scaleC})`;
                ring.style.transform = `translate3d(${rx - 18}px, ${ry - 18}px, 0) scale(${scaleR})`;
                ring.style.opacity = '0.6';
            }
          });
        });
    } else {
        if(cursor) cursor.style.display = 'none';
        if(ring) ring.style.display = 'none';
        document.body.style.cursor = 'auto';
    }

    // Scroll reveal
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.pillar, .product-card, .cat-card, .stat').forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = 'opacity 0.7s ease, transform 0.7s ease';
      observer.observe(el);
    });

    // Newsletter logic
    const nlBtn = document.getElementById('nl-btn');
    if(nlBtn) {
        nlBtn.addEventListener('click', async () => {
            const email = document.getElementById('nl-email').value;
            const msg = document.getElementById('nl-msg');
            if(!email) return msg.innerText = "Please enter an email.";
            
            try {
                const res = await fetch('<?php echo BASE_URL; ?>api/newsletter.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({email})
                });
                const data = await res.json();
                if(data.success) {
                    msg.style.color = 'var(--accent)';
                    msg.innerText = data.message;
                    document.getElementById('nl-email').value = '';
                } else {
                    msg.style.color = '#ff4d4d';
                    msg.innerText = data.error;
                }
            } catch(e) {
                msg.innerText = "Error subscribing. Try again.";
            }
        });
    }
  </script>
</body>
</html>
