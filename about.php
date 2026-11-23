<?php
/**
 * ECOMSHOOT - About Us Page
 * Premium Commercial Photography & Production Studio
 */

$pageIdentifier = 'about';
require_once __DIR__ . '/config/config.php';
require_once INCLUDES_PATH . '/header.php';
?>

<main class="about-ui-redesign">

<!-- Page Header Banner -->
<section class="page-header-banner">
  <div class="container">
    <div class="page-header-content">
      <span class="badge badge-orange">Our Studio Heritage</span>
      <h1 class="page-header-title">The Commercial Photography Studio <span class="font-editorial shimmer-coral">Engineered for Modern Digital Commerce</span></h1>
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
          Ecomshoot was established in Delhi to solve this dilemma. We built a specialized commercial production studio designed from the ground up to satisfy the rigorous technical requirements of Amazon, Shopify, Flipkart, and omnichannel retailers without sacrificing editorial warmth or tactile realism.
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
      <h2 class="section-title-editorial">Hardware &amp; <span class="font-editorial shimmer-purple">Studio Capabilities</span></h2>
      <p class="section-sub-editorial">
        We invest relentlessly in high-resolution optics, calibrated lighting systems, and specialized staging rigs to deliver pristine clarity.
      </p>
    </div>

    <div class="editorial-pillars-grid">
      <div class="editorial-pillar-card luxury-card studio-reveal">
        <div class="pillar-top">
          <div class="pillar-icon-box blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
          </div>
          <span class="pillar-index">OPTICS</span>
        </div>
        <h3 class="pillar-heading">High-Resolution Studio Acuity</h3>
        <p class="pillar-copy">High-resolution commercial digital systems and calibrated optics resolving fine micro-textures down to individual material weaves.</p>
      </div>

      <div class="editorial-pillar-card luxury-card studio-reveal">
        <div class="pillar-top">
          <div class="pillar-icon-box coral">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
          </div>
          <span class="pillar-index">LIGHT</span>
        </div>
        <h3 class="pillar-heading">Calibrated Studio Strobes</h3>
        <p class="pillar-copy">High-speed studio strobe and continuous lighting setups guaranteeing consistent 5600K daylight-balanced illumination.</p>
      </div>

      <div class="editorial-pillar-card luxury-card studio-reveal">
        <div class="pillar-top">
          <div class="pillar-icon-box lavender">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
          </div>
          <span class="pillar-index">RIGS</span>
        </div>
        <h3 class="pillar-heading">Motorized Indexing Rails</h3>
        <p class="pillar-copy">Multi-axis motorized focus rails for ultra-sharp focus-stacked composites and automated 360-degree interactive product turntable capture.</p>
      </div>

      <div class="editorial-pillar-card luxury-card studio-reveal">
        <div class="pillar-top">
          <div class="pillar-icon-box yellow">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
          </div>
          <span class="pillar-index">COLOR</span>
        </div>
        <h3 class="pillar-heading">Color Managed Flow</h3>
        <p class="pillar-copy">Hardware-calibrated proofing displays, spectrophotometer audits, and custom ICC profiles ensuring precise &Delta;E &lt; 1.0 color fidelity across all outputs.</p>
      </div>
    </div>
  </div>
</section>

