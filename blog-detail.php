<?php
/**
 * ECOMSHOOT - Blog Article Detail View
 * Full-Width Editorial Article Architecture with Sticky Sidebar & Dynamic TOC
 * Canonical HTML Endpoint: /blog/{id}
 */

require_once __DIR__ . '/config/config.php';
require_once INCLUDES_PATH . '/breadcrumbs.php';

$db = Database::getInstance()->getConnection();
$isAdmin = Auth::check();

$slug = trim((string)($_GET['slug'] ?? ''));
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

// 1. If accessed via numeric ID, 301 redirect to canonical slug URL
if (($id && $id > 0) && empty($slug)) {
    $stmt = $db->prepare("SELECT slug FROM blogs WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $foundSlug = $stmt->fetchColumn();
    if ($foundSlug) {
        http_response_code(301);
        header('Location: ' . url('blog/' . $foundSlug));
        exit;
    }
}

// 2. Fetch blog by slug (or fallback ID if not yet redirected)
$blog = null;
if (!empty($slug)) {
    $sql = "SELECT b.*, c.name as category_name, c.slug as category_slug 
            FROM blogs b 
            LEFT JOIN blog_categories c ON b.category_id = c.id 
            WHERE b.slug = :s";
    if (!$isAdmin) {
        $sql .= " AND b.status = 'published'";
    }
    $sql .= " LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute(['s' => $slug]);
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($id && $id > 0) {
    $sql = "SELECT b.*, c.name as category_name, c.slug as category_slug 
            FROM blogs b 
            LEFT JOIN blog_categories c ON b.category_id = c.id 
            WHERE b.id = :id";
    if (!$isAdmin) {
        $sql .= " AND b.status = 'published'";
    }
    $sql .= " LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute(['id' => $id]);
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$blog) {
    http_response_code(404);
    $pageIdentifier = '404';
    require_once INCLUDES_PATH . '/header.php';
    echo '<div class="container section" style="text-align: center; padding: 6rem 0;"><h1>Article Not Found</h1><p>The requested blog guide could not be located or has been archived.</p><a href="' . url('blog.php') . '" class="btn btn-primary">Return to Blogs</a></div>';
    require_once INCLUDES_PATH . '/footer.php';
    exit;
}

$pageIdentifier = 'blog-' . $blog['id'];

// Canonical URLs
$canonicalHtmlUrl = blog_url($blog['slug']);

// Custom SEO Metadata
$customSeo = [
  'meta_title' => $blog['title'] . ' | Ecomshoot Insights',
  'meta_description' => $blog['excerpt'],
  'canonical_url' => $canonicalHtmlUrl,
  'og_title' => $blog['title'],
  'og_description' => $blog['excerpt'],
  'og_url' => $canonicalHtmlUrl,
  'og_image' => asset_url($blog['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg'),
  'twitter_title' => $blog['title'],
  'twitter_description' => $blog['excerpt'],
  'twitter_image' => asset_url($blog['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg')
];

// Build Article Schema
$articleSchema = [
  '@context' => 'https://schema.org',
  '@type' => 'BlogPosting',
  'headline' => $blog['title'],
  'description' => $blog['excerpt'],
  'image' => asset_url($blog['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg'),
  'datePublished' => date('c', strtotime($blog['published_at'] ?? $blog['created_at'])),
  'dateModified' => date('c', strtotime($blog['updated_at'] ?? 'now')),
  'author' => [
    '@type' => 'Person',
    'name' => $blog['author_name'],
    'jobTitle' => $blog['author_role'] ?: 'Commercial Photography Director'
  ],
  'publisher' => [
    '@type' => 'Organization',
    'name' => get_setting('site_name', 'Ecomshoot'),
    'url' => BASE_URL,
    'logo' => [
      '@type' => 'ImageObject',
      'url' => asset_url('assets/images/logo.svg')
    ]
  ],
  'mainEntityOfPage' => [
    '@type' => 'WebPage',
    '@id' => $canonicalHtmlUrl
  ]
];

// Decode FAQs & Internal links
$faqs = json_decode($blog['faqs_json'] ?? '[]', true) ?: [];
$internalLinks = json_decode($blog['internal_links_json'] ?? '[]', true) ?: [];

$faqSchema = null;
if (!empty($faqs)) {
  $faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => []
  ];
  foreach ($faqs as $f) {
    $faqSchema['mainEntity'][] = [
      '@type' => 'Question',
      'name' => $f['question'],
      'acceptedAnswer' => [
        '@type' => 'Answer',
        'text' => $f['answer']
      ]
    ];
  }
}

// Build BreadcrumbList Schema for Article
$breadcrumbSchema = [
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
    [
      '@type' => 'ListItem',
      'position' => 1,
      'name' => 'Home',
      'item' => url('')
    ],
    [
      '@type' => 'ListItem',
      'position' => 2,
      'name' => 'Blog',
      'item' => url('blog')
    ],
    [
      '@type' => 'ListItem',
      'position' => 3,
      'name' => $blog['title'],
      'item' => $canonicalHtmlUrl
    ]
  ]
];

$customSchemas = array_filter([$articleSchema, $faqSchema, $breadcrumbSchema]);
if (!empty($blog['schemas_json'])) {
    $customSchemas[] = $blog['schemas_json'];
}

// Fetch Previous Article
$prevStmt = $db->prepare("SELECT id, slug, title FROM blogs WHERE id < :id AND status = 'published' ORDER BY id DESC LIMIT 1");
$prevStmt->execute(['id' => $blog['id']]);
$prevArticle = $prevStmt->fetch(PDO::FETCH_ASSOC);

// Fetch Next Article
$nextStmt = $db->prepare("SELECT id, slug, title FROM blogs WHERE id > :id AND status = 'published' ORDER BY id ASC LIMIT 1");
$nextStmt->execute(['id' => $blog['id']]);
$nextArticle = $nextStmt->fetch(PDO::FETCH_ASSOC);

// Fetch Related Articles (limit 3)
$relStmt = $db->prepare(
  "SELECT b.*, c.name as category_name 
     FROM blogs b 
     LEFT JOIN blog_categories c ON b.category_id = c.id 
     WHERE b.id != :id AND b.status = 'published' 
     ORDER BY b.published_at DESC LIMIT 3"
);
$relStmt->execute(['id' => $blog['id']]);
$relatedBlogs = $relStmt->fetchAll(PDO::FETCH_ASSOC);

// Render HTML content & Extract Table of Contents
$rawHtml = ($blog['content_format'] === 'markdown') 
    ? MarkdownParser::parse($blog['content']) 
    : $blog['content'];

// Dynamic TOC Extractor & Heading Anchor Injector
$tocItems = [];
$hIndex = 0;
$renderedContent = preg_replace_callback('/<h([2-3])([^>]*)>(.*?)<\/h\1>/i', function($matches) use (&$tocItems, &$hIndex) {
    $level = (int)$matches[1];
    $attrs = $matches[2];
    $title = strip_tags($matches[3]);
    $anchorId = 'heading-' . (++$hIndex) . '-' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
    $tocItems[] = [
        'id' => $anchorId,
        'title' => $title,
        'level' => $level
    ];
    return "<h{$level}{$attrs} id=\"{$anchorId}\">{$matches[3]}</h{$level}>";
}, $rawHtml);

// Fetch services for sidebar booking form
try {
  $servicesForForm = $db->query("SELECT title FROM services WHERE status = 'published' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) { $servicesForForm = []; }
if (empty($servicesForForm)) {
  $servicesForForm = [
    'Amazon & E-Commerce Photography',
    'Product Photography',
    'Lifestyle & In-Context',
    'Fashion & Apparel',
    'Creative & Advertising',
    'Macro Jewellery',
    'Photo Retouching',
    'White Background Packshots'
  ];
}

require_once INCLUDES_PATH . '/header.php';
?>

<!-- BreadcrumbList structured data; no visible breadcrumb navigation. -->
<?= render_breadcrumbs([
  ['title' => 'Blogs', 'url' => url('blog.php')],
  ['title' => $blog['title'], 'url' => null]
]) ?>

<article class="section blog-detail-section" style="padding-top: clamp(2rem, 4vw, 3.5rem); padding-bottom: clamp(4rem, 6vw, 6rem);">
  <div class="blog-fullwidth-wrap">

    <!-- 2. Editorial Hero -->
    <section class="article-detail-hero">
      <header class="article-editorial-header">
      <div class="article-meta-badge-row">
        <span class="badge badge-orange"><?= e($blog['category_name'] ?: 'Strategy') ?></span>
        <span class="meta-date">
          Published <?= date('F j, Y', strtotime($blog['published_at'] ?? $blog['created_at'])) ?>
        </span>
        <?php if (!empty($blog['updated_at']) && strtotime($blog['updated_at']) > strtotime($blog['published_at'] ?? $blog['created_at']) + 86400): ?>
          <span class="meta-sep">•</span>
          <span class="meta-updated">Updated <?= date('M j, Y', strtotime($blog['updated_at'])) ?></span>
        <?php endif; ?>
        <span class="meta-sep">•</span>
        <span class="meta-read"><?= e($blog['read_time']) ?></span>
        <span class="meta-sep">•</span>
      </div>

      <h1 class="article-headline">
        <?= e($blog['title']) ?>
      </h1>

      <p class="article-lead-summary">
        <?= e($blog['excerpt']) ?>
      </p>

      <!-- Author Capsule -->
      <div class="article-author-tag-strip">
        <div class="article-author-card">
          <div class="author-avatar author-avatar-lg">
            <?= get_author_initials($blog['author_name']) ?>
          </div>
          <div>
            <div class="author-name">
              <?= e($blog['author_name']) ?>
            </div>
            <div class="author-role">
              <?= e($blog['author_role'] ?: 'Senior Commercial Photography Director') ?>
            </div>
          </div>
        </div>

      </div>
      </header>

      <figure class="article-hero-figure">
        <img src="<?= asset_url($blog['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg') ?>"
             alt="<?= e($blog['featured_image_alt'] ?: $blog['title']) ?>"
             width="1400"
             height="787"
             class="article-hero-img"
             loading="eager">
        <?php if (!empty($blog['featured_image_caption'])): ?>
          <figcaption class="article-hero-caption">
            <?= e($blog['featured_image_caption']) ?>
          </figcaption>
        <?php endif; ?>
      </figure>
    </section>

    <!-- 4. Two-Column Layout Grid (72% Main Content Left, 28% Sticky Sidebar Right) -->
    <div class="article-layout-grid">

      <!-- Main Editorial Body Column (~72%) -->
      <div class="article-main-column">

        <!-- Mobile Collapsible Table of Contents Accordion (Visible on Mobile / Tablet <= 1024px) -->
        <?php if (!empty($tocItems)): ?>
          <div class="mobile-toc-container">
            <button type="button" class="mobile-toc-toggle" id="mobileTocToggle" aria-expanded="false" aria-controls="mobileTocDrawer">
              <span class="mobile-toc-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="8" y1="6" x2="21" y2="6"></line>
                  <line x1="8" y1="12" x2="21" y2="12"></line>
                  <line x1="8" y1="18" x2="21" y2="18"></line>
                  <line x1="3" y1="6" x2="3.01" y2="6"></line>
                  <line x1="3" y1="12" x2="3.01" y2="12"></line>
                  <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
                <span>Table of Contents</span>
              </span>
              <span class="mobile-toc-count"><?= count($tocItems) ?> sections</span>
              <svg class="mobile-toc-chevron" width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
            </button>
            <div class="mobile-toc-drawer" id="mobileTocDrawer" hidden>
              <ul class="toc-nav-list mobile-toc-list">
                <?php foreach ($tocItems as $item): ?>
                  <li class="toc-item toc-level-<?= $item['level'] ?>">
                    <a href="#<?= e($item['id']) ?>" class="toc-nav-link" data-target="<?= e($item['id']) ?>">
                      <?= ($item['level'] === 3) ? '<span class="toc-sub-arrow">↳</span> ' : '' ?><?= e($item['title']) ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        <?php endif; ?>

        <!-- Article Content -->
        <div class="article-content article-body-content" id="articleBody">
          <?= $renderedContent ?>
        </div>

        <!-- Recommended Production Services Box -->
        <?php if (!empty($internalLinks)): ?>
          <div class="article-internal-links-box">
            <h3 class="internal-links-title">
              Recommended Studio Production Services
            </h3>
            <ul class="internal-links-list">
              <?php foreach ($internalLinks as $link): ?>
                <li>
                  <a href="<?= url($link['url']) ?>" class="internal-service-link">
                    <span class="link-arrow">&rarr;</span> <?= e($link['anchor']) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Article FAQ Accordion Section -->
        <?php if (!empty($faqs)): ?>
          <div class="article-faq-section">
            <h2 class="article-faq-title">
              Frequently Asked Questions &amp; Technical Notes
            </h2>
            <div class="faq-list" style="margin: 0;">
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
        <?php endif; ?>

        <!-- Author Biography Card & Studio Booking CTA Section (Editorial Footer Cards) -->
        <div class="article-editorial-footer-grid">
          <!-- Author Biography Card -->
          <div class="article-widget blog-author-widget">
            <div class="article-widget-header">
              <span class="widget-icon">🖋️</span>
              <h3 class="article-widget-title">Author Perspective</h3>
            </div>
            <div class="author-widget-bio">
              <div class="author-avatar author-avatar-lg">
                <?= get_author_initials($blog['author_name']) ?>
              </div>
              <div>
                <div class="author-name">
                  <?= e($blog['author_name']) ?>
                </div>
                <div class="author-role">
                  <?= e($blog['author_role'] ?: 'Lead Studio Director') ?>
                </div>
              </div>
            </div>
            <p class="author-bio-text">
              Specializes in optical calibration, large-scale commercial photo sets, and brand conversion optimization across luxury and retail channels.
            </p>
          </div>

          <!-- Direct Studio Shoot Booking CTA Widget -->
          <div class="article-widget blog-cta-widget">
            <span class="badge badge-lime">Studio Availability</span>
            <h3 class="cta-widget-title">
              Elevate Your Brand's Visual Catalog
            </h3>
            <p class="cta-widget-desc">
              Ship your samples to our Delhi studio for calibrated commercial photography engineered to boost conversions.
            </p>
            <a href="<?= url('contact.php') ?>" class="btn btn-primary cta-widget-btn">
              Book a Studio Shoot &rarr;
            </a>
          </div>
        </div>

        <!-- Social Share Bar -->
        <div class="article-share-strip">
          <span class="share-strip-label">Share This Guide:</span>
          <a href="https://twitter.com/intent/tweet?text=<?= urlencode($blog['title']) ?>&url=<?= urlencode($canonicalHtmlUrl) ?>" target="_blank" rel="noopener noreferrer" class="share-icon-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            <span>X / Twitter</span>
          </a>
          <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($canonicalHtmlUrl) ?>" target="_blank" rel="noopener noreferrer" class="share-icon-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
            <span>LinkedIn</span>
          </a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonicalHtmlUrl) ?>" target="_blank" rel="noopener noreferrer" class="share-icon-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            <span>Facebook</span>
          </a>
          <button type="button" class="share-icon-btn" onclick="navigator.clipboard.writeText('<?= $canonicalHtmlUrl ?>'); this.innerText='✓ Link Copied!';">
            <span>📋 Copy Link</span>
          </button>
        </div>

        <!-- Previous and Next Article Navigation -->
        <?php if ($prevArticle || $nextArticle): ?>
          <nav class="article-prev-next-grid" aria-label="Article Pagination Navigation">
            <?php if ($prevArticle): ?>
              <a href="<?= blog_url($prevArticle['slug']) ?>" class="article-nav-card">
                <span class="article-nav-direction">&larr; Previous Studio Article</span>
                <h4 class="article-nav-title"><?= e($prevArticle['title']) ?></h4>
              </a>
            <?php else: ?>
              <div></div>
            <?php endif; ?>

            <?php if ($nextArticle): ?>
              <a href="<?= blog_url($nextArticle['slug']) ?>" class="article-nav-card article-nav-card-next">
                <span class="article-nav-direction">Next Studio Article &rarr;</span>
                <h4 class="article-nav-title"><?= e($nextArticle['title']) ?></h4>
              </a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>

      </div>

      <!-- Sticky Editorial Sidebar (~28% Column on Desktop) -->
      <aside class="article-sidebar-sticky blog-sidebar-col" aria-label="Table of Contents &amp; Booking">

        <!-- Sidebar Studio Booking / Enquiry Form (Sticky) -->
        <div class="article-widget blog-sidebar-booking-card luxury-card" style="padding: 1.25rem 1.35rem !important;">
          <div class="enquiry-card-header" style="text-align: center; margin-bottom: 0.95rem;">
            <div style="display: flex; justify-content: center; margin-bottom: 0.5rem;">
              <span class="enquiry-card-badge" style="display: inline-block; font-family: 'JetBrains Mono', monospace; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #FF704F; background: rgba(255, 112, 79, 0.12); border: 1px solid rgba(255, 112, 79, 0.35); border-radius: 999px; padding: 0.25rem 0.85rem;">INSTANT STUDIO ESTIMATE</span>
            </div>
            <h3 class="enquiry-card-title" style="color: #FFFFFF; font-size: 1.5rem; font-weight: 800; margin: 0 0 0.75rem 0; letter-spacing: -0.02em;">Book a Photoshoot</h3>
            <div style="height: 1px; background: rgba(255, 255, 255, 0.08); width: 100%;"></div>
          </div>
          <form action="<?= url('submit-lead.php') ?>" method="POST" class="blog-sidebar-form" data-blog-lead-form>
            <?= CSRF::inputField() ?>
            <input type="hidden" name="source_page_url" value="blog/<?= e($blog['slug']) ?>">
            <input type="hidden" name="source_page_title" value="<?= e($blog['title']) ?>">
            <input type="hidden" name="source_page_type" value="blog_detail_sidebar">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.7rem;">
              <div class="form-group" style="margin: 0; min-width: 0;">
                <label for="blog_side_name" style="display: block; color: #FFFFFF; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.3rem;">Full Name <span style="color: #FF704F;">*</span></label>
                <input class="form-control" id="blog_side_name" name="full_name" type="text" placeholder="e.g. Rahul Sharma" required style="width: 100%; height: 40px; background: #0c0913; border: 1px solid #36284b; color: #FFFFFF; border-radius: 9px; padding: 0.45rem 0.65rem; font-size: 0.8rem; box-sizing: border-box;">
              </div>
              <div class="form-group" style="margin: 0; min-width: 0;">
                <label for="blog_side_email" style="display: block; color: #FFFFFF; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.3rem;">Email Address <span style="color: #FF704F;">*</span></label>
                <input class="form-control" id="blog_side_email" name="email" type="email" placeholder="e.g. rahul@company.com" required style="width: 100%; height: 40px; background: #0c0913; border: 1px solid #36284b; color: #FFFFFF; border-radius: 9px; padding: 0.45rem 0.65rem; font-size: 0.8rem; box-sizing: border-box;">
              </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.7rem;">
              <div class="form-group" style="margin: 0; min-width: 0;">
                <label for="blog_side_mobile" style="display: block; color: #FFFFFF; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.3rem;">Contact Number</label>
                <input class="form-control" id="blog_side_mobile" name="mobile" type="tel" placeholder="e.g. 9876543210" required style="width: 100%; height: 40px; background: #0c0913; border: 1px solid #36284b; color: #FFFFFF; border-radius: 9px; padding: 0.45rem 0.65rem; font-size: 0.8rem; box-sizing: border-box;">
              </div>
              <div class="form-group" style="margin: 0; min-width: 0;">
                <label for="blog_side_service" style="display: block; color: #FFFFFF; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.3rem;">Service Required</label>
                <select class="form-control form-select" id="blog_side_service" name="lead_source" required style="width: 100%; height: 40px; background: #0c0913; border: 1px solid #36284b; color: #FFFFFF; border-radius: 9px; padding: 0.45rem 0.65rem; font-size: 0.8rem; box-sizing: border-box; cursor: pointer;">
                  <option value="" style="background: #171120; color: #B8ADCA;">Select service</option>
                  <?php foreach ($servicesForForm as $sTitle): ?>
                    <option value="<?= e($sTitle) ?>" style="background: #171120; color: #FFFFFF;"><?= e($sTitle) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group" style="margin-bottom: 0.85rem;">
              <label for="blog_side_message" style="display: block; color: #FFFFFF; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.3rem;">Project Requirements / Message</label>
              <textarea class="form-control" id="blog_side_message" name="message" rows="2" placeholder="e.g. We need 50 product images for our Amazon listings..." required style="width: 100%; min-height: 64px; background: #0c0913; border: 1px solid #36284b; color: #FFFFFF; border-radius: 9px; padding: 0.5rem 0.65rem; font-size: 0.8rem; box-sizing: border-box; resize: vertical;"></textarea>
            </div>

            <button type="submit" class="btn btn-primary services-submit-btn btn-studio-primary" style="display: block; width: 100%; height: 44px; font-size: 0.95rem; font-weight: 700; border-radius: 10px; background: #7847D4; border: none; color: #FFFFFF; box-shadow: 0 4px 18px rgba(120, 71, 212, 0.4); cursor: pointer; transition: all 0.25s ease;">Submit Enquiry</button>
          </form>
        </div>

        <div class="blog-form-modal" id="blogFormSuccessModal" role="dialog" aria-modal="true" aria-labelledby="blogFormSuccessTitle" aria-describedby="blogFormSuccessMessage" aria-hidden="true" hidden>
          <div class="blog-form-modal__backdrop" data-blog-form-close></div>
          <div class="blog-form-modal__dialog" role="document" tabindex="-1">
            <button type="button" class="blog-form-modal__close" aria-label="Close confirmation" data-blog-form-close>&times;</button>
            <span class="blog-form-modal__check" aria-hidden="true">✓</span>
            <h2 id="blogFormSuccessTitle">Form Submitted Successfully!</h2>
            <p id="blogFormSuccessMessage">Thank you for reaching out. Our team will contact you shortly.</p>
            <button type="button" class="btn btn-primary blog-form-modal__confirm" data-blog-form-close>Okay, Got It</button>
          </div>
        </div>

        <!-- Table of Contents Widget (Desktop Sticky) -->
        <?php if (!empty($tocItems)): ?>
          <div class="article-widget blog-toc-card">
            <div class="blog-toc-header">
              <div class="blog-toc-title-group">
                <svg class="blog-toc-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="8" y1="6" x2="21" y2="6"></line>
                  <line x1="8" y1="12" x2="21" y2="12"></line>
                  <line x1="8" y1="18" x2="21" y2="18"></line>
                  <line x1="3" y1="6" x2="3.01" y2="6"></line>
                  <line x1="3" y1="12" x2="3.01" y2="12"></line>
                  <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
                <span class="blog-toc-title">Table of Contents</span>
              </div>
              <span class="blog-toc-badge"><?= count($tocItems) ?> sections</span>
            </div>
            <ul class="toc-nav-list desktop-toc-list">
              <?php foreach ($tocItems as $item): ?>
                <li class="toc-item toc-level-<?= $item['level'] ?>">
                  <a href="#<?= e($item['id']) ?>" class="toc-nav-link" data-target="<?= e($item['id']) ?>">
                    <?= ($item['level'] === 3) ? '<span class="toc-sub-arrow">↳</span> ' : '' ?><?= e($item['title']) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

      </aside>

    </div>

  </div>
</article>

<!-- 5. Related Articles Section -->
<?php if (!empty($relatedBlogs)): ?>
  <section class="section blog-related-section" style="background-color: #17131F; border-top: 1px solid #3A3047; padding: clamp(3.5rem, 5vw, 5.5rem) 0;">
    <div class="blog-fullwidth-wrap">
      <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
          <span class="badge badge-orange">Continue Reading</span>
          <h2 style="font-family: var(--font-heading); font-size: clamp(1.8rem, 3vw, 2.4rem); font-weight: 700; margin-top: 0.5rem; margin-bottom: 0;">
            Related Studio Perspectives &amp; Masterclasses
          </h2>
        </div>
        <a href="<?= url('blog.php') ?>" class="btn btn-outline">
          View All Guides &rarr;
        </a>
      </div>

      <div class="blog-posts-grid">
        <?php foreach ($relatedBlogs as $rb): ?>
          <article class="blog-card">
            <div class="blog-card-media">
              <a href="<?= blog_url($rb['slug']) ?>" tabindex="-1" aria-hidden="true">
                <img src="<?= asset_url($rb['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg') ?>"
                     alt="<?= e($rb['featured_image_alt'] ?: $rb['title']) ?>"
                     width="400"
                     height="250"
                     loading="lazy">
              </a>
              <span class="blog-card-badge"><?= e($rb['category_name'] ?: 'Strategy') ?></span>
            </div>
            <div class="blog-card-body">
              <div class="blog-card-meta">
                <span><?= date('M j, Y', strtotime($rb['published_at'] ?? 'now')) ?></span>
                <span class="meta-sep">&bull;</span>
                <span><?= e($rb['read_time']) ?></span>
              </div>
              <h3 class="blog-card-title">
                <a href="<?= blog_url($rb['slug']) ?>"><?= e($rb['title']) ?></a>
              </h3>
              <p class="blog-card-desc"><?= e(mb_strimwidth($rb['excerpt'], 0, 120, '...')) ?></p>
              <div class="blog-card-footer">
                <div class="blog-card-author-wrap">
                  <div class="author-avatar author-avatar-sm">
                    <?= get_author_initials($rb['author_name']) ?>
                  </div>
                  <span class="blog-card-author">By <?= e($rb['author_name']) ?></span>
                </div>
                <div class="blog-card-actions">
                  <a href="<?= blog_url($rb['slug']) ?>" class="blog-card-link"><span>Read Guide</span> <span class="arrow">&rarr;</span></a>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
