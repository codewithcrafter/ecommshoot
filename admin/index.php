<?php
/**
 * ECOMSHOOT - Admin Dashboard Overview
 */

$adminTitle = 'Dashboard Overview';
$activeNav = 'dashboard';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/header.php';

$db = Database::getInstance()->getConnection();

// Fetch counts
$serviceCount = (int) $db->query("SELECT count(*) FROM services WHERE status = 'published'")->fetchColumn();
$blogCount = (int) $db->query("SELECT count(*) FROM blogs WHERE status = 'published'")->fetchColumn();
$schemaCount = (int) $db->query("SELECT count(*) FROM `schemas` WHERE is_active = 1")->fetchColumn();
$mediaCount = (int) $db->query("SELECT count(*) FROM media")->fetchColumn();

// Fetch recent submissions
$recentInquiries = $db->query("SELECT * FROM contact_submissions ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent blogs
$recentBlogs = $db->query("SELECT b.*, c.name as category_name FROM blogs b LEFT JOIN blog_categories c ON b.category_id = c.id ORDER BY b.created_at DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Metrics Grid -->
<div class="admin-metrics-grid">
  <div class="metric-card">
    <div class="metric-title">Published Services</div>
    <div class="metric-value"><?= $serviceCount ?></div>
    <div style="font-size: 0.8rem; color: var(--admin-text-muted); margin-top: 0.5rem;">All 9 core services active</div>
  </div>

  <div class="metric-card">
    <div class="metric-title">Studio Articles</div>
    <div class="metric-value"><?= $blogCount ?></div>
    <div style="font-size: 0.8rem; color: var(--admin-text-muted); margin-top: 0.5rem;">Markdown &amp; SEO indexed</div>
  </div>

  <div class="metric-card">
    <div class="metric-title">Active Schemas</div>
    <div class="metric-value"><?= $schemaCount ?></div>
    <div style="font-size: 0.8rem; color: var(--admin-text-muted); margin-top: 0.5rem;">JSON-LD head injected</div>
  </div>

  <div class="metric-card">
    <div class="metric-title">Unread Inquiries</div>
    <div class="metric-value" style="color: var(--admin-coral);"><?= $unreadCount ?></div>
    <div style="font-size: 0.8rem; color: var(--admin-text-muted); margin-top: 0.5rem;">Commercial rate requests</div>
  </div>
</div>

<!-- Quick Actions Bar -->
<div class="admin-card" style="background-color: #FFFDF9; border-color: rgba(249, 115, 22, 0.25);">
  <div class="admin-card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h3 style="font-size: 1.1rem; margin-bottom: 0.25rem;">Quick Operations &amp; SEO Tools</h3>
      <p style="font-size: 0.85rem; color: var(--admin-text-muted); margin: 0;">Perform frequent content tasks and synchronize search engine files.</p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
      <a href="<?= url('admin/blog-edit.php') ?>" class="btn-admin btn-admin-primary">+ Write Article</a>
      <a href="<?= url('admin/schema-edit.php') ?>" class="btn-admin btn-admin-outline">+ Create Schema</a>
      <a href="<?= url('admin/media.php') ?>" class="btn-admin btn-admin-outline">Upload Media</a>
      <a href="<?= url('admin/sitemap.php?regenerate=1') ?>" class="btn-admin btn-admin-outline" style="border-color: var(--admin-coral); color: var(--admin-coral);">
        ⚡ Sync Sitemap &amp; LLMs
      </a>
    </div>
  </div>
</div>

<!-- Recent Contact Inquiries -->
<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title">Recent Client Inquiries &amp; Rate Card Requests</div>
    <a href="<?= url('admin/contacts.php') ?>" class="btn-admin btn-admin-outline" style="font-size: 0.8rem;">View All Inquiries</a>
  </div>
  <div class="admin-card-body" style="padding: 0;">
    <?php if (empty($recentInquiries)): ?>
      <div style="padding: 2rem; text-align: center; color: var(--admin-text-muted);">No inquiries received yet.</div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Status</th>
            <th>Client Name</th>
            <th>Company</th>
            <th>Service Requested</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentInquiries as $inq): ?>
            <tr>
              <td>
                <?php if ($inq['is_read']): ?>
                  <span class="status-pill status-draft">Read</span>
                <?php else: ?>
                  <span class="status-pill" style="background-color: #FEF3C7; color: #92400E;">New</span>
                <?php endif; ?>
              </td>
              <td style="font-weight: 600;"><?= e($inq['full_name']) ?><br><span style="font-size: 0.8rem; color: var(--admin-text-muted); font-weight: normal;"><?= e($inq['email']) ?></span></td>
              <td><?= e($inq['company_name'] ?: 'N/A') ?></td>
              <td><span class="status-pill status-active"><?= e($inq['service_required']) ?></span></td>
              <td style="font-size: 0.82rem; color: var(--admin-text-muted);"><?= date('M j, Y g:ia', strtotime($inq['created_at'])) ?></td>
              <td>
                <a href="<?= url('admin/contacts.php?id=' . $inq['id']) ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">
                  Review Message &rarr;
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<!-- Recent Articles -->
<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title">Recent Articles &amp; Markdown Guides</div>
    <a href="<?= url('admin/blogs.php') ?>" class="btn-admin btn-admin-outline" style="font-size: 0.8rem;">Manage Blog CMS</a>
  </div>
  <div class="admin-card-body" style="padding: 0;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Category</th>
          <th>Format</th>
          <th>Status</th>
          <th>Author</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentBlogs as $rb): ?>
          <tr>
            <td style="font-weight: 600;">
              <a href="<?= blog_url($rb['id']) ?>" target="_blank" style="color: var(--admin-text-main);">
                <?= e($rb['title']) ?> &nearr;
              </a>
            </td>
            <td><?= e($rb['category_name'] ?: 'General') ?></td>
            <td><span class="status-pill status-draft"><?= strtoupper(e($rb['content_format'])) ?></span></td>
            <td><span class="status-pill status-<?= ($rb['status'] === 'published') ? 'published' : 'draft' ?>"><?= ucfirst(e($rb['status'])) ?></span></td>
            <td><?= e($rb['author_name']) ?></td>
            <td>
              <a href="<?= url('admin/blog-edit.php?id=' . $rb['id']) ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem; padding: 0.3rem 0.6rem;">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

