<?php
/**
 * ECOMSHOOT - SEO Metadata Management & Rendering Engine
 */

class SEO {
    private static ?array $cachedMetadata = null;

    public static function getMetadata(string $pageIdentifier): array {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM seo_metadata WHERE page_identifier = :id LIMIT 1");
            $stmt->execute(['id' => $pageIdentifier]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                return $data;
            }
        } catch (Exception $e) {
            // fallback if database query fails
        }

        // Return sensible defaults
        $siteName = get_setting('site_name', 'Ecomshoot');
        $siteTagline = get_setting('site_tagline', 'Premium E-Commerce Product Photography & Creative Production Agency');

        return [
            'meta_title' => $siteName . ' | ' . $siteTagline,
            'meta_description' => 'Ecomshoot produces studio-grade commercial product photography, pure white packshots, and immersive lifestyle visual campaigns engineered for Shopify and Amazon brands.',
            'canonical_url' => url($pageIdentifier === 'home' ? '' : $pageIdentifier . '.php'),
            'og_title' => $siteName . ' | ' . $siteTagline,
            'og_description' => 'Studio-grade commercial product photography engineered for Shopify, Amazon, and luxury retail brands.',
            'og_image' => asset_url('assets/images/portfolio/portfolio-1.jpg'),
            'twitter_title' => $siteName . ' | ' . $siteTagline,
            'twitter_description' => 'Studio-grade commercial product photography engineered for Shopify, Amazon, and luxury retail brands.',
            'twitter_image' => asset_url('assets/images/portfolio/portfolio-1.jpg'),
            'robots_meta' => 'index, follow'
        ];
    }

    public static function render(string $pageIdentifier, array $customOverrides = []): string {
        $meta = self::getMetadata($pageIdentifier);
        $meta = array_merge($meta, $customOverrides);

        $html = [];
        $html[] = '<!-- SEO Metadata Engine -->';
        $html[] = '<title>' . e($meta['meta_title']) . '</title>';
        $html[] = '<meta name="description" content="' . e($meta['meta_description']) . '">';
        $html[] = '<meta name="robots" content="' . e($meta['robots_meta'] ?? 'index, follow') . '">';

        $canonical = $meta['canonical_url'] ?? url();
        $html[] = '<link rel="canonical" href="' . e($canonical) . '">';

        // Open Graph
        $html[] = '<meta property="og:site_name" content="' . e(get_setting('site_name', 'Ecomshoot')) . '">';
        $html[] = '<meta property="og:type" content="website">';
        $html[] = '<meta property="og:title" content="' . e($meta['og_title'] ?: $meta['meta_title']) . '">';
        $html[] = '<meta property="og:description" content="' . e($meta['og_description'] ?: $meta['meta_description']) . '">';
        $html[] = '<meta property="og:url" content="' . e($canonical) . '">';

        $ogImage = $meta['og_image'] ?? '';
        if ($ogImage && !str_starts_with($ogImage, 'http')) {
            $ogImage = asset_url($ogImage);
        }
        if ($ogImage) {
            $html[] = '<meta property="og:image" content="' . e($ogImage) . '">';
        }

        // Twitter Card
        $html[] = '<meta name="twitter:card" content="summary_large_image">';
        $html[] = '<meta name="twitter:title" content="' . e($meta['twitter_title'] ?: $meta['meta_title']) . '">';
        $html[] = '<meta name="twitter:description" content="' . e($meta['twitter_description'] ?: $meta['meta_description']) . '">';
        $twImage = $meta['twitter_image'] ?: $ogImage;
        if ($twImage && !str_starts_with($twImage, 'http')) {
            $twImage = asset_url($twImage);
        }
        if ($twImage) {
            $html[] = '<meta name="twitter:image" content="' . e($twImage) . '">';
        }

        return implode("\n    ", $html);
    }
}

