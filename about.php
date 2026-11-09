<?php
/**
 * ECOMSHOOT - About Us Page
 * Premium Commercial Photography & Production Studio
 */

$pageIdentifier = 'about';
require_once __DIR__ . '/config/config.php';
require_once INCLUDES_PATH . '/breadcrumbs.php';
require_once INCLUDES_PATH . '/header.php';
?>

<!-- Breadcrumbs -->
<?= render_breadcrumbs([['title' => 'About Our Studio', 'url' => null]]) ?>

<!-- Page Header Banner -->
<section class="page-header-banner">
  <div class="container">
    <div class="page-header-content">
      <span class="badge badge-orange">Our Studio Heritage</span>
      <h1 class="page-header-title">The Commercial Photography Studio Engineered for Modern Digital Commerce</h1>
      <p class="page-header-sub">
        We bridge commercial fine art aesthetics with high-throughput marketplace conversion science to deliver visual assets that sell.
      </p>
    </div>
  </div>
</section>

<!-- Company Overview & Story (With Real Studio Photo) -->
<section class="section section-about-preview">
  <div class="container">
    <div class="editorial-split-box">
      <div class="about-text-pane">
        <span class="badge badge-indigo">Our Story</span>
        <h2 class="editorial-h2" style="margin-top: 0.75rem;">Why E-Commerce Photography Needed an Engineering Overhaul</h2>
        <div class="about-lead">
          Traditional commercial photography studios were designed for print magazines and seasonal billboards—slow, prohibitively expensive, and detached from the operational realities of direct-to-consumer inventory cycles.
        </div>
        <p class="about-body">
          At the other extreme, low-cost catalog mills deliver washed-out packshots with sloppy automated cutouts, inaccurate color casts, and zero understanding of brand nuance.
        </p>
        <p class="about-body">
          In 2019, Ecomshoot was founded in New York to solve this dilemma. We built a specialized commercial production house designed from the ground up to satisfy the rigorous technical requirements of Amazon, Shopify Plus, and omnichannel luxury retailers without sacrificing editorial warmth or tactile realism.
        </p>
      </div>
      <div class="about-media-pane">
        <div class="about-img-wrapper">
          <img src="<?= asset_url('assets/images/about/studio-bay.jpg') ?>" 
               alt="Ecomshoot Commercial Photography Studio Facility and Staging Bay" 
               width="600" 
               height="450" 
               loading="eager"
               class="about-studio-img">
          <div class="about-stat-card">
            <span class="stat-big">100%</span>
            <span class="stat-unit">TETHERED PROOFING &amp; CALIBRATED OPTICS</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Studio Hardware & Capabilities -->
<section class="section section-pillars">
  <div class="container">
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-lavender">Technical Excellence</span>
      </div>
      <h2 class="section-title-editorial">Hardware &amp; Studio Capabilities</h2>
      <p class="section-sub-editorial">
        We invest relentlessly in medium format optics, calibrated power packs, and specialized staging rigs to deliver pristine clarity.
      </p>
    </div>

    <div class="editorial-pillars-grid">
      <div class="editorial-pillar-card">
        <div class="pillar-top">
          <div class="pillar-icon-box blue">📸</div>
          <span class="pillar-index">OPTICS</span>
        </div>
        <h3 class="pillar-heading">Medium Format Acuity</h3>
        <p class="pillar-copy">Phase One IQ4 150MP digital systems and Schneider Kreuznach leaf shutter lenses resolving fine micro-textures down to single textile threads.</p>
      </div>

      <div class="editorial-pillar-card">
        <div class="pillar-top">
          <div class="pillar-icon-box coral">💡</div>
          <span class="pillar-index">LIGHT</span>
        </div>
        <h3 class="pillar-heading">Broncolor Strobes</h3>
        <p class="pillar-copy">Broncolor Scoro power packs with 1/10,000s flash duration to freeze kinetic liquid splashes and guarantee constant 5600K color consistency.</p>
      </div>

      <div class="editorial-pillar-card">
        <div class="pillar-top">
          <div class="pillar-icon-box lavender">⚙️</div>
          <span class="pillar-index">RIGS</span>
        </div>
        <h3 class="pillar-heading">Motorized Indexing Rails</h3>
        <p class="pillar-copy">Automated 20-micron focus rails for 60-slice jewellery focus-stacking composites and seamless 360-degree interactive product spin tables.</p>
      </div>

      <div class="editorial-pillar-card">
        <div class="pillar-top">
          <div class="pillar-icon-box yellow">🖥️</div>
          <span class="pillar-index">COLOR</span>
        </div>
        <h3 class="pillar-heading">Color Managed Flow</h3>
        <p class="pillar-copy">EIZO ColorEdge hardware-calibrated proofing monitors, spectrophotometer audits, and custom ICC profiles ensuring &Delta;E &lt; 1.0 color accuracy.</p>
      </div>
    </div>
  </div>
