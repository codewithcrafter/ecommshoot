<?php
/**
 * ECOMSHOOT - HTML to Markdown Bidirectional Conversion Engine
 * Converts sanitized Rich Text HTML into clean, valid GitHub Flavored Markdown (GFM).
 */

class HtmlToMarkdown {

    public static function convert(?string $html): string {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // Clean and prepare HTML
        $html = trim($html);

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $dom->getElementsByTagName('div')->item(0);
        if (!$root) {
            return strip_tags($html);
        }

        $md = self::nodeToMarkdown($root);
        // Normalize consecutive blank lines
        $md = preg_replace("/\n{3,}/", "\n\n", trim($md));
        return $md;
    }

    private static function nodeToMarkdown(DOMNode $node, int $listLevel = 0, ?string $listType = null): string {
        $output = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $child->nodeValue;
                // Collapse whitespace unless inside pre/code
                if ($node->nodeName !== 'pre' && $node->nodeName !== 'code') {
                    $text = preg_replace('/\s+/', ' ', $text);
                }
                $output .= $text;
                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tag = strtolower($child->nodeName);

            switch ($tag) {
                case 'h1':
                    $output .= "\n\n# " . trim(self::nodeToMarkdown($child)) . "\n\n";
                    break;
                case 'h2':
                    $output .= "\n\n## " . trim(self::nodeToMarkdown($child)) . "\n\n";
                    break;
                case 'h3':
                    $output .= "\n\n### " . trim(self::nodeToMarkdown($child)) . "\n\n";
                    break;
                case 'h4':
                    $output .= "\n\n#### " . trim(self::nodeToMarkdown($child)) . "\n\n";
                    break;
                case 'h5':
                    $output .= "\n\n##### " . trim(self::nodeToMarkdown($child)) . "\n\n";
                    break;
                case 'h6':
                    $output .= "\n\n###### " . trim(self::nodeToMarkdown($child)) . "\n\n";
                    break;

                case 'p':
                    $inner = trim(self::nodeToMarkdown($child));
                    if ($inner !== '') {
                        $output .= "\n\n" . $inner . "\n\n";
                    }
                    break;

                case 'br':
                    $output .= "  \n";
                    break;

                case 'hr':
                    $output .= "\n\n---\n\n";
                    break;

                case 'strong':
                case 'b':
                    $inner = trim(self::nodeToMarkdown($child));
                    $output .= $inner !== '' ? "**{$inner}**" : '';
                    break;

                case 'em':
                case 'i':
                    $inner = trim(self::nodeToMarkdown($child));
                    $output .= $inner !== '' ? "*{$inner}*" : '';
                    break;

                case 's':
                case 'del':
                    $inner = trim(self::nodeToMarkdown($child));
                    $output .= $inner !== '' ? "~~{$inner}~~" : '';
                    break;

                case 'u':
                    // Markdown has no native underline tag; standard convention is HTML <u> or pass-through
                    $output .= '<u>' . self::nodeToMarkdown($child) . '</u>';
                    break;

                case 'blockquote':
                    $lines = explode("\n", trim(self::nodeToMarkdown($child)));
                    $quoted = array_map(fn($l) => '> ' . $l, $lines);
                    $output .= "\n\n" . implode("\n", $quoted) . "\n\n";
                    break;

                case 'pre':
                    $code = '';
                    $codeNodes = $child->getElementsByTagName('code');
                    if ($codeNodes->length > 0) {
                        $code = $codeNodes->item(0)->nodeValue;
                    } else {
                        $code = $child->nodeValue;
                    }
                    $output .= "\n\n```\n" . trim($code) . "\n```\n\n";
                    break;

                case 'code':
                    if ($node->nodeName !== 'pre') {
                        $output .= '`' . trim($child->nodeValue) . '`';
                    }
                    break;

                case 'ul':
                case 'ol':
                    $output .= "\n" . self::nodeToMarkdown($child, $listLevel + 1, $tag) . "\n";
                    break;

                case 'li':
                    $indent = str_repeat('  ', max(0, $listLevel - 1));
                    $bullet = ($listType === 'ol') ? '1. ' : '- ';
                    $inner = trim(self::nodeToMarkdown($child, $listLevel, $listType));
                    $output .= "{$indent}{$bullet}{$inner}\n";
                    break;

                case 'a':
                    $href = $child->getAttribute('href');
                    $text = trim(self::nodeToMarkdown($child));
                    if ($href !== '') {
                        $output .= "[{$text}]({$href})";
                    } else {
                        $output .= $text;
                    }
                    break;

                case 'img':
                    $src = $child->getAttribute('src');
                    $alt = $child->getAttribute('alt') ?: 'Image';
                    if ($src !== '') {
                        $output .= "![{$alt}]({$src})";
                    }
                    break;

                case 'figure':
                    $figOutput = '';
                    foreach ($child->childNodes as $figChild) {
                        if ($figChild->nodeName === 'img') {
                            $src = $figChild->getAttribute('src');
                            $alt = $figChild->getAttribute('alt') ?: 'Figure Image';
                            $figOutput .= "![{$alt}]({$src})\n";
                        } elseif ($figChild->nodeName === 'figcaption') {
                            $cap = trim(self::nodeToMarkdown($figChild));
                            if ($cap !== '') {
                                $figOutput .= "_{$cap}_\n";
                            }
                        } else {
                            $figOutput .= self::nodeToMarkdown($figChild);
                        }
                    }
                    $output .= "\n\n" . trim($figOutput) . "\n\n";
                    break;

                case 'table':
                    $output .= "\n\n" . self::tableToMarkdown($child) . "\n\n";
                    break;

                default:
                    $output .= self::nodeToMarkdown($child, $listLevel, $listType);
                    break;
            }
        }

        return $output;
    }

    private static function tableToMarkdown(DOMNode $tableNode): string {
        $rows = [];
        $headerRow = [];
        $hasHeader = false;

        $trElements = $tableNode->getElementsByTagName('tr');
        foreach ($trElements as $tr) {
            $cols = [];
            foreach ($tr->childNodes as $cell) {
                if ($cell->nodeType !== XML_ELEMENT_NODE) continue;
                $cellTag = strtolower($cell->nodeName);
                if ($cellTag === 'th') {
                    $hasHeader = true;
                    $cols[] = str_replace('|', '&#124;', trim(self::nodeToMarkdown($cell)));
                } elseif ($cellTag === 'td') {
                    $cols[] = str_replace('|', '&#124;', trim(self::nodeToMarkdown($cell)));
                }
            }
            if (!empty($cols)) {
                $rows[] = $cols;
            }
        }

        if (empty($rows)) {
            return '';
        }

        $colCount = max(array_map('count', $rows));
        $normalizedRows = [];
        foreach ($rows as $r) {
            while (count($r) < $colCount) {
                $r[] = '';
            }
            $normalizedRows[] = $r;
        }

        $lines = [];
        // First row as header if <th> or by default
        $header = array_shift($normalizedRows);
        $lines[] = '| ' . implode(' | ', $header) . ' |';
        $lines[] = '| ' . implode(' | ', array_fill(0, $colCount, '---')) . ' |';

        foreach ($normalizedRows as $row) {
            $lines[] = '| ' . implode(' | ', $row) . ' |';
        }

        return implode("\n", $lines);
    }
}
