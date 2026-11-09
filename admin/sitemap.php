<?php
/**
 * ECOMSHOOT - Admin Sitemap & LLMs.txt SEO File Center
 */

$adminTitle = 'Sitemap & LLMs.txt Management';
$activeNav = 'sitemap';
require_once __DIR__ . '/../config/config.php';

// Handle manual regeneration
if (isset($_GET['regenerate']) || $_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    $res = SEOGenerator::regenerateAll();
    if ($res['sitemap'] && $res['llms'] && $res['robots']) {
        set_flash('success', 'sitemap.xml, llms.txt, and robots.txt regenerated successfully!');
    } else {
        set_flash('error', 'Error occurred during SEO file regeneration. Check file write permissions.');
    }
    redirect('admin/sitemap.php');
}

$sitemapFile = ROOT_PATH . '/sitemap.xml';
$llmsFile = ROOT_PATH . '/llms.txt';
$robotsFile = ROOT_PATH . '/robots.txt';

$sitemapExists = file_exists($sitemapFile);
$llmsExists = file_exists($llmsFile);
$robotsExists = file_exists($robotsFile);

$sitemapContent = $sitemapExists ? file_get_contents($sitemapFile) : '';
$llmsContent = $llmsExists ? file_get_contents($llmsFile) : '';
$robotsContent = $robotsExists ? file_get_contents($robotsFile) : '';

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h2>Automated SEO Engines: Sitemap, LLMs.txt &amp; Robots.txt</h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">
      Synchronizes published pages, services, and blogs for search engine crawlers and LLM indexing models.
    </div>
  </div>

  <form action="<?= url('admin/sitemap.php') ?>" method="POST">
    <?= CSRF::inputField() ?>
    <button type="submit" class="btn-admin btn-admin-primary" style="padding: 0.65rem 1.25rem;">
      ⚡ Regenerate All SEO Files Now
    </button>
  </form>
</div>

<!-- Status Grid -->
<div class="admin-metrics-grid" style="grid-template-columns: repeat(3, 1fr);">
  
  <div class="metric-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
      <span class="metric-title" style="margin: 0;">sitemap.xml</span>
      <span class="status-pill status-<?= $sitemapExists ? 'published' : 'draft' ?>">
        <?= $sitemapExists ? 'Live on Root' : 'Missing' ?>
      </span>
    </div>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">
      Size: <?= $sitemapExists ? round(filesize($sitemapFile) / 1024, 2) . ' KB' : 'N/A' ?><br>
      Last Updated: <?= $sitemapExists ? date('M j, Y g:ia', filemtime($sitemapFile)) : 'N/A' ?>
    </div>
    <div style="margin-top: 1rem;">
      <a href="<?= url('sitemap.xml') ?>" target="_blank" class="btn-admin btn-admin-outline" style="font-size: 0.75rem;">
        View Live XML &nearr;
      </a>
    </div>
  </div>

  <div class="metric-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
      <span class="metric-title" style="margin: 0;">llms.txt</span>
      <span class="status-pill status-<?= $llmsExists ? 'published' : 'draft' ?>">
        <?= $llmsExists ? 'Live on Root' : 'Missing' ?>
      </span>
    </div>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">
      Size: <?= $llmsExists ? round(filesize($llmsFile) / 1024, 2) . ' KB' : 'N/A' ?><br>
      Last Updated: <?= $llmsExists ? date('M j, Y g:ia', filemtime($llmsFile)) : 'N/A' ?>
    </div>
    <div style="margin-top: 1rem;">
      <a href="<?= url('llms.txt') ?>" target="_blank" class="btn-admin btn-admin-outline" style="font-size: 0.75rem;">
        View Live LLMs.txt &nearr;
      </a>
    </div>
  </div>

  <div class="metric-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
      <span class="metric-title" style="margin: 0;">robots.txt</span>
      <span class="status-pill status-<?= $robotsExists ? 'published' : 'draft' ?>">
        <?= $robotsExists ? 'Live on Root' : 'Missing' ?>
      </span>
    </div>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">
      Size: <?= $robotsExists ? round(filesize($robotsFile) / 1024, 2) . ' KB' : 'N/A' ?><br>
      Last Updated: <?= $robotsExists ? date('M j, Y g:ia', filemtime($robotsFile)) : 'N/A' ?>
    </div>
    <div style="margin-top: 1rem;">
      <a href="<?= url('robots.txt') ?>" target="_blank" class="btn-admin btn-admin-outline" style="font-size: 0.75rem;">
        View Live Robots.txt &nearr;
      </a>
    </div>
  </div>

</div>

<!-- LLMs.txt Live Preview Box -->
<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title">Live llms.txt Content Preview</div>
  </div>
  <div class="admin-card-body">
    <pre style="background: #FAFBFD; padding: 1.25rem; border-radius: 8px; border: 1px solid var(--admin-border); overflow-x: auto; font-family: monospace; font-size: 0.85rem; line-height: 1.5;"><?= e($llmsContent) ?></pre>
  </div>
</div>

<!-- Sitemap.xml Live Preview Box -->
<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title">Live sitemap.xml Content Preview</div>
  </div>
  <div class="admin-card-body">
    <pre style="background: #FAFBFD; padding: 1.25rem; border-radius: 8px; border: 1px solid var(--admin-border); overflow-x: auto; font-family: monospace; font-size: 0.85rem; line-height: 1.5;"><?= e($sitemapContent) ?></pre>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

