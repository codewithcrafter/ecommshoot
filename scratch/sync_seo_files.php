<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/SEOGenerator.php';

$res = SEOGenerator::regenerateAll();
echo "Sitemap: " . ($res['sitemap'] ? 'OK' : 'FAIL') . "\n";
echo "LLMs.txt: " . ($res['llms'] ? 'OK' : 'FAIL') . "\n";
echo "Robots.txt: " . ($res['robots'] ? 'OK' : 'FAIL') . "\n";

