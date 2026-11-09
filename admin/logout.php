<?php
/**
 * ECOMSHOOT - Admin Logout Handler
 */

require_once __DIR__ . '/../config/config.php';

Auth::logout();
set_flash('success', 'You have been logged out securely.');
redirect('admin/login.php');

