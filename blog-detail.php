<?php
/**
 * ECOMSHOOT - Blog Article Detail View
 * Full-Width Editorial Article Architecture with Sticky Sidebar & Dynamic TOC
 * Canonical HTML Endpoint: /blog/{id}
 */

require_once __DIR__ . '/config/config.php';
require_once INCLUDES_PATH . '/breadcrumbs.php';

$db = Database::getInstance()->getConnection();

// 1. Backward Compatibility: 301 Permanent Redirect for legacy slug URLs
$legacySlug = trim($_GET['slug'] ?? '');
if (!empty($legacySlug)) {
    $slugStmt = $db->prepare("SELECT id FROM blogs WHERE slug = :s LIMIT 1");
    $slugStmt->execute(['s' => $legacySlug]);
    $foundId = $slugStmt->fetchColumn();
    if ($foundId) {
        http_response_code(301);
        header('Location: ' . blog_url((int)$foundId));
        exit;
    } else {
        http_response_code(404);
        $pageIdentifier = '404';
        require_once INCLUDES_PATH . '/header.php';
        echo '<div class="container section" style="text-align: center; padding: 6rem 0;"><h1>Article Not Found</h1><p>The requested blog guide could not be located or has been archived.</p><a href="' . url('blog.php') . '" class="btn btn-primary">Return to Blogs</a></div>';
        require_once INCLUDES_PATH . '/footer.php';
        exit;
    }
}

// 2. Validate numeric ID
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    http_response_code(404);
    $pageIdentifier = '404';
    require_once INCLUDES_PATH . '/header.php';
    echo '<div class="container section" style="text-align: center; padding: 6rem 0;"><h1>Article Not Found</h1><p>The requested blog guide could not be located or has been archived.</p><a href="' . url('blog.php') . '" class="btn btn-primary">Return to Blogs</a></div>';
    require_once INCLUDES_PATH . '/footer.php';
    exit;
}

// 3. Fetch blog by numeric ID (enforcing published status for public requests)
$isAdmin = Auth::check();
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
$canonicalHtmlUrl = blog_url($blog['id']);
$canonicalMdUrl = blog_md_url($blog['id']);

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

$customSchemas = array_filter([$articleSchema, $faqSchema]);
if (!empty($blog['schemas_json'])) {
    $customSchemas[] = $blog['schemas_json'];
}

// Fetch assigned tags
$tagStmt = $db->prepare(
    "SELECT t.name, t.slug 
     FROM blog_tags t 
     JOIN blog_tag_relations r ON t.id = r.tag_id 
     WHERE r.blog_id = :bid 
     ORDER BY t.name ASC"
);
$tagStmt->execute(['bid' => $blog['id']]);
$articleTags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Previous Article
$prevStmt = $db->prepare("SELECT id, title FROM blogs WHERE id < :id AND status = 'published' ORDER BY id DESC LIMIT 1");
$prevStmt->execute(['id' => $blog['id']]);
$prevArticle = $prevStmt->fetch(PDO::FETCH_ASSOC);

// Fetch Next Article
$nextStmt = $db->prepare("SELECT id, title FROM blogs WHERE id > :id AND status = 'published' ORDER BY id ASC LIMIT 1");
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

require_once INCLUDES_PATH . '/header.php';
?>

<!-- 1. Breadcrumbs -->
<?= render_breadcrumbs([
  ['title' => 'Blogs', 'url' => url('blog.php')],
  ['title' => $blog['title'], 'url' => null]
]) ?>

