<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();
$db->query("DELETE FROM blogs WHERE id = 5");
MarkdownGenerator::delete(5);
SEOGenerator::regenerateAll();
echo "Cleaned test blog #5 and regenerated SEO files.\n";

