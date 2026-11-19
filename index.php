<?php
/**
 * ECOMSHOOT - Home Page
 * Award-Winning Creative Commercial Product Photography & Production Studio
 */

$pageIdentifier = 'home';
require_once __DIR__ . '/config/config.php';

// Fetch all 9 published services
$db = Database::getInstance()->getConnection();
$services = [];
try {
    $services = $db->query("SELECT * FROM services WHERE status = 'published' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
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

        <div class="hero-reference-note">Discover our full-service photography workflow</div>
      </div>

      <div class="hero-media-col hero-service-cards" aria-label="Photography service highlights">
        <article class="hero-service-card hero-service-card-wide"><span class="hero-card-icon">⌕</span><span class="hero-card-tag">CATALOGUE</span><h2>Product Photography</h2><p>Clean, consistent imagery made for every storefront.</p></article>
        <article class="hero-service-card"><span class="hero-card-icon">◉</span><span class="hero-card-tag">MARKETPLACE</span><h2>White Background</h2><p>Marketplace-ready imagery that passes checks.</p></article>
        <article class="hero-service-card"><span class="hero-card-icon">✦</span><span class="hero-card-tag">LIFESTYLE</span><h2>Styled Campaigns</h2><p>Make customers picture your product in use.</p></article>
        <article class="hero-service-card"><span class="hero-card-icon">↗</span><span class="hero-card-tag">CREATIVE</span><h2>Advertising Visuals</h2><p>Bold art direction for product launches and ads.</p></article>
        <article class="hero-service-card"><span class="hero-card-icon">⌁</span><span class="hero-card-tag">RETOUCHING</span><h2>Editing &amp; Retouching</h2><p>Pixel-clean assets, ready to upload.</p></article>
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

    <!-- Category Filter Controls -->
    <div class="portfolio-filter-row">
      <button class="portfolio-pill-btn active" data-filter="all">All Work</button>
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
            <span class="overlay-tag">Beauty &amp; Cosmetics</span>
            <div class="overlay-title">Botanical Fragrance</div>
            <div class="overlay-sub">Soft lighting and glass reflectors to create a luxury feel on shelf.</div>
            <a href="<?= url('creative-product-photography.php') ?>" class="overlay-link">View Case Study &rarr;</a>
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
            <span class="overlay-tag">Electronics</span>
            <div class="overlay-title">Matte ANC Headphones</div>
            <div class="overlay-sub">Hard shadows and bold colours to create a sharp look.</div>
            <a href="<?= url('ecommerce-product-photography.php') ?>" class="overlay-link">View Case Study &rarr;</a>
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
            <div class="overlay-sub">Macro lighting to showcase sparkle and cut.</div>
            <a href="<?= url('jewellery-product-photography.php') ?>" class="overlay-link">View Case Study &rarr;</a>
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
            <span class="overlay-tag">Food &amp; Beverage</span>
            <div class="overlay-title">Artisan Cornichons</div>
            <div class="overlay-sub">Fresh styling to make people want to open the jar.</div>
            <a href="<?= url('lifestyle-product-photography.php') ?>" class="overlay-link">View Case Study &rarr;</a>
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
            <div class="overlay-sub">Ghost mannequin and flat lay images to showcase fit, fabric and stitching.</div>
            <a href="<?= url('fashion-product-photography.php') ?>" class="overlay-link">View Case Study &rarr;</a>
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
            <span class="overlay-tag">Creative Campaign</span>
            <div class="overlay-title">Cold-Pressed Beverage</div>
            <div class="overlay-sub">Water splash, condensation and colour to create a refreshing look.</div>
            <a href="<?= url('creative-product-photography.php') ?>" class="overlay-link">View Case Study &rarr;</a>
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
              <a href="<?= url($svc['file_name']) ?>"><?= e($svc['title']) ?></a>
            </h3>
            <p class="service-card-summary">
              <?= e($svc['short_description']) ?>
            </p>
            <div class="service-card-footer">
              <a href="<?= url($svc['file_name']) ?>" class="service-link-arrow">
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
<?php require_once INCLUDES_PATH . '/footer.php'; ?>
