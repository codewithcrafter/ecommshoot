<?php
require_once __DIR__ . '/../config/config.php';

echo "1. Generating all Markdown documents in /content/blogs/...\n";
$mdResults = MarkdownGenerator::generateAll();
echo "Markdown Gen Results: " . json_encode($mdResults) . "\n";

echo "2. Regenerating SEO files (sitemap.xml, llms.txt, robots.txt)...\n";
$seoResults = SEOGenerator::regenerateAll();
echo "SEO Results: " . json_encode($seoResults) . "\n";

