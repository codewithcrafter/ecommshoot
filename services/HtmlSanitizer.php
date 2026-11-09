<?php
/**
 * ECOMSHOOT - Server-Side HTML Sanitizer Service
 * Strips malicious tags, XSS vectors, and unsafe protocols from rich text editor input.
 */

class HtmlSanitizer {

    /**
     * Whitelisted HTML tags permitted in blog articles
     */
    private static array $allowedTags = [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'p', 'br', 'hr', 'blockquote', 'pre', 'code',
        'ul', 'ol', 'li',
        'strong', 'b', 'em', 'i', 'u', 's', 'del', 'mark', 'span',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
        'a', 'img', 'figure', 'figcaption', 'div'
    ];

    /**
     * Permitted attributes mapped by tag (or global for all)
     */
    private static array $allowedAttributes = [
        'a' => ['href', 'title', 'target', 'rel', 'class'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading', 'class', 'style'],
        'table' => ['class', 'style'],
        'th' => ['colspan', 'rowspan', 'scope', 'style', 'class'],
        'td' => ['colspan', 'rowspan', 'style', 'class'],
        'div' => ['class', 'style'],
        'span' => ['class', 'style'],
        'p' => ['class', 'style'],
        'figure' => ['class', 'style'],
        'figcaption' => ['class', 'style'],
        'code' => ['class'],
        'pre' => ['class']
    ];

    /**
     * Sanitize raw HTML string
     */
    public static function clean(?string $html): string {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // 1. Remove dangerous script and iframe blocks before parsing
        $cleaned = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $cleaned = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $cleaned);
        $cleaned = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $cleaned);
        $cleaned = preg_replace('#<object(.*?)>(.*?)</object>#is', '', $cleaned);
        $cleaned = preg_replace('#<embed(.*?)>(.*?)</embed>#is', '', $cleaned);
        $cleaned = preg_replace('#<form(.*?)>(.*?)</form>#is', '', $cleaned);

        // 2. Parse using DOMDocument
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div>' . $cleaned . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $dom->getElementsByTagName('div')->item(0);
        if (!$root) {
            return '';
        }

        self::sanitizeNode($root);

        // Export inner HTML
        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    /**
     * Recursively sanitize DOM nodes
     */
    private static function sanitizeNode(DOMNode $node): void {
        // Collect children into an array because removing nodes during iteration mutates childNodes
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tagName = strtolower($child->nodeName);

                // Strip disallowed elements
                if (!in_array($tagName, self::$allowedTags, true)) {
                    $node->removeChild($child);
                    continue;
                }

                // Sanitize attributes
                if ($child->hasAttributes()) {
                    $attrs = [];
                    foreach ($child->attributes as $attr) {
                        $attrs[] = $attr->nodeName;
                    }

                    $tagAllowedAttrs = self::$allowedAttributes[$tagName] ?? ['class', 'style'];

                    foreach ($attrs as $attrName) {
                        $lowerAttr = strtolower($attrName);

                        // Strip event handlers (e.g. onload, onclick, onerror)
                        if (str_starts_with($lowerAttr, 'on')) {
                            $child->removeAttribute($attrName);
                            continue;
                        }

                        // Check whitelist
                        if (!in_array($lowerAttr, $tagAllowedAttrs, true)) {
                            $child->removeAttribute($attrName);
                            continue;
                        }

                        // Validate URL protocols in href / src
                        if ($lowerAttr === 'href' || $lowerAttr === 'src') {
                            $val = trim($child->getAttribute($attrName));
                            if (!self::isSafeUrl($val)) {
                                $child->removeAttribute($attrName);
                            }
                        }

                        // Sanitize style attribute: remove expressions / url / behaviors
                        if ($lowerAttr === 'style') {
                            $styleVal = $child->getAttribute($attrName);
                            if (preg_match('/(expression|behavior|javascript|vbscript|include-source)/i', $styleVal)) {
                                $child->removeAttribute($attrName);
                            }
                        }
                    }
                }

                // If link target is _blank, enforce rel="noopener noreferrer"
                if ($tagName === 'a' && $child->getAttribute('target') === '_blank') {
                    $child->setAttribute('rel', 'noopener noreferrer');
                }

                // Recurse down children
                self::sanitizeNode($child);
            }
        }
    }

    /**
     * Check if a URL protocol is safe
     */
    private static function isSafeUrl(string $url): bool {
        if ($url === '' || $url === '#') {
            return true;
        }

        // Relative path or anchor
        if (str_starts_with($url, '/') || str_starts_with($url, '#') || str_starts_with($url, './') || str_starts_with($url, '../')) {
            return true;
        }

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['scheme'])) {
            return true;
        }

        $scheme = strtolower($parsed['scheme']);
        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    }
}
