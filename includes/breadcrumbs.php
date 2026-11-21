<?php
/**
 * ECOMSHOOT - BreadcrumbList structured-data helper.
 */

/**
 * @param array $items Array of ['title' => string, 'url' => string|null]
 */
function render_breadcrumbs(array $items): string {
    // BreadcrumbList is rendered canonically in the <head> section by SchemaManager.
    // Suppress body script block to prevent duplicate JSON-LD schemas.
    return '';
}

