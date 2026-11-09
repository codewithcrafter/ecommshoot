<?php
/**
 * ECOMSHOOT - Admin Media Library
 */

$adminTitle = 'Media Library';
$activeNav = 'media';
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/Uploader.php';

$db = Database::getInstance()->getConnection();
$error = '';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    CSRF::check();
    $alt = trim($_POST['alt_text'] ?? '');
    $caption = trim($_POST['caption'] ?? '');

    $uploadResult = Uploader::uploadImage($_FILES['media_file'], $alt, $caption);
    if ($uploadResult['success']) {
        set_flash('success', 'Media file uploaded successfully.');
        redirect('admin/media.php');
    } else {
        $error = $uploadResult['error'] ?? 'Upload failed.';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    CSRF::check();
    $delId = (int) $_GET['delete'];
    $stmt = $db->prepare("SELECT * FROM media WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $delId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item) {
        $fullPath = ROOT_PATH . '/' . $item['file_path'];
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
        $delStmt = $db->prepare("DELETE FROM media WHERE id = :id");
        $delStmt->execute(['id' => $delId]);
        set_flash('success', 'Media file deleted.');
    }
    redirect('admin/media.php');
}

$media = $db->query("SELECT * FROM media ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h2>Studio Media Library &amp; Asset Vault</h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">Upload, manage, and inspect commercial image assets.</div>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<!-- Upload Box -->
<div class="admin-card" style="background-color: #FFFDF9; border-color: rgba(37, 99, 235, 0.2);">
  <div class="admin-card-header">
    <div class="admin-card-title">Upload New Image File (JPEG, PNG, WebP, SVG)</div>
  </div>
  <div class="admin-card-body">
    <form action="<?= url('admin/media.php') ?>" method="POST" enctype="multipart/form-data" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
      <?= CSRF::inputField() ?>

      <div style="flex: 1; min-width: 200px;">
        <label class="admin-form-label">Select Image File (Max 10MB)</label>
        <input type="file" name="media_file" accept="image/jpeg,image/png,image/webp,image/svg+xml" class="admin-form-control" required>
      </div>

      <div style="flex: 1; min-width: 180px;">
        <label class="admin-form-label">Alt Text (Accessibility)</label>
        <input type="text" name="alt_text" placeholder="e.g. Perfume hero bottle on white" class="admin-form-control">
      </div>

      <button type="submit" class="btn-admin btn-admin-primary" style="padding: 0.65rem 1.25rem;">
        Upload to Vault
      </button>
    </form>
  </div>
</div>

<!-- Media Gallery Grid -->
<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title">Library Assets (<?= count($media) ?> Items)</div>
  </div>
  <div class="admin-card-body">
    <?php if (empty($media)): ?>
      <div style="text-align: center; padding: 3rem; color: var(--admin-text-muted);">
        No media uploaded yet. Use the upload tool above to add assets to the library.
      </div>
    <?php else: ?>
      <div class="media-grid">
        <?php foreach ($media as $m): ?>
          <div class="media-item">
            <div class="media-thumb">
              <img src="<?= asset_url($m['file_path']) ?>" alt="<?= e($m['alt_text']) ?>" loading="lazy">
            </div>
            <div class="media-info">
              <div class="media-title" title="<?= e($m['original_name']) ?>"><?= e($m['original_name']) ?></div>
              <div style="font-size: 0.72rem; color: var(--admin-text-muted); margin-bottom: 0.5rem;">
                <?= round($m['file_size'] / 1024, 1) ?> KB • <?= e($m['mime_type']) ?>
              </div>
              <div style="display: flex; gap: 0.25rem;">
                <button type="button" class="btn-admin btn-admin-outline btn-copy-url" data-url="<?= asset_url($m['file_path']) ?>" style="font-size: 0.7rem; padding: 0.2rem 0.4rem; flex: 1;">
                  Copy URL
                </button>
                <form action="<?= url('admin/media.php?delete=' . $m['id']) ?>" method="POST" onsubmit="return confirm('Delete this media file permanently?');" style="display:inline;">
                  <?= CSRF::inputField() ?>
                  <button type="submit" class="btn-admin btn-admin-danger" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">
                    Delete
                  </button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

