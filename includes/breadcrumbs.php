<?php
/**
 * ECOMSHOOT - Breadcrumb Navigation Component with JSON-LD Schema
 */

/**
 * @param array $items Array of ['title' => string, 'url' => string|null]
 */
function render_breadcrumbs(array $items): string {
    $html = '<nav class="breadcrumbs-bar" aria-label="Breadcrumb">';
    $html .= '<div class="container">';
    $html .= '<ol class="breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">';

    // Always start with Home
    $fullItems = array_merge([
        ['title' => 'Home', 'url' => url('')]
    ], $items);

    $total = count($fullItems);
    foreach ($fullItems as $idx => $item) {
        $pos = $idx + 1;
        $isLast = ($pos === $total);

        $html .= '<li class="breadcrumb-item' . ($isLast ? ' active' : '') . '" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';

        if (!$isLast && !empty($item['url'])) {
            $html .= '<a itemprop="item" href="' . e($item['url']) . '"><span itemprop="name">' . e($item['title']) . '</span></a>';
        } else {
            $html .= '<span itemprop="name">' . e($item['title']) . '</span>';
        }

        $html .= '<meta itemprop="position" content="' . $pos . '">';

        if (!$isLast) {
            $html .= '<span class="breadcrumb-separator" aria-hidden="true">/</span>';
        }

        $html .= '</li>';
    }

    $html .= '</ol>';
    $html .= '</div>';
    $html .= '</nav>';

    return $html;
}

