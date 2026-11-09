<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance()->getConnection();

function dumpTableInsertIgnore(PDO $db, string $table): string {
    $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) return "-- No seed rows for `$table`\n";

    $cols = array_keys($rows[0]);
    $quotedCols = array_map(function($c) { return "`$c`"; }, $cols);
    $colList = implode(', ', $quotedCols);

    $sql = "-- Seed data for `$table` (Additive INSERT IGNORE - preserves existing records)\n";
    $sql .= "INSERT IGNORE INTO `$table` ($colList) VALUES\n";

    $valRows = [];
    foreach ($rows as $row) {
        $vals = [];
        foreach ($row as $val) {
            if ($val === null) {
                $vals[] = "NULL";
            } else {
                $vals[] = $db->quote($val);
            }
        }
        $valRows[] = "(" . implode(', ', $vals) . ")";
    }
    $sql .= implode(",\n", $valRows) . ";\n\n";
    return $sql;
}

$tables = [
    'admins',
    'pages',
    'services',
    'blog_categories',
    'blog_tags',
    'blogs',
    'blog_tag_relations',
    'media',
    'seo_metadata',
    'schemas',
    'markdown_documents',
    'contact_submissions',
    'leads',
    'website_settings'
];

$output = "-- ============================================================================\n";
$output .= "-- ECOMSHOOT - SAFE PRODUCTION DATABASE REPAIR & MIGRATION\n";
$output .= "-- NON-DESTRUCTIVE: Zero DROP, Zero TRUNCATE, Pure Additive (IF NOT EXISTS)\n";
$output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$output .= "-- ============================================================================\n\n";
$output .= "SET NAMES utf8mb4;\n";
$output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($tables as $table) {
    $create = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC)['Create Table'];
    // Replace "CREATE TABLE" with "CREATE TABLE IF NOT EXISTS"
    $create = preg_replace('/^CREATE TABLE/i', 'CREATE TABLE IF NOT EXISTS', $create);
    $output .= "-- ----------------------------------------------------------------------------\n";
    $output .= "-- Table: $table\n";
    $output .= "-- ----------------------------------------------------------------------------\n";
    $output .= $create . ";\n\n";
}

// Add ALTER TABLE checks for columns that might be missing if tables already existed with older schema
$output .= "-- ----------------------------------------------------------------------------\n";
$output .= "-- Idempotent Column Additions (for pre-existing tables)\n";
$output .= "-- ----------------------------------------------------------------------------\n";
$output .= "ALTER TABLE `blogs` ADD COLUMN IF NOT EXISTS `featured_image_alt` varchar(255) DEFAULT '' AFTER `updated_at`;\n";
$output .= "ALTER TABLE `blogs` ADD COLUMN IF NOT EXISTS `featured_image_caption` varchar(255) DEFAULT '' AFTER `featured_image_alt`;\n";
$output .= "ALTER TABLE `blogs` ADD COLUMN IF NOT EXISTS `markdown_content` longtext DEFAULT NULL AFTER `featured_image_caption`;\n";
$output .= "ALTER TABLE `blogs` ADD COLUMN IF NOT EXISTS `schemas_json` longtext DEFAULT NULL AFTER `markdown_content`;\n";
$output .= "ALTER TABLE `blogs` ADD COLUMN IF NOT EXISTS `schema_types` varchar(255) DEFAULT 'BlogPosting,BreadcrumbList' AFTER `schemas_json`;\n";
$output .= "ALTER TABLE `blog_tags` ADD COLUMN IF NOT EXISTS `created_at` timestamp NULL DEFAULT current_timestamp() AFTER `slug`;\n\n";

$output .= "-- ============================================================================\n";
$output .= "-- SEED / RESTORATION DATA (INSERT IGNORE - PRESERVES EXISTING RECORDS)\n";
$output .= "-- ============================================================================\n\n";

// Only dump seed for tables that need initial records
$seedTables = ['admins', 'pages', 'services', 'blog_categories', 'blog_tags', 'blogs', 'blog_tag_relations', 'seo_metadata', 'schemas', 'website_settings'];
foreach ($seedTables as $st) {
    $output .= dumpTableInsertIgnore($db, $st);
}

$output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
$output .= "-- ============================================================================\n";
$output .= "-- REPAIR & MIGRATION SCRIPT COMPLETED SAFELY\n";
$output .= "-- ============================================================================\n";

file_put_contents(__DIR__ . '/../database/production_safe_migration.sql', $output);
echo "Generated database/production_safe_migration.sql (" . strlen($output) . " bytes)\n";

