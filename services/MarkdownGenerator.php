<?php
/**
 * ECOMSHOOT - Automated Markdown Publishing & Synchronization Service
 * Generates and manages public-facing atomic Markdown (.md) documents
 * stored under /content/blogs/{id}.md
 */

class MarkdownGenerator {

    /**
     * Get the absolute storage directory for published Markdown documents
     */
    public static function getContentDir(): string {
        $dir = defined('CONTENT_PATH') ? CONTENT_PATH . '/blogs' : ROOT_PATH . '/content/blogs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Get the physical file path for a given blog ID
     */
    public static function getFilePath(int $blogId): string {
        return self::getContentDir() . '/' . $blogId . '.md';
    }

    /**
     * Check if a generated Markdown file exists on disk
     */
    public static function hasFile(int $blogId): bool {
        $path = self::getFilePath($blogId);
        return file_exists($path) && filesize($path) > 0;
    }

    /**
     * Retrieve the last generated timestamp of the Markdown file
     */
    public static function getLastGeneratedTime(int $blogId): ?int {
        $path = self::getFilePath($blogId);
        return file_exists($path) ? filemtime($path) : null;
    }

    /**
     * Read the raw content of the generated Markdown file
     */
    public static function getFileContent(int $blogId): ?string {
        $path = self::getFilePath($blogId);
        if (file_exists($path)) {
            $content = file_get_contents($path);
            return ($content !== false) ? $content : null;
        }
        return null;
    }

    /**
     * Delete the generated Markdown file for a blog (when unpublished or deleted)
     */
    public static function delete(int $blogId): bool {
        $path = self::getFilePath($blogId);
        if (file_exists($path)) {
            return @unlink($path);
        }
        return true;
    }

    /**
     * Generate or regenerate the atomic Markdown document for a specific blog
     */
    public static function generate(int $blogId): bool {
        if ($blogId <= 0) {
            return false;
        }

        try {
            $db = Database::getInstance()->getConnection();

            // Fetch blog with category
            $stmt = $db->prepare(
                "SELECT b.*, c.name as category_name, c.slug as category_slug 
                 FROM blogs b 
                 LEFT JOIN blog_categories c ON b.category_id = c.id 
                 WHERE b.id = :id LIMIT 1"
            );
            $stmt->execute(['id' => $blogId]);
            $blog = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$blog) {
                return false;
            }

            // Only published blogs should have public Markdown documents
            if ($blog['status'] !== 'published') {
                self::delete($blogId);
                return false;
            }

            // Fetch assigned tags
            $tStmt = $db->prepare(
                "SELECT t.name, t.slug 
                 FROM blog_tags t 
                 JOIN blog_tag_relations r ON t.id = r.tag_id 
                 WHERE r.blog_id = :bid 
                 ORDER BY t.name ASC"
            );
            $tStmt->execute(['bid' => $blogId]);
            $tags = $tStmt->fetchAll(PDO::FETCH_ASSOC);
            $tagNames = array_column($tags, 'name');

            // Production Base URL
            $baseUrl = rtrim(get_setting('site_base_url', BASE_URL), '/');
            $htmlUrl = $baseUrl . '/blog/' . $blogId;
            $mdUrl = $baseUrl . '/blog/' . $blogId . '.md';

            // Resolve full image URL
            $featImg = $blog['featured_image'] ?: 'assets/images/portfolio/portfolio-1.jpg';
            $fullImgUrl = (strpos($featImg, 'http') === 0) ? $featImg : $baseUrl . '/' . ltrim($featImg, '/');

            // Format dates
            $pubDate = !empty($blog['published_at']) ? date('Y-m-d', strtotime($blog['published_at'])) : date('Y-m-d');
            $modDate = !empty($blog['updated_at']) ? date('c', strtotime($blog['updated_at'])) : date('c');

            // Parse FAQs
            $faqs = json_decode($blog['faqs_json'] ?? '[]', true) ?: [];

            // Parse Internal Links
            $internalLinks = json_decode($blog['internal_links_json'] ?? '[]', true) ?: [];

            // Build YAML Frontmatter
            $lines = [];
            $lines[] = '---';
            $lines[] = 'title: ' . json_encode($blog['title'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $lines[] = 'summary: ' . json_encode($blog['excerpt'] ?? '', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $lines[] = 'author: ' . json_encode($blog['author_name'] ?? 'Marcus Vance', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $lines[] = 'author_designation: ' . json_encode($blog['author_role'] ?? 'Commercial Photography Director', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $lines[] = 'published_date: ' . json_encode($pubDate);
            $lines[] = 'modified_date: ' . json_encode($modDate);
            $lines[] = 'read_time: ' . json_encode($blog['read_time'] ?? '5 min read');
            $lines[] = 'category: ' . json_encode($blog['category_name'] ?: 'Strategy', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            
            // Format Tags
            if (!empty($tagNames)) {
                $lines[] = 'tags:';
                foreach ($tagNames as $tn) {
                    $lines[] = '  - ' . json_encode($tn, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            } else {
                $lines[] = 'tags: []';
            }

            $lines[] = 'featured_image: ' . json_encode($fullImgUrl, JSON_UNESCAPED_SLASHES);
            if (!empty($blog['featured_image_alt'])) {
                $lines[] = 'featured_image_alt: ' . json_encode($blog['featured_image_alt'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            if (!empty($blog['featured_image_caption'])) {
                $lines[] = 'featured_image_caption: ' . json_encode($blog['featured_image_caption'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            $lines[] = 'canonical_html_url: ' . json_encode($htmlUrl, JSON_UNESCAPED_SLASHES);
            $lines[] = 'markdown_url: ' . json_encode($mdUrl, JSON_UNESCAPED_SLASHES);
            $lines[] = '---';
            $lines[] = '';

            // Article Heading & Summary
            $lines[] = '# ' . $blog['title'];
            $lines[] = '';
            if (!empty($blog['excerpt'])) {
                $lines[] = '> ' . $blog['excerpt'];
                $lines[] = '';
            }

            // Article Body Content
            $bodyContent = '';
            if (!empty($blog['markdown_content'])) {
                $bodyContent = trim($blog['markdown_content']);
            } elseif (($blog['content_format'] ?? 'markdown') === 'html' || preg_match('/<[a-z][\s\S]*>/i', $blog['content'] ?? '')) {
                $bodyContent = trim(HtmlToMarkdown::convert($blog['content'] ?? ''));
            } else {
                $bodyContent = trim($blog['content'] ?? '');
            }

            if (!empty($bodyContent)) {
                $lines[] = $bodyContent;
                $lines[] = '';
            }

            // Append FAQs if present
            if (!empty($faqs)) {
                $lines[] = '## Frequently Asked Questions';
                $lines[] = '';
                foreach ($faqs as $faq) {
                    if (!empty($faq['question']) && !empty($faq['answer'])) {
                        $lines[] = '### ' . trim($faq['question']);
                        $lines[] = '';
                        $lines[] = trim($faq['answer']);
                        $lines[] = '';
                    }
                }
            }

            // Append Internal Links if present
            if (!empty($internalLinks)) {
                $lines[] = '## Recommended Production Services';
                $lines[] = '';
                foreach ($internalLinks as $link) {
                    if (!empty($link['anchor']) && !empty($link['url'])) {
                        $targetUrl = (strpos($link['url'], 'http') === 0) ? $link['url'] : $baseUrl . '/' . ltrim($link['url'], '/');
                        $lines[] = '- [' . trim($link['anchor']) . '](' . $targetUrl . ')';
                    }
                }
                $lines[] = '';
            }

            $markdownDocument = implode("\n", $lines) . "\n";

            // Update database markdown_content copy
            $updStmt = $db->prepare("UPDATE blogs SET markdown_content = :md WHERE id = :id");
            $updStmt->execute(['md' => $markdownDocument, 'id' => $blogId]);

            // Atomic file writing
            $targetPath = self::getFilePath($blogId);
            $tempPath = $targetPath . '.tmp.' . bin2hex(random_bytes(6));

            if (file_put_contents($tempPath, $markdownDocument, LOCK_EX) === false) {
                return false;
            }

            if (!rename($tempPath, $targetPath)) {
                @unlink($tempPath);
                return false;
            }

            return true;
        } catch (Exception $e) {
            error_log('MarkdownGenerator Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Regenerate Markdown files for all published blogs
     */
    public static function generateAll(): array {
        $results = ['success' => 0, 'failed' => 0, 'deleted' => 0];
        try {
            $db = Database::getInstance()->getConnection();
            $blogs = $db->query("SELECT id, status FROM blogs")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($blogs as $b) {
                $bid = (int) $b['id'];
                if ($b['status'] === 'published') {
                    if (self::generate($bid)) {
                        $results['success']++;
                    } else {
                        $results['failed']++;
                    }
                } else {
                    self::delete($bid);
                    $results['deleted']++;
                }
            }
        } catch (Exception $e) {
            error_log('MarkdownGenerator::generateAll Error: ' . $e->getMessage());
        }
        return $results;
    }
}

