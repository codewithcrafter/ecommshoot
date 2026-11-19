<?php
/**
 * ECOMSHOOT - Custom Pure-PHP Markdown to HTML Parser
 * Fast, lightweight, zero external dependencies.
 */

class MarkdownParser {

    public static function parse(string $markdown): string {
        $text = str_replace(["\r\n", "\r"], "\n", $markdown);

        // Filter out pseudo-formula presentation boxes (e.g. "Amazon Main Image Formula", "Image Formula")
        $text = preg_replace('/```[a-zA-Z0-9_\-\+]*\s*(?:Amazon\s+Main\s+)?Image\s+Formula:?[\s\S]*?```\n?/i', '', $text);

        // Protect code blocks before block-level transformations
        $codeBlocks = [];
        $text = preg_replace_callback('/```([a-zA-Z0-9_\-\+]*)\n([\s\S]*?)\n```/', function ($matches) use (&$codeBlocks) {
            $lang = trim($matches[1]);
            $code = htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
            $idx = count($codeBlocks);
            $langAttr = $lang ? ' class="language-' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '"' : '';
            $codeBlocks[$idx] = '<pre><code' . $langAttr . '>' . $code . '</code></pre>';
            return '%%CODEBLOCK_' . $idx . '%%';
        }, $text);

        // Protect inline code spans
        $inlineCodes = [];
        $text = preg_replace_callback('/`([^`\n]+)`/', function ($matches) use (&$inlineCodes) {
            $idx = count($inlineCodes);
            $inlineCodes[$idx] = '<code>' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '</code>';
            return '%%INLINECODE_' . $idx . '%%';
        }, $text);

        // Process Tables
        $text = self::parseTables($text);

        // Split into block paragraphs
        $lines = explode("\n", $text);
        $html = '';
        $inList = false;
        $listType = '';
        $inBlockquote = false;
        $pBuffer = [];

        $flushParagraph = function() use (&$html, &$pBuffer) {
            if (!empty($pBuffer)) {
                $pText = trim(implode(' ', $pBuffer));
                if ($pText !== '') {
                    $html .= '<p>' . self::parseInline($pText) . "</p>\n";
                }
                $pBuffer = [];
            }
        };

        $closeList = function() use (&$html, &$inList, &$listType) {
            if ($inList) {
                $html .= "</{$listType}>\n";
                $inList = false;
                $listType = '';
            }
        };

        $closeBlockquote = function() use (&$html, &$inBlockquote) {
            if ($inBlockquote) {
                $html .= "</blockquote>\n";
                $inBlockquote = false;
            }
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Empty line: flush paragraph and close open blocks if needed
            if ($trimmed === '') {
                $flushParagraph();
                $closeList();
                $closeBlockquote();
                continue;
            }

            // Headings (H1 - H6)
            if (preg_match('/^(#{1,6})\s+(.*)$/', $trimmed, $m)) {
                $flushParagraph();
                $closeList();
                $closeBlockquote();
                $level = strlen($m[1]);
                $headingContent = self::parseInline(trim($m[2]));
                $html .= "<h{$level}>{$headingContent}</h{$level}>\n";
                continue;
            }

            // Horizontal Rule
            if (preg_match('/^(\*{3,}|-{3,}|_{3,})$/', $trimmed)) {
                $flushParagraph();
                $closeList();
                $closeBlockquote();
                $html .= "<hr>\n";
                continue;
            }

            // Blockquotes
            if (preg_match('/^>\s?(.*)$/', $trimmed, $m)) {
                $flushParagraph();
                $closeList();
                if (!$inBlockquote) {
                    $html .= "<blockquote>\n";
                    $inBlockquote = true;
                }
                $html .= '<p>' . self::parseInline($m[1]) . "</p>\n";
                continue;
            } else {
                $closeBlockquote();
            }

            // Unordered Lists
            if (preg_match('/^[\*\-\+]\s+(.*)$/', $trimmed, $m)) {
                $flushParagraph();
                if (!$inList || $listType !== 'ul') {
                    $closeList();
                    $html .= "<ul>\n";
                    $inList = true;
                    $listType = 'ul';
                }
                $html .= '<li>' . self::parseInline($m[1]) . "</li>\n";
                continue;
            }

            // Ordered Lists
            if (preg_match('/^\d+\.\s+(.*)$/', $trimmed, $m)) {
                $flushParagraph();
                if (!$inList || $listType !== 'ol') {
                    $closeList();
                    $html .= "<ol>\n";
                    $inList = true;
                    $listType = 'ol';
                }
                $html .= '<li>' . self::parseInline($m[1]) . "</li>\n";
                continue;
            }

            // If we are here, close any active lists
            $closeList();

            // Already handled table markup or code placeholders
            if (str_starts_with($trimmed, '<table') || str_starts_with($trimmed, '%%CODEBLOCK_')) {
                $flushParagraph();
                $html .= $trimmed . "\n";
                continue;
            }

            // Regular paragraph line
            $pBuffer[] = $trimmed;
        }

        $flushParagraph();
        $closeList();
        $closeBlockquote();

        // Restore inline code spans
        foreach ($inlineCodes as $idx => $codeHtml) {
            $html = str_replace('%%INLINECODE_' . $idx . '%%', $codeHtml, $html);
        }

        // Restore code blocks
        foreach ($codeBlocks as $idx => $blockHtml) {
            $html = str_replace('%%CODEBLOCK_' . $idx . '%%', $blockHtml, $html);
        }

        return $html;
    }

