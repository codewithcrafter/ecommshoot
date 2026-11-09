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
    $services = $db->query("SELECT * FROM services WHERE status = 'published' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Query Error [services]: " . $e->getMessage());
    $services = [];
}

require_once INCLUDES_PATH . '/header.php';
?>

<!-- Breadcrumbs -->
<?= render_breadcrumbs([['title' => 'Commercial Disciplines', 'url' => null]]) ?>

<!-- Page Header Banner -->
<section class="page-header-banner">
  <div class="container">
    <div class="page-header-content">
      <span class="badge badge-orange">Studio Capabilities</span>
      <h1 class="page-header-title">Commercial Photography Services Engineered for E-Commerce Success</h1>
      <p class="page-header-sub">
        From compliant Amazon white-background packshots to immersive lifestyle campaigns and fine jewellery macro stacks, explore our 9 specialized commercial disciplines.
      </p>
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
        <article class="service-editorial-card <?= $isFeatured ? 'card-featured' : '' ?>">
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
              <span class="discipline-number">DISCIPLINE 0<?= ($idx + 1) ?></span>
            </div>
            <h2 class="service-card-heading">
              <a href="<?= url($svc['file_name']) ?>"><?= e($svc['title']) ?></a>
            </h2>
            <p class="service-card-summary">
              <?= e($svc['short_description']) ?>
            </p>
            <div class="service-card-footer">
              <a href="<?= url($svc['file_name']) ?>" class="service-link-arrow">
                <span>Explore Discipline &amp; Specs</span>
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
        <span class="badge badge-indigo">Selection Framework</span>
      </div>
      <h2 class="section-title-editorial">Choosing The Right Photography Style For Your Campaign</h2>
      <p class="section-sub-editorial">
        Different digital touchpoints require distinct photographic formats. Here is how our three core photography disciplines compare.
      </p>
    </div>

    <div class="table-responsive" style="overflow-x: auto; background-color: #FFFFFF; border-radius: var(--radius-lg); border: 1px solid var(--color-border); box-shadow: var(--shadow-sm);">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
        <thead>
          <tr style="background-color: var(--color-indigo); color: #FFFFFF; font-family: var(--font-heading);">
            <th style="padding: 1.25rem 1.5rem;">Service Category</th>
            <th style="padding: 1.25rem 1.5rem;">Primary Channel</th>
            <th style="padding: 1.25rem 1.5rem;">Key Objective</th>
            <th style="padding: 1.25rem 1.5rem;">Typical Output</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-bottom: 1px solid var(--color-border);">
            <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--color-text-main);">White Background Packshots</td>
            <td style="padding: 1.25rem 1.5rem; color: var(--color-text-muted);">Amazon Main, Google Shopping, Shopify Category Grids</td>
            <td style="padding: 1.25rem 1.5rem; color: var(--color-text-muted);">Strict marketplace compliance, zero distractions, instant SKU recognition</td>
            <td style="padding: 1.25rem 1.5rem; color: var(--color-text-muted);">Square 3000x3000px, RGB 255 pure white, isolated contact shadow</td>
          </tr>
          <tr style="border-bottom: 1px solid var(--color-border); background-color: var(--color-bg-warm);">
            <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--color-text-main);">Lifestyle &amp; In-Context</td>
            <td style="padding: 1.25rem 1.5rem; color: var(--color-text-muted);">Meta/TikTok Ads, Homepage Heroes, Amazon A+ Content</td>
            <td style="padding: 1.25rem 1.5rem; color: var(--color-text-muted);">Emotional connection, aspirational storytelling, physical scale perception</td>
            <td style="padding: 1.25rem 1.5rem; color: var(--color-text-muted);">Architectural sets, live hand talent, curated botanical/stone props</td>
          </tr>
          <tr>
            <td style="padding: 1.25rem 1.5rem; font-weight: 700; color: var(--color-text-main);">Creative &amp; Advertising</td>
            <td style="padding: 1.25rem 1.5rem; color: var(--color-text-muted);">Paid Social Feeds, Billboards, Press Launches, Product Packaging</td>
            <td style="padding: 1.25rem 1.5rem; color: var(--color-text-muted);">Stop the infinite scroll, establish iconic category leadership</td>
            <td style="padding: 1.25rem 1.5rem; color: var(--color-text-muted);">High-speed kinetic splashes, levitation rigging, dramatic gobo shadows</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
