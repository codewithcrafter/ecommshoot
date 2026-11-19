<?php
/**
 * ECOMSHOOT - Home Page
 * Award-Winning Creative Commercial Product Photography & Production Studio
 */

$pageIdentifier = 'home';
require_once __DIR__ . '/config/config.php';

// Fetch published services (excluding discontinued services)
$db = Database::getInstance()->getConnection();
$services = [];
try {
    $services = $db->query("SELECT * FROM services WHERE status = 'published' AND slug != 'professional-product-photography' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Query Error [services]: " . $e->getMessage());
    $services = [];
}

// Fetch latest 3 published blogs
$blogs = [];
try {
    $blogs = $db->query("SELECT b.*, c.name as category_name FROM blogs b LEFT JOIN blog_categories c ON b.category_id = c.id WHERE b.status = 'published' ORDER BY b.published_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Query Error [blogs]: " . $e->getMessage());
    $blogs = [];
}

$flash = get_flash();

require_once INCLUDES_PATH . '/header.php';
?>

<main class="home-ui-redesign">

<!-- SECTION A – HERO CAMPAIGN -->
<section class="hero-section home-about-hero">
  <div class="container hero-container">
    <div class="hero-split-grid">
      
      <!-- Hero Left: Brand Narrative & Conversion Actions -->
      <div class="hero-text-col">
        <div class="hero-live-badge">
          <span class="live-dot pulse-lime"></span>
          <span>Delhi Commercial Studio | Amazon &amp; Shopify Ready Imagery</span>
        </div>
        
        <h1 class="hero-heading">
          Product Photography <span class="highlight-creative">Agency in Delhi</span>
        </h1>
        
        <p class="hero-lead">
          Every decision made by a customer happens in a matter of seconds. As product photographers in Delhi we create images that will sell your product. We create high quality images for brands selling on Amazon, Shopify, Flipkart and their own website.
        </p>

        <div class="hero-cta-group">
          <a href="<?= url('contact.php') ?>" class="btn btn-lg btn-primary">Book a Shoot</a>
          <a href="#portfolio" class="btn btn-lg btn-outline-white">See Our Work</a>
        </div>

        <!-- Verified Studio Metric Counters -->
        <div class="hero-proof-bar home-about-stats">
          <div class="proof-item">
            <span class="proof-number">250K+</span>
            <span class="proof-label">Images Delivered</span>
          </div>
          <div class="proof-item">
            <span class="proof-number">+38%</span>
            <span class="proof-label">Average Increase In CTR</span>
          </div>
          <div class="proof-item">
            <span class="proof-number">99.8%</span>
            <span class="proof-label">Approval Rate From Marketplace</span>
          </div>
        </div>
      </div>

      <!-- Hero Right: Direct Studio Enquiry Form -->
      <div class="hero-form-col">
        <div class="services-enquiry-card home-hero-enquiry-card" id="homeEnquiryForm">
          <div class="enquiry-card-header">
            <span class="enquiry-card-badge">Instant Studio Estimate</span>
            <h3 class="enquiry-card-title">Book a Photoshoot</h3>
          </div>
          <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>" style="margin-bottom: 1rem;">
              <?= e($flash['message']) ?>
            </div>
          <?php endif; ?>
          <form action="<?= url('submit-lead.php') ?>" method="POST" class="services-enquiry-form">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="source_page_url" value="">
            <input type="hidden" name="source_page_title" value="Homepage - Product Photography Agency in Delhi">
            <input type="hidden" name="source_page_type" value="home_hero">
            
            <div class="services-form-grid">
              <div class="form-group">
                <label class="form-label" for="home_hero_full_name">Full Name <span style="color:#FF6233;">*</span></label>
                <input class="form-control" id="home_hero_full_name" name="full_name" type="text" placeholder="e.g. Rahul Sharma" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="home_hero_email">Email Address <span style="color:#FF6233;">*</span></label>
                <input class="form-control" id="home_hero_email" name="email" type="email" placeholder="e.g. rahul@company.com" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="home_hero_mobile">Contact Number <span style="color:#FF6233;">*</span></label>
                <input class="form-control" id="home_hero_mobile" name="mobile" type="tel" placeholder="e.g. 9876543210" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="home_hero_required">Service Required <span style="color:#FF6233;">*</span></label>
                <select class="form-control form-select" id="home_hero_required" name="lead_source" required>
                  <option value="">Select a service</option>
                  <?php foreach ($services as $svc): ?>
                    <option value="<?= e($svc['title']) ?>"><?= e($svc['title']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="home_hero_message">Project Requirements / Message <span style="color:#FF6233;">*</span></label>
              <textarea class="form-control" id="home_hero_message" name="message" rows="3" placeholder="Tell us about your product line, shoot volume, or launch timeline..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary services-submit-btn" style="width: 100%;">
              <span>Submit Enquiry</span>
              <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="display:inline-block; vertical-align:middle; margin-left:6px;"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" /></svg>
            </button>
            <div class="lead-form-privacy" style="margin-top: 0.75rem; font-size: 0.78rem; color: #9CA3AF; text-align: center; display: flex; align-items: center; justify-content: center; gap: 6px;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
              <span>Strictly confidential. Studio team responds within 24 hours.</span>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- TRUST & STUDIO STANDARDS STRIP -->
<section class="capabilities-strip">
  <div class="container">
    <div class="strip-header">
      <span class="strip-tag">TRUST STRIP</span>
      <span class="strip-label">On Location Styling: Capture lifestyle images to show how your product is used.</span>
    </div>
    <div class="strip-grid">
      <div class="strip-item">
        <span class="strip-check">✓</span>
        <div>
          <strong>Fast Turnaround</strong>
          <span>Product photography sessions at our Delhi studio. 48-72 hour turnaround.</span>
        </div>
      </div>
      <div class="strip-item">
        <span class="strip-check">✓</span>
        <div>
          <strong>Compliance</strong>
          <span>All images will comply with Amazon, Flipkart &amp; Shopify guidelines.</span>
        </div>
      </div>
      <div class="strip-item">
        <span class="strip-check">✓</span>
        <div>
          <strong>Creative Direction</strong>
          <span>We include styling, props and creative planning.</span>
        </div>
      </div>
      <div class="strip-item">
        <span class="strip-check">✓</span>
        <div>
          <strong>Edited &amp; Retouched</strong>
          <span>All images will be colour corrected and clean for upload.</span>
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
      <h2 class="section-title-editorial">Commercial Art Directed Portfolio</h2>
      <p class="section-sub-editorial">
        Take a look at some of our recent shoots. We creatively plan each shoot to ensure your product sells. This includes shoots for beauty, fashion, jewellery, electronics and lifestyle.
      </p>
    </div>

    <!-- Asymmetrical Gallery Grid with Real Commercial Photography -->
    <div class="asymmetric-portfolio-grid">
      
      <!-- Shoot 1: Creative Botanical Perfume Splash (Creative Campaigns) -->
      <div class="portfolio-grid-item span-7">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-1.jpg') ?>" 
               alt="Luxury botanical perfume with liquid refraction and splash droplets" 
               width="700" 
               height="450" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Beauty &amp; Cosmetics</span>
            <div class="overlay-title">Botanical Fragrance</div>
            <div class="overlay-sub">Soft lighting and glass reflectors to create a luxury feel on shelf.</div>
            <a href="<?= service_url('creative-product-photography') ?>" class="overlay-link">View Case Study &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Shoot 2: Matte ANC Headphones (Electronics) -->
      <div class="portfolio-grid-item span-5">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-2.jpg') ?>" 
               alt="Wireless ANC Studio Headphones on dark pedestal with polarized lighting" 
               width="500" 
               height="450" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Electronics</span>
            <div class="overlay-title">Matte ANC Headphones</div>
            <div class="overlay-sub">Hard shadows and bold colours to create a sharp look.</div>
            <a href="<?= service_url('ecommerce-product-photography') ?>" class="overlay-link">View Case Study &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Shoot 3: Diamond Solitaire Ring (Jewellery) -->
      <div class="portfolio-grid-item span-4">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-3.jpg') ?>" 
               alt="Diamond Solitaire Ring 60-Slice Focus Stacking Macro" 
               width="400" 
               height="380" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Luxury Jewellery</span>
            <div class="overlay-title">Diamond Solitaire Ring</div>
            <div class="overlay-sub">Macro lighting to showcase sparkle and cut.</div>
            <a href="<?= service_url('jewellery-product-photography') ?>" class="overlay-link">View Case Study &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Shoot 4: Artisanal Ceramicware (Lifestyle) -->
      <div class="portfolio-grid-item span-4">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-4.jpg') ?>" 
               alt="Minimalist Ceramic Homeware staged in morning sunlight" 
               width="400" 
               height="380" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Food &amp; Beverage</span>
            <div class="overlay-title">Artisan Cornichons</div>
            <div class="overlay-sub">Fresh styling to make people want to open the jar.</div>
            <a href="<?= service_url('lifestyle-product-photography') ?>" class="overlay-link">View Case Study &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Shoot 5: Tailored Wool Blazer (Fashion) -->
      <div class="portfolio-grid-item span-4">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-5.jpg') ?>" 
               alt="Tailored Wool Blazer on model with natural drape" 
               width="400" 
               height="380" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Fashion &amp; Apparel</span>
            <div class="overlay-title">Tailored Wool Blazer</div>
            <div class="overlay-sub">Ghost mannequin and flat lay images to showcase fit, fabric and stitching.</div>
            <a href="<?= service_url('fashion-product-photography') ?>" class="overlay-link">View Case Study &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Shoot 6: Cold-Pressed Citrus Splash (Creative Campaigns) -->
      <div class="portfolio-grid-item span-5">
        <div class="portfolio-inner-frame">
          <img src="<?= asset_url('assets/images/portfolio/portfolio-6.jpg') ?>" 
               alt="Cold-Pressed Artisan Cocktail with splash droplet freezing" 
               width="500" 
               height="420" 
               loading="lazy">
          <div class="portfolio-hover-overlay">
            <span class="overlay-tag">Creative Campaign</span>
            <div class="overlay-title">Cold-Pressed Beverage</div>
            <div class="overlay-sub">Water splash, condensation and colour to create a refreshing look.</div>
            <a href="<?= service_url('creative-product-photography') ?>" class="overlay-link">View Case Study &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Shoot 7: Botanical Face Serum (Beauty & Cosmetics) -->
      <div class="portfolio-grid-item span-7">
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
            <a href="<?= service_url('product-photography') ?>" class="overlay-link">Explore Product Discipline &rarr;</a>
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
        <span class="badge badge-indigo">Our Services</span>
      </div>
      <h2 class="section-title-editorial">Product Photography in Delhi</h2>
      <p class="section-sub-editorial">
        Every product will need a different lighting, angle and styling. Choose from our range of services or combine a few for a complete shoot.
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
              <span class="discipline-number">SHOOT NO. 0<?= ($idx + 1) ?></span>
            </div>
            <h3 class="service-card-heading">
              <a href="<?= service_url($svc['slug']) ?>"><?= e($svc['title']) ?></a>
            </h3>
            <p class="service-card-summary">
              <?= e($svc['short_description']) ?>
            </p>
            <div class="service-card-footer">
              <a href="<?= service_url($svc['slug']) ?>" class="service-link-arrow">
                <span>View Service</span>
                <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" /></svg>
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="services-center-action">
      <a href="<?= url('services.php') ?>" class="btn btn-lg btn-secondary">View All Services</a>
    </div>

  </div>
</section>

<!-- SECTION D – WHY ECOMSHOOT (Image & Text Composition) -->
<section class="section section-pillars">
  <div class="container">
    
    <div class="section-why-split">
      
      <!-- Left: Studio Visuals (Commercial Photography Composition) -->
      <div class="why-media-composition">
        <div class="hero-composition why-composition">
          <!-- Primary Large Showcase Frame -->
          <div class="hero-frame-primary">
            <img src="<?= asset_url('assets/images/hero/hero-main.jpg') ?>" 
                 alt="Luxury botanical cosmetics and skincare commercial campaign photography" 
                 width="640" 
                 height="480" 
                 loading="lazy"
                 decoding="async"
                 class="hero-img-main">
          </div>

          <!-- Secondary Overlapping Offset Frame (Diamond Fire Macro) -->
          <div class="hero-frame-secondary">
            <img src="<?= asset_url('assets/images/services/jewellery-product-photography.jpg') ?>" 
                 alt="Diamond solitaire ring macro detail photography" 
                 width="220" 
                 height="220" 
                 loading="lazy"
                 class="hero-img-inset">
            <div class="inset-caption">60-Slice Focus Stacking</div>
          </div>

          <!-- Floating Optical Spec Badges -->
          <div class="hero-float-badge float-top-left">
            <div class="float-icon-wrap icon-indigo">📸</div>
            <div class="float-text-wrap">
              <span class="float-title">Studio Photography</span>
              <span class="float-sub">Controlled lighting for clean catalogue images.</span>
            </div>
          </div>

          <div class="hero-float-badge float-bottom-right">
            <div class="float-icon-wrap icon-lime">✓</div>
            <div class="float-text-wrap">
              <span class="float-title">Detail &amp; Macro</span>
              <span class="float-sub">Showcase the detail in your product with close up images.</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Commercial Approach & 4 Pillars -->
      <div>
        <span class="badge badge-orange">Why Ecomshoot</span>
        <h2 class="editorial-h2" style="margin-top: 0.75rem; margin-bottom: 1rem;">
          Why Ecomshoot
        </h2>
        <p class="about-lead">
          We don’t see photography as a tick box. As a product photography studio in Delhi we plan each shoot with the end in mind. We ensure that your product is shot with the correct angle, detail and feel to encourage a customer to click add to cart. We have a production studio in Delhi to ensure fast and consistent shoots.
        </p>

        <div class="editorial-pillars-grid">
          <div class="editorial-pillar-card">
            <div class="pillar-top">
              <div class="pillar-icon-box">🔬</div>
              <span class="pillar-index">01</span>
            </div>
            <h3 class="pillar-heading">Clear, Sharp Detail</h3>
            <p class="pillar-copy">We use controlled lighting to ensure your product is captured in high detail with true colours and textures.</p>
          </div>

          <div class="editorial-pillar-card">
            <div class="pillar-top">
              <div class="pillar-icon-box">🎨</div>
              <span class="pillar-index">02</span>
            </div>
            <h3 class="pillar-heading">Marketplace Compliance</h3>
            <p class="pillar-copy">We ensure your images are the correct size, background and format for your Amazon product photography in Delhi and across India.</p>
          </div>

          <div class="editorial-pillar-card">
            <div class="pillar-top">
              <div class="pillar-icon-box">⚡</div>
              <span class="pillar-index">03</span>
            </div>
            <h3 class="pillar-heading">Batch Shooting</h3>
            <p class="pillar-copy">We have an easy process for brands with lots of SKUs to ensure no loss in quality.</p>
          </div>

          <div class="editorial-pillar-card">
            <div class="pillar-top">
              <div class="pillar-icon-box">🛡️</div>
              <span class="pillar-index">04</span>
            </div>
            <h3 class="pillar-heading">Reliable Turnaround</h3>
            <p class="pillar-copy">We have clear timelines, organised files and quick turn arounds to ensure your product is launched on time.</p>
          </div>
        </div>

        <div style="margin-top: 2rem;">
          <a href="<?= url('about.php') ?>" class="btn btn-primary">Find Out More About Our Studio &rarr;</a>
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
        <span class="badge badge-lime">5 Stage Process</span>
      </div>
      <h2 class="section-title-editorial">Our 5 Stage Process</h2>
      <p class="section-sub-editorial">
        An easy and transparent process from start to finish.
      </p>
    </div>

    <div class="process-steps-row">
      <div class="process-step-item">
        <div class="step-num-pill">01</div>
        <h4 class="step-title">Creative Brief</h4>
        <p class="step-desc">We find out about your product, audience and sales channels. We then agree on the look you want.</p>
      </div>

      <div class="process-step-item">
        <div class="step-num-pill">02</div>
        <h4 class="step-title">Concept &amp; Planning</h4>
        <p class="step-desc">We plan your angles, backgrounds, props and shot list to ensure your shoot runs smoothly.</p>
      </div>

      <div class="process-step-item">
        <div class="step-num-pill">03</div>
        <h4 class="step-title">Studio Shoot</h4>
        <p class="step-desc">We shoot your products in our Delhi studio using professional lighting and styling.</p>
      </div>

      <div class="process-step-item">
        <div class="step-num-pill">04</div>
        <h4 class="step-title">Editing &amp; Retouching</h4>
        <p class="step-desc">We colour correct, clean up and enhance your images to match your brand and marketplace guidelines.</p>
      </div>

      <div class="process-step-item">
        <div class="step-num-pill">05</div>
        <h4 class="step-title">Final Deliverables</h4>
        <p class="step-desc">You will receive organised files ready for upload in the correct formats and sizes.</p>
      </div>
    </div>

  </div>
</section>

<!-- SECTION F – INDUSTRIES WE SERVE -->
<section class="section section-industries">
  <div class="container">
    
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-indigo">Lighting Setups</span>
      </div>
      <h2 class="section-title-editorial">Industries We Shoot</h2>
      <p class="section-sub-editorial">
        We use different lighting set ups depending on your product type. Glass, metal, fabric and skincare packaging all react differently to light.
      </p>
    </div>

    <div class="editorial-industries-grid">
      <div class="industry-box">
        <div class="industry-icon">✨</div>
        <h3 class="industry-title">Beauty &amp; Cosmetics</h3>
        <p class="industry-copy">Soft lighting to capture accurate shading and clean reflective packaging.</p>
      </div>

      <div class="industry-box">
        <div class="industry-icon">👗</div>
        <h3 class="industry-title">Fashion &amp; Apparel</h3>
        <p class="industry-copy">Even lighting to capture fabric textures, colours and fit.</p>
      </div>

      <div class="industry-box">
        <div class="industry-icon">💎</div>
        <h3 class="industry-title">Luxury Jewellery</h3>
        <p class="industry-copy">We use precise lighting to highlight your stones, polished metal and detail.</p>
      </div>

      <div class="industry-box">
        <div class="industry-icon">🛋️</div>
        <h3 class="industry-title">Lifestyle &amp; Living</h3>
        <p class="industry-copy">Warm lighting to create a natural feel and showcase your product in a realistic environment.</p>
      </div>

      <div class="industry-box">
        <div class="industry-icon">🔌</div>
        <h3 class="industry-title">Consumer Electronics</h3>
        <p class="industry-copy">Clean sharp lines, controlled reflections and clear ports/buttons.</p>
      </div>

      <div class="industry-box">
        <div class="industry-icon">🍷</div>
        <h3 class="industry-title">Home &amp; Storage</h3>
        <p class="industry-copy">Practical images with good lighting to showcase size, material and use.</p>
      </div>
    </div>

  </div>
</section>

<!-- SECTION G – FEATURED INSIGHTS -->
<section class="section section-insights">
  <div class="container">
    
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-orange">Learn &amp; Grow</span>
      </div>
      <h2 class="section-title-editorial">Guides, tips and Case Studies to help improve your product images.</h2>
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
                Read More &rarr;
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="blog-center-cta">
      <a href="<?= url('blog.php') ?>" class="btn btn-secondary">View All Guides</a>
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
      <h2 class="section-title-editorial">Trusted by growing e-commerce brands.</h2>
      <p class="section-sub-editorial"></p>
    </div>

    <!-- Testimonials Slider Wrapper (Grid on Desktop, Swipeable Carousel on Mobile) -->
    <div class="testimonials-slider-wrapper">
      <div class="editorial-testimonials-grid testimonials-slider-track" id="testimonialTrack">
        <div class="testimonial-box testimonial-slide">
          <div class="testimonial-stars-row">★★★★★</div>
          <p class="testimonial-text">
            “[Client Feedback On Image Quality]”
          </p>
          <div class="testimonial-user">
            <img src="<?= asset_url('assets/images/testimonials/avatar-1.jpg') ?>" alt="Client feedback" width="48" height="48" loading="lazy" class="user-avatar">
            <div>
              <div class="user-name">[Name]</div>
              <div class="user-role">[Role], [Brand]</div>
            </div>
          </div>
        </div>

        <div class="testimonial-box testimonial-slide">
          <div class="testimonial-stars-row">★★★★★</div>
          <p class="testimonial-text">
            “[Client Feedback On Turnaround/Communication]”
          </p>
          <div class="testimonial-user">
            <img src="<?= asset_url('assets/images/testimonials/avatar-2.jpg') ?>" alt="Client feedback" width="48" height="48" loading="lazy" class="user-avatar">
            <div>
              <div class="user-name">[Name]</div>
              <div class="user-role">[Role], [Brand]</div>
            </div>
          </div>
        </div>

        <div class="testimonial-box testimonial-slide">
          <div class="testimonial-stars-row">★★★★★</div>
          <p class="testimonial-text">
            “[Client Feedback On Sales/Conversion]”
          </p>
          <div class="testimonial-user">
            <img src="<?= asset_url('assets/images/testimonials/avatar-3.jpg') ?>" alt="Client feedback" width="48" height="48" loading="lazy" class="user-avatar">
            <div>
              <div class="user-name">[Name]</div>
              <div class="user-role">[Role], [Brand]</div>
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
      <h2 class="section-title-editorial">Answers to some common questions.</h2>
      <p class="section-sub-editorial">
        Everything you need to know about sample shipments, shoot turnarounds, licensing rights, and image deliverables.
      </p>
    </div>

    <div class="faq-accordion-container">
      <div class="faq-item active">
        <button class="faq-question-btn" aria-expanded="true">
          <span>How does product delivery and sampling work?</span>
          <svg class="faq-chevron" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>
        <div class="faq-answer-panel">
          You can send your products to our studio in Delhi via courier or hand delivery. We will log your product, shoot and return safely once the project has been completed.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question-btn" aria-expanded="false">
          <span>How long does it take to produce?</span>
          <svg class="faq-chevron" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>
        <div class="faq-answer-panel">
          Most projects take between 3-5 working days. The time can depend on how many products you have and how much editing is required. Rush turnaround is also available.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question-btn" aria-expanded="false">
          <span>Do you offer commercial use and licensing?</span>
          <svg class="faq-chevron" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>
        <div class="faq-answer-panel">
          Yes! Once your project has been paid for you will have full commercial use rights for your website, marketplaces, ads and social media.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question-btn" aria-expanded="false">
          <span>Can you shoot for Amazon &amp; Shopify?</span>
          <svg class="faq-chevron" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>
        <div class="faq-answer-panel">
          Yes! We follow all guidelines from Amazon, Flipkart &amp; Shopify. This includes white background, sizing and aspect ratio.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question-btn" aria-expanded="false">
          <span>How much does product photography in Delhi cost?</span>
          <svg class="faq-chevron" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>
        <div class="faq-answer-panel">
          The cost depends on how many products you have, what type of shots you want and how much editing. Please send us your list of products and we will give you a clear quote with no hidden costs.
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question-btn" aria-expanded="false">
          <span>What if I’m not satisfied with my results?</span>
          <svg class="faq-chevron" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
          </svg>
        </button>
        <div class="faq-answer-panel">
          Let us know what changes you would like and we will make the changes within the agreed scope.
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
        <span class="badge badge-lime">Free Sample</span>
        <h3 class="test-shoot-heading">Try Our Quality With a 1 SKU test.</h3>
        <p class="test-shoot-desc">
          Send us one hero product. We will light, shoot, and retouch it to your exact specifications so you can evaluate our standard firsthand before booking a full catalog shoot.
        </p>
      </div>
      <div class="test-shoot-action">
        <a href="<?= url('contact.php') ?>" class="btn btn-lg btn-primary">Request Test Shoot &rarr;</a>
      </div>
    </div>
  </div>
</section>

<!-- SECTION J – CREATIVE FOOTER (Included via global footer component) -->
</main>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>
