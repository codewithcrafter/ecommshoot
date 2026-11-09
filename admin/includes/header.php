<?php
/**
 * ECOMSHOOT - Admin Panel Header Component
 */

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/config.php';
}

Auth::requireLogin();
$currentAdmin = Auth::user();
$activeNav = $activeNav ?? 'dashboard';

// Fetch unread inquiries and new leads count
try {
    $db = Database::getInstance()->getConnection();
    $unreadCount = (int) $db->query("SELECT count(*) FROM contact_submissions WHERE is_read = 0")->fetchColumn();
    $newLeadsCount = (int) $db->query("SELECT count(*) FROM leads WHERE status = 'new'")->fetchColumn();
} catch (Exception $e) {
    $unreadCount = 0;
    $newLeadsCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($adminTitle ?? 'Admin Dashboard') ?> | Ecomshoot Studio CMS</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="<?= asset_url('assets/css/admin.css') ?>">
</head>
<body class="admin-body">

  <!-- Sidebar Navigation -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar-header">
      <div class="admin-sidebar-brand">
        ECOM<span>SHOOT</span>
      </div>
      <span style="font-size: 0.7rem; background: #1E293B; padding: 2px 6px; border-radius: 4px; color: #94A3B8;">CMS</span>
    </div>

    <nav class="admin-sidebar-nav">
      <div class="admin-nav-group-title">Core Management</div>
      
      <a href="<?= url('admin/index.php') ?>" class="admin-nav-link <?= ($activeNav === 'dashboard') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
        Dashboard
      </a>

      <a href="<?= url('admin/pages.php') ?>" class="admin-nav-link <?= ($activeNav === 'pages') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
        Website Pages
      </a>

      <a href="<?= url('admin/services.php') ?>" class="admin-nav-link <?= ($activeNav === 'services') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
        Photography Services
      </a>

      <div class="admin-nav-group-title">Blog CMS</div>

      <a href="<?= url('admin/blogs.php') ?>" class="admin-nav-link <?= ($activeNav === 'blogs') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
        All Articles
      </a>

      <a href="<?= url('admin/categories.php') ?>" class="admin-nav-link <?= ($activeNav === 'categories') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
        Categories &amp; Tags
      </a>

      <a href="<?= url('admin/markdown.php') ?>" class="admin-nav-link <?= ($activeNav === 'markdown') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
        Markdown CMS
      </a>

      <div class="admin-nav-group-title">SEO &amp; Structure</div>

      <a href="<?= url('admin/seo.php') ?>" class="admin-nav-link <?= ($activeNav === 'seo') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        SEO Metadata
      </a>

      <a href="<?= url('admin/schemas.php') ?>" class="admin-nav-link <?= ($activeNav === 'schemas') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
        Schema Management
      </a>

      <a href="<?= url('admin/sitemap.php') ?>" class="admin-nav-link <?= ($activeNav === 'sitemap') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        Sitemap &amp; LLMs.txt
      </a>

      <div class="admin-nav-group-title">Assets &amp; Inquiries</div>

      <a href="<?= url('admin/media.php') ?>" class="admin-nav-link <?= ($activeNav === 'media') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        Media Library
      </a>

      <a href="<?= url('admin/leads.php') ?>" class="admin-nav-link <?= ($activeNav === 'leads') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        Popup Leads
        <?php if ($newLeadsCount > 0): ?>
          <span class="admin-badge" style="background: var(--admin-coral);"><?= $newLeadsCount ?></span>
        <?php endif; ?>
      </a>

      <a href="<?= url('admin/contacts.php') ?>" class="admin-nav-link <?= ($activeNav === 'contacts') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        Contact Inquiries
        <?php if ($unreadCount > 0): ?>
          <span class="admin-badge"><?= $unreadCount ?></span>
        <?php endif; ?>
      </a>

      <a href="<?= url('admin/settings.php') ?>" class="admin-nav-link <?= ($activeNav === 'settings') ? 'active' : '' ?>">
        <svg class="admin-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        Website Settings
      </a>
    </nav>

    <div class="admin-sidebar-footer">
      <div>
        <div style="font-weight: 700; color: #FFFFFF;"><?= e($currentAdmin['name']) ?></div>
        <div style="color: #64748B; font-size: 0.75rem;"><?= e(ucfirst($currentAdmin['role'])) ?></div>
      </div>
      <a href="<?= url('admin/logout.php') ?>" style="color: #EF4444; font-weight: 700;" title="Sign out">Exit</a>
    </div>
  </aside>

  <!-- Main Content Wrap -->
  <div class="admin-main">
    <header class="admin-topbar">
      <div class="admin-topbar-title"><?= e($adminTitle ?? 'Dashboard') ?></div>
      <div class="admin-topbar-actions">
        <a href="<?= url('') ?>" target="_blank" class="btn-admin btn-admin-outline" style="font-size: 0.8rem;">
          View Live Website &nearr;
        </a>
        <div class="admin-user-pill">
          <span style="display: inline-block; width: 8px; height: 8px; background: #10B981; border-radius: 50%;"></span>
          <span><?= e($currentAdmin['name']) ?></span>
        </div>
      </div>
    </header>

    <main class="admin-content">
      <?php $flash = get_flash(); if ($flash): ?>
        <div class="alert alert-<?= ($flash['type'] === 'error') ? 'error' : 'success' ?>" style="margin-bottom: 1.5rem;">
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>

