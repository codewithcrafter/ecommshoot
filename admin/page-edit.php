<?php
/**
 * ECOMSHOOT - Admin Page Edit View
 */

$adminTitle = 'Edit Website Page';
$activeNav = 'pages';
require_once __DIR__ . '/../config/config.php';

$id = (int) ($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT * FROM pages WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$page) {
    set_flash('error', 'Page not found.');
    redirect('admin/pages.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();

    $title = trim($_POST['title'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $subheadline = trim($_POST['subheadline'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = ($_POST['status'] === 'draft') ? 'draft' : 'published';

    if (empty($title)) {
        $error = 'Page title cannot be empty.';
    } else {
        $upd = $db->prepare("UPDATE pages SET title = :title, headline = :headline, subheadline = :sub, content = :content, status = :status, updated_at = NOW() WHERE id = :id");
        $upd->execute([
            'title' => $title,
            'headline' => $headline,
            'sub' => $subheadline,
            'content' => $content,
            'status' => $status,
            'id' => $id
        ]);

        // Regenerate SEO files
        SEOGenerator::regenerateAll();

        set_flash('success', 'Page content updated successfully and SEO files synchronized.');
        redirect('admin/pages.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <h2>Edit Page: <?= e($page['title']) ?> (<?= e($page['file_name']) ?>)</h2>
  <a href="<?= url('admin/pages.php') ?>" class="btn-admin btn-admin-outline">&larr; Back to Pages</a>
</div>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-body">
    <form action="<?= url('admin/page-edit.php?id=' . $page['id']) ?>" method="POST">
      <?= CSRF::inputField() ?>

      <div class="admin-form-group">
        <label for="title" class="admin-form-label">Page Title</label>
        <input type="text" id="title" name="title" value="<?= e($page['title']) ?>" class="admin-form-control" required>
      </div>

      <div class="admin-form-group">
        <label for="headline" class="admin-form-label">Primary Hero Headline</label>
        <input type="text" id="headline" name="headline" value="<?= e($page['headline']) ?>" class="admin-form-control">
      </div>

      <div class="admin-form-group">
        <label for="subheadline" class="admin-form-label">Subheadline / Supporting Narrative</label>
        <textarea id="subheadline" name="subheadline" class="admin-form-control" style="min-height: 80px;"><?= e($page['subheadline']) ?></textarea>
      </div>

      <div class="admin-form-group">
        <label for="content" class="admin-form-label">Main Content Text</label>
        <textarea id="content" name="content" class="admin-form-control" style="min-height: 160px;"><?= e($page['content']) ?></textarea>
      </div>

      <div class="admin-form-group">
        <label for="status" class="admin-form-label">Publication Status</label>
        <select id="status" name="status" class="admin-form-control" style="width: 200px;">
          <option value="published" <?= ($page['status'] === 'published') ? 'selected' : '' ?>>Published</option>
          <option value="draft" <?= ($page['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
        </select>
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn-admin btn-admin-primary">Save Changes &amp; Sync SEO</button>
        <a href="<?= url('admin/pages.php') ?>" class="btn-admin btn-admin-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

