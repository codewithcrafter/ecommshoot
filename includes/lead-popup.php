<?php
/**
 * ECOMSHOOT - Automatic Timed Popup Lead Generation Modal
 * Displayed across all public pages after configurable delay
 */
$popupEnabled = get_setting('popup_enabled', '1') === '1';
$popupDelay = (int) get_setting('popup_delay', '5');
$popupCooldown = (int) get_setting('popup_cooldown', '24'); // in hours
$popupHeading = get_setting('popup_heading', "Let's Create Something Extraordinary");
$popupDescription = get_setting('popup_description', "Tell us about your photography requirements. Our team will get in touch with you.");

if ($popupEnabled):
?>
<!-- Automatic Popup Lead Generation Modal -->
<div class="lead-popup-overlay" id="leadPopupOverlay" aria-hidden="true" style="display: none;">
  <div class="lead-popup-dialog luxury-card" id="leadPopupDialog" role="dialog" aria-modal="true" aria-labelledby="leadPopupTitle">
    
    <!-- Close Button -->
    <button type="button" class="lead-popup-close" id="leadPopupClose" aria-label="Close Inquiry Form">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>
    <span class="studio-viewfinder studio-viewfinder-tl" aria-hidden="true"></span>
    <span class="studio-viewfinder studio-viewfinder-br" aria-hidden="true"></span>
    
    <div class="lead-popup-content">
      <aside class="lead-popup-header studio-intake-panel">
        <div class="lead-popup-camera-icon" aria-hidden="true">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
            <circle cx="12" cy="13" r="4"></circle>
          </svg>
        </div>
        <span class="lead-popup-tag">COMMERCIAL STUDIO INTAKE</span>
        <h3 class="lead-popup-title" id="leadPopupTitle"><?php
          $rawHeading = e($popupHeading);
          if (stripos($rawHeading, 'Extraordinary') !== false) {
            echo str_ireplace('Extraordinary', '<span class="lead-highlight-coral font-editorial shimmer-coral">Extraordinary</span>', $rawHeading);
          } else {
            echo $rawHeading;
          }
        ?></h3>
        <p class="lead-popup-desc"><?= e($popupDescription) ?></p>
        <div class="studio-status"><span></span> Studio team online</div>
      </aside>

      <section class="studio-intake-form-panel">
      <div class="lead-popup-success" id="leadPopupSuccess" style="display: none;">
        <div class="lead-success-icon">✓</div>
        <h4>Inquiry Received</h4>
        <p>Thank you for contacting Ecomshoot! Your inquiry has been received successfully.</p>
      </div>

      <!-- Form Error Container -->
      <div class="lead-popup-error" id="leadPopupError" style="display: none;"></div>

      <!-- Lead Capture Form -->
      <form id="leadPopupForm" action="<?= url('submit-lead.php') ?>" method="POST" novalidate>
        <?= CSRF::inputField() ?>
        
        <!-- Honeypot field for anti-bot protection -->
        <div class="lead-form-honeypot" aria-hidden="true" hidden>
          <input type="text" name="website_hp" tabindex="-1" autocomplete="off">
        </div>

        <!-- Automatic Page Tracking Context -->
        <input type="hidden" name="is_ajax" value="1">
        <input type="hidden" name="lead_source" value="Timed Popup">
        <input type="hidden" name="source_page_url" id="popupSourceUrl" value="">
        <input type="hidden" name="source_page_title" id="popupSourceTitle" value="">
        <input type="hidden" name="source_page_type" value="<?= e($currentPage ?? 'public_page') ?>">

        <div class="lead-form-grid">
          <div class="lead-form-row-2col">
            <div class="lead-form-group">
              <label for="popup_full_name" class="lead-form-label">Full Name <span class="required-star">*</span></label>
              <input type="text" id="popup_full_name" name="full_name" class="lead-form-control" placeholder="Your full name" required>
            </div>

            <div class="lead-form-group">
              <label for="popup_email" class="lead-form-label">Email Address <span class="required-star">*</span></label>
              <input type="email" id="popup_email" name="email" class="lead-form-control" placeholder="you@company.com" required>
            </div>
          </div>

          <div class="lead-form-group">
            <label for="popup_mobile" class="lead-form-label">Contact Number / Mobile <span class="required-star">*</span></label>
            <input type="tel" id="popup_mobile" name="mobile" class="lead-form-control" placeholder="+91 98765 43210" inputmode="tel" autocomplete="tel" pattern="[0-9+ ()-]{10,16}" title="Enter an Indian mobile number, for example +91 98765 43210" required>
          </div>

          <div class="lead-form-group">
            <label for="popup_message" class="lead-form-label">Message <span class="required-star">*</span></label>
            <textarea id="popup_message" name="message" class="lead-form-control lead-form-textarea" rows="2" placeholder="Tell us about your shoot requirements..." required></textarea>
          </div>
        </div>

        <div class="lead-form-actions">
          <button type="submit" class="btn btn-primary lead-submit-btn btn-studio-primary" id="leadSubmitBtn">
            <span>Submit Inquiry</span>
            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" /></svg>
          </button>
        </div>

        <div class="lead-form-privacy">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="lead-security-shield-icon"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
          <span>Strictly confidential. Our studio team responds within 24 business hours.</span>
        </div>
      </form>
      </section>

    </div>
  </div>
</div>

<script>
window.__ECOMSHOOT_POPUP_CONFIG__ = {
  enabled: <?= $popupEnabled ? 'true' : 'false' ?>,
  delayMs: <?= max(1, $popupDelay) * 1000 ?>,
  cooldownMs: <?= max(1, $popupCooldown) * 3600 * 1000 ?>
};
</script>
<?php endif; ?>

