<?php
/**
 * ECOMSHOOT - Admin Markdown CMS Editor View
 */

$adminTitle = 'Markdown CMS Editor';
$activeNav = 'markdown';
require_once __DIR__ . '/../config/config.php';

$id = (int) ($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

$doc = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'content' => "# Document Title\n\nWrite your markdown content here...\n\n## Subheading\n\n- Feature 1\n- Feature 2\n\n| Item | Spec |\n| :--- | :--- |\n| Lens | Schneider 80mm |\n| Sensor | 150MP |",
    'status' => 'published',
    'related_type' => 'guide'
];

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM markdown_documents WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $found = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($found) {
        $doc = $found;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $type = trim($_POST['related_type'] ?? 'guide');
    $content = trim($_POST['content'] ?? '');
    $status = ($_POST['status'] === 'draft') ? 'draft' : 'published';

    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    }

    if (empty($title) || empty($content)) {
        $error = 'Document title and markdown content are required.';
    } else {
        if ($id > 0) {
            $upd = $db->prepare(
                "UPDATE markdown_documents SET 
                 title = :title, 
                 slug = :slug, 
                 content = :content, 
                 status = :status, 
                 related_type = :type, 
                 updated_at = NOW() 
                 WHERE id = :id"
            );
            $upd->execute([
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'status' => $status,
                'type' => $type,
                'id' => $id
            ]);
        } else {
            $ins = $db->prepare(
                "INSERT INTO markdown_documents (title, slug, content, status, related_type) 
                 VALUES (:title, :slug, :content, :status, :type)"
            );
            $ins->execute([
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'status' => $status,
                'type' => $type
            ]);
        }

        set_flash('success', 'Markdown document saved successfully.');
        redirect('admin/markdown.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h2><?= ($id > 0) ? 'Edit Markdown: ' . e($doc['title']) : 'Create Markdown Document' ?></h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">Real-time split-screen rendering with pure vanilla JavaScript preview.</div>
  </div>
  <a href="<?= url('admin/markdown.php') ?>" class="btn-admin btn-admin-outline">&larr; Back to Markdown CMS</a>
</div>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<form action="<?= url('admin/markdown-edit.php' . ($id ? '?id=' . $id : '')) ?>" method="POST">
  <?= CSRF::inputField() ?>

  <div class="admin-card">
    <div class="admin-card-body">
      <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem;">
        <div class="admin-form-group">
          <label for="title" class="admin-form-label">Document Title <span style="color:red;">*</span></label>
          <input type="text" id="title" name="title" value="<?= e($doc['title']) ?>" class="admin-form-control" placeholder="e.g. Studio Lighting Calibration Standard Operating Procedure" required>
        </div>

        <div class="admin-form-group">
          <label for="slug" class="admin-form-label">Slug</label>
          <input type="text" id="slug" name="slug" value="<?= e($doc['slug']) ?>" class="admin-form-control" placeholder="auto-generated if empty">
        </div>

        <div class="admin-form-group">
          <label for="status" class="admin-form-label">Status</label>
          <select id="status" name="status" class="admin-form-control">
            <option value="published" <?= ($doc['status'] === 'published') ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= ($doc['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Split Pane Editor -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Live Markdown Editor &amp; Formatted HTML Preview</div>
      <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Type on the left; formatted output updates in real-time on the right.</div>
    </div>
    <div class="admin-card-body">
      <div class="markdown-editor-container">
        
        <div class="markdown-pane">
          <label class="admin-form-label">Markdown Source</label>
          <textarea id="markdownContentInput" name="content" class="markdown-textarea" required><?= e($doc['content']) ?></textarea>
        </div>

        <div class="markdown-pane">
          <label class="admin-form-label">Live Preview</label>
          <div id="markdownPreviewArea" class="markdown-preview-box"></div>
        </div>

      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn-admin btn-admin-primary" style="padding: 0.75rem 1.5rem; font-size: 1rem;">
          Save Markdown Document
        </button>
        <a href="<?= url('admin/markdown.php') ?>" class="btn-admin btn-admin-outline" style="padding: 0.75rem 1.5rem;">Cancel</a>
      </div>
    </div>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

