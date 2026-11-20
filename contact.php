<?php
/**
 * ECOMSHOOT - Contact & Production Estimate Request
 */

$pageIdentifier = 'contact';
require_once __DIR__ . '/config/config.php';
require_once INCLUDES_PATH . '/breadcrumbs.php';

$errors = [];
$formData = [
    'full_name' => '',
    'mobile' => '',
    'email' => '',
    'message' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Spam honeypot verification
    if (!empty($_POST['website_hp'])) {
        // Silent bot discard
        set_flash('success', 'Thank you for contacting Ecomshoot! Your inquiry has been received successfully.');
        redirect('contact.php');
    }

    // 2. CSRF verification
    CSRF::check();

    // 3. Collect and sanitize exactly 4 required fields
    $formData['full_name'] = trim($_POST['full_name'] ?? '');
    $formData['mobile'] = trim($_POST['mobile'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['message'] = trim($_POST['message'] ?? '');

    // 4. Backend Validation
    if (empty($formData['full_name']) || mb_strlen($formData['full_name']) < 2) {
        $errors[] = 'Please enter your full name (minimum 2 characters).';
    }

    if (empty($formData['mobile']) || mb_strlen($formData['mobile']) < 7) {
        $errors[] = 'Please provide a valid contact number / mobile.';
    }

    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if (empty($formData['message']) || mb_strlen($formData['message']) < 5) {
        $errors[] = 'Please enter your message (minimum 5 characters).';
    }

    // 5. If valid, insert into database
    if (empty($errors)) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                "INSERT INTO contact_submissions 
                 (full_name, email, phone, company_name, service_required, message, ip_address, user_agent, is_read) 
                 VALUES (:name, :email, :phone, :company, :service, :message, :ip, :ua, 0)"
            );
            $stmt->execute([
                'name' => $formData['full_name'],
                'email' => $formData['email'],
                'phone' => $formData['mobile'],
                'company' => '',
                'service' => 'General Inquiry',
                'message' => $formData['message'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255)
            ]);

            set_flash('success', 'Thank you for contacting Ecomshoot! Your inquiry has been received successfully. Our team will get in touch with you.');
            redirect('contact.php');
        } catch (Exception $e) {
            error_log('Contact Submission Error: ' . $e->getMessage());
            $errors[] = 'A server error occurred while registering your submission. Please write to ' . e(get_setting('site_email', 'contact@ecommshoot.in')) . '.';
        }
    }
}

$siteEmail = get_setting('site_email', 'contact@ecommshoot.in');
$sitePhone = get_setting('site_phone', '');
$siteAddress = get_setting('site_address', '');
$siteHours = get_setting('site_hours', 'Monday – Friday: 9:00 AM – 6:30 PM IST');

require_once INCLUDES_PATH . '/header.php';
$flash = get_flash();
?>

<!-- BreadcrumbList structured data; no visible breadcrumb navigation. -->
<?= render_breadcrumbs([['title' => 'Contact Studio', 'url' => null]]) ?>

<!-- Page Header Banner -->
<section class="page-header-banner">
  <div class="container">
    <div class="page-header-content">
      <span class="badge badge-coral">Get in Touch</span>
      <h1 class="page-header-title">Connect with Our <span class="font-editorial shimmer-coral">Creative Production Team</span></h1>
      <p class="page-header-sub">
        Tell us about your photography requirements. Our team will get in touch with you promptly to discuss your shoot.
      </p>
    </div>
  </div>
</section>

