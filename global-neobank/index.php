<?php
require_once __DIR__ . '/core/includes/auth_guard.php';
startSecureSession();
$isLoggedIn = !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Global Neobank Roadmap</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
  :root {
    --black: #080810;
    --white: #f0f0f8;
    --gold: #f5c518;
    --teal: #00d4aa;
    --red: #ff3c5f;
    --dim: #1a1a2e;
    --card: #10101e;
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    background: var(--black);
    color: var(--white);
    font-family: 'Space Mono', monospace;
    overflow-x: hidden;
  }

  /* HERO */
  .hero {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 60px 48px;
    position: relative;
    border-bottom: 1px solid #ffffff12;
    overflow: hidden;
  }

  .hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 80% 60% at 70% 40%, #00d4aa18, transparent 70%),
                radial-gradient(ellipse 50% 50% at 20% 80%, #f5c51812, transparent 60%);
    pointer-events: none;
  }

  .hero-eyebrow {
    font-family: 'Space Mono', monospace;
    font-size: 11px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: var(--teal);
    margin-bottom: 24px;
    animation: fadeUp 0.6s ease both;
  }

  .hero-title {
    font-family: 'Syne', sans-serif;
    font-size: clamp(48px, 8vw, 96px);
    font-weight: 800;
    line-height: 0.95;
    letter-spacing: -2px;
    max-width: 800px;
    animation: fadeUp 0.7s 0.1s ease both;
  }

  .hero-title span { color: var(--gold); }

  .hero-sub {
    margin-top: 32px;
    font-size: 13px;
    line-height: 1.8;
    color: #a0a0c0;
    max-width: 520px;
    animation: fadeUp 0.7s 0.2s ease both;
  }

  .hero-badges {
    margin-top: 40px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    animation: fadeUp 0.7s 0.3s ease both;
  }

  .badge {
    padding: 6px 14px;
    border: 1px solid #ffffff22;
    border-radius: 2px;
    font-size: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--teal);
    background: #00d4aa08;
  }

  .ticker {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40px;
    background: var(--gold);
    display: flex;
    align-items: center;
    overflow: hidden;
  }

  .ticker-inner {
    display: flex;
    gap: 80px;
    white-space: nowrap;
    animation: ticker 20s linear infinite;
    font-size: 11px;
    font-weight: 700;
    color: var(--black);
    letter-spacing: 2px;
    padding: 0 40px;
  }

  /* PHASES */
  .phases {
    padding: 80px 48px;
  }

  .section-label {
    font-size: 10px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: var(--teal);
    margin-bottom: 48px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .section-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #ffffff15;
  }

  .phase-grid {
    display: grid;
    gap: 2px;
  }

  .phase {
    background: var(--card);
    border: 1px solid #ffffff08;
    padding: 40px;
    display: grid;
    grid-template-columns: 120px 1fr;
    gap: 40px;
    transition: border-color 0.3s;
    position: relative;
    overflow: hidden;
  }

  .phase:hover {
    border-color: #ffffff25;
  }

  .phase::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
  }

  .phase-1::before { background: var(--teal); }
  .phase-2::before { background: var(--gold); }
  .phase-3::before { background: var(--red); }
  .phase-4::before { background: #a78bfa; }
  .phase-5::before { background: #38bdf8; }

  .phase-num {
    font-family: 'Syne', sans-serif;
    font-size: 64px;
    font-weight: 800;
    line-height: 1;
    color: #ffffff08;
    user-select: none;
  }

  .phase-meta { display: flex; flex-direction: column; }

  .phase-tag {
    font-size: 9px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin-bottom: 8px;
  }

  .phase-1 .phase-tag { color: var(--teal); }
  .phase-2 .phase-tag { color: var(--gold); }
  .phase-3 .phase-tag { color: var(--red); }
  .phase-4 .phase-tag { color: #a78bfa; }
  .phase-5 .phase-tag { color: #38bdf8; }

  .phase-title {
    font-family: 'Syne', sans-serif;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 16px;
    line-height: 1.2;
  }

  .phase-timeline {
    font-size: 10px;
    color: #606080;
    margin-bottom: 20px;
    letter-spacing: 1px;
  }

  .phase-items {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .phase-items li {
    font-size: 12px;
    color: #a0a0c0;
    line-height: 1.6;
    padding-left: 16px;
    position: relative;
  }

  .phase-items li::before {
    content: '→';
    position: absolute;
    left: 0;
    color: #404060;
  }

  /* UNIQUE MODEL */
  .model-section {
    padding: 80px 48px;
    background: var(--dim);
    border-top: 1px solid #ffffff08;
    border-bottom: 1px solid #ffffff08;
  }

  .model-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2px;
    margin-top: 48px;
  }

  .model-card {
    background: var(--card);
    padding: 36px;
    border: 1px solid #ffffff08;
    transition: transform 0.3s, border-color 0.3s;
  }

  .model-card:hover {
    transform: translateY(-4px);
    border-color: var(--teal);
  }

  .model-icon {
    font-size: 28px;
    margin-bottom: 16px;
  }

  .model-card h3 {
    font-family: 'Syne', sans-serif;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--white);
  }

  .model-card p {
    font-size: 12px;
    color: #8080a0;
    line-height: 1.7;
  }

  .highlight { color: var(--gold); }

  /* RISKS */
  .risks {
    padding: 80px 48px;
  }

  .risk-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 48px;
    font-size: 12px;
  }

  .risk-table th {
    text-align: left;
    padding: 12px 20px;
    font-size: 9px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #606080;
    border-bottom: 1px solid #ffffff15;
  }

  .risk-table td {
    padding: 16px 20px;
    border-bottom: 1px solid #ffffff08;
    color: #a0a0c0;
    vertical-align: top;
    line-height: 1.6;
  }

  .risk-table tr:hover td { background: #ffffff04; }

  .risk-level {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 2px;
    font-size: 9px;
    letter-spacing: 2px;
    font-weight: 700;
  }

  .high { background: #ff3c5f22; color: var(--red); border: 1px solid var(--red); }
  .med { background: #f5c51822; color: var(--gold); border: 1px solid var(--gold); }
  .low { background: #00d4aa22; color: var(--teal); border: 1px solid var(--teal); }

  /* FOOTER */
  .footer {
    padding: 48px;
    border-top: 1px solid #ffffff12;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: gap;
  }

  .footer-brand {
    font-family: 'Syne', sans-serif;
    font-size: 20px;
    font-weight: 800;
    color: var(--gold);
  }

  .footer-note {
    font-size: 10px;
    color: #404060;
    letter-spacing: 1px;
  }

  /* ANIMATIONS */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @keyframes ticker {
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
  }

  @media (max-width: 640px) {
    .hero, .phases, .model-section, .risks, .footer { padding: 40px 24px; }
    .phase { grid-template-columns: 1fr; gap: 12px; }
    .phase-num { font-size: 40px; }
  }
</style>
</head>
<body>

<!-- HERO -->
<section class="hero">
  <div class="hero-eyebrow">// Confidential Business Blueprint — 2025</div>
  <h1 class="hero-title">The <span>Global</span><br>Digital Bank.</h1>
  <p class="hero-sub">A fully branchless, AI-powered financial institution — built for everyone on Earth. Zero physical infrastructure. Maximum impact.</p>
  <div class="hero-badges">
    <span class="badge">Crypto + Fiat</span>
    <span class="badge">Global Trading</span>
    <span class="badge">AI Credit Scoring</span>
    <span class="badge">Human Capital Loans</span>
    <span class="badge">No Branches</span>
    <span class="badge">Low Income Focus</span>
  </div>
  
  <div style="margin-top: 32px; animation: fadeUp 0.7s 0.4s ease both;">
    <?php if ($isLoggedIn): ?>
      <a href="home.php" style="display:inline-block; padding:12px 24px; background:var(--teal); color:var(--black); text-decoration:none; font-family:'Syne',sans-serif; font-weight:700; border-radius:4px; letter-spacing:1px;">GO TO DASHBOARD →</a>
    <?php else: ?>
      <a href="login.php" style="display:inline-block; padding:12px 24px; background:var(--teal); color:var(--black); text-decoration:none; font-family:'Syne',sans-serif; font-weight:700; border-radius:4px; letter-spacing:1px; margin-right:12px;">LOGIN</a>
      <a href="register.php" style="display:inline-block; padding:12px 24px; background:transparent; border:1px solid var(--teal); color:var(--teal); text-decoration:none; font-family:'Syne',sans-serif; font-weight:700; border-radius:4px; letter-spacing:1px;">REGISTER</a>
    <?php endif; ?>
  </div>
  <div class="ticker">
    <div class="ticker-inner">
      BANKING FOR EVERYONE &nbsp;·&nbsp; NO BRANCHES &nbsp;·&nbsp; AI-POWERED &nbsp;·&nbsp; GLOBAL FROM DAY ONE &nbsp;·&nbsp; CRYPTO + FIAT &nbsp;·&nbsp; HUMAN CAPITAL MODEL &nbsp;·&nbsp;
      BANKING FOR EVERYONE &nbsp;·&nbsp; NO BRANCHES &nbsp;·&nbsp; AI-POWERED &nbsp;·&nbsp; GLOBAL FROM DAY ONE &nbsp;·&nbsp; CRYPTO + FIAT &nbsp;·&nbsp; HUMAN CAPITAL MODEL &nbsp;·&nbsp;
    </div>
  </div>
</section>

<!-- PHASES -->
<section class="phases">
  <div class="section-label">Full Roadmap</div>
  <div class="phase-grid">

    <div class="phase phase-1">
      <div class="phase-num">01</div>
      <div class="phase-meta">
        <span class="phase-tag">Foundation</span>
        <h2 class="phase-title">Legal Entity & Licensing</h2>
        <div class="phase-timeline">Months 1 – 6</div>
        <ul class="phase-items">
          <li>Register company in a fintech-friendly jurisdiction (UAE, Singapore, Lithuania, or UK)</li>
          <li>Apply for EMI (Electronic Money Institution) or banking license</li>
          <li>Hire a regulatory lawyer specializing in global fintech</li>
          <li>Set up AML / KYC compliance framework from day one</li>
          <li>Open corporate bank accounts with partner banks (BaaS providers like Railsr, Synapse, or Solarisbank)</li>
          <li>Draft your Terms of Service, Privacy Policy & loan agreements</li>
        </ul>
      </div>
    </div>

    <div class="phase phase-2">
      <div class="phase-num">02</div>
      <div class="phase-meta">
        <span class="phase-tag">Technology</span>
        <h2 class="phase-title">Build Core Banking Platform</h2>
        <div class="phase-timeline">Months 4 – 12</div>
        <ul class="phase-items">
          <li>Choose core banking software: build custom OR use Mambu, Thought Machine, or Temenos</li>
          <li>Integrate crypto rails via Fireblocks or BitGo for custody</li>
          <li>Build mobile app (iOS + Android) + web dashboard</li>
          <li>Connect to SWIFT, SEPA, ACH for global fiat transfers</li>
          <li>Integrate AI credit scoring engine (analyze skills, income patterns, employment history)</li>
          <li>Build trading module: stocks, crypto, forex with real-time pricing</li>
          <li>Implement multi-currency wallets with live exchange rates</li>
        </ul>
      </div>
    </div>

    <div class="phase phase-3">
      <div class="phase-num">03</div>
      <div class="phase-meta">
        <span class="phase-tag">Unique Model</span>
        <h2 class="phase-title">Human Capital Loan System</h2>
        <div class="phase-timeline">Months 8 – 14</div>
        <ul class="phase-items">
          <li>Design AI skill-assessment tool — users upload CV, skills, work history</li>
          <li>AI scores creditworthiness based on potential, not just income</li>
          <li>Low-income borrowers get micro-loans against their future earning potential</li>
          <li>If loan defaults: borrower enters "Work for Company" program — freelance tasks, customer support, data labeling, etc.</li>
          <li>Legal team drafts income-share agreements (ISA) compliant in each country</li>
          <li>Build internal talent marketplace where defaulters repay via verified work</li>
        </ul>
      </div>
    </div>

    <div class="phase phase-4">
      <div class="phase-num">04</div>
      <div class="phase-meta">
        <span class="phase-tag">Launch</span>
        <h2 class="phase-title">Beta Launch & User Acquisition</h2>
        <div class="phase-timeline">Months 12 – 18</div>
        <ul class="phase-items">
          <li>Launch closed beta in 3 pilot markets (e.g. India, Nigeria, UAE)</li>
          <li>Offer zero-fee accounts & competitive exchange rates to attract early users</li>
          <li>Partner with local employers and gig platforms for payroll integration</li>
          <li>Run referral program — users earn rewards for inviting others</li>
          <li>Get media coverage in fintech press (TechCrunch, The Block, Finextra)</li>
          <li>Reach 100,000 users before full public launch</li>
        </ul>
      </div>
    </div>

    <div class="phase phase-5">
      <div class="phase-num">05</div>
      <div class="phase-meta">
        <span class="phase-tag">Scale</span>
        <h2 class="phase-title">Global Expansion & Revenue</h2>
        <div class="phase-timeline">Year 2 – 5</div>
        <ul class="phase-items">
          <li>Expand licensing to 20+ countries progressively</li>
          <li>Launch premium tiers with advanced trading, higher loan limits, wealth management</li>
          <li>Revenue streams: FX spreads, trading commissions, loan interest, premium subscriptions, B2B API</li>
          <li>Raise Series A / B from fintech VCs (a16z, Sequoia, Tiger Global)</li>
          <li>Offer business accounts for SMEs and freelancers globally</li>
          <li>Target 10 million users by Year 3</li>
        </ul>
      </div>
    </div>

  </div>
</section>

<!-- UNIQUE MODEL BREAKDOWN -->
<section class="model-section">
  <div class="section-label">What Makes You Different</div>
  <div class="model-grid">

    <div class="model-card">
      <div class="model-icon">🧠</div>
      <h3>AI Credit Scoring</h3>
      <p>No credit history? No problem. Our AI reads your <span class="highlight">skills, experience, and work patterns</span> to decide loan eligibility — opening banking to billions of unbanked people.</p>
    </div>

    <div class="model-card">
      <div class="model-icon">🤝</div>
      <h3>Human Capital Repayment</h3>
      <p>If a borrower defaults, they don't go to collections. They <span class="highlight">work for the company</span> through our talent marketplace — repaying through verified freelance tasks and services.</p>
    </div>

    <div class="model-card">
      <div class="model-icon">₿</div>
      <h3>Crypto + Fiat in One</h3>
      <p>Hold, send, and trade both crypto and traditional currencies in one account. <span class="highlight">Convert between them instantly</span> at real market rates with no hidden fees.</p>
    </div>

    <div class="model-card">
      <div class="model-icon">📈</div>
      <h3>Built-in Trading</h3>
      <p>Stocks, crypto, forex — all inside the same app where you bank. <span class="highlight">No need for separate brokerage accounts.</span> Trade with as little as $1.</p>
    </div>

    <div class="model-card">
      <div class="model-icon">🌍</div>
      <h3>Truly Global</h3>
      <p>Multi-currency accounts, local payment rails in every market, and <span class="highlight">zero foreign transaction fees</span>. Built for expats, freelancers, and digital nomads.</p>
    </div>

    <div class="model-card">
      <div class="model-icon">💸</div>
      <h3>Low Income Focus</h3>
      <p>Most banks ignore low-income users. We <span class="highlight">actively serve them</span> with micro-loans, zero minimum balance, and AI-powered financial coaching to grow their wealth.</p>
    </div>

  </div>
</section>

<!-- RISKS & HOW TO HANDLE THEM -->
<section class="risks">
  <div class="section-label">Key Risks & Mitigations</div>
  <table class="risk-table">
    <thead>
      <tr>
        <th>Risk</th>
        <th>Level</th>
        <th>How to Handle It</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Getting a banking license globally</td>
        <td><span class="risk-level high">HIGH</span></td>
        <td>Start with an EMI license in one jurisdiction. Use Banking-as-a-Service (BaaS) partners to operate in other countries while you get full licenses over time.</td>
      </tr>
      <tr>
        <td>Human capital / work-for-loan legality</td>
        <td><span class="risk-level high">HIGH</span></td>
        <td>This model is novel — hire top-tier lawyers to structure Income Share Agreements (ISAs) compliant with labor laws in each country. Start in countries with flexible ISA laws.</td>
      </tr>
      <tr>
        <td>Loan defaults & bad debt</td>
        <td><span class="risk-level high">HIGH</span></td>
        <td>Keep initial loan amounts small ($50–$500). Build strong AI scoring. The work-repayment model reduces total loss significantly vs. traditional collections.</td>
      </tr>
      <tr>
        <td>Crypto regulatory uncertainty</td>
        <td><span class="risk-level med">MED</span></td>
        <td>Register with crypto regulators in each market. Follow MiCA in Europe. Keep fiat and crypto wallets structurally separate for compliance purposes.</td>
      </tr>
      <tr>
        <td>Competing with Revolut, Wise, Chime</td>
        <td><span class="risk-level med">MED</span></td>
        <td>Your human capital model and low-income focus is a genuine differentiator they don't have. Own that niche aggressively first before going broad.</td>
      </tr>
      <tr>
        <td>Funding — this is capital intensive</td>
        <td><span class="risk-level med">MED</span></td>
        <td>Start lean with BaaS infrastructure. Raise a pre-seed round from angel investors. Prove the model with 10,000 users before raising a larger round.</td>
      </tr>
      <tr>
        <td>Cybersecurity & fraud</td>
        <td><span class="risk-level low">LOW</span></td>
        <td>Use bank-grade encryption, biometric authentication, and partner with established fraud detection providers like Sardine or Seon from day one.</td>
      </tr>
    </tbody>
  </table>
</section>

<!-- FOOTER -->
<div class="footer">
  <div class="footer-brand">GLOBALBANK™</div>
  <div class="footer-note">Confidential Roadmap · Built with vision · 2025 – 2030</div>
</div>

</body>
</html>
