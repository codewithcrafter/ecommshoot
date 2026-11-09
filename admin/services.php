<?php
/**
 * ECOMSHOOT - Admin Services Management
 */

$adminTitle = 'Commercial Services Management';
$activeNav = 'services';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/header.php';

$db = Database::getInstance()->getConnection();
$services = $db->query("SELECT * FROM services ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <div class="admin-card-title">Commercial Photography Services (9 Core Services)</div>
      <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Manage H1 headings, descriptions, benefits, specs, and JSON data.</div>
    </div>
  </div>
  <div class="admin-card-body" style="padding: 0;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Service Name</th>
          <th>Dedicated Route</th>
          <th>Badge</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($services as $s): ?>
          <tr>
            <td style="font-weight: 700; color: var(--admin-text-muted); width: 60px;">#<?= e($s['display_order']) ?></td>
            <td style="font-weight: 700;">
              <?= e($s['title']) ?>
              <div style="font-size: 0.8rem; color: var(--admin-text-muted); font-weight: normal; margin-top: 0.2rem;">
                H1: <?= e(mb_strimwidth($s['h1_heading'], 0, 70, '...')) ?>
              </div>
            </td>
            <td>
              <a href="<?= url($s['file_name']) ?>" target="_blank" style="color: var(--admin-primary); font-family: monospace;">
                <?= e($s['file_name']) ?> &nearr;
              </a>
            </td>
            <td><span class="status-pill status-draft"><?= e($s['badge']) ?></span></td>
            <td><span class="status-pill status-<?= ($s['status'] === 'published') ? 'published' : 'draft' ?>"><?= ucfirst(e($s['status'])) ?></span></td>
            <td>
              <a href="<?= url('admin/service-edit.php?id=' . $s['id']) ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                Edit Specs &rarr;
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

