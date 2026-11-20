<?php
/**
 * ECOMSHOOT - All Services Directory
 * Premium Commercial Photography & Production Disciplines
 */

$pageIdentifier = 'services';
require_once __DIR__ . '/config/config.php';
require_once INCLUDES_PATH . '/breadcrumbs.php';

$db = Database::getInstance()->getConnection();
$services = [];
try {
    $services = $db->query("SELECT * FROM services WHERE status = 'published' AND slug != 'professional-product-photography' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Query Error [services]: " . $e->getMessage());
    $services = [];
}

require_once INCLUDES_PATH . '/header.php';
$flash = get_flash();
$serviceDirectoryCopy = [
  'product-photography' => 'Our product photography services produce professional shots with realistic textures, colours and shapes, so new customers can add to basket with confidence. We treat your products as if they were our own, and we photograph, light and inspect every shot. If your product photography is holding you back, this is where you should start. The improvements will be visible on your product page within days.',
  'ecommerce-product-photography' => 'Our product photography services work across Shopify, Amazon and multi-channel e-commerce stores. They keep lighting, angles and colour consistent across your whole catalog photography set. We also offer quick turnaround bulk product photography for your Delhi business, so your product shots stay consistent across all your SKUs with no compromise on quality.',
  'lifestyle-product-photography' => 'We showcase your product in real-life situations, which helps customers picture the item in their own home. This increases engagement with your social media ads, homepage banners and marketplace A+ content, and helps customers connect with your product on an emotional level.',
  'fashion-product-photography' => 'Whether you need ghost mannequin shots, flat lays or model lookbooks, we present your clothing with clean lines. Realistic detail of the fabric, stitching and shape helps customers make a purchase decision. This is perfect for boutique stores, D2C brands and exporters across Delhi NCR.',
  'creative-product-photography' => 'We produce creative product shots that catch people’s attention. Bold colours, splash shots, levitation and bespoke set designs create a professional, advertising-ready image. This is perfect if you want your brand to stand out, and these product photography services will help you achieve great conversions.',
  'jewellery-product-photography' => 'We use macro focus stacking to eliminate unwanted reflections. This lets your diamonds sparkle and your gold and silver show a realistic shine. This service is perfect for designers, showrooms and online jewellers, and the detail helps show off your product.',
  'product-photo-editing-retouching' => 'Our editing transforms your raw studio shots. We use clipping paths, scratch and dust removal, frequency separation and colour balancing to get your images looking perfect. Our product photography services deliver web-ready, marketplace-ready and print-ready files, so your team won’t need to edit any images. We ensure fast turnaround times with consistent quality.',
  'white-background-product-photography' => 'We produce high-quality pure white (RGB 255, 255, 255) product shots that help your product pass Amazon, Walmart and Google Shopping requirements on first upload. This keeps your catalog photography compliant, distraction-free and easy to compare.',
];
?>

<main class="services-ui-redesign">

<!-- BreadcrumbList structured data; no visible breadcrumb navigation. -->
<?= render_breadcrumbs([['title' => 'Commercial Disciplines', 'url' => null]]) ?>

