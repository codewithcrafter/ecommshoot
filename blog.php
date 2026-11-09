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

// Fetch tags for tag cloud
$allTags = $db->query("SELECT * FROM blog_tags ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Map blog tags
$blogTagsMap = [];
if (!empty($allMatchedBlogs)) {
    $blogIds = array_column($allMatchedBlogs, 'id');
    $placeholders = implode(',', array_fill(0, count($blogIds), '?'));
    $btStmt = $db->prepare("SELECT r.blog_id, t.name, t.slug FROM blog_tag_relations r JOIN blog_tags t ON r.tag_id = t.id WHERE r.blog_id IN ($placeholders) ORDER BY t.name ASC");
    $btStmt->execute($blogIds);
    $relRows = $btStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($relRows as $rr) {
        $blogTagsMap[$rr['blog_id']][] = $rr;
    }
}

require_once INCLUDES_PATH . '/header.php';
?>

<!-- Breadcrumbs -->
<?= render_breadcrumbs([['title' => 'Blogs', 'url' => null]]) ?>

<!-- 1. Full-Width Editorial Hero Banner -->
<section class="blog-editorial-hero">
  <div class="blog-fullwidth-wrap">
    <div class="blog-hero-split">
      <div>
        <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 1rem; flex-wrap: wrap;">
          <span class="badge badge-orange">Studio Intelligence</span>
          <span class="badge" style="background: var(--color-primary-teal); color: #FFFFFF;">Commercial Perspectives</span>
        </div>
        <h1 style="font-family: var(--font-heading); font-size: clamp(2.4rem, 4.5vw, 3.8rem); font-weight: 700; line-height: 1.15; margin: 0; letter-spacing: -0.02em;">
          Commercial Photography Intelligence &amp; Visual Strategy
        </h1>
        <p class="blog-hero-lead">
          In-depth technical guides, lighting masterclasses, Amazon compliance blueprints, and art direction frameworks engineered by our senior commercial studio leads.
        </p>
      </div>

      <!-- Quick Metrics Pill -->
      <div style="background: #FFFFFF; border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm);">
        <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-orange); margin-bottom: 0.5rem;">STUDIO ARCHIVE SPECIFICATIONS</div>
        <div style="font-size: 1.05rem; font-weight: 600; color: var(--color-text-main); margin-bottom: 1.25rem;">Direct access to calibrated lighting diagrams &amp; raw Markdown documents</div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; border-top: 1px solid var(--color-border); padding-top: 1.25rem; text-align: center;">
          <div>
            <div style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--color-primary-teal);"><?= count($allMatchedBlogs) ?></div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted);">Published Guides</div>
          </div>
          <div>
            <div style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--color-orange);"><?= count($categories) ?></div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted);">Disciplines</div>
          </div>
          <div>
            <div style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: #6366F1;">Dual</div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted);">HTML + .MD</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 2. Interactive Filter & Search Controls -->