<!-- Contact Form & Studio Info -->
<section class="section">
  <div class="container">
    <div class="contact-grid">
      
      <!-- Studio Details Panel -->
      <div class="contact-info-panel luxury-card studio-reveal">
        <div>
          <span class="badge badge-sand" style="margin-bottom: 1.5rem;">Creative Production</span>
          <h3>Studio Information &amp; Production Inquiries</h3>
          <p>
            Connect with Ecomshoot for professional commercial product photography, e-commerce catalog shooting, lifestyle, and high-end creative visual production.
          </p>

          <ul class="contact-details-list">
            <li class="contact-detail-item">
              <div class="contact-detail-icon">✉️</div>
              <div>
                <div class="contact-detail-title">Production Email</div>
                <div class="contact-detail-val">
                  <a href="mailto:<?= e($siteEmail) ?>" style="color: #FFFFFF; font-weight: 600;">
                    <?= e($siteEmail) ?>
                  </a>
                </div>
              </div>
            </li>

            <?php if (!empty($sitePhone)): ?>
              <li class="contact-detail-item">
                <div class="contact-detail-icon">📞</div>
                <div>
                  <div class="contact-detail-title">Direct Studio Line</div>
                  <div class="contact-detail-val">
                    <a href="tel:<?= e(preg_replace('/[^0-9\+]/', '', $sitePhone)) ?>" style="color: #FFFFFF; font-weight: 600;">
                      <?= e($sitePhone) ?>
                    </a>
                  </div>
                </div>
              </li>
            <?php endif; ?>

            <?php if (!empty($siteAddress)): ?>
              <li class="contact-detail-item">
                <div class="contact-detail-icon">📍</div>
                <div>
                  <div class="contact-detail-title">Studio Location</div>
                  <div class="contact-detail-val"><?= e($siteAddress) ?></div>
                </div>
              </li>
            <?php endif; ?>

            <?php if (!empty($siteHours)): ?>
              <li class="contact-detail-item">
                <div class="contact-detail-icon">⏰</div>
                <div>
                  <div class="contact-detail-title">Operating Hours</div>
                  <div class="contact-detail-val"><?= e($siteHours) ?></div>
                </div>
              </li>
            <?php endif; ?>
          </ul>
        </div>

        <div style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.15); font-size: 0.88rem; color: rgba(255, 255, 255, 0.85); line-height: 1.5;">
          <strong>Quick Response Guarantee:</strong><br>
          Every inquiry is reviewed by our lead production directors with rate estimates within 24 business hours.
        </div>
      </div>

      <!-- Contact Form Card -->
      <div class="contact-form-card luxury-card studio-reveal">
        <h2 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Send Us a Message</h2>
        <p style="margin-bottom: 2rem; color: var(--color-text-muted);">
          Fill out the 4 details below and our team will get in touch with you.
        </p>

        <?php if ($flash): ?>
          <div class="alert alert-<?= ($flash['type'] === 'error') ? 'error' : 'success' ?>">
            <?= e($flash['message']) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 1.25rem;">
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="<?= url('contact.php') ?>" method="POST" id="contactForm" novalidate>
          <?= CSRF::inputField() ?>
          
          <!-- Bot Honeypot -->
          <div style="display: none;" aria-hidden="true">
            <input type="text" name="website_hp" tabindex="-1" autocomplete="off">
          </div>

          <div class="form-group">
            <label for="full_name" class="form-label">Full Name <span style="color: var(--color-coral);">*</span></label>
            <input type="text" id="full_name" name="full_name" value="<?= e($formData['full_name']) ?>" class="form-control" placeholder="e.g. Sarah Jenkins" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="mobile" class="form-label">Contact Number / Mobile <span style="color: var(--color-coral);">*</span></label>
              <input type="tel" id="mobile" name="mobile" value="<?= e($formData['mobile']) ?>" class="form-control" placeholder="e.g. +91 98765 43210" required>
            </div>

            <div class="form-group">
              <label for="email" class="form-label">Email Address <span style="color: var(--color-coral);">*</span></label>
              <input type="email" id="email" name="email" value="<?= e($formData['email']) ?>" class="form-control" placeholder="e.g. sarah@company.com" required>
            </div>
          </div>

          <div class="form-group">
            <label for="message" class="form-label">Message <span style="color: var(--color-coral);">*</span></label>
            <textarea id="message" name="message" class="form-control" rows="5" placeholder="Tell us about your photography requirements..." required><?= e($formData['message']) ?></textarea>
          </div>

          <button type="submit" class="btn btn-lg btn-primary btn-studio-primary" style="width: 100%;">
            Submit Inquiry &rarr;
          </button>
        </form>

      </div>

    </div>
  </div>
</section>

      </div>

    </div>
  </div>
</section>

<?php 
$hidePreFooterCTA = true; // Avoid double CTA on contact page
require_once INCLUDES_PATH . '/footer.php'; 
?>

