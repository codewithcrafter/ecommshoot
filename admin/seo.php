<?php
/**
 * ECOMSHOOT - Admin SEO Metadata Management
 */

$adminTitle = 'SEO & Open Graph Management';
$activeNav = 'seo';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/header.php';

$db = Database::getInstance()->getConnection();
$seoList = $db->query("SELECT * FROM seo_metadata ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-card">
  <div class="admin-card-header">
    <div>
      <div class="admin-card-title">Search Engine Optimization &amp; Social Card Configurations</div>
      <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Manage title tags, meta descriptions, canonical URLs, and Open Graph tags per route.</div>
    </div>
  </div>
  <div class="admin-card-body" style="padding: 0;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Page Identifier</th>
          <th>Meta Title</th>
          <th>Meta Description</th>
          <th>Robots</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($seoList as $s): ?>
          <tr>
            <td><code><?= e($s['page_identifier']) ?></code></td>
            <td style="font-weight: 600; max-width: 250px;"><?= e($s['meta_title']) ?></td>
            <td style="font-size: 0.82rem; color: var(--admin-text-muted); max-width: 320px;"><?= e(mb_strimwidth($s['meta_description'], 0, 90, '...')) ?></td>
            <td><span class="status-pill status-draft"><?= e($s['robots_meta'] ?? 'index, follow') ?></span></td>
            <td>
              <a href="<?= url('admin/seo-edit.php?id=' . $s['id']) ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem; padding: 0.35rem 0.7rem;">
                Edit Meta Tags &rarr;
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