</section>

<!-- Core Leadership Team (With Real Portraits) -->
<section class="section section-services">
  <div class="container">
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-orange">Our Artisans</span>
      </div>
      <h2 class="section-title-editorial">Meet Our Creative &amp; Production Directors</h2>
      <p class="section-sub-editorial">
        A multidisciplinary team of lighting engineers, prop stylists, art directors, and master digital retouchers.
      </p>
    </div>

    <div class="editorial-services-grid">
      <div class="service-editorial-card" style="text-align: center;">
        <div class="card-media-wrapper" style="aspect-ratio: 1 / 1;">
          <img src="<?= asset_url('assets/images/about/team-marcus.jpg') ?>" alt="Marcus Vance, Creative Director" width="400" height="400" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <div class="card-content-wrapper" style="align-items: center;">
          <h3 class="service-card-heading">Marcus Vance</h3>
          <span class="badge badge-orange" style="margin-bottom: 0.75rem;">Creative Director</span>
          <p class="service-card-summary">
            Over 15 years directing commercial fashion and luxury brand campaigns in New York and Paris. Passionate about narrative staging and kinetic capture.
          </p>
        </div>
      </div>

      <div class="service-editorial-card" style="text-align: center;">
        <div class="card-media-wrapper" style="aspect-ratio: 1 / 1;">
          <img src="<?= asset_url('assets/images/about/team-elena.jpg') ?>" alt="Elena Rostova, Lead Lighting Engineer" width="400" height="400" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <div class="card-content-wrapper" style="align-items: center;">
          <h3 class="service-card-heading">Elena Rostova</h3>
          <span class="badge badge-indigo" style="margin-bottom: 0.75rem;">Lead Lighting Engineer</span>
          <p class="service-card-summary">
            Former industrial optics technician specializing in complex reflective surfaces, macro jewellery focus stacking, and anti-glare polarization geometry.
          </p>
        </div>
      </div>

      <div class="service-editorial-card" style="text-align: center;">
        <div class="card-media-wrapper" style="aspect-ratio: 1 / 1;">
          <img src="<?= asset_url('assets/images/about/team-david.jpg') ?>" alt="David Chen, Head of Digital Post-Production" width="400" height="400" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <div class="card-content-wrapper" style="align-items: center;">
          <h3 class="service-card-heading">David Chen</h3>
          <span class="badge badge-lime" style="margin-bottom: 0.75rem;">Head of Digital Post-Production</span>
          <p class="service-card-summary">
            Oversees our in-house retouching bay. Expert in frequency separation, vector clipping paths, ghost mannequin jointing, and multi-format web optimization.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Studio Commitments & Guarantees -->
<section class="section section-pillars">
  <div class="container">
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-blue">Our Standards</span>
      </div>
      <h2 class="section-title-editorial">The Ecomshoot Quality Standard</h2>
      <p class="section-sub-editorial">
        Four non-negotiable operational commitments that safeguard your brand reputation and bottom line.
      </p>
    </div>

    <div class="editorial-pillars-grid">
      <div class="editorial-pillar-card">
        <div class="pillar-top">
          <div class="pillar-icon-box blue">✓</div>
          <span class="pillar-index">01</span>
        </div>
        <h3 class="pillar-heading">Zero AI Hallucinations</h3>
        <p class="pillar-copy">We shoot genuine physical merchandise under authentic optical glass. No fake synthetic renders that mislead customers and drive up returns.</p>
      </div>

      <div class="editorial-pillar-card">
        <div class="pillar-top">
          <div class="pillar-icon-box coral">✓</div>
          <span class="pillar-index">02</span>
        </div>
        <h3 class="pillar-heading">100% Hand Clipping</h3>
        <p class="pillar-copy">We never use fuzzy automated cutout scripts. Every product outline is meticulously vectorized by senior retouchers with the Photoshop Pen Tool.</p>
      </div>

      <div class="editorial-pillar-card">
        <div class="pillar-top">
          <div class="pillar-icon-box lavender">✓</div>
          <span class="pillar-index">03</span>
        </div>
        <h3 class="pillar-heading">Perpetual Commercial Usage</h3>
        <p class="pillar-copy">Every deliverable includes unlimited, worldwide, perpetual commercial usage rights across paid ads, websites, packaging, and billboards.</p>
      </div>

      <div class="editorial-pillar-card">
        <div class="pillar-top">
          <div class="pillar-icon-box yellow">✓</div>
          <span class="pillar-index">04</span>
        </div>
        <h3 class="pillar-heading">Guaranteed Timelines</h3>
        <p class="pillar-copy">We honor strict production schedules agreed upon during pre-production scoping, with real-time tracking links provided to your team.</p>
      </div>
    </div>
  </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
