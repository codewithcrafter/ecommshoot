<?php
/**
 * ECOMSHOOT - Dedicated Service Page Template View
 * Renders complete, original, SEO-optimized service page with benefits, features,
 * photography examples, industries, process steps, FAQs, related services, and JSON-LD schema.
 */

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../config/config.php';
}
require_once INCLUDES_PATH . '/breadcrumbs.php';

// Fetch service record by slug
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM services WHERE slug = :slug AND status = 'published' LIMIT 1");
$stmt->execute(['slug' => $serviceSlug]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    http_response_code(404);
    $pageIdentifier = '404';
    require_once INCLUDES_PATH . '/header.php';
    echo '<div class="container section" style="text-align: center; padding: 6rem 0;"><h1>Service Not Found</h1><p>The requested photography service could not be located.</p><a href="' . url('services.php') . '" class="btn btn-primary">Return to Services</a></div>';
    require_once INCLUDES_PATH . '/footer.php';
    exit;
}

$pageIdentifier = $service['slug'];
$serviceCopy = $servicePageCopy ?? [];
$relatedCopy = $relatedServiceCopy ?? [];
$copy = static fn (string $key, string $default): string => $serviceCopy[$key] ?? $default;

// Decode JSON fields
$benefits = json_decode($service['benefits_json'] ?? '[]', true) ?: [];
$features = json_decode($service['features_json'] ?? '[]', true) ?: [];
$industries = json_decode($service['industries_json'] ?? '[]', true) ?: [];
$process = json_decode($service['process_json'] ?? '[]', true) ?: [];
$faqs = json_decode($service['faqs_json'] ?? '[]', true) ?: [];

// Fetch Related Services
$relatedSlugs = array_filter(array_map('trim', explode(',', $service['related_slugs'] ?? '')));
$relatedServices = [];
if (!empty($relatedSlugs)) {
    $placeholders = implode(',', array_fill(0, count($relatedSlugs), '?'));
    $relStmt = $db->prepare("SELECT * FROM services WHERE slug IN ($placeholders) AND status = 'published'");
    $relStmt->execute(array_values($relatedSlugs));
    $relatedServices = $relStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Build dynamic Service and FAQPage Schema
$serviceSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $service['title'],
    'serviceType' => $service['title'],
    'description' => $service['short_description'],
    'provider' => [
        '@type' => 'Organization',
        'name' => get_setting('site_name', 'Ecomshoot'),
        'url' => BASE_URL,
        'telephone' => get_setting('site_phone', '+1 (800) 555-SHOOT'),
        'email' => get_setting('site_email', 'contact@ecommshoot.in')
    ],
    'areaServed' => 'Global'
];

$faqSchema = null;
if (!empty($faqs)) {
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => []
    ];
    foreach ($faqs as $faq) {
        $faqSchema['mainEntity'][] = [
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq['answer']
            ]
        ];
    }
}

$customSchemas = array_filter([$serviceSchema, $faqSchema]);

require_once INCLUDES_PATH . '/header.php';
?>

<!-- Breadcrumbs Navigation -->
<?= render_breadcrumbs([
    ['title' => 'Services', 'url' => url('services.php')],
    ['title' => $service['title'], 'url' => null]
]) ?>

<!-- Page Hero / Banner -->
<section class="page-header-banner">
  <div class="container">
    <div class="page-header-content">
      <span class="badge badge-orange"><?= e($service['badge'] ?: 'Specialized Service') ?></span>
      <h1 class="page-header-title"><?= e($service['h1_heading']) ?></h1>
      <p class="page-header-sub"><?= e($copy('hero_description', $service['short_description'])) ?></p>
      <div style="margin-top: 1.75rem; display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
        <a href="<?= url('contact.php') ?>" class="btn btn-primary"><?= e($copy('hero_primary_cta', 'Book This Service')) ?></a>
        <a href="#process" class="btn btn-outline-white"><?= e($copy('hero_secondary_cta', 'Review Production Process ↓')) ?></a>
      </div>
    </div>
  </div>
</section>

