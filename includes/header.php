<?php
/**
 * ECOMSHOOT - Global Site Header & Navigation Component
 * Premium E-Commerce Product Photography Agency
 */

if (!headers_sent()) {
  header('Content-Type: text/html; charset=UTF-8');
}

if (!defined('ROOT_PATH')) {
  require_once __DIR__ . '/../config/config.php';
}

$currentPage = $pageIdentifier ?? 'home';
$customSeo = $customSeo ?? [];
$customSchemas = $customSchemas ?? [];

// Fetch social links and contact info
$siteEmail = get_setting('site_email', 'contact@ecommshoot.in');
$sitePhone = get_setting('site_phone', '');
$socialIg = get_setting('social_instagram', '');
$socialFb = get_setting('social_facebook', '');
$socialLi = get_setting('social_linkedin', '');
$socialYt = get_setting('social_youtube', '');

// Fetch published services for the dropdown (excluding discontinued services)
try {
  $db = Database::getInstance()->getConnection();
  $navServices = $db->query("SELECT title, slug, file_name, badge, short_description FROM services WHERE status = 'published' AND slug != 'professional-product-photography' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $navServices = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <!-- Automated SEO & Social Metadata Engine -->
  <?= SEO::render($currentPage, $customSeo) ?>

  <!-- Independent JSON-LD Schema Architecture -->
  <?= SchemaManager::renderHeadSchemas($currentPage, $customSchemas) ?>

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="<?= asset_url('assets/images/logo.svg') ?>">

  <!-- Unified Site Font: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600;1,700&display=swap" rel="stylesheet">

  <!-- Master Design System Stylesheet -->
  <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('public/css/studio-luxury.css') ?>">
</head>

<body>

  <!-- Top Dispatch Bar -->
  <div class="top-dispatch-bar">
    <div class="container dispatch-container">
      <div class="dispatch-left">
        <span class="dispatch-social-label">Follow Us:</span>
        <div class="dispatch-social-links">
          <?php if (!empty($socialIg)): ?>
            <a href="<?= e($socialIg) ?>" target="_blank" rel="noopener noreferrer" class="dispatch-social-link" title="Instagram" aria-label="Ecomshoot on Instagram">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
          <?php endif; ?>
          <?php if (!empty($socialFb)): ?>
            <a href="<?= e($socialFb) ?>" target="_blank" rel="noopener noreferrer" class="dispatch-social-link" title="Facebook" aria-label="Ecomshoot on Facebook">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
          <?php endif; ?>
          <?php if (!empty($socialLi)): ?>
            <a href="<?= e($socialLi) ?>" target="_blank" rel="noopener noreferrer" class="dispatch-social-link" title="LinkedIn" aria-label="Ecomshoot on LinkedIn">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
            </a>
          <?php endif; ?>
          <?php if (!empty($socialYt)): ?>
            <a href="<?= e($socialYt) ?>" target="_blank" rel="noopener noreferrer" class="dispatch-social-link" title="YouTube" aria-label="Ecomshoot on YouTube">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="dispatch-right">
        <a href="mailto:<?= e($siteEmail) ?>" class="dispatch-email">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
          </svg>
          <?= e($siteEmail) ?>
        </a>
        <?php if (!empty($sitePhone)): ?>
          <a href="tel:<?= e(preg_replace('/[^0-9\+]/', '', $sitePhone)) ?>" class="dispatch-phone">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
              <path d="M20 15.5c-1.2 0-2.4-.2-3.6-.6-.3-.1-.7 0-1 .2l-2.2 2.2c-2.8-1.4-5.1-3.8-6.6-6.6l2.2-2.2c.3-.3.4-.7.2-1-.4-1.1-.6-2.3-.6-3.5 0-.6-.4-1-1-1H4c-.6 0-1 .4-1 1 0 9.4 7.6 17 17 17 .6 0 1-.4 1-1v-3.4c0-.6-.4-1-1-1z" />
            </svg>
            <?= e($sitePhone) ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Primary Sticky Header Navigation -->
  <header class="site-header" id="siteHeader">
    <div class="container nav-container">

      <!-- Brand Logo (4:1 Aspect Ratio Preserved, Never Clipped) -->
      <a href="<?= url('') ?>" class="brand-logo" aria-label="Ecomshoot Homepage">
        <img src="<?= asset_url('assets/images/logo-light.svg') ?>" alt="Ecomshoot Commercial Photography Agency Logo"
          width="168" height="42">
      </a>

      <!-- Desktop & Mobile Main Nav -->
      <nav class="main-nav" id="mainNav" aria-label="Main Navigation">
        <!-- Mobile Drawer Header -->
        <div class="mobile-drawer-header">
          <a href="<?= url('') ?>" class="mobile-drawer-logo" aria-label="Ecomshoot Homepage">
            <img src="<?= asset_url('assets/images/logo-light.svg') ?>" alt="Ecomshoot Logo" width="140" height="35" class="drawer-logo-img">
          </a>
          <button type="button" class="mobile-drawer-close" id="mobileDrawerClose" aria-label="Close navigation menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <ul class="nav-list">
          <li class="nav-item">
            <a href="<?= url('') ?>" class="nav-link <?= ($currentPage === 'home') ? 'active' : '' ?>">Home</a>
          </li>

          <li class="nav-item">
            <a href="<?= url('about.php') ?>" class="nav-link <?= ($currentPage === 'about') ? 'active' : '' ?>">About
              Us</a>
          </li>

          <!-- Services Dropdown (Clicking 'Services' navigates to /services; clicking chevron opens submenu) -->
          <li class="nav-item has-dropdown" id="servicesDropdownParent">
            <div class="nav-link-dropdown-group">
              <a href="<?= url('services.php') ?>"
                class="nav-link nav-link-services <?= (str_contains($currentPage, 'service') || str_contains($currentPage, 'photography') || str_contains($currentPage, 'retouching')) ? 'active' : '' ?>"
                id="servicesNavLink">
                <span>Services</span>
              </a>
              <button type="button"
                class="nav-dropdown-toggle-btn"
                id="servicesDropdownToggle"
                role="button"
                aria-label="Toggle Services submenu"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="servicesDropdownMenu">
                <svg class="nav-chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd" />
                </svg>
              </button>
            </div>

            <!-- Compact Mega Dropdown Menu (Fixed/Dynamic Desktop Positioning, Accordion on Mobile) -->
            <div class="services-dropdown" id="servicesDropdownMenu" role="menu"
              aria-labelledby="servicesDropdownToggle">

              <!-- Dropdown Header Bar -->
              <div class="dropdown-header-bar">
                <div class="dropdown-header-title">
                  <span class="dropdown-header-tag">PHOTOGRAPHY DISCIPLINES</span>
                </div>
                <a href="<?= url('services.php') ?>" class="dropdown-view-all">
                  View All Services &rarr;
                </a>
              </div>

              <!-- Two-Column Dropdown Grid of All 9 Services with Visual Thumbnails -->
              <div class="dropdown-grid-two-col">
                <?php foreach ($navServices as $svc): ?>
                  <a href="<?= service_url($svc['slug']) ?>" class="dropdown-item-compact" role="menuitem">
                    <span class="item-thumb-wrapper">
                      <img src="<?= asset_url('assets/images/services/' . $svc['slug'] . '.jpg') ?>"
                        alt="<?= e($svc['title']) ?> photography discipline thumbnail" width="36" height="36"
                        loading="lazy" class="item-thumb-img">
                    </span>
                    <div class="item-text-wrap">
                      <span class="item-title"><?= e($svc['title']) ?></span>
                      <?php if (!empty($svc['badge'])): ?>
                        <span class="item-micro-badge"><?= e($svc['badge']) ?></span>
                      <?php endif; ?>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>

              <!-- Compact Dropdown Bottom Bar -->
              <div class="dropdown-footer-bar">
                <span>Looking for turnkey catalog rates?</span>
                <a href="<?= url('contact.php') ?>" class="dropdown-footer-cta">Get Rate Card &rarr;</a>
              </div>
            </div>
          </li>

          <li class="nav-item">
            <a href="<?= url('blog.php') ?>"
              class="nav-link <?= ($currentPage === 'blog' || $currentPage === 'blog-detail') ? 'active' : '' ?>">Blogs</a>
          </li>

          <li class="nav-item">
            <a href="<?= url('contact.php') ?>"
              class="nav-link <?= ($currentPage === 'contact') ? 'active' : '' ?>">Contact</a>
          </li>
        </ul>
      </nav>
      <div class="mobile-nav-backdrop" aria-hidden="true"></div>

      <!-- Header Action & Mobile Menu Toggle -->
      <div class="header-actions">
        <a href="<?= url('contact.php') ?>" class="btn btn-primary nav-cta">Book a Shoot</a>
        <button class="mobile-toggle" id="mobileMenuToggle" aria-label="Toggle navigation menu" aria-controls="mainNav" aria-expanded="false">
          <span class="hamburger-bar"></span>
          <span class="hamburger-bar"></span>
          <span class="hamburger-bar"></span>
        </button>
      </div>

    </div>
  </header>
