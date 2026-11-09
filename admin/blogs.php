<?php
/**
 * ECOMSHOOT - Admin Blog CMS Article Listing
 */

$adminTitle = 'Blog CMS & Guides';
$activeNav = 'blogs';
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance()->getConnection();

// Handle deletion
if (isset($_GET['delete'])) {
    CSRF::check();
    $deleteId = (int) $_GET['delete'];
    
    // Remove relations
    $db->prepare("DELETE FROM blog_tag_relations WHERE blog_id = :id")->execute(['id' => $deleteId]);
    
    // Remove blog
    $stmt = $db->prepare("DELETE FROM blogs WHERE id = :id");
    $stmt->execute(['id' => $deleteId]);
    
    // Remove generated physical markdown document
    MarkdownGenerator::delete($deleteId);
    
    SEOGenerator::regenerateAll();
    set_flash('success', 'Article deleted successfully and SEO files synchronized.');
    redirect('admin/blogs.php');
}

// Filters
$tagFilter = (int) ($_GET['tag_id'] ?? 0);
$catFilter = (int) ($_GET['cat_id'] ?? 0);
$searchQuery = trim($_GET['q'] ?? '');

$sql = "SELECT b.*, c.name as category_name, 
        GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ', ') as tags_list
        FROM blogs b 
        LEFT JOIN blog_categories c ON b.category_id = c.id 
        LEFT JOIN blog_tag_relations rel ON rel.blog_id = b.id 
        LEFT JOIN blog_tags t ON t.id = rel.tag_id 
        WHERE 1=1";
$params = [];

if ($tagFilter > 0) {
    $sql .= " AND b.id IN (SELECT blog_id FROM blog_tag_relations WHERE tag_id = :tag_id)";
    $params['tag_id'] = $tagFilter;
}

if ($catFilter > 0) {
    $sql .= " AND b.category_id = :cat_id";
    $params['cat_id'] = $catFilter;
}

if ($searchQuery !== '') {
    $sql .= " AND (b.title LIKE :q1 OR b.excerpt LIKE :q2 OR b.content LIKE :q3)";
    $params['q1'] = "%{$searchQuery}%";
    $params['q2'] = "%{$searchQuery}%";
    $params['q3'] = "%{$searchQuery}%";
}

$sql .= " GROUP BY b.id ORDER BY b.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $db->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$tags = $db->query("SELECT * FROM blog_tags ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h2>Studio Intelligence &amp; Blog CMS</h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">Create, edit, draft, and publish commercial photography articles with Markdown and Schema.</div>
  </div>
  <a href="<?= url('admin/blog-edit.php') ?>" class="btn-admin btn-admin-primary">+ Write New Article</a>
</div>

<!-- Search & Filtering Bar -->
<div class="admin-card" style="margin-bottom: 1.5rem;">
  <div class="admin-card-body" style="padding: 1.25rem;">
    <form action="<?= url('admin/blogs.php') ?>" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
      <div style="flex: 2; min-width: 220px;">
        <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="Search articles by title or keyword..." class="admin-form-control" style="font-size: 0.88rem;">
      </div>

      <div style="flex: 1; min-width: 180px;">
        <select name="cat_id" class="admin-form-control" style="font-size: 0.88rem;">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= ($catFilter === (int)$cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="flex: 1; min-width: 180px;">
        <select name="tag_id" class="admin-form-control" style="font-size: 0.88rem;">
          <option value="">All Tags</option>
          <?php foreach ($tags as $t): ?>
            <option value="<?= $t['id'] ?>" <?= ($tagFilter === (int)$t['id']) ? 'selected' : '' ?>>#<?= e($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <button type="submit" class="btn-admin btn-admin-primary">Filter</button>
        <?php if ($searchQuery || $tagFilter || $catFilter): ?>
          <a href="<?= url('admin/blogs.php') ?>" class="btn-admin btn-admin-outline" title="Reset filter">Reset</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-body" style="padding: 0;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Article Title</th>
          <th>Category &amp; Tags</th>
          <th>Author</th>
          <th>Format</th>
          <th>Status</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($blogs)): ?>
          <tr>
            <td colspan="7" style="text-align: center; padding: 3rem; color: var(--admin-text-muted);">No articles found matching criteria.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($blogs as $b): ?>
            <tr>
              <td style="font-weight: 700; max-width: 300px;">
                <a href="<?= blog_url($b['id']) ?>" target="_blank" style="color: var(--admin-text-main);">
                  <?= e($b['title']) ?> &nearr;
                </a>
                <div style="font-size: 0.75rem; color: var(--admin-text-muted); font-family: monospace; margin-top: 2px;">
                  /blog/<?= $b['id'] ?> &bull; <a href="<?= blog_md_url($b['id']) ?>" target="_blank" style="color: #6366F1;">.md</a>
                </div>
              </td>
              <td>
                <div style="font-weight: 600; color: var(--admin-primary); font-size: 0.85rem;">
                  <?= e($b['category_name'] ?: 'Uncategorized') ?>
                </div>
                <?php if (!empty($b['tags_list'])): ?>
                  <div style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 2px;">
                    Tags: <span style="color: #64748B;"><?= e($b['tags_list']) ?></span>
                  </div>
                <?php endif; ?>
              </td>
              <td><?= e($b['author_name']) ?></td>
              <td><span class="status-pill status-draft"><?= strtoupper(e($b['content_format'])) ?></span></td>
              <td>
                <span class="status-pill status-<?= ($b['status'] === 'published') ? 'published' : 'draft' ?>">
                  <?= ucfirst(e($b['status'])) ?>
                </span>
              </td>
              <td style="font-size: 0.82rem; color: var(--admin-text-muted); white-space: nowrap;">
                <?= date('M j, Y', strtotime($b['published_at'] ?? $b['created_at'])) ?>
              </td>
              <td>
                <div style="display: flex; gap: 0.5rem;">
                  <a href="<?= url('admin/blog-edit.php?id=' . $b['id']) ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">Edit</a>
                  <form action="<?= url('admin/blogs.php?delete=' . $b['id']) ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this article?');" style="display:inline;">
                    <?= CSRF::inputField() ?>
                    <button type="submit" class="btn-admin btn-admin-danger" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