<!-- Introduction Section (With Real Service Photo) -->
<section class="section">
  <div class="container">
    <div class="service-intro-grid">
      <div>
        <span class="badge badge-indigo"><?= e($copy('overview_badge', 'Service Overview')) ?></span>
        <h2 style="margin-bottom: 1.25rem;"><?= e($copy('overview_heading', 'Engineered Visual Quality for Serious Brands')) ?></h2>
        <div class="service-intro-text">
          <?php foreach (preg_split('/\R{2,}/', trim($service['introduction'])) as $introductionParagraph): ?>
            <?php if (trim($introductionParagraph) !== ''): ?>
              <p><?= nl2br(e(trim($introductionParagraph))) ?></p>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
        <?php $overviewMidHeading = $copy('overview_mid_heading', ''); ?>
        <?php if ($overviewMidHeading !== ''): ?>
          <h3 style="margin-top: 1.25rem;"><?= e($overviewMidHeading) ?></h3>
        <?php endif; ?>
        <?php $overviewFollowup = $copy('overview_followup', 'Whether you are launching a flagship SKU or updating an entire multichannel inventory, our calibrated studio pipeline delivers repeatable, pixel-accurate visual assets that exceed retailer guidelines.'); ?>
        <?php if ($overviewFollowup !== ''): ?>
          <p style="margin-top: 1.25rem;"><?= e($overviewFollowup) ?></p>
        <?php endif; ?>
      </div>
      <div class="service-media-frame">
        <img src="<?= asset_url($service['featured_image'] ?: 'assets/images/services/' . $service['slug'] . '.jpg') ?>" 
             alt="<?= e($service['title']) ?> Studio Setup and Lighting" 
             width="600" 
             height="450" 
             loading="eager">
      </div>
    </div>
  </div>
</section>

