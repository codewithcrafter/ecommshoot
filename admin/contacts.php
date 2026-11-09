<?php
/**
 * ECOMSHOOT - Admin Contact Submissions Inbox
 */

$adminTitle = 'Contact Inquiries & Submissions';
$activeNav = 'contacts';
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance()->getConnection();

// Handle toggle read status
if (isset($_GET['toggle_read'])) {
    CSRF::check();
    $id = (int) $_GET['toggle_read'];
    $stmt = $db->prepare("UPDATE contact_submissions SET is_read = IF(is_read = 1, 0, 1) WHERE id = :id");
    $stmt->execute(['id' => $id]);
    set_flash('success', 'Inquiry status updated.');
    redirect('admin/contacts.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    CSRF::check();
    $delId = (int) $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM contact_submissions WHERE id = :id");
    $stmt->execute(['id' => $delId]);
    set_flash('success', 'Inquiry deleted.');
    redirect('admin/contacts.php');
}

// Check if a single inquiry is selected for viewing
$viewId = (int) ($_GET['id'] ?? 0);
$selectedInquiry = null;
if ($viewId > 0) {
    $vStmt = $db->prepare("SELECT * FROM contact_submissions WHERE id = :id LIMIT 1");
    $vStmt->execute(['id' => $viewId]);
    $selectedInquiry = $vStmt->fetch(PDO::FETCH_ASSOC);
    if ($selectedInquiry && !$selectedInquiry['is_read']) {
        // Automatically mark as read when viewed
        $db->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = :id")->execute(['id' => $viewId]);
        $selectedInquiry['is_read'] = 1;
    }
}

$inquiries = $db->query("SELECT * FROM contact_submissions ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h2>Client Shoot Inquiries &amp; Rate Requests</h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">Incoming commercial production leads received through the website contact form.</div>
  </div>
</div>

<?php if ($selectedInquiry): ?>
  <!-- Selected Inquiry Message Modal / Detail Box -->
  <div class="admin-card" style="border: 2px solid var(--admin-primary); background-color: #FFFDF9;">
    <div class="admin-card-header">
      <div class="admin-card-title">Inquiry Details: <?= e($selectedInquiry['full_name']) ?> (<?= e($selectedInquiry['company_name'] ?: 'Independent') ?>)</div>
      <a href="<?= url('admin/contacts.php') ?>" class="btn-admin btn-admin-outline" style="font-size: 0.75rem;">Close Message</a>
    </div>
    <div class="admin-card-body">
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; background: #FFFFFF; padding: 1rem; border-radius: 8px; border: 1px solid var(--admin-border);">
        <div>
          <div style="font-size: 0.75rem; color: var(--admin-text-muted);">Email</div>
          <div style="font-weight: 600;"><a href="mailto:<?= e($selectedInquiry['email']) ?>"><?= e($selectedInquiry['email']) ?></a></div>
        </div>
        <div>
          <div style="font-size: 0.75rem; color: var(--admin-text-muted);">Phone</div>
          <div style="font-weight: 600;"><a href="tel:<?= e($selectedInquiry['phone']) ?>"><?= e($selectedInquiry['phone']) ?></a></div>
        </div>
        <div>
          <div style="font-size: 0.75rem; color: var(--admin-text-muted);">Service Requested</div>
          <div style="font-weight: 700; color: var(--admin-primary);"><?= e($selectedInquiry['service_required']) ?></div>
        </div>
      </div>

      <div style="margin-bottom: 1.5rem;">
        <label class="admin-form-label">Client Project Description &amp; Scope Message:</label>
        <div style="background: #FFFFFF; padding: 1.25rem; border-radius: 8px; border: 1px solid var(--admin-border); font-size: 0.95rem; line-height: 1.6; white-space: pre-wrap;"><?= e($selectedInquiry['message']) ?></div>
      </div>

      <div style="font-size: 0.78rem; color: var(--admin-text-muted);">
        Submitted on <?= date('F j, Y \a\t g:ia', strtotime($selectedInquiry['created_at'])) ?> • IP: <?= e($selectedInquiry['ip_address']) ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Inquiries Table -->
<div class="admin-card">
  <div class="admin-card-body" style="padding: 0;">
    <?php if (empty($inquiries)): ?>
      <div style="text-align: center; padding: 3rem; color: var(--admin-text-muted);">No client inquiries submitted yet.</div>
    <?php else: ?>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Status</th>
            <th>Name</th>
            <th>Company</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Service Requested</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($inquiries as $inq): ?>
            <tr style="<?= (!$inq['is_read']) ? 'background-color: #FEFCE8;' : '' ?>">
              <td>
                <form action="<?= url('admin/contacts.php?toggle_read=' . $inq['id']) ?>" method="POST" style="display:inline;">
                  <?= CSRF::inputField() ?>
                  <button type="submit" class="status-pill status-<?= ($inq['is_read'] ? 'draft' : 'published') ?>" style="cursor: pointer; border: none;">
                    <?= ($inq['is_read'] ? 'Read' : 'New') ?>
                  </button>
                </form>
              </td>
              <td style="font-weight: 700;"><?= e($inq['full_name']) ?></td>
              <td><?= e($inq['company_name'] ?: 'N/A') ?></td>
              <td><a href="mailto:<?= e($inq['email']) ?>" style="color: var(--admin-primary);"><?= e($inq['email']) ?></a></td>
              <td><?= e($inq['phone']) ?></td>
              <td><span class="status-pill status-active"><?= e($inq['service_required']) ?></span></td>
              <td style="font-size: 0.82rem; color: var(--admin-text-muted);"><?= date('M j, Y g:ia', strtotime($inq['created_at'])) ?></td>
              <td>
                <div style="display: flex; gap: 0.4rem;">
                  <a href="<?= url('admin/contacts.php?id=' . $inq['id']) ?>" class="btn-admin btn-admin-primary" style="font-size: 0.72rem; padding: 0.25rem 0.5rem;">View</a>
                  <form action="<?= url('admin/contacts.php?delete=' . $inq['id']) ?>" method="POST" onsubmit="return confirm('Delete this inquiry permanently?');" style="display:inline;">
                    <?= CSRF::inputField() ?>
                    <button type="submit" class="btn-admin btn-admin-danger" style="font-size: 0.72rem; padding: 0.25rem 0.5rem;">Delete</button>
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

