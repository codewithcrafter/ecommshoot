<?php
/**
 * ECOMSHOOT - Admin Blog Categories & Tag Management
 */

$adminTitle = 'Blog Categories & Tags';
$activeNav = 'categories';
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance()->getConnection();
$error = '';

// Handle category creation / update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    CSRF::check();
    $catId = (int) ($_POST['cat_id'] ?? 0);
    $name = trim($_POST['cat_name'] ?? '');
    $slug = trim($_POST['cat_slug'] ?? '');
    $desc = trim($_POST['cat_desc'] ?? '');

    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }

    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        try {
            if ($catId > 0) {
                $upd = $db->prepare("UPDATE blog_categories SET name = :name, slug = :slug, description = :desc WHERE id = :id");
                $upd->execute(['name' => $name, 'slug' => $slug, 'desc' => $desc, 'id' => $catId]);
                set_flash('success', 'Category updated successfully.');
            } else {
                $ins = $db->prepare("INSERT INTO blog_categories (name, slug, description) VALUES (:name, :slug, :desc)");
                $ins->execute(['name' => $name, 'slug' => $slug, 'desc' => $desc]);
                set_flash('success', 'Category created successfully.');
            }
            redirect('admin/categories.php');
        } catch (Exception $e) {
            $error = 'Category slug or name already exists.';
        }
    }
}

// Handle category deletion
if (isset($_GET['delete_cat'])) {
    CSRF::check();
    $delId = (int) $_GET['delete_cat'];
    $stmt = $db->prepare("DELETE FROM blog_categories WHERE id = :id");
    $stmt->execute(['id' => $delId]);
    set_flash('success', 'Category deleted successfully.');
    redirect('admin/categories.php');
}

// Handle tag creation / update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_tag'])) {
    CSRF::check();
    $tagId = (int) ($_POST['tag_id'] ?? 0);
    $tagName = trim($_POST['tag_name'] ?? '');
    $tagSlug = trim($_POST['tag_slug'] ?? '');

    if (empty($tagSlug)) {
        $tagSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $tagName), '-'));
    }

    if (empty($tagName)) {
        $error = 'Tag name is required.';
    } else {
        try {
            if ($tagId > 0) {
                $upd = $db->prepare("UPDATE blog_tags SET name = :name, slug = :slug WHERE id = :id");
                $upd->execute(['name' => $tagName, 'slug' => $tagSlug, 'id' => $tagId]);
                set_flash('success', 'Tag updated successfully.');
            } else {
                $ins = $db->prepare("INSERT INTO blog_tags (name, slug) VALUES (:name, :slug)");
                $ins->execute(['name' => $tagName, 'slug' => $tagSlug]);
                set_flash('success', 'Tag created successfully.');
            }
            redirect('admin/categories.php');
        } catch (Exception $e) {
            $error = 'Tag name or slug already exists.';
        }
    }
}

// Handle tag deletion
if (isset($_GET['delete_tag'])) {
    CSRF::check();
    $delTagId = (int) $_GET['delete_tag'];
    
    // Remove associations first
    $db->prepare("DELETE FROM blog_tag_relations WHERE tag_id = :id")->execute(['id' => $delTagId]);
    
    // Delete tag
    $stmt = $db->prepare("DELETE FROM blog_tags WHERE id = :id");
    $stmt->execute(['id' => $delTagId]);
    set_flash('success', 'Tag deleted successfully.');
    redirect('admin/categories.php');
}

// Search filter
$searchQ = trim($_GET['q'] ?? '');

$catSql = "SELECT c.*, count(b.id) as blog_count 
           FROM blog_categories c 
           LEFT JOIN blogs b ON b.category_id = c.id";
if ($searchQ !== '') {
    $catSql .= " WHERE c.name LIKE :q1 OR c.slug LIKE :q2";
}
$catSql .= " GROUP BY c.id ORDER BY c.name ASC";
$catStmt = $db->prepare($catSql);
if ($searchQ !== '') {
    $catStmt->execute(['q1' => "%$searchQ%", 'q2' => "%$searchQ%"]);
} else {
    $catStmt->execute();
}
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

$tagSql = "SELECT t.*, count(rel.blog_id) as blog_count 
           FROM blog_tags t 
           LEFT JOIN blog_tag_relations rel ON rel.tag_id = t.id";
if ($searchQ !== '') {
    $tagSql .= " WHERE t.name LIKE :q1 OR t.slug LIKE :q2";
}
$tagSql .= " GROUP BY t.id ORDER BY t.name ASC";
$tagStmt = $db->prepare($tagSql);
if ($searchQ !== '') {
    $tagStmt->execute(['q1' => "%$searchQ%", 'q2' => "%$searchQ%"]);
} else {
    $tagStmt->execute();
}
$tags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);

// Check if an edit item is requested
$editCat = null;
if (isset($_GET['edit_cat'])) {
    $cStmt = $db->prepare("SELECT * FROM blog_categories WHERE id = :id LIMIT 1");
    $cStmt->execute(['id' => (int) $_GET['edit_cat']]);
    $editCat = $cStmt->fetch(PDO::FETCH_ASSOC);
}

