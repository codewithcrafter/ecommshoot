<?php
/**
 * ECOMSHOOT - Admin Pages Management
 */

$adminTitle = 'Static Pages Management';
$activeNav = 'pages';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/header.php';

$db = Database::getInstance()->getConnection();
$pages = $db->query("SELECT * FROM pages ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title">Core Website Public Pages</div>
  </div>
  <div class="admin-card-body" style="padding: 0;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Page Title</th>
          <th>Slug</th>
          <th>File Routing</th>
          <th>Status</th>
          <th>Last Modified</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pages as $p): ?>
          <tr>
            <td style="font-weight: 700;">
              <?= e($p['title']) ?>
            </td>
            <td><code><?= e($p['slug']) ?></code></td>
            <td><a href="<?= url($p['file_name']) ?>" target="_blank" style="color: var(--admin-primary);"><?= e($p['file_name']) ?> &nearr;</a></td>
            <td><span class="status-pill status-published"><?= ucfirst(e($p['status'])) ?></span></td>
            <td style="font-size: 0.82rem; color: var(--admin-text-muted);"><?= date('M j, Y g:ia', strtotime($p['updated_at'])) ?></td>
            <td>
              <a href="<?= url('admin/page-edit.php?id=' . $p['id']) ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                Edit Page Content &rarr;
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

