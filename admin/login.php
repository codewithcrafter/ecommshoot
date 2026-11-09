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
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset_url('assets/css/admin.css') ?>">
</head>
<body style="background-color: #0F172A; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">

  <div style="width: 100%; max-width: 440px; background-color: #FFFFFF; border-radius: 16px; padding: 2.75rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);">
    
    <div style="text-align: center; margin-bottom: 2rem;">
      <a href="<?= url('') ?>">
        <img src="<?= asset_url('assets/images/logo.svg') ?>" alt="Ecomshoot" width="160" height="42" style="margin: 0 auto 1.25rem auto;">
      </a>
      <h1 style="font-size: 1.5rem; margin-bottom: 0.35rem;">Studio Staff Portal</h1>
      <p style="font-size: 0.88rem; color: var(--admin-text-muted); margin: 0;">Sign in to access CMS &amp; Production Management</p>
    </div>

    <?php $flash = get_flash(); if ($flash): ?>
      <div class="alert alert-<?= ($flash['type'] === 'error') ? 'error' : 'success' ?>">
        <?= e($flash['message']) ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-error">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form action="<?= url('admin/login.php') ?>" method="POST">
      <?= CSRF::inputField() ?>

      <div class="admin-form-group">
        <label for="email" class="admin-form-label">Email Address</label>
        <input type="email" id="email" name="email" value="<?= e($emailInput ?: 'admin@ecomshoot.com') ?>" class="admin-form-control" required autofocus>
      </div>

      <div class="admin-form-group">
        <label for="password" class="admin-form-label">Password</label>
        <input type="password" id="password" name="password" value="Admin@Ecomshoot2026!" class="admin-form-control" required>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem; justify-content: center;">
        Sign In to Portal &rarr;
      </button>
    </form>

    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--admin-border); text-align: center;">
      <div style="font-size: 0.8rem; color: var(--admin-text-muted); margin-bottom: 0.5rem;">
        Demo Credentials:<br>
        <strong>admin@ecomshoot.com</strong> / <strong>Admin@Ecomshoot2026!</strong>
      </div>
      <a href="<?= url('') ?>" style="font-size: 0.85rem; font-weight: 600; color: var(--admin-primary);">&larr; Return to Live Website</a>
    </div>

  </div>

</body>
</html>

