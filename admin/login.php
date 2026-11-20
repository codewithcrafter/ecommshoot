<?php
/**
 * ECOMSHOOT - Admin Authentication Login
 */

require_once __DIR__ . '/../config/config.php';

// Redirect if already logged in
if (Auth::check()) {
    redirect('admin/index.php');
}

$error = '';
$emailInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();

    $emailInput = trim($_POST['email'] ?? '');
    $passwordInput = $_POST['password'] ?? '';

    if (empty($emailInput) || empty($passwordInput)) {
        $error = 'Please provide both your registered email and password.';
    } else {
        if (Auth::login($emailInput, $passwordInput)) {
            set_flash('success', 'Welcome back to the Ecomshoot Studio CMS.');
            redirect('admin/index.php');
        } else {
            $error = 'Invalid authentication credentials. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Login | Ecomshoot CMS</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="<?= asset_url('assets/css/admin.css') ?>">
</head>
<body class="admin-login-body">

  <div class="admin-login-card">
    
    <div class="admin-login-brand">
      <a href="<?= url('') ?>" class="admin-login-logo">
        <img src="<?= asset_url('assets/images/logo.svg') ?>" alt="Ecomshoot" width="168" height="44">
      </a>
      <h1 class="admin-login-title">Studio Staff Portal</h1>
      <p class="admin-login-subtitle">Sign in to access CMS &amp; Production Management</p>
    </div>

    <?php $flash = get_flash(); if ($flash): ?>
      <div class="alert alert-<?= ($flash['type'] === 'error') ? 'error' : 'success' ?>">
        <span><?= ($flash['type'] === 'error') ? '⚠️' : '✓' ?></span>
        <span><?= e($flash['message']) ?></span>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-error">
        <span>⚠️</span>
        <span><?= e($error) ?></span>
      </div>
    <?php endif; ?>

    <form action="<?= url('admin/login.php') ?>" method="POST">
      <?= CSRF::inputField() ?>

      <div class="admin-form-group">
        <label for="email" class="admin-form-label">Email Address</label>
        <input type="email" id="email" name="email" value="<?= e($emailInput ?: 'admin@ecomshoot.com') ?>" class="admin-form-control" placeholder="admin@ecomshoot.com" required autofocus>
      </div>

      <div class="admin-form-group">
        <label for="password" class="admin-form-label">Password</label>
        <input type="password" id="password" name="password" value="Admin@Ecomshoot2026!" class="admin-form-control" placeholder="••••••••••••" required>
      </div>

      <button type="submit" class="admin-login-btn">
        <span>Sign In to Portal</span>
        <span>&rarr;</span>
      </button>
    </form>

    <div class="admin-login-demo-box">
      <div class="admin-login-demo-label">Demo Staff Credentials</div>
      <div class="admin-login-demo-creds">
        admin@ecomshoot.com &bull; Admin@Ecomshoot2026!
      </div>
    </div>

    <div style="text-align: center;">
      <a href="<?= url('') ?>" class="admin-login-back-link">&larr; Return to Live Website</a>
    </div>

  </div>

</body>
</html>

