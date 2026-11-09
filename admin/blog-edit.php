<?php
/**
 * ECOMSHOOT - Admin Blog Editor & Markdown CMS
 * Complete Multi-Tag, Custom Category, Secure Media Upload & Schema Architecture
 */

$adminTitle = 'Article Editor';
$activeNav = 'blogs';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/Uploader.php';
require_once __DIR__ . '/../services/MarkdownParser.php';

$db = Database::getInstance()->getConnection();
$id = (int) ($_GET['id'] ?? 0);

$blog = [
    'id' => 0,
    'category_id' => null,
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'content_format' => 'markdown',
    'featured_image' => 'assets/images/portfolio/portfolio-1.jpg',
    'featured_image_alt' => '',
    'featured_image_caption' => '',
    'author_name' => 'Marcus Vance',
    'author_role' => 'Commercial Photography Director',
    'status' => 'published',
    'faqs_json' => '[]',
    'internal_links_json' => '[]',
    'read_time' => '5 min read',
    'markdown_content' => '',
    'schemas_json' => '',
    'schema_types' => '["BlogPosting","Article","BreadcrumbList"]'
];

$assignedTagIds = [];

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM blogs WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $found = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($found) {
        $blog = array_merge($blog, $found);
        // Fetch assigned tags
        $tStmt = $db->prepare("SELECT tag_id FROM blog_tag_relations WHERE blog_id = :bid");
        $tStmt->execute(['bid' => $id]);
        $assignedTagIds = $tStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();

    // Check if regenerating Markdown file specifically
    if (isset($_POST['action']) && $_POST['action'] === 'regenerate_markdown_file') {
        if ($id > 0) {
            if (MarkdownGenerator::generate($id)) {
                SEOGenerator::regenerateAll();
                set_flash('success', "Markdown file regenerated successfully at /content/blogs/{$id}.md");
            } else {
                set_flash('error', "Could not generate Markdown file. Ensure the article status is published.");
            }
        }
        redirect('admin/blog-edit.php?id=' . $id);
    }

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $catIdInput = trim($_POST['category_id'] ?? '');
    $customCatName = trim($_POST['custom_category_name'] ?? '');
    $customCatSlug = trim($_POST['custom_category_slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $featuredImg = trim($_POST['featured_image'] ?? 'assets/images/portfolio/portfolio-1.jpg');
    $featuredImgAlt = trim($_POST['featured_image_alt'] ?? '');
    $featuredImgCaption = trim($_POST['featured_image_caption'] ?? '');
    $authorName = trim($_POST['author_name'] ?? 'Marcus Vance');
    $authorRole = trim($_POST['author_role'] ?? 'Commercial Photography Director');
    $readTime = trim($_POST['read_time'] ?? '5 min read');
    $status = ($_POST['status'] === 'draft') ? 'draft' : 'published';
    $faqsJson = trim($_POST['faqs_json'] ?? '[]');
    $internalLinksJson = trim($_POST['internal_links_json'] ?? '[]');
    $markdownContent = trim($_POST['markdown_content'] ?? '');
    $schemasJson = trim($_POST['schemas_json'] ?? '');
    $schemaTypesArray = (array) ($_POST['schema_types'] ?? ['BlogPosting', 'Article', 'BreadcrumbList']);
    $schemaTypesJson = json_encode(array_values($schemaTypesArray));

    // Auto-generate slug from title if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    }

    // 1. Process Category (Existing vs "+ Other / Create Custom Category")
    $catId = null;
    if ($catIdInput === '__custom__' || !empty($customCatName)) {
        if (!empty($customCatName)) {
            if (empty($customCatSlug)) {
                $customCatSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $customCatName), '-'));
            }
            // Check if category already exists by slug or name
            $cCheck = $db->prepare("SELECT id FROM blog_categories WHERE slug = :s OR name = :n LIMIT 1");
            $cCheck->execute(['s' => $customCatSlug, 'n' => $customCatName]);
            $existCat = $cCheck->fetch(PDO::FETCH_ASSOC);
            if ($existCat) {
                $catId = (int) $existCat['id'];
            } else {
                $insCat = $db->prepare("INSERT INTO blog_categories (name, slug, description) VALUES (:n, :s, :d)");
                $insCat->execute([
                    'n' => $customCatName,
                    's' => $customCatSlug,
                    'd' => 'Dedicated visual and commercial photography discipline for ' . $customCatName
                ]);
                $catId = (int) $db->lastInsertId();
            }
        }
    } else {
        $catId = (int) $catIdInput ?: null;
    }

    // 2. Handle Featured Image Upload if provided
    if (!empty($_FILES['featured_image_file']['tmp_name']) && $_FILES['featured_image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = Uploader::uploadImage($_FILES['featured_image_file'], $featuredImgAlt, $featuredImgCaption);
        if ($uploadRes['success']) {
            $featuredImg = $uploadRes['file_path'];
        } else {
            $error = $uploadRes['error'];
        }
    }

    // 3. Process Tags (Selected checkboxes + newly added comma-separated tags)
    $selectedTagIds = array_map('intval', (array) ($_POST['tag_ids'] ?? []));
    $newTagsInput = trim($_POST['new_tags'] ?? '');
    if (!empty($newTagsInput)) {
        $rawTags = explode(',', $newTagsInput);
        foreach ($rawTags as $raw) {
            $tName = trim($raw);
            if ($tName === '') continue;
            $tSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $tName), '-'));
            $tCheck = $db->prepare("SELECT id FROM blog_tags WHERE slug = :s OR name = :n LIMIT 1");
            $tCheck->execute(['s' => $tSlug, 'n' => $tName]);
            $existTag = $tCheck->fetch(PDO::FETCH_ASSOC);
            if ($existTag) {
                $selectedTagIds[] = (int) $existTag['id'];
            } else {
                $tIns = $db->prepare("INSERT INTO blog_tags (name, slug, created_at) VALUES (:n, :s, NOW())");
                $tIns->execute(['n' => $tName, 's' => $tSlug]);
                $selectedTagIds[] = (int) $db->lastInsertId();
            }
        }
    }
    $selectedTagIds = array_filter(array_unique($selectedTagIds));

    // 4. Validate Schema JSON syntax if provided
    if (!empty($schemasJson)) {
        $decoded = json_decode($schemasJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'Invalid JSON in Schema JSON-LD Editor: ' . json_last_error_msg();
        }
    }

    if (empty($title) || empty($content)) {
        $error = 'Article title and content body cannot be empty.';
    }

    // Sanitize rich text HTML to prevent XSS
    $sanitizedContent = HtmlSanitizer::clean($content);

    // Synchronize Markdown document version
    if (empty($markdownContent) || ($blog['content_format'] ?? 'markdown') === 'html') {
        $markdownContent = HtmlToMarkdown::convert($sanitizedContent);
    }

    if (empty($error)) {
        if ($id > 0) {
            // Update
            $upd = $db->prepare(
                "UPDATE blogs SET 
                 category_id = :cat, 
                 title = :title, 
                 slug = :slug, 
                 excerpt = :excerpt, 
                 content = :content, 
                 content_format = 'html', 
                 featured_image = :img, 
                 featured_image_alt = :alt, 
                 featured_image_caption = :cap, 
                 author_name = :aname, 
                 author_role = :arole, 
                 read_time = :rtime, 
                 status = :status, 
                 faqs_json = :faqs, 
                 internal_links_json = :links, 
                 markdown_content = :md, 
                 schemas_json = :schemas, 
                 schema_types = :types, 
                 updated_at = NOW() 
                 WHERE id = :id"
            );
            $upd->execute([
                'cat' => $catId,
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $excerpt,
                'content' => $sanitizedContent,
                'img' => $featuredImg,
                'alt' => $featuredImgAlt,
                'cap' => $featuredImgCaption,
                'aname' => $authorName,
                'arole' => $authorRole,
                'rtime' => $readTime,
                'status' => $status,
                'faqs' => $faqsJson,
                'links' => $internalLinksJson,
                'md' => $markdownContent,
                'schemas' => $schemasJson,
                'types' => $schemaTypesJson,
                'id' => $id
            ]);
            $savedId = $id;
        } else {
            // Insert
            $ins = $db->prepare(
                "INSERT INTO blogs 
                 (category_id, title, slug, excerpt, content, content_format, featured_image, featured_image_alt, featured_image_caption, author_name, author_role, read_time, status, faqs_json, internal_links_json, markdown_content, schemas_json, schema_types, published_at) 
                 VALUES (:cat, :title, :slug, :excerpt, :content, 'html', :img, :alt, :cap, :aname, :arole, :rtime, :status, :faqs, :links, :md, :schemas, :types, NOW())"
            );
            $ins->execute([
                'cat' => $catId,
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $excerpt,
                'content' => $sanitizedContent,
                'img' => $featuredImg,
                'alt' => $featuredImgAlt,
                'cap' => $featuredImgCaption,
                'aname' => $authorName,
                'arole' => $authorRole,
                'rtime' => $readTime,
                'status' => $status,
                'faqs' => $faqsJson,
                'links' => $internalLinksJson,
                'md' => $markdownContent,
                'schemas' => $schemasJson,
                'types' => $schemaTypesJson
            ]);
            $savedId = (int) $db->lastInsertId();
        }

        // Synchronize Tag Relations
        $delRel = $db->prepare("DELETE FROM blog_tag_relations WHERE blog_id = :bid");
        $delRel->execute(['bid' => $savedId]);
        if (!empty($selectedTagIds)) {
            $insRel = $db->prepare("INSERT INTO blog_tag_relations (blog_id, tag_id) VALUES (:bid, :tid)");
            foreach ($selectedTagIds as $tid) {
                if ($tid > 0) {
                    $insRel->execute(['bid' => $savedId, 'tid' => $tid]);
                }
            }
        }

        // Synchronize Physical Markdown File
        if ($status === 'published') {
            MarkdownGenerator::generate($savedId);
        } else {
            MarkdownGenerator::delete($savedId);
        }

        // Regenerate SEO files (sitemap.xml, llms.txt, robots.txt)
        SEOGenerator::regenerateAll();

        set_flash('success', 'Article saved successfully, tags synchronized, and SEO files regenerated.');
        redirect('admin/blogs.php');
    }
}