<section style="background-color: #FAF8F5; border-bottom: 1px solid var(--color-border); padding: 1.25rem 0;">
  <div class="blog-fullwidth-wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem;">
      
      <!-- Category Tabs Navigation -->
      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
        <a href="<?= url('blog.php') ?>" class="btn btn-sm <?= ($categorySlug === '' && $tagSlug === '') ? 'btn-primary' : 'btn-outline' ?>">All Disciplines</a>
        <?php foreach ($categories as $cat): ?>
          <a href="<?= url('blog.php?category=' . urlencode($cat['slug'])) ?>" class="btn btn-sm <?= ($categorySlug === $cat['slug']) ? 'btn-primary' : 'btn-outline' ?>">
            <?= e($cat['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Search Box -->
      <form action="<?= url('blog.php') ?>" method="GET" style="display: flex; flex-wrap: wrap; gap: 0.5rem; width: 100%; max-width: 360px;">
        <?php if ($categorySlug): ?>
          <input type="hidden" name="category" value="<?= e($categorySlug) ?>">
        <?php endif; ?>
        <?php if ($tagSlug): ?>
          <input type="hidden" name="tag" value="<?= e($tagSlug) ?>">
        <?php endif; ?>
        <input type="search" name="q" value="<?= e($searchQuery) ?>" placeholder="Search guides, optics, setups..." class="form-control" style="flex: 1 1 180px; min-width: 0; padding: 0.5rem 0.95rem; font-size: 0.88rem; background: #FFFFFF;">
        <button type="submit" class="btn btn-sm btn-secondary">Search</button>
      </form>

    </div>

    <!-- Tag Filter Chips -->
    <?php if (!empty($allTags)): ?>
      <div style="display: flex; gap: 0.45rem; flex-wrap: wrap; align-items: center; margin-top: 1rem; padding-top: 0.85rem; border-top: 1px solid rgba(0,0,0,0.06);">
        <span style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--color-text-muted); margin-right: 0.35rem;">Popular Tags:</span>
        <?php foreach ($allTags as $t): ?>
          <a href="<?= url('blog.php?tag=' . urlencode($t['slug'])) ?>" style="font-size: 0.78rem; padding: 0.25rem 0.65rem; border-radius: 9999px; text-decoration: none; border: 1px solid var(--color-border); background: <?= ($tagSlug === $t['slug']) ? 'var(--color-orange)' : '#FFFFFF' ?>; color: <?= ($tagSlug === $t['slug']) ? '#FFFFFF' : 'var(--color-text-main)' ?>; font-weight: <?= ($tagSlug === $t['slug']) ? '700' : '500' ?>; transition: all 0.15s ease;">
            #<?= e($t['name']) ?>
          </a>
        <?php endforeach; ?>
        <?php if ($tagSlug !== '' || $categorySlug !== '' || $searchQuery !== ''): ?>
          <a href="<?= url('blog.php') ?>" style="font-size: 0.78rem; color: #DC2626; margin-left: 0.5rem; text-decoration: underline; font-weight: 600;">Clear All Filters &times;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- 3. Full-Width Editorial Catalog Section -->
<section class="section" style="padding-top: clamp(2.5rem, 4vw, 4rem); padding-bottom: clamp(4rem, 6vw, 6rem);">
  <div class="blog-fullwidth-wrap">

    <?php if (empty($blogs)): ?>
      <div style="text-align: center; padding: 6rem 1rem; background: var(--color-cream-light); border-radius: var(--radius-xl); border: 1px dashed var(--color-border);">
        <span style="font-size: 2.5rem; display: block; margin-bottom: 1rem;">🔍</span>
        <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 0.5rem;">No studio articles found</h3>
        <p style="color: var(--color-text-muted); max-width: 480px; margin: 0 auto 1.5rem auto;">No published articles matched your selected search criteria. Try clearing tags or searching for broader terms like "lighting" or "Amazon".</p>
        <a href="<?= url('blog.php') ?>" class="btn btn-primary">Reset All Filters</a>
      </div>
    <?php else: ?>

      <!-- Prominent Featured Article Spotlight (if applicable) -->
      <?php if ($featuredBlog): ?>
        <article class="featured-article-spotlight">
          <div class="featured-spotlight-media">
            <img src="<?= asset_url($featuredBlog['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg') ?>" 
                 alt="<?= e($featuredBlog['featured_image_alt'] ?: $featuredBlog['title']) ?>" 
                 width="800" 
                 height="500" 
                 loading="eager">
            <span class="blog-cat-pill" style="top: 18px; left: 18px; font-size: 0.75rem; padding: 0.35rem 0.85rem; background: var(--color-orange); color: #FFFFFF;">
              Featured • <?= e($featuredBlog['category_name'] ?: 'Strategy') ?>
            </span>
          </div>
          <div class="featured-spotlight-content">
            <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap;">
              <span class="blog-time-meta" style="margin-bottom: 0; font-weight: 600; color: var(--color-text-muted);">
                Published <?= date('F j, Y', strtotime($featuredBlog['published_at'] ?? 'now')) ?>
              </span>
              <span>•</span>
              <span style="font-size: 0.82rem; color: var(--color-text-light);"><?= e($featuredBlog['read_time']) ?></span>
              <span>•</span>
              <a href="<?= blog_md_url($featuredBlog['id']) ?>" target="_blank" style="font-size: 0.75rem; color: #4338CA; background: rgba(79, 70, 229, 0.08); padding: 0.15rem 0.5rem; border-radius: 4px; text-decoration: none; font-weight: 700; font-family: monospace;">.md &nearr;</a>
            </div>

            <h2 class="featured-spotlight-title">
              <a href="<?= blog_url($featuredBlog['id']) ?>"><?= e($featuredBlog['title']) ?></a>
            </h2>

            <p class="featured-spotlight-excerpt">
              <?= e($featuredBlog['excerpt']) ?>
            </p>

            <!-- Tags -->
            <?php if (!empty($blogTagsMap[$featuredBlog['id']])): ?>
              <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                <?php foreach ($blogTagsMap[$featuredBlog['id']] as $tg): ?>
                  <span style="font-size: 0.75rem; padding: 0.2rem 0.6rem; background: #FFFFFF; border: 1px solid var(--color-border); border-radius: 9999px; color: var(--color-text-muted); font-weight: 500;">
                    #<?= e($tg['name']) ?>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--color-border); padding-top: 1.25rem;">
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="author-avatar" style="width: 38px; height: 38px; font-size: 0.85rem;">
                  <?= substr($featuredBlog['author_name'], 0, 2) ?>
                </div>
                <div>
                  <div style="font-family: var(--font-heading); font-size: 0.9rem; font-weight: 700;"><?= e($featuredBlog['author_name']) ?></div>
                  <div style="font-size: 0.75rem; color: var(--color-text-light);"><?= e($featuredBlog['author_role'] ?: 'Studio Lead') ?></div>
                </div>
              </div>

              <a href="<?= blog_url($featuredBlog['id']) ?>" class="btn btn-primary" style="padding: 0.6rem 1.4rem;">
                Read Full Guide &rarr;
              </a>
            </div>

          </div>
        </article>
      <?php endif; ?>

      <!-- Section Title for Grid -->
      <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
          <span class="badge badge-orange">Studio Knowledge Base</span>
          <h2 style="font-family: var(--font-heading); font-size: clamp(1.6rem, 2.8vw, 2.2rem); font-weight: 700; margin-top: 0.5rem; margin-bottom: 0;">
            <?= ($featuredBlog) ? 'Latest Studio Perspectives' : 'Matching Guides (' . $totalArticles . ')' ?>
          </h2>
        </div>
        <div style="font-size: 0.85rem; color: var(--color-text-muted);">
          Showing Page <?= $currentPageNum ?> of <?= $totalPages ?>
        </div>
      </div>

      <!-- Responsive Multi-Column Blog Card Grid -->
      <div class="blog-editorial-grid-wide">
        <?php foreach ($gridBlogs as $b): ?>
          <article class="editorial-blog-card">
            <div class="blog-media-wrap">
              <img src="<?= asset_url($b['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg') ?>" 
                   alt="<?= e($b['featured_image_alt'] ?: $b['title']) ?>" 
                   width="420" 
                   height="260" 
                   loading="lazy">
              <span class="blog-cat-pill"><?= e($b['category_name'] ?: 'Strategy') ?></span>
            </div>
            <div class="blog-body-wrap">
              <div class="blog-time-meta">
                <?= e($b['read_time']) ?> • Published <?= date('M j, Y', strtotime($b['published_at'] ?? 'now')) ?>
              </div>
              <h3 class="blog-heading">
                <a href="<?= blog_url($b['id']) ?>"><?= e($b['title']) ?></a>
              </h3>
              <p class="blog-excerpt-copy">
                <?= e(mb_strimwidth($b['excerpt'], 0, 135, '...')) ?>
              </p>

              <!-- Tags list on card -->
              <?php if (!empty($blogTagsMap[$b['id']])): ?>
                <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-bottom: 1rem;">
                  <?php foreach ($blogTagsMap[$b['id']] as $tg): ?>
                    <span style="font-size: 0.72rem; padding: 0.15rem 0.5rem; background: #FFFFFF; border: 1px solid var(--color-border); border-radius: 4px; color: var(--color-text-muted);">
                      #<?= e($tg['name']) ?>
                    </span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <div class="blog-footer-meta">
                <span class="blog-author-tag">By <?= e($b['author_name']) ?></span>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                  <a href="<?= blog_md_url($b['id']) ?>" target="_blank" style="font-size: 0.75rem; color: #4338CA; text-decoration: none; font-family: monospace; font-weight: 700; background: rgba(67, 56, 202, 0.08); padding: 0.2rem 0.5rem; border-radius: 4px;" title="View raw Markdown document">.md &nearr;</a>
                  <a href="<?= blog_url($b['id']) ?>" class="blog-read-link">
                    Read Guide &rarr;
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
<section class="section" style="background-color: var(--color-primary-teal); color: #FFFFFF; padding: clamp(4rem, 6vw, 6rem) 0;">
  <div class="blog-fullwidth-wrap">
    <div style="max-width: 860px; margin: 0 auto; text-align: center;">
      <span class="badge" style="background: rgba(255,255,255,0.15); color: var(--color-lime); margin-bottom: 1rem;">Direct Studio Consultation</span>
      <h2 style="font-family: var(--font-heading); font-size: clamp(2rem, 3.8vw, 3rem); font-weight: 700; line-height: 1.2; margin-bottom: 1.25rem;">
        Need Custom Technical Art Direction for Your Next Commercial Product Campaign?
      </h2>
      <p style="font-size: 1.15rem; line-height: 1.6; color: rgba(255,255,255,0.85); margin-bottom: 2.25rem;">
        Discuss your SKU count, lighting mood boards, and marketplace compliance needs directly with our New York studio production directors.
      </p>
      <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
        <a href="<?= url('contact.php') ?>" class="btn btn-primary btn-lg" style="background: var(--color-orange); border-color: var(--color-orange);">
          Book Studio Estimate
        </a>
        <a href="<?= url('services.php') ?>" class="btn btn-outline-white btn-lg">
          Explore All 9 Disciplines
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
