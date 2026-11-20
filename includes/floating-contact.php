<?php
/**
 * ECOMSHOOT - Floating Contact Buttons (WhatsApp & Call Now)
 * Persistent floating contact actions for all public pages.
 */

$enableWhatsApp = get_setting('enable_whatsapp_button', '1') === '1';
$enableCall = get_setting('enable_call_button', '1') === '1';

$rawWaNumber = trim(get_setting('whatsapp_number', ''));
$rawCallNumber = trim(get_setting('call_number', '') ?: get_setting('site_phone', ''));

$waDigits = preg_replace('/[^0-9]/', '', $rawWaNumber);
$callDigits = preg_replace('/[^0-9\+]/', '', $rawCallNumber);

$defaultWaMsg = get_setting('whatsapp_default_message', 'Hello Ecomshoot, I would like to discuss my product photography requirements.');
$waUrl = !empty($waDigits) ? 'https://wa.me/' . $waDigits . '?text=' . rawurlencode($defaultWaMsg) : '';
$callUrl = !empty($callDigits) ? 'tel:' . $callDigits : '';

// Hide entire container if both buttons are disabled or missing numbers
if ((!$enableWhatsApp || empty($waUrl)) && (!$enableCall || empty($callUrl))) {
    return;
}
?>

<!-- Floating Quick Contact Widget -->
<aside class="floating-contact-dock" aria-label="Quick Studio Contact Options">
  <?php if ($enableCall && !empty($callUrl)): ?>
    <a href="<?= e($callUrl) ?>" 
       class="floating-contact-btn floating-btn-call" 
       aria-label="Direct Studio Phone: <?= e($rawCallNumber) ?>" 
       title="Call Direct: <?= e($rawCallNumber) ?>">
      <span class="floating-tooltip">Call Now: <?= e($rawCallNumber) ?></span>
      <svg class="floating-icon" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
        <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 0 0-1.01.24l-2.2 2.2a15.05 15.05 0 0 1-6.59-6.59l2.2-2.21a.96.96 0 0 0 .25-1A11.36 11.36 0 0 1 8.57 3.9c0-.55-.45-1-1-1H3.99c-.55 0-1 .45-1 1 0 9.39 7.63 17.02 17.02 17.02.55 0 1-.45 1-1v-3.54c0-.55-.45-1-1-1z"/>
      </svg>
      <span class="floating-btn-label">CALL NOW</span>
    </a>
  <?php endif; ?>

  <?php if ($enableWhatsApp && !empty($waUrl)): ?>
    <a href="<?= e($waUrl) ?>" 
       target="_blank" 
       rel="noopener noreferrer" 
       class="floating-contact-btn floating-btn-whatsapp" 
       aria-label="Chat with Ecomshoot on WhatsApp" 
       title="Chat on WhatsApp">
      <span class="floating-tooltip">Chat with us on WhatsApp</span>
      <svg class="floating-icon" viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true">
        <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm.01 1.67c2.2 0 4.26.86 5.82 2.41a8.174 8.174 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.44 0-2.86-.38-4.11-1.1l-.29-.17-3.06.8 1.03-2.98-.19-.31a8.21 8.21 0 0 1-1.26-4.48c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.58c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.25-.75-.67-1.26-1.5-1.41-1.75-.14-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.44.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.12-.23-.19-.48-.31z"/>
      </svg>
      <span class="floating-btn-label">WHATSAPP</span>
    </a>
  <?php endif; ?>
</aside>