<!-- Page Header Banner -->
<section class="page-header-banner services-enquiry-hero">
  <div class="container">
    <div class="services-hero-grid">
      <div class="page-header-content">
        <span class="badge badge-orange">Studio Capabilities</span>
        <h1 class="page-header-title services-hero-title"><span>Product Photography</span><span>Services in Delhi</span><span>Engineered for</span><span class="font-editorial shimmer-coral">E-Commerce Success</span></h1>
        <p class="page-header-sub">
          We offer 8 specialized product photography services in Delhi to help boost your sales. From Amazon white background packshots and lifestyle shoots to macro fine jewellery photography, we have you covered. Whether you need one main product shot or bulk product photography for thousands of SKUs, you will find the right fit below.
        </p>
      </div>
      <div class="services-enquiry-card luxury-card studio-reveal">
        <div class="enquiry-card-header">
          <span class="enquiry-card-badge">Instant Studio Estimate</span>
          <h3 class="enquiry-card-title">Book a Photoshoot</h3>
        </div>
        <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>"><?= e($flash['message']) ?></div><?php endif; ?>
        <form action="<?= url('submit-lead.php') ?>" method="POST" class="services-enquiry-form">
          <?= CSRF::inputField() ?>
          <input type="hidden" name="source_page_url" value="services">
          <input type="hidden" name="source_page_title" value="Product Photography Services in Delhi Engineered for E-Commerce Success">
          <input type="hidden" name="source_page_type" value="services_page">
          <div class="services-form-grid">
            <div class="form-group">
              <label class="form-label" for="services_full_name">Full Name</label>
              <input class="form-control" id="services_full_name" name="full_name" type="text" placeholder="e.g. Rahul Sharma" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="services_email">Email Address</label>
              <input class="form-control" id="services_email" name="email" type="email" placeholder="e.g. rahul@company.com" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="services_mobile">Contact Number</label>
              <input class="form-control" id="services_mobile" name="mobile" type="tel" placeholder="e.g. 9876543210" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="services_required">Service Required</label>
              <select class="form-control form-select" id="services_required" name="lead_source" required>
                <option value="">Select a service</option>
                <?php foreach ($services as $svc): ?>
                  <option value="<?= e($svc['title']) ?>"><?= e($svc['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="services_message">Project Requirements / Message</label>
            <textarea class="form-control" id="services_message" name="message" rows="3" placeholder="e.g. We need 50 product images for our Amazon listings..." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary services-submit-btn btn-studio-primary">Submit Enquiry</button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- Editorial Services Grid -->
<section class="section section-services">
  <div class="container">
    <div class="editorial-services-grid">
      <?php foreach ($services as $idx => $svc): 
        $isFeatured = ($idx < 2);
      ?>
        <article class="service-editorial-card luxury-card studio-reveal <?= $isFeatured ? 'card-featured' : '' ?>">
          <div class="card-media-wrapper">
            <img src="<?= asset_url($svc['featured_image'] ?: 'assets/images/services/' . $svc['slug'] . '.jpg') ?>" 
                 alt="<?= e($svc['title']) ?> commercial photography sample" 
                 width="400" 
                 height="260" 
                 loading="lazy"
                 class="service-card-photo">
            <?php if (!empty($svc['badge'])): ?>
              <span class="service-card-badge"><?= e($svc['badge']) ?></span>
            <?php endif; ?>
          </div>
          
          <div class="card-content-wrapper">
            <div class="card-meta-line">
              <span class="discipline-number">SERVICE 0<?= ($idx + 1) ?></span>
            </div>
            <h2 class="service-card-heading">
              <a href="<?= service_url($svc['slug']) ?>"><?= e($svc['title']) ?></a>
            </h2>
            <p class="service-card-summary">
              <?= e($serviceDirectoryCopy[$svc['slug']] ?? $svc['short_description']) ?>
            </p>
            <div class="service-card-footer">
              <a href="<?= service_url($svc['slug']) ?>" class="service-link-arrow">
                <span>View Service &amp; Spec &rarr;</span>
                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" /></svg>
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Photography Category Comparison Table -->
<section class="section section-pillars">
  <div class="container">
    <div class="section-editorial-header">
      <div class="header-tagline">
        <span class="badge badge-indigo">CHOOSING THE RIGHT STYLE</span>
      </div>
      <h2 class="section-title-editorial">Choosing the Right Photography Style for <span class="font-editorial shimmer-coral">Your Campaign</span></h2>
      <p class="section-sub-editorial">
        Depending on your digital requirements, here are the differences between our three key styles. Select the right product photography services for your needs.
      </p>
    </div>

    <div class="services-style-cards-grid">
      <div class="services-style-card luxury-card studio-reveal">
        <div class="style-card-header">
          <span class="style-card-tag">Service Type</span>
          <h3 class="style-card-title">White Background Packshots</h3>
        </div>
        <div class="style-card-body">
          <div class="style-card-field">
            <span class="field-label">Main Channel</span>
            <p class="field-value">Amazon Main, Google Shopping, Shopify grid</p>
          </div>
          <div class="style-card-field">
            <span class="field-label">Purpose</span>
            <p class="field-value">Ensure your product is compliant, distraction-free and easy to recognise</p>
          </div>
          <div class="style-card-field">
            <span class="field-label">Typical Output</span>
            <p class="field-value">Square 3000x3000px, pure white (RGB 255), soft contact shadow</p>
          </div>
        </div>
      </div>

      <div class="services-style-card luxury-card studio-reveal">
        <div class="style-card-header">
          <span class="style-card-tag">Service Type</span>
          <h3 class="style-card-title">Lifestyle &amp; In-Context</h3>
        </div>
        <div class="style-card-body">
          <div class="style-card-field">
            <span class="field-label">Main Channel</span>
            <p class="field-value">Meta/Instagram, homepage hero, Amazon A+ Content</p>
          </div>
          <div class="style-card-field">
            <span class="field-label">Purpose</span>
            <p class="field-value">Connect with customers emotionally, create aspirational content and show scale</p>
          </div>
          <div class="style-card-field">
            <span class="field-label">Typical Output</span>
            <p class="field-value">Styled sets, hand models and props</p>
          </div>
        </div>
      </div>

      <div class="services-style-card luxury-card studio-reveal">
        <div class="style-card-header">
          <span class="style-card-tag">Service Type</span>
          <h3 class="style-card-title">Creative &amp; Advertising</h3>
        </div>
        <div class="style-card-body">
          <div class="style-card-field">
            <span class="field-label">Main Channel</span>
            <p class="field-value">Social media ads, hoardings, press launches and packaging</p>
          </div>
          <div class="style-card-field">
            <span class="field-label">Purpose</span>
            <p class="field-value">Catch people’s attention and create category leadership</p>
          </div>
          <div class="style-card-field">
            <span class="field-label">Typical Output</span>
            <p class="field-value">Splash, levitation and dramatic shadows</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

</main>
<?php
$footerCtaCopy = [
  'badge' => 'STUDIO CAPACITY AVAILABLE THIS QUARTER',
  'heading' => 'Ready to Elevate Your Product Catalog?',
  'description' => 'We can create high-quality product shots from your product samples at our Delhi studio. We offer live tethered proofing, colour calibration and images that will boost your checkouts on Shopify, Amazon and premium retailers. Get a bespoke quote for product photography services, catalog photography or bulk product photography.',
  'primary_cta' => 'Book Your Studio Shoot',
  'secondary_cta' => 'View All Services',
];
require_once INCLUDES_PATH . '/footer.php';
?>