<article class="section" style="padding-top: clamp(2rem, 4vw, 3.5rem); padding-bottom: clamp(4rem, 6vw, 6rem);">
  <div class="blog-fullwidth-wrap">

    <!-- 2. Full-Width Editorial Header -->
    <header class="article-editorial-header">
      <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap;">
        <span class="badge badge-orange"><?= e($blog['category_name'] ?: 'Strategy') ?></span>
        <span style="font-size: 0.88rem; color: var(--color-text-light); font-weight: 500;">
          Published <?= date('F j, Y', strtotime($blog['published_at'] ?? $blog['created_at'])) ?>
        </span>
        <?php if (!empty($blog['updated_at']) && strtotime($blog['updated_at']) > strtotime($blog['published_at'] ?? $blog['created_at']) + 86400): ?>
          <span style="font-size: 0.88rem; color: var(--color-text-light);">• Updated <?= date('M j, Y', strtotime($blog['updated_at'])) ?></span>
        <?php endif; ?>
        <span style="font-size: 0.88rem; color: var(--color-text-light);">•</span>
        <span style="font-size: 0.88rem; color: var(--color-text-light); font-weight: 500;"><?= e($blog['read_time']) ?></span>
        <span style="font-size: 0.88rem; color: var(--color-text-light);">•</span>
        <a href="<?= blog_md_url($blog['id']) ?>" target="_blank" class="badge" style="background: #4F46E5; color: #FFFFFF; text-decoration: none; font-size: 0.75rem; letter-spacing: 0.05em; font-family: monospace;" title="Direct raw Markdown representation for AI/LLMs">
          Markdown .md &nearr;
        </a>
      </div>

      <h1 class="article-headline">
        <?= e($blog['title']) ?>
      </h1>

      <p class="article-lead-summary">
        <?= e($blog['excerpt']) ?>
      </p>

      <!-- Author Capsule & Tag Pills -->
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border);">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <div class="author-avatar" style="width: 48px; height: 48px; font-size: 1.05rem; background: var(--color-primary-teal);">
            <?= substr($blog['author_name'], 0, 2) ?>
          </div>
          <div>
            <div style="font-family: var(--font-heading); font-weight: 700; font-size: 1rem; color: var(--color-text-main);">
              <?= e($blog['author_name']) ?>
            </div>
            <div style="font-size: 0.82rem; color: var(--color-text-light);">
              <?= e($blog['author_role'] ?: 'Senior Commercial Photography Director') ?>
            </div>
          </div>
        </div>

        <?php if (!empty($articleTags)): ?>
          <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
            <?php foreach ($articleTags as $tag): ?>
              <a href="<?= url('blog.php?tag=' . urlencode($tag['slug'])) ?>" style="font-size: 0.78rem; padding: 0.25rem 0.65rem; border-radius: 9999px; background: #FFFFFF; color: var(--color-primary-teal); border: 1px solid var(--color-border); text-decoration: none; font-weight: 600;">
                #<?= e($tag['name']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </header>

    <!-- 3. Wide Featured Image Section -->
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

    <!-- 4. Two-Column Layout Grid (70-75% Main Content, 25-30% Sticky Sidebar) -->
    <div class="article-layout-grid">

      <!-- Main Editorial Body Column -->
      <div class="article-main-column">

        <!-- Article Content -->
        <div class="article-content" id="articleBody">
          <?= $renderedContent ?>
        </div>

        <!-- Recommended Production Services Box -->
        <?php if (!empty($internalLinks)): ?>
          <div style="margin: 3.5rem 0; padding: 2rem; background-color: var(--color-cream-light); border-radius: var(--radius-lg); border: 1px solid var(--color-border);">
            <h3 style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-text-main);">
              Recommended Studio Production Services
            </h3>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem;">
              <?php foreach ($internalLinks as $link): ?>
                <li>
                  <a href="<?= url($link['url']) ?>" style="font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-primary-teal); text-decoration: none;">
                    <span style="color: var(--color-orange);">&rarr;</span> <?= e($link['anchor']) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Article FAQ Accordion Section -->
        <?php if (!empty($faqs)): ?>
          <div style="margin: 4rem 0;">
            <h2 style="font-family: var(--font-heading); font-size: clamp(1.6rem, 2.5vw, 2rem); font-weight: 700; margin-bottom: 1.5rem; color: var(--color-text-main);">
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
              <a href="<?= blog_url($prevArticle['id']) ?>" class="article-nav-card">
                <span class="article-nav-direction">&larr; Previous Studio Article</span>
                <h4 class="article-nav-title"><?= e($prevArticle['title']) ?></h4>
              </a>
            <?php else: ?>
              <div></div>
            <?php endif; ?>

            <?php if ($nextArticle): ?>
              <a href="<?= blog_url($nextArticle['id']) ?>" class="article-nav-card" style="text-align: right;">
                <span class="article-nav-direction">Next Studio Article &rarr;</span>
                <h4 class="article-nav-title"><?= e($nextArticle['title']) ?></h4>
              </a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>

      </div>

      <!-- Sticky Editorial Sidebar (25-30% Column on Desktop) -->
      <aside class="article-sidebar-sticky" aria-label="Article Sidebar Information">

        <!-- Table of Contents Widget -->
        <?php if (!empty($tocItems)): ?>
          <div class="article-widget">
            <h3 class="article-widget-title">
              <span>📑</span> Table of Contents
            </h3>
            <ul class="toc-nav-list">
              <?php foreach ($tocItems as $item): ?>
                <li style="margin-left: <?= ($item['level'] === 3) ? '1rem' : '0' ?>;">
                  <a href="#<?= e($item['id']) ?>" class="toc-nav-link">
                    <?= ($item['level'] === 3) ? '↳ ' : '' ?><?= e($item['title']) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Author Biography Card -->
        <div class="article-widget" style="background-color: var(--color-cream-light);">
          <h3 class="article-widget-title">
            <span>🖋️</span> Author Perspective
          </h3>
          <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 0.85rem;">
            <div class="author-avatar" style="width: 48px; height: 48px; font-size: 1rem; background: var(--color-primary-teal);">
              <?= substr($blog['author_name'], 0, 2) ?>
            </div>
            <div>
              <div style="font-family: var(--font-heading); font-weight: 700; color: var(--color-text-main); font-size: 0.95rem;">
                <?= e($blog['author_name']) ?>
              </div>
              <div style="font-size: 0.78rem; color: var(--color-text-light);">
                <?= e($blog['author_role'] ?: 'Lead Studio Director') ?>
              </div>
            </div>
          </div>
          <p style="font-size: 0.88rem; line-height: 1.6; color: var(--color-text-muted); margin: 0;">
            Specializes in optical calibration, large-scale commercial photo sets, and brand conversion optimization across luxury and retail channels.
          </p>
        </div>

        <!-- Direct Studio Shoot Booking CTA Widget -->
        <div class="article-widget" style="background: linear-gradient(135deg, #0F2C2E 0%, #153E41 100%); color: #FFFFFF; border: none;">
          <span class="badge" style="background: rgba(255,255,255,0.15); color: var(--color-lime); margin-bottom: 0.75rem; font-size: 0.72rem;">Studio Availability</span>
          <h3 style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 700; color: #FFFFFF; line-height: 1.3; margin-bottom: 0.75rem;">
            Elevate Your Brand's Visual Catalog
          </h3>
          <p style="font-size: 0.85rem; line-height: 1.55; color: rgba(255,255,255,0.85); margin-bottom: 1.25rem;">
            Ship your samples to our New York studio for calibrated commercial photography engineered to boost conversions.
          </p>
          <a href="<?= url('contact.php') ?>" class="btn btn-primary" style="width: 100%; text-align: center; justify-content: center; background: var(--color-orange); border-color: var(--color-orange); font-size: 0.9rem;">
            Book a Studio Shoot &rarr;
          </a>
        </div>

        <!-- Raw Markdown Document Card -->
        <div class="article-widget" style="padding: 1.25rem; text-align: center; background: #FFFFFF;">
          <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-muted); margin-bottom: 0.4rem;">Machine-Readable Format</div>
          <div style="font-size: 0.85rem; color: var(--color-text-main); margin-bottom: 0.85rem;">Clean Markdown with YAML frontmatter for LLM indexing &amp; researchers.</div>
          <a href="<?= $canonicalMdUrl ?>" target="_blank" class="btn btn-sm btn-outline" style="width: 100%; justify-content: center; font-family: monospace; font-size: 0.8rem;">
            View /blog/<?= (int)$blog['id'] ?>.md &nearr;
          </a>
        </div>

      </aside>

    </div>

  </div>
