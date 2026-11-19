<?php
/**
 * ECOMSHOOT - Studio Intelligence & Editorial Blog Catalog
 * Premium Full-Width Commercial Photography Publication
 */

$pageIdentifier = 'blog';
require_once __DIR__ . '/config/config.php';
require_once INCLUDES_PATH . '/breadcrumbs.php';

$db = Database::getInstance()->getConnection();

// Category, Tag, Search & Pagination filter
$categorySlug = trim($_GET['category'] ?? '');
$tagSlug = trim($_GET['tag'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');
$currentPageNum = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;

$sql = "SELECT DISTINCT b.*, c.name as category_name, c.slug as category_slug 
        FROM blogs b 
        LEFT JOIN blog_categories c ON b.category_id = c.id";

if ($tagSlug !== '') {
    $sql .= " INNER JOIN blog_tag_relations btr ON b.id = btr.blog_id 
              INNER JOIN blog_tags bt ON btr.tag_id = bt.id";
}

$sql .= " WHERE b.status = 'published'";
$params = [];

if ($categorySlug !== '') {
    $sql .= " AND c.slug = :cat";
    $params['cat'] = $categorySlug;
}

if ($tagSlug !== '') {
    $sql .= " AND bt.slug = :tag";
    $params['tag'] = $tagSlug;
}

if ($searchQuery !== '') {
    $sql .= " AND (b.title LIKE :q1 OR b.excerpt LIKE :q2 OR b.content LIKE :q3)";
    $params['q1'] = '%' . $searchQuery . '%';
    $params['q2'] = '%' . $searchQuery . '%';
    $params['q3'] = '%' . $searchQuery . '%';
}

$sql .= " ORDER BY b.published_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$allMatchedBlogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalArticles = count($allMatchedBlogs);
$totalPages = max(1, (int)ceil($totalArticles / $perPage));
if ($currentPageNum > $totalPages) {
    $currentPageNum = $totalPages;
}

$offset = ($currentPageNum - 1) * $perPage;
$blogs = array_slice($allMatchedBlogs, $offset, $perPage);

// If first page and no active search, separate first article as prominent featured spotlight
$featuredBlog = null;
$gridBlogs = $blogs;
if ($currentPageNum === 1 && empty($searchQuery) && empty($categorySlug) && empty($tagSlug) && count($blogs) > 0) {
    $featuredBlog = $blogs[0];
    $gridBlogs = array_slice($blogs, 1);
}

// Fetch categories for filter tabs
$categories = $db->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

require_once INCLUDES_PATH . '/header.php';
?>

<!-- BreadcrumbList structured data; no visible breadcrumb navigation. -->
<?= render_breadcrumbs([['title' => 'Blogs', 'url' => null]]) ?>

<!-- 1. Full-Width Editorial Hero Banner -->
<section class="blog-editorial-hero">
  <div class="blog-fullwidth-wrap">
    <div class="blog-hero-split">
      <div class="blog-hero-text">
        <div class="blog-hero-tagline">
          <span class="badge badge-orange">Studio Intelligence</span>
          <span class="badge badge-indigo">Commercial Perspectives</span>
        </div>
        <h1 class="blog-hero-title">
          Commercial Photography Intelligence &amp; Visual Strategy
        </h1>
        <p class="blog-hero-lead">
          In-depth technical guides, lighting masterclasses, Amazon compliance blueprints, and art direction frameworks engineered by our senior commercial studio leads.
        </p>
      </div>

    </div>
  </div>
</section>

<!-- 2. OrderPrintex-Inspired Fluid Category Navigation & Search Bar -->
<section class="blog-category-nav-section">
  <div class="blog-fullwidth-wrap">
    <div class="blog-nav-search-bar">
      
      <!-- Fluid Category Chips Track -->
      <div class="blog-category-track">
        <a href="<?= url('blog.php') ?>" class="blog-cat-chip <?= ($categorySlug === '' && $tagSlug === '') ? 'is-active' : '' ?>">
          All Disciplines
        </a>
        <?php foreach ($categories as $cat): ?>
          <a href="<?= url('blog.php?category=' . urlencode($cat['slug'])) ?>" class="blog-cat-chip <?= ($categorySlug === $cat['slug']) ? 'is-active' : '' ?>">
            <?= e($cat['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Clean Search Form -->
      <form action="<?= url('blog.php') ?>" method="GET" class="blog-search-form" role="search">
        <?php if ($categorySlug): ?>
          <input type="hidden" name="category" value="<?= e($categorySlug) ?>">
        <?php endif; ?>
        <?php if ($tagSlug): ?>
          <input type="hidden" name="tag" value="<?= e($tagSlug) ?>">
        <?php endif; ?>
        <input type="search" name="q" value="<?= e($searchQuery) ?>" placeholder="Search guides, optics, setups..." class="blog-search-input" aria-label="Search articles" autocomplete="off">
        <button type="submit" class="blog-search-btn" aria-label="Search">
          <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/></svg>
          <span>Search</span>
        </button>
      </form>

    </div>

  </div>
</section>

<!-- 3. Full-Width Editorial Catalog Section -->
<section class="section blog-catalog-section">
  <div class="blog-fullwidth-wrap">

    <?php if (empty($blogs)): ?>
      <div class="blog-empty-state">
        <span class="empty-icon">🔍</span>
        <h3 class="empty-title">No studio articles found</h3>
        <p class="empty-text">No published articles matched your selected search criteria. Try clearing tags or searching for broader terms like "lighting" or "Amazon".</p>
        <a href="<?= url('blog.php') ?>" class="btn btn-primary">Reset All Filters</a>
      </div>
    <?php else: ?>

      <!-- Prominent Featured Article Spotlight -->
      <?php if ($featuredBlog): ?>
        <article class="blog-featured-article">
          <div class="blog-featured-media">
            <img src="<?= asset_url($featuredBlog['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg') ?>" 
                 alt="<?= e($featuredBlog['featured_image_alt'] ?: $featuredBlog['title']) ?>" 
                 width="800" 
                 height="500" 
                 loading="eager">
            <span class="blog-featured-badge">
              Featured &bull; <?= e($featuredBlog['category_name'] ?: 'Strategy') ?>
            </span>
          </div>
          <div class="blog-featured-content">
            <div class="blog-card-meta">
              <span class="meta-date">Published <?= date('F j, Y', strtotime($featuredBlog['published_at'] ?? 'now')) ?></span>
              <span class="meta-sep">&bull;</span>
              <span class="meta-read"><?= e($featuredBlog['read_time']) ?></span>
            </div>

            <h2 class="blog-featured-title">
              <a href="<?= blog_url($featuredBlog['slug']) ?>"><?= e($featuredBlog['title']) ?></a>
            </h2>

            <p class="blog-featured-desc">
              <?= e($featuredBlog['excerpt']) ?>
            </p>

            <div class="blog-featured-footer">
              <div class="blog-author-lockup">
                <div class="author-avatar">
                  <?= get_author_initials($featuredBlog['author_name']) ?>
                </div>
                <div>
                  <div class="author-name"><?= e($featuredBlog['author_name']) ?></div>
                  <div class="author-role"><?= e($featuredBlog['author_role'] ?: 'Studio Lead') ?></div>
                </div>
              </div>

              <a href="<?= blog_url($featuredBlog['slug']) ?>" class="btn btn-primary blog-featured-cta">
                <span>Read Full Guide</span>
                <span class="arrow">&rarr;</span>
              </a>
            </div>

          </div>
        </article>
      <?php endif; ?>

      <!-- Section Title for Grid -->
      <div class="blog-grid-header">
        <div>
          <span class="badge badge-orange">Studio Knowledge Base</span>
          <h2 class="blog-grid-title">
            <?= ($featuredBlog) ? 'Latest Studio Perspectives' : 'Matching Guides (' . $totalArticles . ')' ?>
          </h2>
        </div>
        <div class="blog-grid-count">
          Showing Page <?= $currentPageNum ?> of <?= $totalPages ?>
        </div>
      </div>

      <!-- Responsive Multi-Column Blog Card Grid (OrderPrintex Reference) -->
      <div class="blog-posts-grid">
        <?php foreach ($gridBlogs as $b): ?>
          <article class="blog-card">
            <div class="blog-card-media">
              <a href="<?= blog_url($b['slug']) ?>" tabindex="-1" aria-hidden="true">
                <img src="<?= asset_url($b['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg') ?>"
                     alt="<?= e($b['featured_image_alt'] ?: $b['title']) ?>"
                     width="420"
                     height="260"
                     loading="lazy">
              </a>
              <span class="blog-card-badge"><?= e($b['category_name'] ?: 'Strategy') ?></span>
            </div>
            <div class="blog-card-body">
              <div class="blog-card-meta">
                <span><?= date('M j, Y', strtotime($b['published_at'] ?? 'now')) ?></span>
                <span class="meta-sep">&bull;</span>
                <span><?= e($b['read_time']) ?></span>
              </div>
              <h3 class="blog-card-title">
                <a href="<?= blog_url($b['slug']) ?>"><?= e($b['title']) ?></a>
              </h3>
              <p class="blog-card-desc">
                <?= e(mb_strimwidth($b['excerpt'], 0, 140, '...')) ?>
              </p>

              <div class="blog-card-footer">
                <div class="blog-card-author-wrap">
                  <div class="author-avatar author-avatar-sm">
                    <?= get_author_initials($b['author_name']) ?>
                  </div>
                  <span class="blog-card-author">By <?= e($b['author_name']) ?></span>
                </div>
                <div class="blog-card-actions">
                  <a href="<?= blog_url($b['slug']) ?>" class="blog-card-link">
                    <span>Read Full Article</span> <span class="arrow">&rarr;</span>
                  </a>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <!-- 4. Numeric Pagination Controls -->
      <?php if ($totalPages > 1): ?>
        <nav class="blog-pagination-nav" aria-label="Blog Pagination Navigation">
          <?php if ($currentPageNum > 1): ?>
            <a href="<?= url('blog.php?' . http_build_query(array_merge($_GET, ['page' => $currentPageNum - 1]))) ?>" class="blog-page-btn" aria-label="Previous Page">&larr; Prev</a>
          <?php endif; ?>

          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="<?= url('blog.php?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>" class="blog-page-btn <?= ($p === $currentPageNum) ? 'active' : '' ?>" aria-label="Page <?= $p ?>" <?= ($p === $currentPageNum) ? 'aria-current="page"' : '' ?>>
              <?= $p ?>
            </a>
          <?php endfor; ?>

          <?php if ($currentPageNum < $totalPages): ?>
            <a href="<?= url('blog.php?' . http_build_query(array_merge($_GET, ['page' => $currentPageNum + 1]))) ?>" class="blog-page-btn" aria-label="Next Page">Next &rarr;</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>

    <?php endif; ?>

  </div>
</section>

<!-- 5. Editorial Newsletter / Studio Shoot Consultation CTA -->
<section class="section section-editorial-consultation">
  <div class="blog-fullwidth-wrap">
    <div class="editorial-consultation-card">
      <span class="badge badge-editorial-consultation">Direct Studio Consultation</span>
      <h2 class="editorial-consultation-title">
        Need Custom Technical Art Direction for Your Next Commercial Product Campaign?
      </h2>
      <p class="editorial-consultation-desc">
        Discuss your SKU count, lighting mood boards, and marketplace compliance needs directly with our New York studio production directors.
      </p>
      <div class="editorial-consultation-actions">
        <a href="<?= url('contact.php') ?>" class="btn btn-primary btn-lg">
          Book Studio Estimate
        </a>
        <a href="<?= url('services.php') ?>" class="btn btn-secondary btn-lg cta-btn-secondary">
          Explore All Services
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
