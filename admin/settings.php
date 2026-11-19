<?php
/**
 * ECOMSHOOT - Admin Website Settings & Configuration
 */

$adminTitle = 'Website Settings';
$activeNav = 'settings';
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();

    $settingsToUpdate = [
        'site_name' => trim($_POST['site_name'] ?? 'Ecomshoot'),
        'site_tagline' => trim($_POST['site_tagline'] ?? ''),
        'site_email' => trim($_POST['site_email'] ?? 'contact@ecommshoot.in'),
        'site_phone' => trim($_POST['site_phone'] ?? ''),
        'site_address' => trim($_POST['site_address'] ?? ''),
        'site_hours' => trim($_POST['site_hours'] ?? ''),
        'site_base_url' => rtrim(trim($_POST['site_base_url'] ?? ''), '/'),
        'social_instagram' => trim($_POST['social_instagram'] ?? ''),
        'social_facebook' => trim($_POST['social_facebook'] ?? ''),
        'social_linkedin' => trim($_POST['social_linkedin'] ?? ''),
        'social_pinterest' => trim($_POST['social_pinterest'] ?? ''),
        'social_youtube' => trim($_POST['social_youtube'] ?? ''),
        'popup_enabled' => trim($_POST['popup_enabled'] ?? '1'),
        'popup_delay' => trim($_POST['popup_delay'] ?? '5'),
        'popup_cooldown' => trim($_POST['popup_cooldown'] ?? '60'),
        'popup_heading' => trim($_POST['popup_heading'] ?? "Let's Create Something Extraordinary"),
        'popup_description' => trim($_POST['popup_description'] ?? "Tell us about your photography requirements. Our team will get in touch with you."),
        'whatsapp_number' => trim($_POST['whatsapp_number'] ?? ''),
        'call_number' => trim($_POST['call_number'] ?? ''),
        'whatsapp_default_message' => trim($_POST['whatsapp_default_message'] ?? 'Hello Ecomshoot, I would like to discuss my product photography requirements.'),
        'enable_whatsapp_button' => trim($_POST['enable_whatsapp_button'] ?? '1'),
        'enable_call_button' => trim($_POST['enable_call_button'] ?? '1'),
        'llms_company_summary' => trim($_POST['llms_company_summary'] ?? '')
    ];

    $updStmt = $db->prepare(
        "INSERT INTO website_settings (setting_key, setting_value, updated_at) 
         VALUES (:k, :v, NOW()) 
         ON DUPLICATE KEY UPDATE setting_value = :v, updated_at = NOW()"
    );

    foreach ($settingsToUpdate as $key => $val) {
        $updStmt->execute(['k' => $key, 'v' => $val]);
    }

    // Automatically regenerate sitemap.xml, llms.txt, and robots.txt
    SEOGenerator::regenerateAll();

    set_flash('success', 'Website settings updated successfully and SEO files synchronized.');
    redirect('admin/settings.php');
}

// Reload fresh settings
$settings = [];
$rows = $db->query("SELECT setting_key, setting_value FROM website_settings")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $settings[$r['setting_key']] = $r['setting_value'];
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h2>Global Website Settings &amp; Studio Configurations</h2>
    <div style="font-size: 0.85rem; color: var(--admin-text-muted);">Configure brand identity, studio contact information, social links, and LLM summary.</div>
  </div>
</div>