$editTag = null;
if (isset($_GET['edit_tag'])) {
    $tStmt = $db->prepare("SELECT * FROM blog_tags WHERE id = :id LIMIT 1");
    $tStmt->execute(['id' => (int) $_GET['edit_tag']]);
    $editTag = $tStmt->fetch(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h2>Article Categories &amp; Tag Taxonomy</h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">Organize your commercial photography articles with normalized categories and many-to-many tags.</div>
  </div>
  <div>
    <form action="<?= url('admin/categories.php') ?>" method="GET" style="display: flex; gap: 0.5rem;">
      <input type="text" name="q" value="<?= e($searchQ) ?>" placeholder="Search categories &amp; tags..." class="admin-form-control" style="font-size: 0.85rem; width: 220px;">
      <button type="submit" class="btn-admin btn-admin-primary">Search</button>
      <?php if ($searchQ): ?>
        <a href="<?= url('admin/categories.php') ?>" class="btn-admin btn-admin-outline">Clear</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
  
  <!-- Categories Column -->
  <div>
    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title"><?= $editCat ? 'Edit Category: ' . e($editCat['name']) : 'Add New Topic Category' ?></div>
        <?php if ($editCat): ?>
          <a href="<?= url('admin/categories.php') ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem;">Cancel Edit</a>
        <?php endif; ?>
      </div>
      <div class="admin-card-body">
        <form action="<?= url('admin/categories.php') ?>" method="POST">
          <?= CSRF::inputField() ?>
          <input type="hidden" name="save_category" value="1">
          <?php if ($editCat): ?>
            <input type="hidden" name="cat_id" value="<?= $editCat['id'] ?>">
          <?php endif; ?>

          <div class="admin-form-group">
            <label class="admin-form-label">Category Name <span style="color:red;">*</span></label>
            <input type="text" name="cat_name" value="<?= e($editCat['name'] ?? '') ?>" class="admin-form-control" placeholder="e.g. Amazon Compliance" required>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label">Category Slug (Optional, auto-generated)</label>
            <input type="text" name="cat_slug" value="<?= e($editCat['slug'] ?? '') ?>" class="admin-form-control" placeholder="e.g. amazon-compliance">
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label">Short Description</label>
            <textarea name="cat_desc" class="admin-form-control" style="min-height: 60px;"><?= e($editCat['description'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn-admin btn-admin-primary">
            <?= $editCat ? 'Update Category' : 'Create Category' ?>
          </button>
        </form>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">Existing Categories (<?= count($categories) ?>)</div>
      </div>
      <div class="admin-card-body" style="padding: 0;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Slug</th>
              <th>Articles</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $cat): ?>
              <tr>
                <td style="font-weight: 600;"><?= e($cat['name']) ?></td>
                <td><code><?= e($cat['slug']) ?></code></td>
                <td>
                  <a href="<?= url('admin/blogs.php?cat_id=' . $cat['id']) ?>" class="status-pill status-draft" title="View articles in this category">
                    <?= $cat['blog_count'] ?> articles &rarr;
                  </a>
                </td>
                <td style="text-align: right;">
                  <a href="<?= url('admin/categories.php?edit_cat=' . $cat['id']) ?>" class="btn-admin btn-admin-outline" style="font-size: 0.72rem; padding: 0.2rem 0.5rem;">Edit</a>
                  <a href="<?= url('admin/categories.php?delete_cat=' . $cat['id'] . '&csrf=' . CSRF::token()) ?>" 
                     class="btn-admin btn-admin-danger" 
                     style="font-size: 0.72rem; padding: 0.2rem 0.5rem;" 
                     onclick="return confirm('Delete category <?= e($cat['name']) ?>?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Tags Column -->
  <div>
    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title"><?= $editTag ? 'Edit Tag: #' . e($editTag['name']) : 'Add New Topic Tag' ?></div>
        <?php if ($editTag): ?>
          <a href="<?= url('admin/categories.php') ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem;">Cancel Edit</a>
        <?php endif; ?>
      </div>
      <div class="admin-card-body">
        <form action="<?= url('admin/categories.php') ?>" method="POST">
          <?= CSRF::inputField() ?>
          <input type="hidden" name="save_tag" value="1">
          <?php if ($editTag): ?>
            <input type="hidden" name="tag_id" value="<?= $editTag['id'] ?>">
          <?php endif; ?>

          <div class="admin-form-group">
            <label class="admin-form-label">Tag Name <span style="color:red;">*</span></label>
            <input type="text" name="tag_name" value="<?= e($editTag['name'] ?? '') ?>" class="admin-form-control" placeholder="e.g. Focus Stacking" required>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label">Tag Slug (Optional, auto-generated)</label>
            <input type="text" name="tag_slug" value="<?= e($editTag['slug'] ?? '') ?>" class="admin-form-control" placeholder="e.g. focus-stacking">
          </div>

          <button type="submit" class="btn-admin btn-admin-primary">
            <?= $editTag ? 'Update Tag' : 'Create Tag' ?>
          </button>
        </form>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">Existing Tags (<?= count($tags) ?>)</div>
      </div>
      <div class="admin-card-body" style="padding: 0;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Tag Name</th>
              <th>Slug</th>
              <th>Articles</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tags as $t): ?>
              <tr>
                <td style="font-weight: 600;">
                  <span style="color: var(--admin-primary);">#<?= e($t['name']) ?></span>
                </td>
                <td><code><?= e($t['slug']) ?></code></td>
                <td>
                  <a href="<?= url('admin/blogs.php?tag_id=' . $t['id']) ?>" class="status-pill status-draft" title="Filter articles with this tag">
                    <?= $t['blog_count'] ?> articles &rarr;
                  </a>
                </td>
                <td style="text-align: right;">
                  <a href="<?= url('admin/categories.php?edit_tag=' . $t['id']) ?>" class="btn-admin btn-admin-outline" style="font-size: 0.72rem; padding: 0.2rem 0.5rem;">Edit</a>
                  <a href="<?= url('admin/categories.php?delete_tag=' . $t['id'] . '&csrf=' . CSRF::token()) ?>" 
                     class="btn-admin btn-admin-danger" 
                     style="font-size: 0.72rem; padding: 0.2rem 0.5rem;" 
                     onclick="return confirm('Delete tag #<?= e($t['name']) ?>?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