    private static function parseInline(string $text): string {
        // Images: ![alt](url)
        $text = preg_replace_callback('/!\[([^\]]*)\]\(([^)]+)\)/', function ($m) {
            $alt = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
            return '<img src="' . $url . '" alt="' . $alt . '" loading="lazy" class="content-img">';
        }, $text);

        // Links: [text](url)
        $text = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($m) {
            $label = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
            return '<a href="' . $url . '">' . $label . '</a>';
        }, $text);

        // Bold: **text** or __text__
        $text = preg_replace('/(\*\*|__)(.*?)\1/', '<strong>$2</strong>', $text);

        // Italics: *text* or _text_
        $text = preg_replace('/(\*|_)(.*?)\1/', '<em>$2</em>', $text);

        // Strikethrough: ~~text~~
        $text = preg_replace('/~~(.*?)~~/', '<del>$1</del>', $text);

        return $text;
    }

    private static function parseTables(string $text): string {
        $pattern = '/((?:(?:\|[^\n]+\|\r?\n?)+))/';
        return preg_replace_callback($pattern, function ($matches) {
            $rawTable = trim($matches[1]);
            $lines = array_filter(explode("\n", $rawTable));
            if (count($lines) < 2) return $matches[1];

            $headerLine = array_shift($lines);
            $separatorLine = array_shift($lines);

            // Check if separator line has dashes
            if (!preg_match('/^\|(?:\s*:?-+:?\s*\|)+$/', trim($separatorLine))) {
                return $matches[1];
            }

            $headers = array_map('trim', explode('|', trim($headerLine, '|')));
            $tableHtml = "<div class=\"table-responsive\"><table class=\"markdown-table\">\n<thead>\n<tr>\n";
            foreach ($headers as $h) {
                $tableHtml .= '<th>' . self::parseInline($h) . "</th>\n";
            }
            $tableHtml .= "</tr>\n</thead>\n<tbody>\n";

            foreach ($lines as $rowLine) {
                $rowTrimmed = trim($rowLine);
                if (empty($rowTrimmed)) continue;
                $cells = array_map('trim', explode('|', trim($rowTrimmed, '|')));
                $tableHtml .= "<tr>\n";
                foreach ($cells as $cell) {
                    $tableHtml .= '<td>' . self::parseInline($cell) . "</td>\n";
                }
                $tableHtml .= "</tr>\n";
            }

            $tableHtml .= "</tbody>\n</table></div>\n";
            return $tableHtml;
        }, $text);
    }
}

