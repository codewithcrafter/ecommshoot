<?php
/**
 * ECOMSHOOT - Independent Schema Management System
 */

$adminTitle = 'Schema Management System';
$activeNav = 'schemas';
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance()->getConnection();

// Handle status toggle
if (isset($_GET['toggle'])) {
    CSRF::check();
    $toggleId = (int) $_GET['toggle'];
    $stmt = $db->prepare("UPDATE `schemas` SET is_active = IF(is_active = 1, 0, 1), updated_at = NOW() WHERE id = :id");
    $stmt->execute(['id' => $toggleId]);
    set_flash('success', 'Schema active state toggled successfully.');
    redirect('admin/schemas.php');
}

// Handle deletion
if (isset($_GET['delete'])) {
    CSRF::check();
    $delId = (int) $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM `schemas` WHERE id = :id");
    $stmt->execute(['id' => $delId]);
    set_flash('success', 'Schema deleted successfully.');
    redirect('admin/schemas.php');
}

$schemas = $db->query("SELECT * FROM `schemas` ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h2>Independent JSON-LD Schema Architecture</h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">Manage structured Schema.org definitions injected dynamically into public page head blocks.</div>
  </div>
  <a href="<?= url('admin/schema-edit.php') ?>" class="btn-admin btn-admin-primary">+ Create New Schema</a>
</div>

<div class="admin-card">
  <div class="admin-card-body" style="padding: 0;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Schema Name</th>
          <th>Schema Type</th>
          <th>Assigned Page</th>
          <th>Status</th>
          <th>Last Updated</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($schemas as $s): ?>
          <tr>
            <td style="font-weight: 700;">
              <?= e($s['name']) ?>
            </td>
            <td><span class="status-pill status-draft" style="font-family: monospace;"><?= e($s['schema_type']) ?></span></td>
            <td><code><?= e($s['page_identifier']) ?></code></td>
            <td>
              <form action="<?= url('admin/schemas.php?toggle=' . $s['id']) ?>" method="POST" style="display:inline;">
                <?= CSRF::inputField() ?>
                <button type="submit" class="status-pill status-<?= ($s['is_active'] ? 'active' : 'inactive') ?>" style="cursor: pointer; border: none;">
                  <?= ($s['is_active'] ? 'Active (Live)' : 'Disabled') ?>
                </button>
              </form>
            </td>
            <td style="font-size: 0.82rem; color: var(--admin-text-muted);"><?= date('M j, Y g:ia', strtotime($s['updated_at'])) ?></td>
            <td>
              <div style="display: flex; gap: 0.5rem;">
                <a href="<?= url('admin/schema-edit.php?id=' . $s['id']) ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">Edit</a>
                <form action="<?= url('admin/schemas.php?delete=' . $s['id']) ?>" method="POST" onsubmit="return confirm('Permanently delete this schema definition?');" style="display:inline;">
                  <?= CSRF::inputField() ?>
                  <button type="submit" class="btn-admin btn-admin-danger" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

