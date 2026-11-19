<?php
/**
 * ECOMSHOOT - Global Site Footer Component
 * Premium E-Commerce Product Photography Agency
 */
?>

  <?php if (empty($hidePreFooterCTA)): ?>
  <!-- Pre-Footer Editorial Conversion Section -->
  <section class="section section-cta">
    <div class="container">
      <div class="cta-banner-editorial">
        <div class="cta-banner-badge">
          <span class="pulse-dot-terracotta"></span>
          <span><?= e($footerCtaCopy['badge'] ?? 'Get Started') ?></span>
        </div>
        <?php $ctaHeading = $footerCtaCopy['heading'] ?? 'Ready to upgrade your product catalogue?'; ?>
        <?php if ($ctaHeading !== ''): ?>
          <h2 class="cta-banner-title"><?= e($ctaHeading) ?></h2>
        <?php endif; ?>
        <p class="cta-banner-desc">
          <?= e($footerCtaCopy['description'] ?? 'Whether you are a new D2C brand or established seller, our product Photography Agency in Delhi can help. Simply send us your product list and we will get back to you with a plan and quote.') ?>
        </p>
        <?php $ctaPrimary = $footerCtaCopy['primary_cta'] ?? 'Book Your Studio Shoot'; $ctaSecondary = $footerCtaCopy['secondary_cta'] ?? 'View All Services'; ?>
        <?php if ($ctaPrimary !== '' || $ctaSecondary !== ''): ?>
          <div class="cta-banner-actions">
            <?php if ($ctaPrimary !== ''): ?><a href="<?= url('contact.php') ?>" class="btn btn-lg btn-primary"><?= e($ctaPrimary) ?></a><?php endif; ?>
            <?php if ($ctaSecondary !== ''): ?><a href="<?= url('services.php') ?>" class="btn btn-lg btn-outline-white cta-secondary-btn"><?= e($ctaSecondary) ?></a><?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Main Website Footer -->
  <footer class="site-footer">
    <div class="container">
      
      <!-- Top Footer Grid -->
      <div class="footer-grid">
        
        <!-- Col 1: Brand Narrative & Studio Location -->
        <div class="footer-brand-col">
          <a href="<?= url('') ?>" class="footer-logo" aria-label="Ecomshoot Homepage">
            <img src="<?= asset_url('assets/images/logo-light.svg') ?>" alt="Ecomshoot Logo" width="168" height="42" class="footer-logo-img">
          </a>
          <p class="footer-bio">
            <?= e(get_setting('llms_company_summary', 'Ecomshoot is a premier commercial product photography studio and digital production house. We engineer high-performance visual assets for ambitious D2C brands, Shopify Plus merchants, and global enterprise retailers.')) ?>
          </p>
          <div class="footer-contact-list">
            <?php if (!empty(get_setting('site_address', ''))): ?>
              <div class="footer-contact-item">
                <span class="contact-icon">📍</span>
                <span><?= e(get_setting('site_address')) ?></span>
              </div>
            <?php endif; ?>
            <div class="footer-contact-item">
              <span class="contact-icon">✉️</span>
              <a href="mailto:<?= e(get_setting('site_email', 'contact@ecommshoot.in')) ?>"><?= e(get_setting('site_email', 'contact@ecommshoot.in')) ?></a>
            </div>
            <?php if (!empty(get_setting('site_phone', ''))): ?>
              <div class="footer-contact-item">
                <span class="contact-icon">📞</span>
                <a href="tel:<?= e(preg_replace('/[^0-9\+]/', '', get_setting('site_phone'))) ?>"><?= e(get_setting('site_phone')) ?></a>
              </div>
            <?php endif; ?>
            <?php if (!empty(get_setting('site_hours', ''))): ?>
              <div class="footer-contact-item">
                <span class="contact-icon">⏰</span>
                <span><?= e(get_setting('site_hours')) ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Col 2: Studio Navigation Links -->
        <div class="footer-nav-col">
          <h4 class="footer-col-title">Studio Navigation</h4>
          <ul class="footer-links">
            <li><a href="<?= url('') ?>">Homepage</a></li>
            <li><a href="<?= url('about.php') ?>">About Our Studio</a></li>
            <li><a href="<?= url('services.php') ?>">All Photography Services</a></li>
            <li><a href="<?= url('blog.php') ?>">Blogs &amp; Guides</a></li>
            <li><a href="<?= url('contact.php') ?>">Book a Shoot / Request Estimate</a></li>
          </ul>
        </div>

        <!-- Col 3: Dedicated Services Directory -->
        <div class="footer-services-col">
          <h4 class="footer-col-title">Photography Disciplines</h4>
          <ul class="footer-links">
            <li><a href="<?= service_url('product-photography') ?>">Product Photography</a></li>
            <li><a href="<?= service_url('ecommerce-product-photography') ?>">E-Commerce Photography</a></li>
            <li><a href="<?= service_url('lifestyle-product-photography') ?>">Lifestyle Photography</a></li>
            <li><a href="<?= service_url('fashion-product-photography') ?>">Fashion &amp; Apparel</a></li>
            <li><a href="<?= service_url('creative-product-photography') ?>">Creative &amp; Advertising</a></li>
            <li><a href="<?= service_url('jewellery-product-photography') ?>">Jewellery &amp; Luxury</a></li>
            <li><a href="<?= service_url('product-photo-editing-retouching') ?>">Editing &amp; Retouching</a></li>
            <li><a href="<?= service_url('white-background-product-photography') ?>">White Background</a></li>
          </ul>
        </div>

      </div>

      <!-- Bottom Bar -->
      <div class="footer-bottom-bar">
        <div class="footer-copyright">
          &copy; <?= date('Y') ?> <strong>Ecomshoot Creative Production Inc.</strong> All rights reserved. Precision Commercial Photography &amp; Art Direction.
        </div>
        <div class="footer-social-links">
          <?php if (!empty(get_setting('social_instagram'))): ?>
            <a href="<?= e(get_setting('social_instagram')) ?>" target="_blank" rel="noopener" aria-label="Instagram" class="social-link">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
          <?php endif; ?>
          <?php if (!empty(get_setting('social_facebook'))): ?>
            <a href="<?= e(get_setting('social_facebook')) ?>" target="_blank" rel="noopener" aria-label="Facebook" class="social-link">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
          <?php endif; ?>
          <?php if (!empty(get_setting('social_linkedin'))): ?>
            <a href="<?= e(get_setting('social_linkedin')) ?>" target="_blank" rel="noopener" aria-label="LinkedIn" class="social-link">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
            </a>
          <?php endif; ?>
          <?php if (!empty(get_setting('social_youtube'))): ?>
            <a href="<?= e(get_setting('social_youtube')) ?>" target="_blank" rel="noopener" aria-label="YouTube" class="social-link">
              <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </a>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </footer>

  <!-- Lead Generation Automatic Timed Popup Modal Component -->
  <?php require_once INCLUDES_PATH . '/lead-popup.php'; ?>

  <!-- Persistent Floating Contact Buttons (WhatsApp & Call Now) -->
  <?php require_once INCLUDES_PATH . '/floating-contact.php'; ?>

  <!-- Master JavaScript Engine -->
  <script src="<?= asset_url('assets/js/main.js') ?>" defer></script>
</body>
</html>
