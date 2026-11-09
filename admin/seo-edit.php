<?php
/**
 * ECOMSHOOT - Admin SEO Edit View
 */

$adminTitle = 'Edit SEO Metadata';
$activeNav = 'seo';
require_once __DIR__ . '/../config/config.php';

$id = (int) ($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT * FROM seo_metadata WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$seo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$seo) {
    set_flash('error', 'SEO record not found.');
    redirect('admin/seo.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();

    $title = trim($_POST['meta_title'] ?? '');
    $desc = trim($_POST['meta_description'] ?? '');
    $canonical = trim($_POST['canonical_url'] ?? '');
    $ogTitle = trim($_POST['og_title'] ?? '');
    $ogDesc = trim($_POST['og_description'] ?? '');
    $ogImage = trim($_POST['og_image'] ?? '');
    $twTitle = trim($_POST['twitter_title'] ?? '');
    $twDesc = trim($_POST['twitter_description'] ?? '');
    $twImage = trim($_POST['twitter_image'] ?? '');
    $robots = trim($_POST['robots_meta'] ?? 'index, follow');

    if (empty($title) || empty($desc)) {
        $error = 'Meta Title and Meta Description are required.';
    } else {
        $upd = $db->prepare(
            "UPDATE seo_metadata SET 
             meta_title = :t, 
             meta_description = :d, 
             canonical_url = :c, 
             og_title = :ogt, 
             og_description = :ogd, 
             og_image = :ogi, 
             twitter_title = :twt, 
             twitter_description = :twd, 
             twitter_image = :twi, 
             robots_meta = :rm, 
             updated_at = NOW() 
             WHERE id = :id"
        );
        $upd->execute([
            't' => $title,
            'd' => $desc,
            'c' => $canonical,
            'ogt' => $ogTitle,
            'ogd' => $ogDesc,
            'ogi' => $ogImage,
            'twt' => $twTitle,
            'twd' => $twDesc,
            'twi' => $twImage,
            'rm' => $robots,
            'id' => $id
        ]);

        SEOGenerator::regenerateAll();

        set_flash('success', 'SEO metadata updated successfully.');
        redirect('admin/seo.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h2>Edit SEO &amp; Social Meta: <code><?= e($seo['page_identifier']) ?></code></h2>
  </div>
  <a href="<?= url('admin/seo.php') ?>" class="btn-admin btn-admin-outline">&larr; Back to SEO</a>
</div>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-body">
    <form action="<?= url('admin/seo-edit.php?id=' . $seo['id']) ?>" method="POST">
      <?= CSRF::inputField() ?>

      <h3 style="font-size: 1rem; color: var(--admin-primary); margin-bottom: 1rem;">Primary HTML Meta Elements</h3>

      <div class="admin-form-group">
        <label for="meta_title" class="admin-form-label">Meta Title Tag (&lt;title&gt;) <span style="color:red;">*</span></label>
        <input type="text" id="meta_title" name="meta_title" value="<?= e($seo['meta_title']) ?>" class="admin-form-control" required>
        <div style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 0.2rem;">Recommended length: 50-60 characters.</div>
      </div>

      <div class="admin-form-group">
        <label for="meta_description" class="admin-form-label">Meta Description (&lt;meta name="description"&gt;) <span style="color:red;">*</span></label>
        <textarea id="meta_description" name="meta_description" class="admin-form-control" style="min-height: 80px;" required><?= e($seo['meta_description']) ?></textarea>
        <div style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 0.2rem;">Recommended length: 150-160 characters.</div>
      </div>

      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
        <div class="admin-form-group">
          <label for="canonical_url" class="admin-form-label">Canonical URL</label>
          <input type="text" id="canonical_url" name="canonical_url" value="<?= e($seo['canonical_url']) ?>" class="admin-form-control">
        </div>
        <div class="admin-form-group">
          <label for="robots_meta" class="admin-form-label">Robots Tag</label>
          <input type="text" id="robots_meta" name="robots_meta" value="<?= e($seo['robots_meta'] ?? 'index, follow') ?>" class="admin-form-control">
        </div>
      </div>

      <h3 style="font-size: 1rem; color: var(--admin-primary); margin-top: 1.5rem; margin-bottom: 1rem;">Open Graph (Facebook, LinkedIn, iMessage)</h3>

      <div class="admin-form-group">
        <label for="og_title" class="admin-form-label">og:title</label>
        <input type="text" id="og_title" name="og_title" value="<?= e($seo['og_title']) ?>" class="admin-form-control">
      </div>

      <div class="admin-form-group">
        <label for="og_description" class="admin-form-label">og:description</label>
        <textarea id="og_description" name="og_description" class="admin-form-control" style="min-height: 60px;"><?= e($seo['og_description']) ?></textarea>
      </div>

      <div class="admin-form-group">
        <label for="og_image" class="admin-form-label">og:image (1200x630 recommended)</label>
        <input type="text" id="og_image" name="og_image" value="<?= e($seo['og_image']) ?>" class="admin-form-control">
      </div>

      <h3 style="font-size: 1rem; color: var(--admin-primary); margin-top: 1.5rem; margin-bottom: 1rem;">Twitter Cards (summary_large_image)</h3>

      <div class="admin-form-group">
        <label for="twitter_title" class="admin-form-label">twitter:title</label>
        <input type="text" id="twitter_title" name="twitter_title" value="<?= e($seo['twitter_title']) ?>" class="admin-form-control">
      </div>

      <div class="admin-form-group">
        <label for="twitter_description" class="admin-form-label">twitter:description</label>
        <textarea id="twitter_description" name="twitter_description" class="admin-form-control" style="min-height: 60px;"><?= e($seo['twitter_description']) ?></textarea>
      </div>

      <div class="admin-form-group">
        <label for="twitter_image" class="admin-form-label">twitter:image</label>
        <input type="text" id="twitter_image" name="twitter_image" value="<?= e($seo['twitter_image']) ?>" class="admin-form-control">
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn-admin btn-admin-primary">Save SEO Tags</button>
        <a href="<?= url('admin/seo.php') ?>" class="btn-admin btn-admin-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

