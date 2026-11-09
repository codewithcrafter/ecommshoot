<?php
/**
 * ECOMSHOOT - Dedicated Markdown CMS Document Manager
 */

$adminTitle = 'Markdown CMS';
$activeNav = 'markdown';
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance()->getConnection();

// Handle delete
if (isset($_GET['delete'])) {
    CSRF::check();
    $delId = (int) $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM markdown_documents WHERE id = :id");
    $stmt->execute(['id' => $delId]);
    set_flash('success', 'Markdown document deleted.');
    redirect('admin/markdown.php');
}

$docs = $db->query("SELECT * FROM markdown_documents ORDER BY updated_at DESC")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h2>Dedicated Markdown Document CMS</h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">Manage standalone technical documentation, style guides, and Markdown briefs.</div>
  </div>
  <a href="<?= url('admin/markdown-edit.php') ?>" class="btn-admin btn-admin-primary">+ Create Markdown Document</a>
</div>

<div class="admin-card">
  <div class="admin-card-body" style="padding: 0;">
    <?php if (empty($docs)): ?>
      <div style="text-align: center; padding: 3rem; color: var(--admin-text-muted);">
        No Markdown documents created yet. Click "+ Create Markdown Document" above to start.
      </div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Document Title</th>
            <th>Slug</th>
            <th>Classification</th>
            <th>Status</th>
            <th>Last Modified</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($docs as $d): ?>
            <tr>
              <td style="font-weight: 700;"><?= e($d['title']) ?></td>
              <td><code><?= e($d['slug']) ?></code></td>
              <td><span class="status-pill status-draft"><?= strtoupper(e($d['related_type'])) ?></span></td>
              <td><span class="status-pill status-<?= ($d['status'] === 'published') ? 'published' : 'draft' ?>"><?= ucfirst(e($d['status'])) ?></span></td>
              <td style="font-size: 0.82rem; color: var(--admin-text-muted);"><?= date('M j, Y g:ia', strtotime($d['updated_at'])) ?></td>
              <td>
                <div style="display: flex; gap: 0.5rem;">
                  <a href="<?= url('admin/markdown-edit.php?id=' . $d['id']) ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">Edit</a>
                  <form action="<?= url('admin/markdown.php?delete=' . $d['id']) ?>" method="POST" onsubmit="return confirm('Delete this Markdown document?');" style="display:inline;">
                    <?= CSRF::inputField() ?>
                    <button type="submit" class="btn-admin btn-admin-danger" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

