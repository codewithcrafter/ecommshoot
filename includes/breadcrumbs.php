<?php
/**
 * ECOMSHOOT - BreadcrumbList structured-data helper.
 */

/**
 * @param array $items Array of ['title' => string, 'url' => string|null]
 */
function render_breadcrumbs(array $items): string {
    // Keep accurate BreadcrumbList metadata without rendering a visible navigation bar.
    $fullItems = array_merge([
        ['title' => 'Home', 'url' => url('')]
    ], $items);

    $listItems = [];
    foreach ($fullItems as $idx => $item) {
        $listItem = [
            '@type' => 'ListItem',
            'position' => $idx + 1,
            'name' => $item['title']
        ];

        if (!empty($item['url'])) {
            $listItem['item'] = $item['url'];
        }

        $listItems[] = $listItem;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $listItems
    ];

    return '<script type="application/ld+json">' . json_encode(
        $schema,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) . '</script>';
}