<!-- Service Benefits -->
<?php if (!empty($benefits)): ?>
<section class="section section-subtle">
  <div class="container">
    <div class="section-header">
      <span class="badge badge-orange"><?= e($copy('impact_badge', 'Business Impact')) ?></span>
      <h2 class="section-title"><?= e($copy('impact_heading', 'Measurable Commercial Benefits')) ?></h2>
      <p class="section-subtitle">
        <?= e($copy('impact_subtitle', 'How our specialized ' . strtolower($service['title']) . ' directly impacts checkouts, brand perception, and operating margins.')) ?>
      </p>
    </div>

    <div class="service-benefits-grid">
      <?php foreach ($benefits as $b): ?>
        <div class="benefit-card">
          <div class="pillar-icon coral" style="margin-bottom: 1rem;">★</div>
          <h3 class="benefit-title"><?= e($b['title']) ?></h3>
          <p class="benefit-desc"><?= e($b['description']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Technical Features & Specifications -->
<?php if (!empty($features)): ?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="badge badge-indigo"><?= e($copy('features_badge', 'Studio Specifications')) ?></span>
      <h2 class="section-title"><?= e($copy('features_heading', 'Technical Features & Deliverables')) ?></h2>
      <p class="section-subtitle">
        <?= e($copy('features_subtitle', 'Every project is executed according to rigid technical specifications to ensure effortless integration across your digital channels.')) ?>
      </p>
    </div>

    <div class="features-list-grid">
      <?php foreach ($features as $f): ?>
        <div class="feature-box">
          <div class="feature-check">✓</div>
          <div>
            <h3 class="feature-title"><?= e($f['title']) ?></h3>
            <p class="feature-desc"><?= e($f['description']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php $featuresFollowup = $copy('features_followup', ''); ?>
    <?php if ($featuresFollowup !== ''): ?>
      <p class="section-subtitle" style="margin-top: 1.5rem;"><?= e($featuresFollowup) ?></p>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- Photography Examples Showcase -->
<section class="section section-subtle">
  <div class="container">
    <div class="section-header">
      <span class="badge badge-lime"><?= e($copy('visual_badge', 'Visual Proof')) ?></span>
      <h2 class="section-title"><?= e($copy('visual_heading', 'Photography Output & Production Examples')) ?></h2>
      <?php $visualSubtitle = $copy('visual_subtitle', 'Real studio captures illustrating light falloff, edge sharpness, surface texture fidelity, and master color balancing.'); ?>
      <?php if ($visualSubtitle !== ''): ?>
        <p class="section-subtitle"><?= e($visualSubtitle) ?></p>
      <?php endif; ?>
    </div>

    <div class="portfolio-grid">
      <div class="portfolio-card">
        <img src="<?= asset_url('assets/images/services/' . $service['slug'] . '.jpg') ?>" 
             alt="<?= e($service['title']) ?> Hero Studio Shot" 
             width="400" 
             height="300" 
             loading="lazy">
        <div class="portfolio-overlay">
          <span class="portfolio-tag">Primary Commercial Hero</span>
          <div class="portfolio-title"><?= e($service['title']) ?></div>
        </div>
      </div>

      <div class="portfolio-card">
        <img src="<?= asset_url('assets/images/hero/hero-main.jpg') ?>" 
             alt="<?= e($service['title']) ?> Detail Texture Capture" 
             width="400" 
             height="300" 
             loading="lazy">
        <div class="portfolio-overlay">
          <span class="portfolio-tag">Optical Detail Zoom</span>
          <div class="portfolio-title">Micro-Texture Acuity</div>
        </div>
      </div>

      <div class="portfolio-card">
        <img src="<?= asset_url('assets/images/about/studio-bay.jpg') ?>" 
             alt="<?= e($service['title']) ?> Commercial Studio Set" 
             width="400" 
             height="300" 
             loading="lazy">
        <div class="portfolio-overlay">
          <span class="portfolio-tag">Engineered Lighting Bay</span>
          <div class="portfolio-title">Calibrated Strobe Control</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Industries Served -->
<?php if (!empty($industries)): ?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="badge badge-orange"><?= e($copy('category_badge', 'Category Expertise')) ?></span>
      <h2 class="section-title"><?= e($copy('category_heading', 'Industries Specialized in This Format')) ?></h2>
      <p class="section-subtitle">
        <?= e($copy('category_subtitle', 'We understand how materials, reflections, and product shapes in these sectors react to professional studio light shaping.')) ?>
      </p>
    </div>

    <div class="industries-grid">
      <?php foreach ($industries as $ind): ?>
        <div class="industry-card">
          <div class="industry-name"><?= e($ind['name']) ?></div>
          <p class="industry-desc"><?= e($ind['description']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Production Process (Step 01 - 04) -->
<?php if (!empty($process)): ?>
<section class="section section-subtle" id="process">
  <div class="container">
    <div class="section-header">
      <span class="badge badge-indigo"><?= e($copy('process_badge', 'Production Roadmap')) ?></span>
      <h2 class="section-title"><?= e($copy('process_heading', 'Our Structured Studio Workflow')) ?></h2>
      <p class="section-subtitle">
        <?= e($copy('process_subtitle', 'A predictable, milestone-driven production timeline from initial intake to final high-resolution delivery.')) ?>
      </p>
    </div>

    <div class="process-grid">
      <?php foreach ($process as $proc): ?>
        <div class="process-card">
          <div class="process-number"><?= e($proc['step']) ?></div>
          <h3 class="process-title"><?= e($proc['title']) ?></h3>
          <p class="process-desc"><?= e($proc['description']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Relevant Frequently Asked Questions -->
<?php if (!empty($faqs)): ?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="badge badge-lime"><?= e($copy('faq_badge', 'Service FAQ')) ?></span>
      <h2 class="section-title">Frequently Asked Questions</h2>
      <p class="section-subtitle">
        <?= e($copy('faq_subtitle', 'Answers to common technical, logistics, and turnaround questions about ' . strtolower($service['title']) . '.')) ?>
      </p>
    </div>

    <div class="faq-list">
      <?php foreach ($faqs as $i => $faq): ?>
        <div class="faq-item <?= ($i === 0) ? 'active' : '' ?>">
          <button class="faq-question" aria-expanded="<?= ($i === 0) ? 'true' : 'false' ?>">
            <span><?= e($faq['question']) ?></span>
            <svg class="faq-icon" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
          </button>
          <div class="faq-answer">
            <?= e($faq['answer']) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Related Services Section -->
<?php if (!empty($relatedServices)): ?>
<section class="section section-subtle">
  <div class="container">
    <div class="section-header">
      <span class="badge badge-lavender"><?= e($copy('related_badge', 'Complementary Solutions')) ?></span>
      <h2 class="section-title"><?= e($copy('related_heading', 'Related Commercial Photography Services')) ?></h2>
      <p class="section-subtitle">
        <?= e($copy('related_subtitle', 'Pair your project with these complementary photographic and post-production capabilities for full visual synergy.')) ?>
      </p>
    </div>

    <div class="services-grid">
      <?php foreach ($relatedServices as $rel): ?>
        <?php $relatedItem = $relatedCopy[$rel['slug']] ?? []; ?>
        <article class="service-card">
          <div class="service-card-media">
            <img src="<?= asset_url($rel['featured_image'] ?: 'assets/images/services/' . $rel['slug'] . '.jpg') ?>" alt="<?= e($rel['title']) ?>" width="400" height="250" loading="lazy">
          </div>
          <div class="service-card-body">
            <h3 class="service-card-title">
              <a href="<?= url($rel['file_name']) ?>"><?= e($relatedItem['title'] ?? $rel['title']) ?></a>
            </h3>
            <?php $relatedDescription = $relatedItem['description'] ?? $rel['short_description']; ?>
            <?php if ($relatedDescription !== ''): ?>
              <p class="service-card-text"><?= e($relatedDescription) ?></p>
            <?php endif; ?>
            <a href="<?= url($rel['file_name']) ?>" class="service-card-link">
              <?= e($relatedItem['link'] ?? $copy('related_link', 'Explore ' . $rel['title'] . ' →')) ?>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
if (!empty($servicePageCopy)) {
    $footerCtaCopy = [
        'badge' => $copy('footer_badge', ''),
        'heading' => $copy('footer_heading', ''),
        'description' => $copy('footer_description', ''),
        'primary_cta' => $copy('footer_primary_cta', ''),
        'secondary_cta' => $copy('footer_secondary_cta', ''),
    ];
}
require_once INCLUDES_PATH . '/footer.php';
?>