// Fetch all categories & tags for UI
$categories = $db->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$allTags = $db->query("SELECT * FROM blog_tags ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Decode current schema types
$currentSchemaTypes = json_decode($blog['schema_types'] ?? '[]', true) ?: ['BlogPosting', 'Article', 'BreadcrumbList'];

require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= asset_url('assets/css/admin-editor.css') ?>">

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
  <div>
    <h2><?= ($id > 0) ? 'Edit Article: ' . e($blog['title']) : 'Write New Studio Article' ?></h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">Markdown editor with synchronized live browser preview, dynamic tags, schema generator &amp; media manager.</div>
  </div>
  <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
    <?php if ($id > 0): ?>
      <a href="<?= blog_url($id) ?>" target="_blank" class="btn-admin btn-admin-outline">View HTML (/blog/<?= $id ?>) &nearr;</a>
      <a href="<?= blog_md_url($id) ?>" target="_blank" class="btn-admin btn-admin-outline" style="border-color: #6366F1; color: #4338CA;">View .MD (/blog/<?= $id ?>.md) &nearr;</a>
    <?php endif; ?>
    <a href="<?= url('admin/blogs.php') ?>" class="btn-admin btn-admin-outline">&larr; Back to Articles</a>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<!-- Markdown Physical File Status & Direct Publishing System -->
<?php if ($id > 0): ?>
  <?php 
  $mdExists = MarkdownGenerator::hasFile($id);
  $mdMtime = MarkdownGenerator::getLastGeneratedTime($id);
  $mdRelativePath = 'content/blogs/' . $id . '.md';
  ?>
  <div class="admin-card" style="border-left: 4px solid #6366F1; margin-bottom: 1.5rem;">
    <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
      <div>
        <div class="admin-card-title">Atomic Markdown Publishing Status (/content/blogs/<?= $id ?>.md)</div>
        <div style="font-size: 0.8rem; color: var(--admin-text-muted);">
          Physical Markdown document automatically served to LLM indexers and developers at <code>/blog/<?= $id ?>.md</code>.
        </div>
      </div>
      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <?php if ($mdExists): ?>
          <a href="<?= blog_md_url($id) ?>" target="_blank" class="btn-admin btn-admin-outline" style="border-color: #6366F1; color: #4338CA;">
            View Markdown (/blog/<?= $id ?>.md) &nearr;
          </a>
        <?php endif; ?>
        <button type="button" class="btn-admin btn-admin-primary" onclick="confirmRegenerateMarkdown()">
          ⚡ Regenerate Markdown File
        </button>
      </div>
    </div>
    <div class="admin-card-body">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div style="padding: 0.85rem; background: #FAFBFD; border-radius: 6px; border: 1px solid var(--admin-border);">
          <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--admin-text-muted); font-weight: 700;">File Status</div>
          <div style="margin-top: 0.25rem; font-weight: 700; color: <?= $mdExists ? '#059669' : '#DC2626' ?>;">
            <?= $mdExists ? '● Generated &amp; Publicly Available' : '○ Not Generated / Unpublished' ?>
          </div>
        </div>

        <div style="padding: 0.85rem; background: #FAFBFD; border-radius: 6px; border: 1px solid var(--admin-border);">
          <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--admin-text-muted); font-weight: 700;">Storage Path</div>
          <div style="margin-top: 0.25rem; font-family: monospace; font-size: 0.85rem; color: var(--admin-text-main);">
            <?= e($mdRelativePath) ?>
          </div>
        </div>

        <div style="padding: 0.85rem; background: #FAFBFD; border-radius: 6px; border: 1px solid var(--admin-border);">
          <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--admin-text-muted); font-weight: 700;">Last Generated</div>
          <div style="margin-top: 0.25rem; font-size: 0.85rem; color: var(--admin-text-main);">
            <?= $mdMtime ? date('M j, Y - g:i A', $mdMtime) : 'Never' ?>
          </div>
        </div>

        <div style="padding: 0.85rem; background: #FAFBFD; border-radius: 6px; border: 1px solid var(--admin-border);">
          <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--admin-text-muted); font-weight: 700;">HTTP Endpoint</div>
          <div style="margin-top: 0.25rem; font-family: monospace; font-size: 0.85rem; color: #4338CA;">
            /blog/<?= $id ?>.md
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<form action="<?= url('admin/blog-edit.php' . ($id ? '?id=' . $id : '')) ?>" method="POST" enctype="multipart/form-data" id="blogEditorForm">
  <?= CSRF::inputField() ?>

  <!-- Main Responsive Grid Layout (Prevents Category Dropdown Overlap & Viewport Clipping) -->
  <div class="admin-card">
    <div class="admin-card-body">
      
      <div class="editor-main-grid">
        <!-- Left Column: Title, Slug, Excerpt -->
        <div>
          <div class="admin-form-group">
            <label for="title" class="admin-form-label">Article Title <span style="color:red;">*</span></label>
            <input type="text" id="title" name="title" value="<?= e($blog['title']) ?>" class="admin-form-control" placeholder="e.g. How High-Resolution Photography Boosts Shopify Conversions" required>
          </div>

          <div class="admin-form-group">
            <label for="slug" class="admin-form-label">Canonical URL Slug (auto-generated if empty)</label>
            <input type="text" id="slug" name="slug" value="<?= e($blog['slug']) ?>" class="admin-form-control" placeholder="e.g. how-photography-boosts-shopify-conversions">
          </div>

          <div class="admin-form-group">
            <label for="excerpt" class="admin-form-label">Summary / Meta Excerpt</label>
            <textarea id="excerpt" name="excerpt" class="admin-form-control" style="min-height: 80px;" placeholder="Brief synopsis for Google SERP snippet and cards..."><?= e($blog['excerpt']) ?></textarea>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="admin-form-group">
              <label for="author_name" class="admin-form-label">Author Name</label>
              <input type="text" id="author_name" name="author_name" value="<?= e($blog['author_name']) ?>" class="admin-form-control">
            </div>
            <div class="admin-form-group">
              <label for="author_role" class="admin-form-label">Author Job Title</label>
              <input type="text" id="author_role" name="author_role" value="<?= e($blog['author_role']) ?>" class="admin-form-control">
            </div>
          </div>
        </div>

        <!-- Right Column: Category, Status, Tags, Image -->
        <div>
          <!-- Category with Custom Option -->
          <div class="admin-form-group">
            <label for="category_id" class="admin-form-label">Topic Category</label>
            <select id="category_id" name="category_id" class="admin-form-control" onchange="toggleCustomCategory(this.value)">
              <option value="">-- Select Category --</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($blog['category_id'] == $c['id']) ? 'selected' : '' ?>>
                  <?= e($c['name']) ?>
                </option>
              <?php endforeach; ?>
              <option value="__custom__" style="font-weight: 700; color: var(--admin-primary);">+ Other / Create Custom Category</option>
            </select>
          </div>

          <!-- Dynamic Custom Category Container -->
          <div id="customCategoryContainer" style="display: none; padding: 0.85rem; background: #EEF2FF; border: 1px dashed #6366F1; border-radius: 6px; margin-bottom: 1rem;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #312E81; margin-bottom: 0.5rem;">Create New Category On-The-Fly:</div>
            <input type="text" name="custom_category_name" id="customCategoryName" class="admin-form-control" placeholder="Category Name (e.g. Drone Photography)" style="margin-bottom: 0.5rem;">
            <input type="text" name="custom_category_slug" id="customCategorySlug" class="admin-form-control" placeholder="Slug (optional, e.g. drone-photography)">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="admin-form-group">
              <label for="status" class="admin-form-label">Publication Status</label>
              <select id="status" name="status" class="admin-form-control">
                <option value="published" <?= ($blog['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= ($blog['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
              </select>
            </div>

            <div class="admin-form-group">
              <label for="read_time" class="admin-form-label">Read Time</label>
              <input type="text" id="read_time" name="read_time" value="<?= e($blog['read_time']) ?>" class="admin-form-control" placeholder="e.g. 5 min read">
            </div>
          </div>

          <!-- Blog Tags Multi-Select & On-the-Fly Creation -->
          <div class="admin-form-group">
            <label class="admin-form-label" style="display: flex; justify-content: space-between;">
              <span>Article Tags</span>
              <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Select or add new below</span>
            </label>
            
            <div class="tag-selector-wrap">
              <?php if (empty($allTags)): ?>
                <div style="font-size: 0.8rem; color: var(--admin-text-muted);">No tags exist yet. Type comma-separated tags below.</div>
              <?php else: ?>
                <?php foreach ($allTags as $tag): ?>
                  <label class="tag-checkbox-pill <?= in_array($tag['id'], $assignedTagIds) ? 'active' : '' ?>">
                    <input type="checkbox" name="tag_ids[]" value="<?= $tag['id'] ?>" <?= in_array($tag['id'], $assignedTagIds) ? 'checked' : '' ?> onchange="this.parentElement.classList.toggle('active', this.checked)">
                    <span><?= e($tag['name']) ?></span>
                  </label>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <input type="text" name="new_tags" class="admin-form-control" placeholder="Add new tags: e.g. Shopify, Lighting, 4K (comma separated)" style="font-size: 0.82rem;">
          </div>

        </div>
      </div>

    </div>
  </div>

  <!-- Featured Media Upload & Metadata System -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Featured Image Upload &amp; Media Attributes</div>
      <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Secure server-side MIME verification, automatic resizing &amp; SEO alt text.</div>
    </div>
    <div class="admin-card-body">
      <div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(300px, 1fr); gap: 1.5rem; align-items: start;">
        <div>
          <div class="admin-form-group">
            <label class="admin-form-label">Upload New Featured Image (JPG, PNG, WebP &mdash; Max 5MB)</label>
            <input type="file" name="featured_image_file" id="featured_image_file" accept="image/jpeg,image/png,image/webp" class="admin-form-control" onchange="previewSelectedImage(this)">
          </div>

          <div class="admin-form-group">
            <label for="featured_image" class="admin-form-label">Or Specify Image Path / URL</label>
            <input type="text" id="featured_image" name="featured_image" value="<?= e($blog['featured_image']) ?>" class="admin-form-control" placeholder="assets/images/portfolio/portfolio-1.jpg">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="admin-form-group">
              <label for="featured_image_alt" class="admin-form-label">Image Alt Text (SEO)</label>
              <input type="text" id="featured_image_alt" name="featured_image_alt" value="<?= e($blog['featured_image_alt'] ?? '') ?>" class="admin-form-control" placeholder="e.g. Commercial studio lighting equipment and softbox setup">
            </div>

            <div class="admin-form-group">
              <label for="featured_image_caption" class="admin-form-label">Image Caption (Optional)</label>
              <input type="text" id="featured_image_caption" name="featured_image_caption" value="<?= e($blog['featured_image_caption'] ?? '') ?>" class="admin-form-control" placeholder="e.g. High-key lighting arrangement at Ecomshoot Studio">
            </div>
          </div>
        </div>

        <!-- Preview Card -->
        <div class="image-upload-card" style="text-align: center;">
          <div style="font-size: 0.8rem; font-weight: 700; color: var(--admin-text-muted); margin-bottom: 0.5rem;">Current Image Preview:</div>
          <img id="imagePreviewElement" src="<?= asset_url($blog['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg') ?>" alt="Preview" class="image-preview-thumb">
          <div style="margin-top: 0.75rem;">
            <button type="button" class="btn-admin btn-admin-outline" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;" onclick="document.getElementById('featured_image').value=''; document.getElementById('imagePreviewElement').src='';">Clear Image</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- WYSIWYG Rich Text Editor & Markdown Synchronized Studio CMS -->
  <div class="admin-card">
    <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
      <div>
        <div class="admin-card-title">Article Content Editor (WYSIWYG Rich Text &amp; Markdown CMS)</div>
        <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Visual rich text editing with live HTML formatting, inline media uploads, table generator, and automatic Markdown synchronization.</div>
      </div>
      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <button type="button" class="btn-admin btn-admin-outline" onclick="generateMarkdownFromBlog()">⚡ Generate Markdown from Blog</button>
        <button type="button" class="btn-admin btn-admin-outline" onclick="downloadMarkdownFile()">⬇ Download .md File</button>
      </div>
    </div>
    <div class="admin-card-body">
      <!-- WYSIWYG Rich Text Editor Container -->
      <div id="richEditorRoot"></div>

      <!-- Hidden Primary Form Textarea -->
      <textarea id="markdownContentInput" name="content" style="display: none;" required><?= e($blog['content']) ?></textarea>

      <!-- Hidden Markdown Document Storage -->
      <textarea id="markdownFullDoc" name="markdown_content" style="display: none;"><?= e($blog['markdown_content']) ?></textarea>
    </div>
  </div>

  <!-- Automatic Schema Generation & JSON-LD Management -->
  <div class="admin-card">
    <div class="admin-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
      <div>
        <div class="admin-card-title">Automatic Schema.org Architecture &amp; JSON-LD Generator</div>
        <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Select schemas to generate. Output is validated and injected into Google SERP Rich Snippets.</div>
      </div>
      <div style="display: flex; gap: 0.5rem;">
        <button type="button" class="btn-admin btn-admin-primary" style="font-size: 0.82rem; padding: 0.4rem 0.85rem;" onclick="generateSchemasJson()">⚡ Generate Schema JSON-LD</button>
        <button type="button" class="btn-admin btn-admin-outline" style="font-size: 0.82rem; padding: 0.4rem 0.85rem;" onclick="validateSchemaSyntax()">✓ Validate JSON Syntax</button>
      </div>
    </div>
    <div class="admin-card-body">
      
      <label class="admin-form-label" style="margin-bottom: 0.5rem;">Select Schema Types to Include in Structured Graph:</label>
      <div class="schema-selector-grid">
        <?php 
        $availableSchemas = [
            'BlogPosting' => 'BlogPosting (Articles)',
            'Article' => 'Article (General)',
            'BreadcrumbList' => 'BreadcrumbList (Hierarchy)',
            'FAQPage' => 'FAQPage (Q&A Accordions)',
            'Organization' => 'Organization (Ecomshoot)',
            'WebPage' => 'WebPage (Document)',
            'WebSite' => 'WebSite (Portal)',
            'Product' => 'Product (Commercial)',
            'Service' => 'Service (Studio Scope)',
            'LocalBusiness' => 'LocalBusiness (Studio HQ)',
            'Custom' => 'Custom JSON-LD'
        ];
        foreach ($availableSchemas as $typeKey => $typeLabel):
        ?>
          <label class="schema-type-pill">
            <input type="checkbox" name="schema_types[]" value="<?= $typeKey ?>" <?= in_array($typeKey, $currentSchemaTypes) ? 'checked' : '' ?> id="schema_type_<?= $typeKey ?>">
            <span><?= $typeLabel ?></span>
          </label>
        <?php endforeach; ?>
      </div>

      <div class="admin-form-group">
        <label for="schemas_json" class="admin-form-label">Schema JSON-LD Code Block (Editable)</label>
        <textarea id="schemas_json" name="schemas_json" class="admin-form-control" style="min-height: 220px; font-family: monospace; font-size: 0.85rem;" placeholder="Click 'Generate Schema JSON-LD' or write custom schema here..."><?= e($blog['schemas_json']) ?></textarea>
        <div id="schemaValidationNotice" style="margin-top: 0.4rem; font-size: 0.82rem;"></div>
      </div>

    </div>
  </div>

  <!-- Advanced Structured Content (FAQs & Internal Links) -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Structured Data &amp; Internal Linking (JSON Arrays)</div>
    </div>
    <div class="admin-card-body">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <div class="admin-form-group">
          <label for="faqs_json" class="admin-form-label">Article FAQs (JSON Array)</label>
          <textarea id="faqs_json" name="faqs_json" class="admin-form-control" style="min-height: 110px; font-family: monospace; font-size: 0.85rem;" placeholder='[{"question":"...","answer":"..."}]'><?= e($blog['faqs_json']) ?></textarea>
        </div>
        <div class="admin-form-group">
          <label for="internal_links_json" class="admin-form-label">Internal Related Services Links (JSON Array)</label>
          <textarea id="internal_links_json" name="internal_links_json" class="admin-form-control" style="min-height: 110px; font-family: monospace; font-size: 0.85rem;" placeholder='[{"anchor":"Product Photography","url":"product-photography.php"}]'><?= e($blog['internal_links_json']) ?></textarea>
        </div>
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
        <button type="submit" class="btn-admin btn-admin-primary" style="padding: 0.75rem 1.75rem; font-size: 1rem;">
          Save Article &amp; Synchronize SEO
        </button>
        <a href="<?= url('admin/blogs.php') ?>" class="btn-admin btn-admin-outline" style="padding: 0.75rem 1.75rem;">Cancel</a>
      </div>
    </div>
  </div>

</form>

<script>
// Toggle Custom Category Container
function toggleCustomCategory(val) {
  const container = document.getElementById('customCategoryContainer');
  if (val === '__custom__') {
    container.style.display = 'block';
    document.getElementById('customCategoryName').focus();
  } else {
    container.style.display = 'none';
  }
}

// Live Image Preview for File Input
function previewSelectedImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('imagePreviewElement').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Synchronize Live Markdown Preview (split-screen)
function syncLivePreview() {
  const input = document.getElementById('markdownContentInput');
  const preview = document.getElementById('markdownPreviewArea');
  if (!input || !preview) return;

  const raw = input.value;
  // Lightweight client-side Markdown parser for instant preview
  let html = raw
    .replace(/^### (.*$)/gim, '<h3>$1</h3>')
    .replace(/^## (.*$)/gim, '<h2>$1</h2>')
    .replace(/^# (.*$)/gim, '<h1>$1</h1>')
    .replace(/^\> (.*$)/gim, '<blockquote>$1</blockquote>')
    .replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/gim, '<em>$1</em>')
    .replace(/!\[(.*?)\]\((.*?)\)/gim, '<img alt="$1" src="$2" style="max-width:100%; border-radius:6px;" />')
    .replace(/\[(.*?)\]\((.*?)\)/gim, '<a href="$2" target="_blank">$1</a>')
    .replace(/^\- (.*$)/gim, '<li>$1</li>')
    .replace(/\n\n/gim, '<p></p>');

  preview.innerHTML = html;
}

// Generate Markdown from Blog Details with Frontmatter
function generateMarkdownFromBlog() {
  const currentInput = document.getElementById('markdownContentInput').value.trim();
  if (currentInput.length > 50) {
    if (!confirm('Compile and regenerate Markdown with frontmatter from current form fields?')) {
      return;
    }
  }

  const articleId = <?= (int)$id ?> || 1;
  const siteUrl = window.location.origin;
  const title = document.getElementById('title').value || 'Untitled Studio Guide';
  const slug = document.getElementById('slug').value || '';
  const excerpt = document.getElementById('excerpt').value || '';
  const authorName = document.getElementById('author_name').value || 'Marcus Vance';
  const authorRole = document.getElementById('author_role').value || 'Commercial Photography Director';
  const readTime = document.getElementById('read_time').value || '5 min read';
  const featuredImage = document.getElementById('featured_image').value || '';
  const featuredImageAlt = document.getElementById('featured_image_alt').value || '';
  const featuredImageCaption = document.getElementById('featured_image_caption').value || '';
  const catSelect = document.getElementById('category_id');
  const categoryName = (catSelect.selectedIndex > 0) ? catSelect.options[catSelect.selectedIndex].text : 'General';
  
  // Selected tags
  const tagPills = document.querySelectorAll('input[name="tag_ids[]"]:checked');
  const tags = [];
  tagPills.forEach(p => {
    tags.push(p.parentElement.innerText.trim());
  });

  const content = document.getElementById('markdownContentInput').value || '';

  const frontmatter = [
    '---',
    'title: ' + JSON.stringify(title),
    'summary: ' + JSON.stringify(excerpt),
    'author: ' + JSON.stringify(authorName),
    'author_designation: ' + JSON.stringify(authorRole),
    'published_date: ' + JSON.stringify(new Date().toISOString().slice(0, 10)),
    'modified_date: ' + JSON.stringify(new Date().toISOString()),
    'read_time: ' + JSON.stringify(readTime),
    'category: ' + JSON.stringify(categoryName),
    'tags: ' + JSON.stringify(tags),
    'featured_image: ' + JSON.stringify(featuredImage),
    'featured_image_alt: ' + JSON.stringify(featuredImageAlt),
    'featured_image_caption: ' + JSON.stringify(featuredImageCaption),
    'canonical_html_url: ' + JSON.stringify(siteUrl + '/blog/' + articleId),
    'markdown_url: ' + JSON.stringify(siteUrl + '/blog/' + articleId + '.md'),
    '---',
    '',
    content
  ].join('\n');

  document.getElementById('markdownFullDoc').value = frontmatter;
  alert('Markdown document with YAML frontmatter generated successfully.');
}

// Download .md File Client-Side
function downloadMarkdownFile() {
  generateMarkdownFromBlog();
  const md = document.getElementById('markdownFullDoc').value || document.getElementById('markdownContentInput').value;
  const articleId = <?= (int)$id ?> || 1;
  const blob = new Blob([md], { type: 'text/markdown;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = articleId + '.md';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

// Checkbox-based Schema Generator
function generateSchemasJson() {
  const articleId = <?= (int)$id ?> || 1;
  const title = document.getElementById('title').value || 'Ecomshoot Commercial Photography Guide';
  const excerpt = document.getElementById('excerpt').value || '';
  const authorName = document.getElementById('author_name').value || 'Marcus Vance';
  const authorRole = document.getElementById('author_role').value || 'Commercial Photography Director';
  const image = document.getElementById('featured_image').value || 'assets/images/portfolio/portfolio-1.jpg';
  const siteUrl = window.location.origin;
  const articleUrl = siteUrl + '/blog/' + articleId;

  // Check which schema types are checked
  const checkedTypes = [];
  document.querySelectorAll('input[name="schema_types[]"]:checked').forEach(cb => {
    checkedTypes.push(cb.value);
  });

  const graph = [];

  if (checkedTypes.includes('Organization')) {
    graph.push({
      "@type": "Organization",
      "@id": siteUrl + "/#organization",
      "name": "Ecomshoot",
      "url": siteUrl,
      "logo": siteUrl + "/assets/images/logo.svg",
      "email": "contact@ecommshoot.in"
    });
  }

  if (checkedTypes.includes('WebSite')) {
    graph.push({
      "@type": "WebSite",
      "@id": siteUrl + "/#website",
      "url": siteUrl,
      "name": "Ecomshoot",
      "publisher": { "@id": siteUrl + "/#organization" }
    });
  }

  if (checkedTypes.includes('WebPage')) {
    graph.push({
      "@type": "WebPage",
      "@id": articleUrl + "#webpage",
      "url": articleUrl,
      "name": title,
      "description": excerpt,
      "isPartOf": { "@id": siteUrl + "/#website" }
    });
  }

  if (checkedTypes.includes('BlogPosting') || checkedTypes.includes('Article')) {
    graph.push({
      "@type": checkedTypes.includes('BlogPosting') ? "BlogPosting" : "Article",
      "@id": articleUrl + "#article",
      "headline": title,
      "description": excerpt,
      "image": image.startsWith('http') ? image : siteUrl + '/' + image,
      "datePublished": new Date().toISOString(),
      "dateModified": new Date().toISOString(),
      "mainEntityOfPage": articleUrl,
      "author": {
        "@type": "Person",
        "name": authorName,
        "jobTitle": authorRole
      },
      "publisher": {
        "@type": "Organization",
        "name": "Ecomshoot",
        "url": siteUrl,
        "logo": {
          "@type": "ImageObject",
          "url": siteUrl + "/assets/images/logo.svg"
        }
      }
    });
  }

  if (checkedTypes.includes('BreadcrumbList')) {
    graph.push({
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": siteUrl
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Blogs",
          "item": siteUrl + "/blog.php"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": title,
          "item": articleUrl
        }
      ]
    });
  }

  // Parse FAQs if checked
  if (checkedTypes.includes('FAQPage')) {
    try {
      const faqs = JSON.parse(document.getElementById('faqs_json').value || '[]');
      if (Array.isArray(faqs) && faqs.length > 0) {
        const faqEntities = faqs.map(f => ({
          "@type": "Question",
          "name": f.question || '',
          "acceptedAnswer": {
            "@type": "Answer",
            "text": f.answer || ''
          }
        }));
        graph.push({
          "@type": "FAQPage",
          "mainEntity": faqEntities
        });
      }
    } catch(e) {
      console.warn('FAQ JSON parse error');
    }
  }

  if (checkedTypes.includes('Service')) {
    graph.push({
      "@type": "Service",
      "name": "E-Commerce Commercial Product Photography",
      "provider": { "@id": siteUrl + "/#organization" },
      "serviceType": "Commercial Visual Production"
    });
  }

  if (checkedTypes.includes('LocalBusiness')) {
    graph.push({
      "@type": "LocalBusiness",
      "name": "Ecomshoot Creative Studio",
      "email": "contact@ecommshoot.in",
      "url": siteUrl
    });
  }

  const rootSchema = {
    "@context": "https://schema.org",
    "@graph": graph
  };

  document.getElementById('schemas_json').value = JSON.stringify(rootSchema, null, 2);
  validateSchemaSyntax();
}

// Confirm Regenerate Markdown File on Server
function confirmRegenerateMarkdown() {
  if (confirm('Regenerate the physical Markdown file (/content/blogs/<?= (int)$id ?>.md) atomically from database? This will refresh the publicly served .md file.')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= url("admin/blog-edit.php?id=" . $id) ?>';
    
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = 'csrf_token';
    csrf.value = '<?= CSRF::getToken() ?>';
    form.appendChild(csrf);

    const act = document.createElement('input');
    act.type = 'hidden';
    act.name = 'action';
    act.value = 'regenerate_markdown_file';
    form.appendChild(act);

    document.body.appendChild(form);
    form.submit();
  }
}

// Validate Schema JSON Syntax
function validateSchemaSyntax() {
  const area = document.getElementById('schemas_json');
  const notice = document.getElementById('schemaValidationNotice');
  const val = area.value.trim();

  if (!val) {
    notice.innerHTML = '<span style="color:var(--admin-text-muted);">Schema is empty.</span>';
    return;
  }

  try {
    const parsed = JSON.parse(val);
    if (parsed['@context']) {
      notice.innerHTML = '<span style="color:#059669; font-weight:600;">✓ Valid Schema.org JSON-LD syntax confirmed.</span>';
    } else {
      notice.innerHTML = '<span style="color:#D97706; font-weight:600;">⚠ Valid JSON, but recommended "@context": "https://schema.org" is missing.</span>';
    }
  } catch (err) {
    notice.innerHTML = '<span style="color:#DC2626; font-weight:600;">✗ JSON Syntax Error: ' + err.message + '</span>';
  }
}

</script>
<script src="<?= asset_url('assets/js/admin-editor.js') ?>"></script>
<script>
// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  if (typeof syncLivePreview === 'function') {
    syncLivePreview();
  }

  // Initialize Rich Text WYSIWYG Editor
  if (typeof initRichEditor === 'function') {
    window.richEditor = initRichEditor('#richEditorRoot', {
      textarea: '#markdownContentInput',
      markdownTextarea: '#markdownFullDoc',
      csrfToken: '<?= CSRF::getToken() ?>',
      uploadUrl: '<?= url("admin/api-media-upload.php") ?>',
      mediaListUrl: '<?= url("admin/api-media-list.php") ?>',
      articleId: '<?= (int)$blog["id"] ?>',
      articleStatus: '<?= e($blog["status"]) ?>',
      articleTitle: '<?= addslashes(e($blog["title"])) ?>',
      liveUrl: '<?= ($id > 0) ? blog_url($id) : "" ?>'
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
