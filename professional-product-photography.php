<?php
/**
 * ECOMSHOOT - Discontinued Service Canonical Redirect
 * "Professional Product Photography - Enterprise" has been discontinued.
 * Permanently redirecting to canonical core service: Product Photography.
 */
require_once __DIR__ . '/config/config.php';

http_response_code(301);
header("HTTP/1.1 301 Moved Permanently");
header('Location: ' . url('product-photography.php'), true, 301);
exit;
