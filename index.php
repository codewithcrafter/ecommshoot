<?php
/**
 * ECOMSHOOT - Home Page
 * Award-Winning Creative Commercial Product Photography & Production Studio
 */

$pageIdentifier = 'home';
require_once __DIR__ . '/config/config.php';

// Fetch all 9 published services
$db = Database::getInstance()->getConnection();
$services = $db->query("SELECT * FROM services WHERE status = 'published' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest 3 published blogs
$blogs = $db->query("SELECT b.*, c.name as category_name FROM blogs b LEFT JOIN blog_categories c ON b.category_id = c.id WHERE b.status = 'published' ORDER BY b.published_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

require_once INCLUDES_PATH . '/header.php';
?>

<!-- SECTION A – HERO CAMPAIGN -->
<section class="hero-section">
  <div class="container hero-container">
    <div class="hero-split-grid">
      
      <!-- Hero Left: Brand Narrative & Conversion Actions -->
      <div class="hero-text-col">
        <div class="hero-live-badge">
          <span class="live-dot pulse-lime"></span>
          <span>NEW YORK COMMERCIAL STUDIO · HIGH-SPEED 150MP TETHERED PRODUCTION</span>
        </div>
        
        <h1 class="hero-heading">
          WE MAKE PRODUCTS LOOK <span class="highlight-creative">UNFORGETTABLE.</span>
        </h1>
        
        <p class="hero-lead">
          From high-impact product photography to lifestyle campaigns, Ecomshoot creates visual content that helps brands stand out across every digital storefront.
        </p>

        <div class="hero-cta-group">
          <a href="<?= url('contact.php') ?>" class="btn btn-lg btn-primary">Book a Shoot</a>
          <a href="#portfolio" class="btn btn-lg btn-outline-white">Explore Our Work</a>
        </div>

        <!-- Verified Studio Metric Counters -->
        <div class="hero-proof-bar">
          <div class="proof-item">
            <span class="proof-number">250K+</span>
            <span class="proof-label">SKUs Photographed</span>
          </div>
          <div class="proof-sep"></div>
          <div class="proof-item">
            <span class="proof-number">+38%</span>
            <span class="proof-label">Average Conversion Lift</span>
          </div>
          <div class="proof-sep"></div>
          <div class="proof-item">
            <span class="proof-number">99.8%</span>
            <span class="proof-label">Color Matching Fidelity</span>
          </div>
        </div>
      </div>

      <!-- Hero Right: Carefully Composed Editorial Photography Layout -->
      <div class="hero-media-col">
        <div class="hero-composition">
          <!-- Primary Large Showcase Frame -->
          <div class="hero-frame-primary">
            <img src="<?= asset_url('assets/images/hero/hero-main.jpg') ?>" 
                 alt="Luxury botanical cosmetics and skincare commercial campaign photography" 
                 width="640" 
                 height="480" 
                 loading="eager"
                 decoding="async"
                 class="hero-img-main">
          </div>

          <!-- Secondary Overlapping Offset Frame (Diamond Fire Macro) -->
          <div class="hero-frame-secondary">
            <img src="<?= asset_url('assets/images/services/jewellery-product-photography.jpg') ?>" 
                 alt="Diamond solitaire ring macro detail photography" 
                 width="220" 
                 height="220" 
                 loading="eager"
                 class="hero-img-inset">
            <div class="inset-caption">60-Slice Focus Stacking</div>
          </div>

          <!-- Floating Optical Spec Badges -->
          <div class="hero-float-badge float-top-left">
            <div class="float-icon-wrap icon-indigo">📸</div>
            <div class="float-text-wrap">
              <span class="float-title">Phase One 150MP</span>
              <span class="float-sub">Medium Format Acuity</span>
            </div>
          </div>

          <div class="hero-float-badge float-bottom-right">
            <div class="float-icon-wrap icon-lime">✓</div>
            <div class="float-text-wrap">
              <span class="float-title">Amazon Compliant</span>
              <span class="float-sub">RGB 255 Pure White</span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- TRUST & STUDIO STANDARDS STRIP -->
<section class="capabilities-strip">
  <div class="container">
    <div class="strip-header">
      <span class="strip-tag">OPTICAL BENCHMARKS</span>
      <span class="strip-label">Commercial Photography &amp; Art Direction Standards</span>
    </div>
    <div class="strip-grid">
      <div class="strip-item">
        <span class="strip-check">✓</span>
        <div>
          <strong>Phase One 150MP</strong>
          <span>Medium-format resolution with infinite zoom depth</span>
        </div>
      </div>
      <div class="strip-item">
        <span class="strip-check">✓</span>
        <div>
          <strong>Broncolor Lighting</strong>
          <span>Calibrated parabolic strobes with 5600K constant output</span>
        </div>
      </div>
      <div class="strip-item">
        <span class="strip-check">✓</span>
        <div>
          <strong>Spectral Color Fidelity</strong>
          <span>Hardware profiled with X-Rite swatches maintaining &Delta;E &lt; 1.0</span>
        </div>
      </div>
      <div class="strip-item">
        <span class="strip-check">✓</span>
        <div>
          <strong>In-House Styling Bay</strong>
          <span>Dedicated prep, garment steaming &amp; extensive prop library</span>
        </div>
      </div>
      <div class="strip-item">
        <span class="strip-check">✓</span>
        <div>
          <strong>48-Hour Turnaround</strong>
          <span>Expedited rush pipelines for seasonal launches</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SECTION B – SELECTED SHOOTS (Prominent Photography Portfolio) -->
<section class="section section-portfolio" id="portfolio">
  <div class="container">
    
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-orange">Selected Shoots</span>
      </div>
      <h2 class="section-title-editorial">Art-Directed Commercial Portfolio</h2>
      <p class="section-sub-editorial">
        Explore recent campaigns across beauty, luxury jewellery, apparel, consumer electronics, and dynamic splash captures.
      </p>
    </div>

    <!-- Category Filter Controls -->
    <div class="portfolio-filter-row">
      <button class="portfolio-pill-btn active" data-filter="all">All Shoots</button>
      <button class="portfolio-pill-btn" data-filter="beauty">Beauty &amp; Cosmetics</button>
      <button class="portfolio-pill-btn" data-filter="fashion">Fashion &amp; Apparel</button>
      <button class="portfolio-pill-btn" data-filter="jewellery">Luxury Jewellery</button>
      <button class="portfolio-pill-btn" data-filter="electronics">Electronics</button>
      <button class="portfolio-pill-btn" data-filter="lifestyle">Lifestyle</button>
      <button class="portfolio-pill-btn" data-filter="creative">Creative Campaigns</button>
    </div>

    <!-- Asymmetrical Gallery Grid with Real Commercial Photography -->
    <div class="asymmetric-portfolio-grid">
      
      <!-- Shoot 1: Creative Botanical Perfume Splash (Creative Campaigns) -->
      <div class="portfolio-grid-item span-7" data-category="creative">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-1.jpg') ?>" 
               alt="Luxury botanical perfume with liquid refraction and splash droplets" 
               width="700" 
               height="450" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Creative Product Campaign</span>
            <div class="overlay-title">Botanical Fragrance Refraction</div>
            <div class="overlay-sub">1/8000s High-Speed Kinetic Splash · Broncolor Scoro</div>
            <a href="<?= url('creative-product-photography.php') ?>" class="overlay-link">Explore Creative Discipline &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Shoot 2: Matte ANC Headphones (Electronics) -->
      <div class="portfolio-grid-item span-5" data-category="electronics">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-2.jpg') ?>" 
               alt="Wireless ANC Studio Headphones on dark pedestal with polarized lighting" 
               width="500" 
               height="450" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Consumer Electronics</span>
            <div class="overlay-title">Matte ANC Headphones</div>
            <div class="overlay-sub">Cross-Polarized Anti-Glare Lighting · Phase One 150MP</div>
            <a href="<?= url('ecommerce-product-photography.php') ?>" class="overlay-link">Explore E-Commerce Discipline &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Shoot 3: Diamond Solitaire Ring (Jewellery) -->
      <div class="portfolio-grid-item span-4" data-category="jewellery">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-3.jpg') ?>" 
               alt="Diamond Solitaire Ring 60-Slice Focus Stacking Macro" 
               width="400" 
               height="380" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Luxury Jewellery</span>
            <div class="overlay-title">Diamond Solitaire Ring</div>
            <div class="overlay-sub">60-Slice Focus Stacking · Fiber Optic Sparkle Rigs</div>
            <a href="<?= url('jewellery-product-photography.php') ?>" class="overlay-link">Explore Jewellery Discipline &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Shoot 4: Artisanal Ceramicware (Lifestyle) -->
      <div class="portfolio-grid-item span-4" data-category="lifestyle">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-4.jpg') ?>" 
               alt="Minimalist Ceramic Homeware staged in morning sunlight" 
               width="400" 
               height="380" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Lifestyle &amp; Living</span>
            <div class="overlay-title">Artisanal Ceramicware</div>
            <div class="overlay-sub">Warm Natural Morning Light Simulation · Architectural Staging</div>
            <a href="<?= url('lifestyle-product-photography.php') ?>" class="overlay-link">Explore Lifestyle Discipline &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Shoot 5: Tailored Wool Blazer (Fashion) -->
      <div class="portfolio-grid-item span-4" data-category="fashion">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-5.jpg') ?>" 
               alt="Tailored Wool Blazer on model with natural drape" 
               width="400" 
               height="380" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Fashion &amp; Apparel</span>
            <div class="overlay-title">Tailored Wool Blazer</div>
            <div class="overlay-sub">Runway Lighting · Calibrated Fabric Drape Fidelity</div>
            <a href="<?= url('fashion-product-photography.php') ?>" class="overlay-link">Explore Fashion Discipline &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Shoot 6: Cold-Pressed Citrus Splash (Creative Campaigns) -->
      <div class="portfolio-grid-item span-5" data-category="creative">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-6.jpg') ?>" 
               alt="Cold-Pressed Artisan Cocktail with splash droplet freezing" 
               width="500" 
               height="420" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Creative Beverage</span>
            <div class="overlay-title">Cold-Pressed Beverage</div>
            <div class="overlay-sub">1/10000s Kinetic Action Freezing · Micro Condensation</div>
            <a href="<?= url('creative-product-photography.php') ?>" class="overlay-link">Explore Creative Discipline &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Shoot 7: Botanical Face Serum (Beauty & Cosmetics) -->
      <div class="portfolio-grid-item span-7" data-category="beauty">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-7.jpg') ?>" 
               alt="Luxury botanical skincare serum with amber bottle and golden highlights" 
               width="700" 
               height="420" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Beauty &amp; Cosmetics</span>
            <div class="overlay-title">Amber Botanical Serum</div>
            <div class="overlay-sub">Diffused Wraps · Frosted Glass Reflection Control</div>
            <a href="<?= url('product-photography.php') ?>" class="overlay-link">Explore Product Discipline &rarr;</a>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- SECTION C – WHAT WE SHOOT (All 9 Commercial Services) -->
<section class="section section-services" id="services">
  <div class="container">
    
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-indigo">What We Shoot</span>
      </div>
      <h2 class="section-title-editorial">Specialized Commercial Photography Disciplines</h2>
      <p class="section-sub-editorial">
        Every product category demands tailored optical precision, custom light shaping, and meticulous digital finishing. Explore all nine dedicated disciplines.
      </p>
    </div>

    <!-- Editorial Services Grid -->
    <div class="editorial-services-grid">
      <?php foreach ($services as $idx => $svc): ?>
        <article class="service-editorial-card">
          <div class="card-media-wrapper">
            <img src="<?= asset_url($svc['featured_image'] ?: 'assets/images/services/' . $svc['slug'] . '.jpg') ?>" 
                 alt="<?= e($svc['title']) ?> commercial photography sample" 
                 width="400" 
                 height="250" 
                 loading="lazy"
                 class="service-card-photo">
            <?php if (!empty($svc['badge'])): ?>
              <span class="service-card-badge"><?= e($svc['badge']) ?></span>
            <?php endif; ?>
          </div>
          
          <div class="card-content-wrapper">
            <div class="card-meta-line">
              <span class="discipline-number">DISCIPLINE 0<?= ($idx + 1) ?></span>
            </div>
            <h3 class="service-card-heading">
              <a href="<?= url($svc['file_name']) ?>"><?= e($svc['title']) ?></a>
            </h3>
            <p class="service-card-summary">
              <?= e($svc['short_description']) ?>
            </p>
            <div class="service-card-footer">
              <a href="<?= url($svc['file_name']) ?>" class="service-link-arrow">
                <span>Explore Discipline</span>
                <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" /></svg>
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="services-center-action">
      <a href="<?= url('services.php') ?>" class="btn btn-lg btn-secondary">View Complete Services Directory</a>
    </div>

  </div>
</section>

<!-- SECTION D – WHY ECOMSHOOT (Image & Text Composition) -->
<section class="section section-pillars">
  <div class="container">
    
    <div class="section-why-split">
      
      <!-- Left: Studio Visuals -->
      <div class="why-media-frame">
        <img src="<?= asset_url('assets/images/about/studio-bay.jpg') ?>" 
             alt="Ecomshoot state of the art New York commercial photography studio" 
             width="600" 
             height="500" 
             loading="lazy">
        <div class="why-floating-card">
          <div class="why-floating-title">12,000 SQ. FT. PRODUCTION HOUSE</div>
          <div class="why-floating-desc">Located in New York City with live client tethering, specialized product prep bays, and motorized 360 turntables.</div>
        </div>
      </div>

      <!-- Right: Commercial Approach & 4 Pillars -->
      <div>
        <span class="badge badge-orange">Why Ecomshoot</span>
        <h2 class="editorial-h2" style="margin-top: 0.75rem; margin-bottom: 1rem;">
          Engineering Product Imagery That Commands Decisive Checkouts
        </h2>
        <p class="about-lead">
          We do not treat photography as simple documentation. Every single SKU is treated as a strategic investment asset engineered to sell across Shopify, Amazon, and luxury retail channels.
        </p>

        <div class="editorial-pillars-grid">
          <div class="editorial-pillar-card">
            <div class="pillar-top">
              <div class="pillar-icon-box">🔬</div>
              <span class="pillar-index">01</span>
            </div>
            <h3 class="pillar-heading">150MP Optical Acuity</h3>
            <p class="pillar-copy">Phase One medium format systems providing infinite crop latitude, high-density zoom capability, and raw texture definition that sells.</p>
          </div>

          <div class="editorial-pillar-card">
            <div class="pillar-top">
              <div class="pillar-icon-box">🎨</div>
              <span class="pillar-index">02</span>
            </div>
            <h3 class="pillar-heading">Spectral Color Fidelity</h3>
            <p class="pillar-copy">Hardware calibrated with X-Rite ColorCheckers, guaranteeing &Delta;E &lt; 1.0 to prevent costly customer product returns.</p>
          </div>

          <div class="editorial-pillar-card">
            <div class="pillar-top">
              <div class="pillar-icon-box">⚡</div>
              <span class="pillar-index">03</span>
            </div>
            <h3 class="pillar-heading">High-Volume Batching</h3>
            <p class="pillar-copy">Dedicated prep and shoot bays capable of capturing and delivering 500+ SKUs weekly with zero variance across seasonal collections.</p>
          </div>

          <div class="editorial-pillar-card">
            <div class="pillar-top">
              <div class="pillar-icon-box">🛡️</div>
              <span class="pillar-index">04</span>
            </div>
            <h3 class="pillar-heading">Marketplace Certified</h3>
            <p class="pillar-copy">Certified RGB 255 pure white packshots meeting all Amazon Vendor Central, Shopify Plus, and Google Shopping specs.</p>
          </div>
        </div>

        <div style="margin-top: 2rem;">
          <a href="<?= url('about.php') ?>" class="btn btn-primary">Read More About Our Studio &rarr;</a>
        </div>
      </div>

    </div>

  </div>
</section>

<!-- SECTION E – BEHIND THE SHOOT (Production Workflow with Studio Visuals) -->
<section class="section section-process">
  <div class="container">
    
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-lime">Behind The Shoot</span>
      </div>
      <h2 class="section-title-editorial">Our 5-Stage Studio Production Pipeline</h2>
      <p class="section-sub-editorial">
        From initial brief to final cloud asset delivery, our workflow is transparent, collaborative, and engineered for speed.
      </p>
    </div>

    <div class="process-steps-row">
      <div class="process-step-item">
        <div class="step-num-pill">01</div>
        <h4 class="step-title">Creative Brief</h4>
        <p class="step-desc">Establish art direction, moodboards, required angles, aspect ratios, and marketplace compliance needs.</p>
      </div>

      <div class="process-step-item">
        <div class="step-num-pill">02</div>
        <h4 class="step-title">Concept &amp; Planning</h4>
        <p class="step-desc">Products arrive at our New York facility, cataloged, barcoded, steamed, polished, and scheduled on shoot bays.</p>
      </div>

      <div class="process-step-item">
        <div class="step-num-pill">03</div>
        <h4 class="step-title">Studio Shooting</h4>
        <p class="step-desc">Captured on 150MP medium format with live remote client links for real-time desktop approval.</p>
      </div>

      <div class="process-step-item">
        <div class="step-num-pill">04</div>
        <h4 class="step-title">Editing &amp; Retouching</h4>
        <p class="step-desc">Hand-drawn clipping paths, frequency separation, and color grading matching physical master swatches.</p>
      </div>

      <div class="process-step-item">
        <div class="step-num-pill">05</div>
        <h4 class="step-title">Final Delivery</h4>
        <p class="step-desc">Instant cloud delivery formatted in WebP, TIFF, and high-res JPEG ready for immediate deployment.</p>
      </div>
    </div>

  </div>
</section>

<!-- SECTION F – INDUSTRIES WE SERVE -->
<section class="section section-industries">
  <div class="container">
    
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-indigo">Market Verticals</span>
      </div>
      <h2 class="section-title-editorial">Specialized Optical Setups for Every Sector</h2>
      <p class="section-sub-editorial">
        Different materials reflect, scatter, and absorb light uniquely. We engineer tailored lighting rigs for each category.
      </p>
    </div>

    <div class="editorial-industries-grid">
      <div class="industry-box">
        <div class="industry-icon">✨</div>
        <h3 class="industry-title">Beauty &amp; Cosmetics</h3>
        <p class="industry-copy">Diffused wraps for frosted glass bottles, iridescent oil swatches, and texture drops with zero blinding glare.</p>
      </div>

      <div class="industry-box">
        <div class="industry-icon">👗</div>
        <h3 class="industry-title">Fashion &amp; Apparel</h3>
        <p class="industry-copy">Volumetric 3D ghost mannequin framing and seam-to-hem drape fidelity capturing true textile weight.</p>
      </div>

      <div class="industry-box">
        <div class="industry-icon">💎</div>
        <h3 class="industry-title">Luxury Jewellery</h3>
        <p class="industry-copy">Ultrasonic cleaning, 60-slice focus stacking, and isolated diamond fire sparkler modifiers.</p>
      </div>

      <div class="industry-box">
        <div class="industry-icon">🛋️</div>
        <h3 class="industry-title">Lifestyle &amp; Living</h3>
        <p class="industry-copy">Architectural staging sets, natural limestone slabs, and warm simulated morning light.</p>
      </div>

      <div class="industry-box">
        <div class="industry-icon">🔌</div>
        <h3 class="industry-title">Consumer Electronics</h3>
        <p class="industry-copy">Cross-polarization filters for OLED displays, micro ports, and matte metal chassis without hot-spots.</p>
      </div>

      <div class="industry-box">
        <div class="industry-icon">🍷</div>
        <h3 class="industry-title">Home &amp; Beverage</h3>
        <p class="industry-copy">Kinetic splashes, micro condensation beading, and culinary prop styling that stimulates appetite.</p>
      </div>
    </div>

  </div>
</section>

<!-- SECTION G – FEATURED INSIGHTS -->
<section class="section section-insights">
  <div class="container">
    
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-orange">Studio Intelligence</span>
      </div>
      <h2 class="section-title-editorial">Masterclasses, Case Studies &amp; Guides</h2>
      <p class="section-sub-editorial">
        Actionable visual strategy, lighting blueprints, and conversion optimization insights written by our studio leads.
      </p>
    </div>

    <div class="editorial-blog-grid">
      <?php foreach ($blogs as $b): ?>
        <article class="editorial-blog-card">
          <div class="blog-media-wrap">
            <img src="<?= asset_url($b['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg') ?>" 
                 alt="<?= e($b['title']) ?>" 
                 width="400" 
                 height="250" 
                 loading="lazy">
            <span class="blog-cat-pill"><?= e($b['category_name'] ?: 'Strategy') ?></span>
          </div>
          <div class="blog-body-wrap">
            <div class="blog-time-meta"><?= e($b['read_time']) ?> • Published <?= date('M d, Y', strtotime($b['published_at'] ?? 'now')) ?></div>
            <h3 class="blog-heading">
              <a href="<?= blog_url($b['id']) ?>"><?= e($b['title']) ?></a>
            </h3>
            <p class="blog-excerpt-copy">
              <?= e(mb_strimwidth($b['excerpt'], 0, 115, '...')) ?>
            </p>
            <div class="blog-footer-meta">
              <span class="blog-author-tag">By <?= e($b['author_name']) ?></span>
              <a href="<?= blog_url($b['id']) ?>" class="blog-read-link">
                Read Guide &rarr;
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="blog-center-cta">
      <a href="<?= url('blog.php') ?>" class="btn btn-secondary">Browse All Studio Insights</a>
    </div>

  </div>
</section>

<!-- SECTION H – CLIENT TESTIMONIALS (Genuine Client Feedback with Real Portraits) -->
<section class="section section-testimonials" id="testimonials">
  <div class="container">
    
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-indigo">Client Feedback</span>
      </div>
      <h2 class="section-title-editorial">Trusted by Fast-Growing E-Commerce Leaders</h2>
      <p class="section-sub-editorial">
        Read firsthand accounts from founders, creative directors, and brand operators who scaled sales with Ecomshoot.
      </p>
    </div>

    <!-- Testimonials Slider Wrapper (Grid on Desktop, Swipeable Carousel on Mobile) -->
    <div class="testimonials-slider-wrapper">
      <div class="editorial-testimonials-grid testimonials-slider-track" id="testimonialTrack">
        <div class="testimonial-box testimonial-slide">
          <div class="testimonial-stars-row">★★★★★</div>
          <p class="testimonial-text">
            "Switching our 350-SKU catalog to Ecomshoot was the single highest-ROI decision we made last quarter. Our Shopify checkout conversion jumped from 2.1% to 3.4% within three weeks of updating the imagery."
          </p>
          <div class="testimonial-user">
            <img src="<?= asset_url('assets/images/testimonials/avatar-1.jpg') ?>" alt="Sarah Vance" width="48" height="48" loading="lazy" class="user-avatar">
            <div>
              <div class="user-name">Sarah Vance</div>
              <div class="user-role">Founder, Lumina Botanical Skincare</div>
            </div>
          </div>
        </div>

        <div class="testimonial-box testimonial-slide">
          <div class="testimonial-stars-row">★★★★★</div>
          <p class="testimonial-text">
            "Their pure white background packshots passed Amazon Vendor Central automated compliance without a single hitch. The color accuracy on our cashmere apparel is flawless—it cut our return rate by nearly 28%."
          </p>
          <div class="testimonial-user">
            <img src="<?= asset_url('assets/images/testimonials/avatar-2.jpg') ?>" alt="Marcus Keller" width="48" height="48" loading="lazy" class="user-avatar">
            <div>
              <div class="user-name">Marcus Keller</div>
              <div class="user-role">Head of Merchandising, Aura Modern Apparel</div>
            </div>
          </div>
        </div>

        <div class="testimonial-box testimonial-slide">
          <div class="testimonial-stars-row">★★★★★</div>
          <p class="testimonial-text">
            "Photographing diamond rings with micro focus stacking is virtually impossible for most agencies. Ecomshoot rendered the metal sheen and diamond fire with mind-blowing clarity. They are true master artists."
          </p>
          <div class="testimonial-user">
            <img src="<?= asset_url('assets/images/testimonials/avatar-3.jpg') ?>" alt="Julian Laurent" width="48" height="48" loading="lazy" class="user-avatar">
            <div>
              <div class="user-name">Julian Laurent</div>
              <div class="user-role">Creative Director, Laurent Fine Jewels</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile Slider Controls (Hidden on Desktop) -->
      <div class="testimonials-mobile-controls" aria-label="Testimonial slider controls">
        <button type="button" class="slider-arrow slider-arrow-prev" id="testimonialPrev" aria-label="Previous testimonial">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        </button>
        <div class="slider-dots" id="testimonialDots">
          <button type="button" class="slider-dot active" data-slide="0" aria-label="Go to testimonial 1"></button>
          <button type="button" class="slider-dot" data-slide="1" aria-label="Go to testimonial 2"></button>
          <button type="button" class="slider-dot" data-slide="2" aria-label="Go to testimonial 3"></button>
        </div>
        <button type="button" class="slider-arrow slider-arrow-next" id="testimonialNext" aria-label="Next testimonial">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>

  </div>
</section>

<!-- SECTION I – FAQ ACCORDION & SINGLE-SKU TEST SHOOT CTA -->
<section class="section section-faq">
  <div class="container">
    
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-orange">Frequently Answered</span>
      </div>
      <h2 class="section-title-editorial">Common Questions About Working with Ecomshoot</h2>
      <p class="section-sub-editorial">
        Everything you need to know about sample shipments, shoot turnarounds, licensing rights, and image deliverables.
      </p>
    </div>

    <div class="faq-accordion-container">
      <div class="faq-item active">
        <button class="faq-question-btn" aria-expanded="true">
          <span>How does product shipping and sample intake work?</span>
          <svg class="faq-chevron" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>
        <div class="faq-answer-panel">
          Once your shoot is scheduled, we issue a dedicated studio intake shipping label and inventory manifest. You ship your products directly to our New York studio suite. Our team unpacks, inspects, and preps every piece. Once shooting is finalized, we return your items via insured courier or donate them to local charities upon request.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question-btn" aria-expanded="false">
          <span>What is the standard production turnaround time?</span>
          <svg class="faq-chevron" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>
        <div class="faq-answer-panel">
          Our standard studio turnaround is 5 to 7 business days from the physical delivery of your items to final retouched file delivery. For urgent seasonal launches or holiday campaigns, expedited 48-hour rush production is available.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question-btn" aria-expanded="false">
          <span>Do you provide commercial usage and licensing rights?</span>
          <svg class="faq-chevron" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>
        <div class="faq-answer-panel">
          Yes! All final delivered assets come with perpetual, worldwide, royalty-free commercial usage rights across your website, Amazon listings, print marketing, social media ads, and digital billboards.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question-btn" aria-expanded="false">
          <span>Can we attend the shoot remotely in real time?</span>
          <svg class="faq-chevron" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>
        <div class="faq-answer-panel">
          Absolutely. Over 80% of our clients join via our live tethered web portal, allowing you to view 100MP captures on your desktop in real-time and provide instant direction from anywhere in the world.
        </div>
      </div>
    </div>

  </div>
</section>

<!-- SINGLE SKU TEST SHOOT CALL TO ACTION -->
<section class="section section-test-shoot">
  <div class="container">
    <div class="test-shoot-box">
      <div class="test-shoot-content">
        <span class="badge badge-lime">Sample Shoot Available</span>
        <h3 class="test-shoot-heading">Experience Our Quality With a Single SKU Test</h3>
        <p class="test-shoot-desc">
          Send us one hero product. We will light, shoot, and retouch it to your exact specifications so you can evaluate our standard firsthand before booking a full catalog shoot.
        </p>
      </div>
      <div class="test-shoot-action">
        <a href="<?= url('contact.php') ?>" class="btn btn-lg btn-primary">Request Test Shot &rarr;</a>
      </div>
    </div>
  </div>
</section>

<!-- SECTION J – CREATIVE FOOTER (Included via global footer component) -->
<?php require_once INCLUDES_PATH . '/footer.php'; ?>