<!-- Verified studio tools and production technology -->
<section class="section about-tools-section">
  <div class="container">
    <div class="section-editorial-header">
      <div class="header-tagline"><span class="badge badge-orange">Studio Toolkit</span></div>
      <h2 class="section-title-editorial">Our Tools &amp; <span class="font-editorial shimmer-purple">Technology</span></h2>
      <p class="section-sub-editorial">Verified capture, lighting, retouching and calibration tools used across our studio workflow.</p>
    </div>
    <div class="about-tools-grid">
      <article class="about-tool-card luxury-card studio-reveal">
        <div class="tool-icon-wrap">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
        </div>
        <h3>Camera Systems</h3>
        <p>High-resolution full-frame &amp; medium-format studio systems for ultra-fine product detail.</p>
      </article>
      <article class="about-tool-card luxury-card studio-reveal">
        <div class="tool-icon-wrap">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m14.31 8 5.74 9.94"/><path d="M9.69 8h11.48"/><path d="m7.38 12 5.74-9.94"/><path d="M9.69 16 3.95 6.06"/><path d="M14.31 16H2.83"/><path d="m16.62 12-5.74 9.94"/></svg>
        </div>
        <h3>Professional Lenses</h3>
        <p>Macro, tilt-shift, and prime optics calibrated for distortion-free product geometry.</p>
      </article>
      <article class="about-tool-card luxury-card studio-reveal">
        <div class="tool-icon-wrap">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        </div>
        <h3>Studio Lighting</h3>
        <p>Balanced strobe and continuous lighting setups ensuring consistent 5600K color temperature.</p>
      </article>
      <article class="about-tool-card luxury-card studio-reveal">
        <div class="tool-icon-wrap">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
        </div>
        <h3>Product Styling</h3>
        <p>Precision props, acrylic blocks, suspension rigs, and specialized staging surfaces.</p>
      </article>
      <article class="about-tool-card luxury-card studio-reveal">
        <div class="tool-icon-wrap">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
        </div>
        <h3>Macro Photography</h3>
        <p>Focus-stacking rails and high-magnification optics for intricate jewelry and texture captures.</p>
      </article>
      <article class="about-tool-card luxury-card studio-reveal">
        <div class="tool-icon-wrap">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
        </div>
        <h3>Colour Management</h3>
        <p>Hardware-calibrated display monitors, spectrophotometer targets, and custom ICC profiling.</p>
      </article>
      <article class="about-tool-card luxury-card studio-reveal">
        <div class="tool-icon-wrap">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
        </div>
        <h3>Post-Production</h3>
        <p>Multi-layer non-destructive editing, hand-drawn clipping paths, and natural shadow generation.</p>
      </article>
      <article class="about-tool-card luxury-card studio-reveal">
        <div class="tool-icon-wrap">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m21.64 3.64-1.28-1.28a1.21 1.21 0 0 0-1.72 0L2.36 18.64a1.21 1.21 0 0 0 0 1.72l1.28 1.28a1.2 1.2 0 0 0 1.72 0L21.64 5.36a1.2 1.2 0 0 0 0-1.72Z"/><path d="m14 7 3 3"/><path d="M5 6v4"/><path d="M19 14v4"/><path d="M10 2v2"/><path d="M7 8H3"/><path d="M21 16h-4"/><path d="M11 3H9"/></svg>
        </div>
        <h3>Retouching Workflow</h3>
        <p>Dust removal, scratch cleanup, frequency separation, and shape symmetry refinement.</p>
      </article>
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
      <h2 class="section-title-editorial">The Ecomshoot <span class="font-editorial shimmer-coral">Quality Standard</span></h2>
      <p class="section-sub-editorial">
        Four non-negotiable operational commitments that safeguard your brand reputation and bottom line.
      </p>
    </div>

    <div class="editorial-pillars-grid">
      <div class="editorial-pillar-card luxury-card studio-reveal">
        <div class="pillar-top">
          <div class="pillar-icon-box blue">✓</div>
          <span class="pillar-index">01</span>
        </div>
        <h3 class="pillar-heading">Zero AI Hallucinations</h3>
        <p class="pillar-copy">We shoot genuine physical merchandise under authentic optical glass. No fake synthetic renders that mislead customers and drive up returns.</p>
      </div>

      <div class="editorial-pillar-card luxury-card studio-reveal">
        <div class="pillar-top">
          <div class="pillar-icon-box coral">✓</div>
          <span class="pillar-index">02</span>
        </div>
        <h3 class="pillar-heading">100% Hand Clipping</h3>
        <p class="pillar-copy">We never use fuzzy automated cutout scripts. Every product outline is meticulously vectorized by senior retouchers with the Photoshop Pen Tool.</p>
      </div>

      <div class="editorial-pillar-card luxury-card studio-reveal">
        <div class="pillar-top">
          <div class="pillar-icon-box lavender">✓</div>
          <span class="pillar-index">03</span>
        </div>
        <h3 class="pillar-heading">Perpetual Commercial Usage</h3>
        <p class="pillar-copy">Every deliverable includes unlimited, worldwide, perpetual commercial usage rights across paid ads, websites, packaging, and billboards.</p>
      </div>

      <div class="editorial-pillar-card luxury-card studio-reveal">
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

</main>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>
