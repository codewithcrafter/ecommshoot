<?php
/**
 * ECOMSHOOT - Schema Management System & JSON-LD Head Renderer
 */

class SchemaManager {

    /**
     * Validate JSON string syntax
     */
    public static function validateJSON(string $jsonString): array {
        $trimmed = trim($jsonString);
        if (empty($trimmed)) {
            return ['valid' => false, 'error' => 'Schema JSON cannot be empty.'];
        }

        $decoded = json_decode($trimmed, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['valid' => false, 'error' => 'Invalid JSON syntax: ' . json_last_error_msg()];
        }

        if (!is_array($decoded)) {
            return ['valid' => false, 'error' => 'Schema must be a valid JSON object.'];
        }

        if (empty($decoded['@context'])) {
            return ['valid' => false, 'error' => 'Schema is missing required "@context" property (usually "https://schema.org").'];
        }

        if (empty($decoded['@type'])) {
            return ['valid' => false, 'error' => 'Schema is missing required "@type" property.'];
        }

        return ['valid' => true, 'data' => $decoded];
    }

    /**
     * Retrieve all active schemas for a given page identifier + global 'all' schemas
     */
    public static function getSchemasForPage(string $pageIdentifier): array {
        $schemas = [];
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                "SELECT * FROM `schemas` 
                 WHERE is_active = 1 
                   AND (page_identifier = :page_id OR page_identifier = 'all')
                 ORDER BY id ASC"
            );
            $stmt->execute(['page_id' => $pageIdentifier]);
            $schemas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('SchemaManager Error: ' . $e->getMessage());
        }
        return $schemas;
    }

    /**
     * Render JSON-LD script blocks into the HTML head section safely
     */
    public static function renderHeadSchemas(string $pageIdentifier, array $dynamicSchemas = []): string {
        $storedSchemas = self::getSchemasForPage($pageIdentifier);
        $output = [];
        $output[] = '<!-- Independent JSON-LD Schema Architecture -->';

        // 1. Output stored schemas from database
        foreach ($storedSchemas as $item) {
            $json = trim($item['schema_json']);
            // Replace placeholder URL if needed
            $json = str_replace('http://localhost:8000', BASE_URL, $json);
            $decoded = json_decode($json, true);
            if ($decoded) {
                $pretty = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $output[] = '<script type="application/ld+json">' . "\n" . $pretty . "\n" . '</script>';
            }
        }

        // 2. Output dynamic schemas passed in (e.g. from service or blog views)
        foreach ($dynamicSchemas as $dyn) {
            if (is_array($dyn)) {
                $pretty = json_encode($dyn, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $output[] = '<script type="application/ld+json">' . "\n" . $pretty . "\n" . '</script>';
            } elseif (is_string($dyn)) {
                $decoded = json_decode($dyn, true);
                if ($decoded) {
                    $pretty = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    $output[] = '<script type="application/ld+json">' . "\n" . $pretty . "\n" . '</script>';
                }
            }
        }

        return implode("\n    ", $output);
    }
}

