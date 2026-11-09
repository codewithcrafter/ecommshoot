<?php
/**
 * ECOMSHOOT - Admin Schema Editor
 */

$adminTitle = 'Schema Editor';
$activeNav = 'schemas';
require_once __DIR__ . '/../config/config.php';

$id = (int) ($_GET['id'] ?? 0);
$db = Database::getInstance()->getConnection();

$schema = [
    'id' => 0,
    'name' => '',
    'schema_type' => 'Organization',
    'page_identifier' => 'all',
    'schema_json' => "{\n  \"@context\": \"https://schema.org\",\n  \"@type\": \"Organization\",\n  \"name\": \"Ecomshoot\",\n  \"url\": \"http://localhost:8000\"\n}",
    'is_active' => 1
];

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM `schemas` WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $found = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($found) {
        $schema = $found;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();

    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['schema_type'] ?? 'Organization');
    $pageId = trim($_POST['page_identifier'] ?? 'all');
    $rawJson = trim($_POST['schema_json'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    // Validate JSON syntax using SchemaManager
    $validation = SchemaManager::validateJSON($rawJson);
    if (!$validation['valid']) {
        $error = $validation['error'];
    } elseif (empty($name)) {
        $error = 'Schema identifier name is required.';
    } else {
        if ($id > 0) {
            $upd = $db->prepare(
                "UPDATE `schemas` SET 
                 name = :n, 
                 schema_type = :t, 
                 page_identifier = :p, 
                 schema_json = :j, 
                 is_active = :a, 
                 updated_at = NOW() 
                 WHERE id = :id"
            );
            $upd->execute([
                'n' => $name,
                't' => $type,
                'p' => $pageId,
                'j' => $rawJson,
                'a' => $isActive,
                'id' => $id
            ]);
        } else {
            $ins = $db->prepare(
                "INSERT INTO `schemas` (name, schema_type, page_identifier, schema_json, is_active) 
                 VALUES (:n, :t, :p, :j, :a)"
            );
            $ins->execute([
                'n' => $name,
                't' => $type,
                'p' => $pageId,
                'j' => $rawJson,
                'a' => $isActive
            ]);
        }

        set_flash('success', 'Schema definition validated and saved successfully.');
        redirect('admin/schemas.php');
    }
}

// Fetch list of all page identifiers for the dropdown
$pageOptions = ['all' => 'Global (Injected on all public pages)'];
$pages = $db->query("SELECT slug, title FROM pages ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($pages as $p) {
    $pageOptions[$p['slug']] = 'Page: ' . $p['title'] . ' (' . $p['slug'] . ')';
}
$services = $db->query("SELECT slug, title FROM services ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($services as $s) {
    $pageOptions[$s['slug']] = 'Service: ' . $s['title'] . ' (' . $s['slug'] . ')';
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h2><?= ($id > 0) ? 'Edit Schema: ' . e($schema['name']) : 'Create New JSON-LD Schema' ?></h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">Independent schema architecture rendered into page &lt;head&gt;.</div>
  </div>
  <a href="<?= url('admin/schemas.php') ?>" class="btn-admin btn-admin-outline">&larr; Back to Schemas</a>
</div>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-body">
    <form action="<?= url('admin/schema-edit.php' . ($id ? '?id=' . $id : '')) ?>" method="POST">
      <?= CSRF::inputField() ?>

      <div style="display: grid; grid-template-columns: 2fr 1.5fr 1.5fr; gap: 1rem;">
        <div class="admin-form-group">
          <label for="name" class="admin-form-label">Schema Descriptive Name <span style="color:red;">*</span></label>
          <input type="text" id="name" name="name" value="<?= e($schema['name']) ?>" class="admin-form-control" placeholder="e.g. Master Organization Schema" required>
        </div>

        <div class="admin-form-group">
          <label for="schema_type" class="admin-form-label">Schema Type</label>
          <select id="schema_type" name="schema_type" class="admin-form-control">
            <?php 
            $types = ['Organization', 'WebSite', 'WebPage', 'Service', 'Article', 'BlogPosting', 'BreadcrumbList', 'FAQPage', 'Product', 'LocalBusiness'];
            foreach ($types as $t): ?>
              <option value="<?= $t ?>" <?= ($schema['schema_type'] === $t) ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="admin-form-group">
          <label for="page_identifier" class="admin-form-label">Assign to Page</label>
          <select id="page_identifier" name="page_identifier" class="admin-form-control">
            <?php foreach ($pageOptions as $slugKey => $label): ?>
              <option value="<?= e($slugKey) ?>" <?= ($schema['page_identifier'] === $slugKey) ? 'selected' : '' ?>>
                <?= e($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="admin-form-group">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
          <label for="schemaJsonTextarea" class="admin-form-label" style="margin-bottom: 0;">JSON-LD Source Code (&lt;script type="application/ld+json"&gt;)</label>
          <div id="jsonValidationStatus"></div>
        </div>
        <textarea id="schemaJsonTextarea" name="schema_json" class="admin-form-control" style="min-height: 280px; font-family: 'Consolas', monospace; font-size: 0.92rem; line-height: 1.5;" required><?= e($schema['schema_json']) ?></textarea>
      </div>

      <div class="admin-form-group">
        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
          <input type="checkbox" name="is_active" value="1" <?= ($schema['is_active'] ? 'checked' : '') ?>>
          <span style="font-weight: 600;">Enable this Schema (Injected automatically into assigned page &lt;head&gt;)</span>
        </label>
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn-admin btn-admin-primary">Validate &amp; Save Schema</button>
        <a href="<?= url('admin/schemas.php') ?>" class="btn-admin btn-admin-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