<form action="<?= url('admin/settings.php') ?>" method="POST">
  <?= CSRF::inputField() ?>

  <!-- General Settings Card -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">General Brand Identity</div>
    </div>
    <div class="admin-card-body">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <div class="admin-form-group">
          <label class="admin-form-label">Studio / Brand Name</label>
          <input type="text" name="site_name" value="<?= e($settings['site_name'] ?? 'Ecomshoot') ?>" class="admin-form-control" required>
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Canonical Base URL (No trailing slash)</label>
          <input type="text" name="site_base_url" value="<?= e($settings['site_base_url'] ?? BASE_URL) ?>" class="admin-form-control" required>
        </div>
      </div>

      <div class="admin-form-group">
        <label class="admin-form-label">Brand Tagline</label>
        <input type="text" name="site_tagline" value="<?= e($settings['site_tagline'] ?? '') ?>" class="admin-form-control">
      </div>
    </div>
  </div>

  <!-- Contact & Studio Information -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Studio Location &amp; Contact Information</div>
    </div>
    <div class="admin-card-body">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <div class="admin-form-group">
          <label class="admin-form-label">Public Contact Email <span style="color:red;">*</span></label>
          <input type="email" name="site_email" value="<?= e($settings['site_email'] ?? 'contact@ecommshoot.in') ?>" class="admin-form-control" required>
          <div style="font-size:0.75rem; color:var(--admin-text-muted); margin-top:0.25rem;">Default official agency email: contact@ecommshoot.in</div>
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Public Studio Phone (Optional - leave blank to hide)</label>
          <input type="text" name="site_phone" value="<?= e($settings['site_phone'] ?? '') ?>" class="admin-form-control" placeholder="e.g. +91 98765 43210 or leave blank to hide">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <div class="admin-form-group">
          <label class="admin-form-label">Studio Physical Address (Optional)</label>
          <input type="text" name="site_address" value="<?= e($settings['site_address'] ?? '') ?>" class="admin-form-control" placeholder="Leave blank if not displaying a physical address">
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Operating Hours (Optional)</label>
          <input type="text" name="site_hours" value="<?= e($settings['site_hours'] ?? 'Monday – Friday: 9:00 AM – 6:30 PM IST') ?>" class="admin-form-control">
        </div>
      </div>
    </div>
  </div>

  <!-- Automatic Lead Generation Popup Configuration -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Automatic Lead Generation Popup System</div>
      <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Controls the timed modal dialog appearing on all public website pages.</div>
    </div>
    <div class="admin-card-body">
      <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
        <div class="admin-form-group">
          <label class="admin-form-label">Popup Status</label>
          <select name="popup_enabled" class="admin-form-control">
            <option value="1" <?= (($settings['popup_enabled'] ?? '1') === '1') ? 'selected' : '' ?>>Enabled (Active)</option>
            <option value="0" <?= (($settings['popup_enabled'] ?? '1') === '0') ? 'selected' : '' ?>>Disabled (Turn Off)</option>
          </select>
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Appearance Delay (Seconds)</label>
          <input type="number" name="popup_delay" min="1" max="60" value="<?= e($settings['popup_delay'] ?? '5') ?>" class="admin-form-control" required>
          <div style="font-size:0.75rem; color:var(--admin-text-muted); margin-top:0.25rem;">Standard default: 5 seconds</div>
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">User Cooldown (Minutes)</label>
          <input type="number" name="popup_cooldown" min="0" max="10080" value="<?= e($settings['popup_cooldown'] ?? '60') ?>" class="admin-form-control" required>
          <div style="font-size:0.75rem; color:var(--admin-text-muted); margin-top:0.25rem;">Minutes before re-showing to the same visitor (0 = every session)</div>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <div class="admin-form-group">
          <label class="admin-form-label">Popup Heading</label>
          <input type="text" name="popup_heading" value="<?= e($settings['popup_heading'] ?? "Let's Create Something Extraordinary") ?>" class="admin-form-control" required>
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Popup Supporting Text</label>
          <input type="text" name="popup_description" value="<?= e($settings['popup_description'] ?? "Tell us about your photography requirements. Our team will get in touch with you.") ?>" class="admin-form-control" required>
        </div>
      </div>
    </div>
  </div>

  <!-- Floating Contact Buttons (WhatsApp & Call Now) Configuration -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Floating Contact Buttons (WhatsApp &amp; Call Now)</div>
      <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Configures the persistent floating contact buttons displayed across all public website pages.</div>
    </div>
    <div class="admin-card-body">
      <!-- WhatsApp Configuration -->
      <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--admin-border);">
        <h4 style="margin-bottom: 0.75rem; color: #16A34A; display: flex; align-items: center; gap: 0.5rem;">
          <span>🟢</span> WhatsApp Click-to-Chat Settings
        </h4>
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
          <div class="admin-form-group">
            <label class="admin-form-label">WhatsApp Button Status</label>
            <select name="enable_whatsapp_button" class="admin-form-control">
              <option value="1" <?= (($settings['enable_whatsapp_button'] ?? '1') === '1') ? 'selected' : '' ?>>Enabled (Show on all public pages)</option>
              <option value="0" <?= (($settings['enable_whatsapp_button'] ?? '1') === '0') ? 'selected' : '' ?>>Disabled (Hide button)</option>
            </select>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label">WhatsApp Business Number (with country code)</label>
            <input type="text" name="whatsapp_number" value="<?= e($settings['whatsapp_number'] ?? '+91 98765 43210') ?>" class="admin-form-control" placeholder="+91 98765 43210">
            <div style="font-size:0.75rem; color:var(--admin-text-muted); margin-top:0.25rem;">Used for official click-to-chat URL: <code>https://wa.me/&lt;number&gt;</code>. Non-digit characters will be stripped automatically.</div>
          </div>
        </div>

        <div class="admin-form-group" style="margin-top: 0.75rem;">
          <label class="admin-form-label">Default WhatsApp Prefilled Message</label>
          <input type="text" name="whatsapp_default_message" value="<?= e($settings['whatsapp_default_message'] ?? 'Hello Ecomshoot, I would like to discuss my product photography requirements.') ?>" class="admin-form-control">
          <div style="font-size:0.75rem; color:var(--admin-text-muted); margin-top:0.25rem;">Automatically populated in visitor's WhatsApp composer when clicking the button.</div>
        </div>
      </div>

      <!-- Call Now Configuration -->
      <div>
        <h4 style="margin-bottom: 0.75rem; color: #0284C7; display: flex; align-items: center; gap: 0.5rem;">
          <span>📞</span> Call Now Floating Button Settings
        </h4>
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
          <div class="admin-form-group">
            <label class="admin-form-label">Call Now Button Status</label>
            <select name="enable_call_button" class="admin-form-control">
              <option value="1" <?= (($settings['enable_call_button'] ?? '1') === '1') ? 'selected' : '' ?>>Enabled (Show on all public pages)</option>
              <option value="0" <?= (($settings['enable_call_button'] ?? '1') === '0') ? 'selected' : '' ?>>Disabled (Hide button)</option>
            </select>
          </div>

          <div class="admin-form-group">
            <label class="admin-form-label">Direct Studio Phone Number</label>
            <input type="text" name="call_number" value="<?= e($settings['call_number'] ?? $settings['site_phone'] ?? '+91 98765 43210') ?>" class="admin-form-control" placeholder="+91 98765 43210">
            <div style="font-size:0.75rem; color:var(--admin-text-muted); margin-top:0.25rem;">Directly triggers phone dialer on mobile devices via <code>tel:</code> link.</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Social Profiles -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">Social Media Profiles</div>
      <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Icons only appear on the public header and footer if a valid URL is provided.</div>
    </div>
    <div class="admin-card-body">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <div class="admin-form-group">
          <label class="admin-form-label">Instagram Profile URL</label>
          <input type="url" name="social_instagram" value="<?= e($settings['social_instagram'] ?? '') ?>" class="admin-form-control" placeholder="https://instagram.com/ecomshoot">
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Facebook Page URL</label>
          <input type="url" name="social_facebook" value="<?= e($settings['social_facebook'] ?? '') ?>" class="admin-form-control" placeholder="https://facebook.com/ecomshoot">
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">LinkedIn Company URL</label>
          <input type="url" name="social_linkedin" value="<?= e($settings['social_linkedin'] ?? '') ?>" class="admin-form-control" placeholder="https://linkedin.com/company/ecomshoot">
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">YouTube URL</label>
          <input type="url" name="social_youtube" value="<?= e($settings['social_youtube'] ?? '') ?>" class="admin-form-control" placeholder="https://youtube.com/@ecomshoot">
        </div>

        <div class="admin-form-group">
          <label class="admin-form-label">Pinterest URL</label>
          <input type="url" name="social_pinterest" value="<?= e($settings['social_pinterest'] ?? '') ?>" class="admin-form-control" placeholder="https://pinterest.com/ecomshoot">
        </div>
      </div>
    </div>
  </div>

  <!-- LLMs.txt Summary -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="admin-card-title">LLMs.txt Company Summary Engine</div>
      <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Exported into llms.txt for AI agent search and knowledge scrapers.</div>
    </div>
    <div class="admin-card-body">
      <div class="admin-form-group">
        <label class="admin-form-label">Company Summary for LLM Indexers</label>
        <textarea name="llms_company_summary" class="admin-form-control" style="min-height: 110px;"><?= e($settings['llms_company_summary'] ?? '') ?></textarea>
      </div>

      <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn-admin btn-admin-primary" style="padding: 0.75rem 1.75rem; font-size: 1rem;">
          Save All Settings &amp; Sync SEO Files
        </button>
      </div>
    </div>
  </div>

</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