</article>

<!-- 5. Related Articles Section -->
<?php if (!empty($relatedBlogs)): ?>
  <section class="section" style="background-color: var(--color-cream-light); border-top: 1px solid var(--color-border); padding: clamp(3.5rem, 5vw, 5.5rem) 0;">
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

      <div class="blog-editorial-grid-wide" style="grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));">
        <?php foreach ($relatedBlogs as $rb): ?>
          <article class="editorial-blog-card">
            <div class="blog-media-wrap">
              <img src="<?= asset_url($rb['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg') ?>"
                   alt="<?= e($rb['featured_image_alt'] ?: $rb['title']) ?>" 
                   width="400" 
                   height="250" 
                   loading="lazy">
              <span class="blog-cat-pill"><?= e($rb['category_name'] ?: 'Strategy') ?></span>
            </div>
            <div class="blog-body-wrap">
              <div class="blog-time-meta"><?= e($rb['read_time']) ?> • Published <?= date('M j, Y', strtotime($rb['published_at'] ?? 'now')) ?></div>
              <h3 class="blog-heading">
                <a href="<?= blog_url($rb['id']) ?>"><?= e($rb['title']) ?></a>
              </h3>
              <p class="blog-excerpt-copy"><?= e(mb_strimwidth($rb['excerpt'], 0, 115, '...')) ?></p>
              <div class="blog-footer-meta">
                <span class="blog-author-tag">By <?= e($rb['author_name']) ?></span>
                <a href="<?= blog_url($rb['id']) ?>" class="blog-read-link">Read Guide &rarr;</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
