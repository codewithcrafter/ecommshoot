<?php
/**
 * ECOMSHOOT - Automated SEO Engine & File Generator
 * Handles dynamic regeneration of sitemap.xml, llms.txt, and robots.txt
 */

class SEOGenerator {

    /**
     * Regenerate all automated SEO files in one centralized call
     */
    public static function regenerateAll(): array {
        $results = [
            'sitemap' => self::generateSitemap(),
            'llms' => self::generateLlmsTxt(),
            'robots' => self::generateRobotsTxt()
        ];
        return $results;
    }

    /**
     * Safely write content to file atomically
     */
    private static function safeWriteFile(string $filePath, string $content): bool {
        $tempPath = $filePath . '.tmp.' . bin2hex(random_bytes(4));
        if (file_put_contents($tempPath, $content) === false) {
            return false;
        }
        // Atomic rename
        if (!rename($tempPath, $filePath)) {
            @unlink($tempPath);
            return false;
        }
        return true;
    }

    /**
     * Generate standard compliant sitemap.xml
     */
    public static function generateSitemap(): bool {
        try {
            $db = Database::getInstance()->getConnection();

            $urls = [];

            // 1. Static Published Pages
            $pages = $db->query("SELECT slug, file_name, updated_at FROM pages WHERE status = 'published' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($pages as $p) {
                $loc = ($p['slug'] === 'home') ? url('') : url($p['file_name']);
                $urls[] = [
                    'loc' => $loc,
                    'lastmod' => date('Y-m-d', strtotime($p['updated_at'] ?? 'now')),
                    'changefreq' => ($p['slug'] === 'home') ? 'daily' : 'weekly',
                    'priority' => ($p['slug'] === 'home') ? '1.0' : '0.8'
                ];
            }

            // 2. Published Services
            $services = $db->query("SELECT slug, file_name, updated_at FROM services WHERE status = 'published' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($services as $s) {
                $urls[] = [
                    'loc' => service_url($s['slug']),
                    'lastmod' => date('Y-m-d', strtotime($s['updated_at'] ?? 'now')),
                    'changefreq' => 'weekly',
                    'priority' => '0.9'
                ];
            }

            // 3. Published Blogs (Canonical Slug-Based URLs)
            $blogs = $db->query("SELECT id, slug, updated_at FROM blogs WHERE status = 'published' ORDER BY published_at DESC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($blogs as $b) {
                $urls[] = [
                    'loc' => blog_url($b['slug']),
                    'lastmod' => date('Y-m-d', strtotime($b['updated_at'] ?? 'now')),
                    'changefreq' => 'monthly',
                    'priority' => '0.7'
                ];
            }

            // Deduplicate URLs by 'loc'
            $uniqueUrls = [];
            foreach ($urls as $u) {
                $uniqueUrls[$u['loc']] = $u;
            }

            // Build XML
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            foreach ($uniqueUrls as $entry) {
                $xml .= "  <url>\n";
                $xml .= '    <loc>' . htmlspecialchars($entry['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
                $xml .= '    <lastmod>' . $entry['lastmod'] . "</lastmod>\n";
                $xml .= '    <changefreq>' . $entry['changefreq'] . "</changefreq>\n";
                $xml .= '    <priority>' . $entry['priority'] . "</priority>\n";
                $xml .= "  </url>\n";
            }
            $xml .= '</urlset>';

            $sitemapFile = ROOT_PATH . '/sitemap.xml';
            return self::safeWriteFile($sitemapFile, $xml);
        } catch (Exception $e) {
            error_log('SEOGenerator Sitemap Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate llms.txt standard file for LLM scraping and indexing
     */
    public static function generateLlmsTxt(): bool {
        try {
            $db = Database::getInstance()->getConnection();

            $siteName = get_setting('site_name', 'Ecomshoot');
            $siteTagline = get_setting('site_tagline', 'Premium E-Commerce Product Photography & Creative Production Agency');
            $companySummary = get_setting('llms_company_summary', '');
            $email = get_setting('site_email', 'contact@ecomshoot.com');
            $phone = get_setting('site_phone', '+1 (800) 555-SHOOT');
            $address = get_setting('site_address', 'Studio 4B, 742 Creative Boulevard, New York, NY 10013');

            $lines = [];
            $lines[] = "# {$siteName}";
            $lines[] = "> {$siteTagline}";
            $lines[] = "";
            $lines[] = "## Company Overview";
            $lines[] = $companySummary;
            $lines[] = "";
            $lines[] = "## Contact Information";
            $lines[] = "- **Website**: " . url('');
            $lines[] = "- **Email**: {$email}";
            $lines[] = "- **Phone**: {$phone}";
            $lines[] = "- **Studio Address**: {$address}";
            $lines[] = "- **Business Hours**: " . get_setting('site_hours', 'Monday – Friday: 9:00 AM – 6:30 PM IST');
            $lines[] = "";

            // Key Public Pages
            $lines[] = "## Core Website Pages";
            $pages = $db->query("SELECT title, slug, file_name, headline FROM pages WHERE status = 'published' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($pages as $p) {
                $loc = ($p['slug'] === 'home') ? url('') : url($p['file_name']);
                $headline = $p['headline'] ? " - " . $p['headline'] : "";
                $lines[] = "- [{$p['title']}]({$loc}){$headline}";
            }
            $lines[] = "";

            // Commercial Photography Services
            $lines[] = "## Commercial Photography Services";
            $services = $db->query("SELECT title, slug, file_name, short_description FROM services WHERE status = 'published' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($services as $s) {
                $loc = service_url($s['slug']);
                $lines[] = "- [{$s['title']}]({$loc}): {$s['short_description']}";
            }
            $lines[] = "";

            // Recent Articles & Guides (Canonical HTML & Markdown URLs)
            $lines[] = "## Industry Insights & Knowledge Base";
            $blogs = $db->query("SELECT id, slug, title, excerpt FROM blogs WHERE status = 'published' ORDER BY published_at DESC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($blogs as $b) {
                $htmlLoc = blog_url($b['slug']);
                $mdLoc = blog_md_url($b['slug']);
                $lines[] = "Title: " . $b['title'];
                $lines[] = "HTML: " . $htmlLoc;
                $lines[] = "Markdown: " . $mdLoc;
                if (!empty($b['excerpt'])) {
                    $lines[] = "Description: " . $b['excerpt'];
                }
                $lines[] = "";
            }

            $content = implode("\n", $lines);
            $llmsFile = ROOT_PATH . '/llms.txt';
            return self::safeWriteFile($llmsFile, $content);
        } catch (Exception $e) {
            error_log('SEOGenerator LLMs Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate robots.txt
     */
    public static function generateRobotsTxt(): bool {
        $sitemapUrl = url('sitemap.xml');
        $content = "User-agent: *\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /config/\n";
        $content .= "Disallow: /includes/\n";
        $content .= "Disallow: /services/\n";
        $content .= "Disallow: /database/\n";
        $content .= "Allow: /\n\n";
        $content .= "Sitemap: {$sitemapUrl}\n";

        $robotsFile = ROOT_PATH . '/robots.txt';
        return self::safeWriteFile($robotsFile, $content);
    }
}

