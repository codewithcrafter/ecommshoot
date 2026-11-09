<?php
/**
 * ECOMSHOOT - Admin Service Edit
 */

$adminTitle = 'Edit Service';
$activeNav = 'services';
require_once __DIR__ . '/../config/config.php';

$id = (int) ($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT * FROM services WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    set_flash('error', 'Service not found.');
    redirect('admin/services.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();

    $title = trim($_POST['title'] ?? '');
    $h1 = trim($_POST['h1_heading'] ?? '');
    $badge = trim($_POST['badge'] ?? '');
    $order = (int) ($_POST['display_order'] ?? 0);
    $shortDesc = trim($_POST['short_description'] ?? '');
    $intro = trim($_POST['introduction'] ?? '');
    $benefitsJson = trim($_POST['benefits_json'] ?? '');
    $featuresJson = trim($_POST['features_json'] ?? '');
    $industriesJson = trim($_POST['industries_json'] ?? '');
    $processJson = trim($_POST['process_json'] ?? '');
    $faqsJson = trim($_POST['faqs_json'] ?? '');
    $status = ($_POST['status'] === 'draft') ? 'draft' : 'published';

    // Validate JSON fields
    foreach (['benefits_json' => $benefitsJson, 'features_json' => $featuresJson, 'industries_json' => $industriesJson, 'process_json' => $processJson, 'faqs_json' => $faqsJson] as $fieldName => $jsonVal) {
        if (!empty($jsonVal)) {
            json_decode($jsonVal);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = "Invalid JSON in {$fieldName}: " . json_last_error_msg();
                break;
            }
        }
    }

    if (empty($error)) {
        if (empty($title) || empty($h1)) {
            $error = 'Service Title and H1 Heading are required.';
        } else {
            $upd = $db->prepare(
                "UPDATE services SET 
                 title = :title, 
                 h1_heading = :h1, 
                 badge = :badge, 
                 display_order = :order, 
                 short_description = :short, 
                 introduction = :intro, 
                 benefits_json = :benefits, 
                 features_json = :features, 
                 industries_json = :industries, 
                 process_json = :process, 
                 faqs_json = :faqs, 
                 status = :status, 
                 updated_at = NOW() 
                 WHERE id = :id"
            );
            $upd->execute([
                'title' => $title,
                'h1' => $h1,
                'badge' => $badge,
                'order' => $order,
                'short' => $shortDesc,
                'intro' => $intro,
                'benefits' => $benefitsJson,
                'features' => $featuresJson,
                'industries' => $industriesJson,
                'process' => $processJson,
                'faqs' => $faqsJson,
                'status' => $status,
                'id' => $id
            ]);

            // Regenerate SEO
            SEOGenerator::regenerateAll();

            set_flash('success', 'Service updated successfully and SEO files synchronized.');
            redirect('admin/services.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h2>Edit Service: <?= e($service['title']) ?></h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">Dedicated Page: <code><?= e($service['file_name']) ?></code></div>
  </div>
  <a href="<?= url('admin/services.php') ?>" class="btn-admin btn-admin-outline">&larr; Back to Services</a>
</div>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-body">
    <form action="<?= url('admin/service-edit.php?id=' . $service['id']) ?>" method="POST">
      <?= CSRF::inputField() ?>

      <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem;">
        <div class="admin-form-group">
          <label for="title" class="admin-form-label">Service Title</label>
          <input type="text" id="title" name="title" value="<?= e($service['title']) ?>" class="admin-form-control" required>
        </div>
        <div class="admin-form-group">
          <label for="badge" class="admin-form-label">Visual Badge</label>
          <input type="text" id="badge" name="badge" value="<?= e($service['badge']) ?>" class="admin-form-control">
        </div>
        <div class="admin-form-group">
          <label for="display_order" class="admin-form-label">Order</label>
          <input type="number" id="display_order" name="display_order" value="<?= e($service['display_order']) ?>" class="admin-form-control">
        </div>
      </div>

      <div class="admin-form-group">
        <label for="h1_heading" class="admin-form-label">Unique Primary H1 Heading</label>
        <input type="text" id="h1_heading" name="h1_heading" value="<?= e($service['h1_heading']) ?>" class="admin-form-control" required>
      </div>

      <div class="admin-form-group">
        <label for="short_description" class="admin-form-label">Short Description</label>
        <textarea id="short_description" name="short_description" class="admin-form-control" style="min-height: 80px;" required><?= e($service['short_description']) ?></textarea>
      </div>

      <div class="admin-form-group">
        <label for="introduction" class="admin-form-label">Detailed Introduction Content</label>
        <textarea id="introduction" name="introduction" class="admin-form-control" style="min-height: 120px;" required><?= e($service['introduction']) ?></textarea>
      </div>

      <div class="admin-form-group">
        <label for="benefits_json" class="admin-form-label">Service Benefits (JSON Array)</label>
        <textarea id="benefits_json" name="benefits_json" class="admin-form-control" style="min-height: 110px; font-family: monospace;"><?= e($service['benefits_json']) ?></textarea>
      </div>

      <div class="admin-form-group">
        <label for="features_json" class="admin-form-label">Technical Features (JSON Array)</label>
        <textarea id="features_json" name="features_json" class="admin-form-control" style="min-height: 110px; font-family: monospace;"><?= e($service['features_json']) ?></textarea>
      </div>

      <div class="admin-form-group">
        <label for="industries_json" class="admin-form-label">Industries Served (JSON Array)</label>
        <textarea id="industries_json" name="industries_json" class="admin-form-control" style="min-height: 110px; font-family: monospace;"><?= e($service['industries_json']) ?></textarea>
      </div>

      <div class="admin-form-group">
        <label for="process_json" class="admin-form-label">Production Process 01-04 (JSON Array)</label>
        <textarea id="process_json" name="process_json" class="admin-form-control" style="min-height: 110px; font-family: monospace;"><?= e($service['process_json']) ?></textarea>
      </div>

      <div class="admin-form-group">
        <label for="faqs_json" class="admin-form-label">Relevant FAQs (JSON Array)</label>
        <textarea id="faqs_json" name="faqs_json" class="admin-form-control" style="min-height: 120px; font-family: monospace;"><?= e($service['faqs_json']) ?></textarea>
      </div>

      <div class="admin-form-group">
        <label for="status" class="admin-form-label">Status</label>
        <select id="status" name="status" class="admin-form-control" style="width: 200px;">
          <option value="published" <?= ($service['status'] === 'published') ? 'selected' : '' ?>>Published</option>
          <option value="draft" <?= ($service['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
        </select>
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn-admin btn-admin-primary">Save Specifications &amp; Sync SEO</button>
        <a href="<?= url('admin/services.php') ?>" class="btn-admin btn-admin-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

