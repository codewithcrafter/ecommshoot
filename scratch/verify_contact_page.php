<?php
$html = file_get_contents('http://127.0.0.1:8000/contact.php');

$checks = [
    'Official Email contact@ecommshoot.in' => strpos($html, 'contact@ecommshoot.in') !== false,
    'No Fake NY Facility' => strpos($html, 'New York Facility') === false,
    'No Fake 555-SHOOT' => strpos($html, '+1 (800) 555-SHOOT') === false,
    'Has Full Name Field' => strpos($html, 'name="full_name"') !== false,
    'Has Mobile Field' => strpos($html, 'name="mobile"') !== false,
    'Has Email Field' => strpos($html, 'name="email"') !== false,
    'Has Message Field' => strpos($html, 'name="message"') !== false,
    'Removed Company Name Field' => strpos($html, 'name="company_name"') === false,
    'Removed Service Required Select' => strpos($html, 'name="service_required"') === false,
    'Has Lead Popup Modal' => strpos($html, 'id="leadPopupOverlay"') !== false
];

foreach ($checks as $title => $pass) {
    echo ($pass ? "[PASS] " : "[FAIL] ") . $title . "\n";
}

