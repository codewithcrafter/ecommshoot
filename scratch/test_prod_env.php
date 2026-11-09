<?php
define('PRODUCTION_MODE', true);
$_SERVER['HTTP_HOST'] = 'ecommshoots.in';
require_once __DIR__ . '/../config/config.php';

echo "Environment: " . ($isLocalDev ? 'Local' : 'Production') . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "log_errors: " . ini_get('log_errors') . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_HOST: " . DB_HOST . "\n";

