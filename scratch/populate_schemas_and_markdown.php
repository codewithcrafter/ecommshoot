<?php
/**
 * Master Script: Populate Schemas and Markdown Documents
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/SchemaManager.php';
require_once __DIR__ . '/../services/MarkdownGenerator.php';

$db = Database::getInstance()->getConnection();

echo "====================================================\n";
echo "1. REGENERATING BLOG MARKDOWN FILES\n";
echo "====================================================\n";
$blogRes = MarkdownGenerator::generateAll();
echo "Blogs generated: {$blogRes['success']}, failed: {$blogRes['failed']}, deleted: {$blogRes['deleted']}\n";

// Also ensure slug-named copies exist in /content/blogs/
$blogs = $db->query("SELECT id, slug, markdown_content FROM blogs WHERE status = 'published'")->fetchAll(PDO::FETCH_ASSOC);
$blogDir = MarkdownGenerator::getContentDir();
foreach ($blogs as $b) {
    $idFile = $blogDir . '/' . $b['id'] . '.md';
    $slugFile = $blogDir . '/' . $b['slug'] . '.md';
    if (file_exists($idFile)) {
        copy($idFile, $slugFile);
        echo "Created slug copy: {$b['slug']}.md\n";
    }
}

echo "\n====================================================\n";
echo "2. GENERATING SERVICES & PAGES MARKDOWN DOCUMENTS\n";
echo "====================================================\n";

$servicesDir = ROOT_PATH . '/content/services';
if (!is_dir($servicesDir)) @mkdir($servicesDir, 0755, true);

$pagesDir = ROOT_PATH . '/content/pages';
if (!is_dir($pagesDir)) @mkdir($pagesDir, 0755, true);

$services = $db->query("SELECT * FROM services WHERE status = 'published' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);

foreach ($services as $s) {
    $faqs = json_decode($s['faqs_json'], true) ?: [];
    $benefits = json_decode($s['benefits_json'], true) ?: [];
    $features = json_decode($s['features_json'], true) ?: [];
    $process = json_decode($s['process_json'], true) ?: [];

    $mdLines = [];
    $mdLines[] = '---';
    $mdLines[] = 'title: ' . json_encode($s['title'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $mdLines[] = 'slug: ' . json_encode($s['slug']);
    $mdLines[] = 'service_type: ' . json_encode($s['badge'] ?: 'Commercial Photography');
    $mdLines[] = 'summary: ' . json_encode($s['short_description'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $mdLines[] = 'canonical_url: ' . json_encode(service_url($s['slug']));
    $mdLines[] = 'featured_image: ' . json_encode(asset_url($s['featured_image']));
    $mdLines[] = '---';
    $mdLines[] = '';
    $mdLines[] = '# ' . $s['h1_heading'];
    $mdLines[] = '';
    $mdLines[] = '> ' . $s['short_description'];
    $mdLines[] = '';
    $mdLines[] = '## Service Overview';
    $mdLines[] = '';
    $mdLines[] = trim($s['introduction']);
    $mdLines[] = '';

    if (!empty($benefits)) {
        $mdLines[] = '## Key Benefits & Commercial Impact';
        $mdLines[] = '';
        foreach ($benefits as $b) {
            $title = $b['title'] ?? $b['name'] ?? '';
            $desc = $b['description'] ?? $b['desc'] ?? '';
            if ($title) {
                $mdLines[] = '### ' . $title;
                if ($desc) $mdLines[] = $desc;
                $mdLines[] = '';
            }
        }
    }

    if (!empty($features)) {
        $mdLines[] = '## Technical Specifications';
        $mdLines[] = '';
        foreach ($features as $f) {
            $name = $f['title'] ?? $f['feature'] ?? $f['name'] ?? '';
            $desc = $f['description'] ?? $f['spec'] ?? $f['value'] ?? '';
            if ($name) {
                $mdLines[] = '- **' . $name . '**: ' . $desc;
            }
        }
        $mdLines[] = '';
    }

    if (!empty($process)) {
        $mdLines[] = '## Production Workflow';
        $mdLines[] = '';
        foreach ($process as $idx => $p) {
            $step = $idx + 1;
            $title = $p['title'] ?? $p['step'] ?? "Phase $step";
            $desc = $p['description'] ?? $p['desc'] ?? '';
            $mdLines[] = "### Step $step: $title";
            if ($desc) $mdLines[] = $desc;
            $mdLines[] = '';
        }
    }

    if (!empty($faqs)) {
        $mdLines[] = '## Frequently Asked Questions';
        $mdLines[] = '';
        foreach ($faqs as $f) {
            if (!empty($f['question']) && !empty($f['answer'])) {
                $mdLines[] = '### ' . trim($f['question']);
                $mdLines[] = trim($f['answer']);
                $mdLines[] = '';
            }
        }
    }

    $mdLines[] = '## Studio Contact & Booking';
    $mdLines[] = 'Ready to schedule a shoot or request custom volume pricing? [Contact our studio team](' . url('contact.php') . ') or email **contact@ecomshoot.com**.';
    $mdLines[] = '';

    $fullMd = implode("\n", $mdLines);

    // Write physical file
    file_put_contents($servicesDir . '/' . $s['slug'] . '.md', $fullMd);

    // Save to markdown_documents table
    $stmt = $db->prepare("SELECT id FROM markdown_documents WHERE slug = :slug LIMIT 1");
    $stmt->execute(['slug' => $s['slug']]);
    $docId = $stmt->fetchColumn();

    if ($docId) {
        $uStmt = $db->prepare("UPDATE markdown_documents SET title = :title, content = :content, status = 'published', related_type = 'service', related_id = :rid WHERE id = :id");
        $uStmt->execute([
            'title' => $s['title'] . ' Service Technical Brief',
            'content' => $fullMd,
            'rid' => $s['id'],
            'id' => $docId
        ]);
        echo "Updated markdown document in DB: {$s['slug']}\n";
    } else {
        $iStmt = $db->prepare("INSERT INTO markdown_documents (title, slug, content, status, related_type, related_id) VALUES (:title, :slug, :content, 'published', 'service', :rid)");
        $iStmt->execute([
            'title' => $s['title'] . ' Service Technical Brief',
            'slug' => $s['slug'],
            'content' => $fullMd,
            'rid' => $s['id']
        ]);
        echo "Inserted markdown document in DB: {$s['slug']}\n";
    }
}

// Generate Markdown for Core Pages (home, about, services, contact)
$corePages = [
    [
        'title' => 'Home Page Overview & Commercial Production Capabilities',
        'slug' => 'home',
        'related_type' => 'page',
        'related_id' => 1,
        'content' => "# Ecomshoot - Visual Assets Engineered to Multiply E-Commerce Conversions\n\n> Premier commercial product photography studio and post-production house serving direct-to-consumer (D2C) brands, high-growth Shopify merchants, Amazon enterprise sellers, and international fashion retailers.\n\n## Studio Core Disciplines\n- **Studio Packshots**: Pure RGB 255,255,255 white background catalog photography passing marketplace compliance on first upload.\n- **Lifestyle Editorial Staging**: Authentic context imagery captured on custom-built indoor and outdoor stage sets.\n- **Apparel & Fashion**: Ghost mannequin 3D hollow-body captures, pin-sharp flat lays, and live-model lookbooks.\n- **Micro-Detailed Jewellery**: 150MP macro focus-stacked imagery rendering diamond scintillation and precious metal finish.\n- **Commercial Post-Production**: Hand-drawn vector clipping paths, frequency separation retouching, and ICC color calibration.\n\n## Studio Facility & Technology\n- **Camera Systems**: Phase One IQ4 150MP Medium Format, Sony Alpha 61MP Full-Frame.\n- **Optics**: Schneider Kreuznach prime lenses, Canon L-Series tilt-shift lenses.\n- **Lighting**: Broncolor digital studio strobes with calibrated color temperature consistency across all flash power outputs.\n- **Turnaround**: Standard 5-7 business days with 48-hour rush options available.\n\n## Booking & Project Initiation\nContact the studio team at [contact@ecomshoot.com](mailto:contact@ecomshoot.com) or book via [Online Intake Form](" . url('contact.php') . ")."
    ],
    [
        'title' => 'About Ecomshoot Studio Philosophy & Production Standards',
        'slug' => 'about',
        'related_type' => 'page',
        'related_id' => 2,
        'content' => "# About Ecomshoot - Artistry Meets E-Commerce Conversion Science\n\n> Founded by seasoned commercial photographers and e-commerce strategists, Ecomshoot bridges the gap between artistic commercial photography and high-conversion catalog assets.\n\n## Our Mission\nTo provide high-growth brands with visual assets that dramatically reduce returns, increase average order value (AOV), and establish undeniable category dominance.\n\n## The Ecomshoot Quality Standard\n1. **Color Accuracy Guarantee**: Every setup utilizes X-Rite ColorChecker charts and custom ICC profiles to ensure online images match physical merchandise 100%.\n2. **Defect-Free Cleanliness**: Products undergo ultrasonic cleaning, anti-static dusting, and fabric steaming prior to being placed before the lens.\n3. **Marketplace Compliance**: 100% compliant with Amazon Seller Central, Walmart Marketplace, Google Merchant Center, and Shopify Plus specifications.\n\n## Facility Specifications\n- 8,500 sq ft dedicated production facility.\n- Climate-controlled intake and storage warehouse.\n- Dedicated prop room, kitchen set, living room set, and drive-in cyclorama.\n\n## Get In Touch\n- **Email**: contact@ecomshoot.com\n- **Studio Address**: Studio 4B, 742 Creative Boulevard, New York, NY 10013"
    ],
    [
        'title' => 'Complete Commercial Photography Services Catalog',
        'slug' => 'services-catalog',
        'related_type' => 'page',
        'related_id' => 3,
        'content' => "# Commercial Product Photography Services Catalog\n\n> Comprehensive visual production solutions tailored for modern digital brands and enterprise retailers.\n\n## Catalog Offerings\n1. [Product Photography](" . service_url('product-photography') . "): Clean, conversion-focused multi-angle captures.\n2. [E-Commerce Product Photography](" . service_url('ecommerce-product-photography') . "): High-volume catalog consistency for Shopify & Amazon.\n3. [Lifestyle Product Photography](" . service_url('lifestyle-product-photography') . "): In-context staging with professional models and props.\n4. [Fashion & Apparel Photography](" . service_url('fashion-product-photography') . "): Ghost mannequin, flat lays, and live-model editorials.\n5. [Creative & Advertising Photography](" . service_url('creative-product-photography') . "): High-speed splash, levitation, and concept campaigns.\n6. [Jewellery & Luxury Photography](" . service_url('jewellery-product-photography') . "): Focus-stacked macro captures for fine jewelry and watches.\n7. [Product Photo Editing & Retouching](" . service_url('product-photo-editing-retouching') . "): High-end post-production, clipping paths, and color matching.\n8. [White Background Photography](" . service_url('white-background-product-photography') . "): Guaranteed RGB 255,255,255 marketplace compliant images.\n\n## Intake & Volume Pricing\nCustom volume discounts apply for batches exceeding 50, 100, and 500 SKUs. Contact studio management for custom rate cards."
    ],
    [
        'title' => 'Studio Intake, Sample Shipping & Production Booking Guidelines',
        'slug' => 'contact-guidelines',
        'related_type' => 'page',
        'related_id' => 5,
        'content' => "# Studio Intake, Sample Shipping & Production Booking Guidelines\n\n> Step-by-step instructions for booking studio production runs and safely dispatching merchandise samples.\n\n## Step 1: Request Rate Card & Production Availability\nSubmit your SKU count, service requirements, and desired delivery timeframe through our [Online Booking Form](" . url('contact.php') . ").\n\n## Step 2: Shipping Sample Merchandise\n- Our intake department provides a pre-addressed shipping tag and unique Production Batch ID.\n- Securely pack samples with protective bubble wrap.\n- Include a printed copy of your Packing Manifest inside the parcel.\n\n## Step 3: Studio Shoot & Real-Time Review\n- Upon parcel arrival, items are logged into secure climate-controlled lockers.\n- You receive a private digital proofing gallery link to review unretouched captures in real-time.\n\n## Step 4: High-Resolution Delivery & Sample Return\n- Master TIFF, high-res JPEG, and web-optimized WebP files delivered via secure cloud link.\n- Samples can be safely returned via your preferred shipping courier or donated per your written authorization."
    ]
];

foreach ($corePages as $cp) {
    file_put_contents($pagesDir . '/' . $cp['slug'] . '.md', $cp['content']);
    $stmt = $db->prepare("SELECT id FROM markdown_documents WHERE slug = :slug LIMIT 1");
    $stmt->execute(['slug' => $cp['slug']]);
    $docId = $stmt->fetchColumn();

    if ($docId) {
        $uStmt = $db->prepare("UPDATE markdown_documents SET title = :title, content = :content, status = 'published', related_type = :rtype, related_id = :rid WHERE id = :id");
        $uStmt->execute([
            'title' => $cp['title'],
            'content' => $cp['content'],
            'rtype' => $cp['related_type'],
            'rid' => $cp['related_id'],
            'id' => $docId
        ]);
        echo "Updated page markdown document in DB: {$cp['slug']}\n";
    } else {
        $iStmt = $db->prepare("INSERT INTO markdown_documents (title, slug, content, status, related_type, related_id) VALUES (:title, :slug, :content, 'published', :rtype, :rid)");
        $iStmt->execute([
            'title' => $cp['title'],
            'slug' => $cp['slug'],
            'content' => $cp['content'],
            'rtype' => $cp['related_type'],
            'rid' => $cp['related_id']
        ]);
        echo "Inserted page markdown document in DB: {$cp['slug']}\n";
    }
}

echo "\n====================================================\n";
echo "3. BUILDING RELEVANT JSON-LD SCHEMAS FOR ALL PAGES\n";
echo "====================================================\n";

$schemasToInsert = [];

// 1. Home LocalBusiness / PhotographyStudio
$schemasToInsert[] = [
    'page_identifier' => 'home',
    'schema_type' => 'PhotographyStudio',
    'schema_json' => json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'PhotographyStudio',
        'name' => 'Ecomshoot - Commercial Product Photography Studio',
        'url' => 'http://localhost:8000',
        'logo' => 'http://localhost:8000/assets/images/logo.png',
        'image' => 'http://localhost:8000/assets/images/portfolio/portfolio-1.jpg',
        'description' => 'Premier commercial product photography studio and post-production house serving direct-to-consumer (D2C) brands, high-growth Shopify merchants, Amazon enterprise sellers, and international fashion retailers.',
        'telephone' => '+1-800-555-7466',
        'email' => 'contact@ecomshoot.com',
        'priceRange' => '$$',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Studio 4B, 742 Creative Boulevard',
            'addressLocality' => 'New York',
            'addressRegion' => 'NY',
            'postalCode' => '10013',
            'addressCountry' => 'US'
        ],
        'openingHoursSpecification' => [
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'opens' => '09:00',
                'closes' => '18:30'
            ]
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
];

// 2. About Page Schemas
$schemasToInsert[] = [
    'page_identifier' => 'about',
    'schema_type' => 'AboutPage',
    'schema_json' => json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'AboutPage',
        'name' => 'About Ecomshoot | Commercial Photography Studio',
        'url' => 'http://localhost:8000/about',
        'description' => 'Learn how Ecomshoot combines commercial photography artistry with e-commerce conversion science to produce revenue-multiplying visual assets.',
        'mainEntity' => [
            '@type' => 'PhotographyStudio',
            'name' => 'Ecomshoot',
            'url' => 'http://localhost:8000',
            'foundingDate' => '2019',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Studio 4B, 742 Creative Boulevard',
                'addressLocality' => 'New York',
                'addressRegion' => 'NY',
                'postalCode' => '10013',
                'addressCountry' => 'US'
            ]
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
];

$schemasToInsert[] = [
    'page_identifier' => 'about',
    'schema_type' => 'BreadcrumbList',
    'schema_json' => json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => 'http://localhost:8000'
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'About Us',
                'item' => 'http://localhost:8000/about'
            ]
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
];

// 3. Services Catalog Schemas
$schemasToInsert[] = [
    'page_identifier' => 'services',
    'schema_type' => 'CollectionPage',
    'schema_json' => json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => 'Commercial Photography Services Catalog | Ecomshoot',
        'url' => 'http://localhost:8000/services',
        'description' => 'Complete catalog of professional commercial photography services: white background packshots, editorial lifestyle staging, apparel ghost mannequin, jewellery macro, and high-end retouching.',
        'mainEntity' => [
            '@type' => 'OfferCatalog',
            'name' => 'Ecomshoot Commercial Photography Services',
            'itemListElement' => array_map(function($svc) {
                return [
                    '@type' => 'Offer',
                    'itemOffered' => [
                        '@type' => 'Service',
                        'name' => $svc['title'],
                        'description' => $svc['short_description'],
                        'url' => 'http://localhost:8000/service/' . $svc['slug']
                    ]
                ];
            }, $services)
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
];

$schemasToInsert[] = [
    'page_identifier' => 'services',
    'schema_type' => 'BreadcrumbList',
    'schema_json' => json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => 'http://localhost:8000'
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Services',
                'item' => 'http://localhost:8000/services'
            ]
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
];

// 4. Blog Catalog Schemas
$schemasToInsert[] = [
    'page_identifier' => 'blog',
    'schema_type' => 'Blog',
    'schema_json' => json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Blog',
        'name' => 'Ecomshoot Insights & Photography Journal',
        'url' => 'http://localhost:8000/blog',
        'description' => 'Expert perspectives, studio lighting guides, marketplace compliance updates, and conversion optimization strategies for digital brands.',
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Ecomshoot',
            'url' => 'http://localhost:8000'
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
];

$schemasToInsert[] = [
    'page_identifier' => 'blog',
    'schema_type' => 'BreadcrumbList',
    'schema_json' => json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => 'http://localhost:8000'
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Insights & Studio Journal',
                'item' => 'http://localhost:8000/blog'
            ]
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
];

// 5. Contact Page Schemas
$schemasToInsert[] = [
    'page_identifier' => 'contact',
    'schema_type' => 'ContactPage',
    'schema_json' => json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'ContactPage',
        'name' => 'Contact Ecomshoot Studio | Book a Shoot & Request a Quote',
        'url' => 'http://localhost:8000/contact',
        'description' => 'Get in touch with Ecomshoot studio directors to schedule a commercial product photography shoot, request custom rate cards, or arrange sample delivery.',
        'mainEntity' => [
            '@type' => 'PhotographyStudio',
            'name' => 'Ecomshoot',
            'url' => 'http://localhost:8000',
            'telephone' => '+1-800-555-7466',
            'email' => 'contact@ecomshoot.com',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Studio 4B, 742 Creative Boulevard',
                'addressLocality' => 'New York',
                'addressRegion' => 'NY',
                'postalCode' => '10013',
                'addressCountry' => 'US'
            ]
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
];

$schemasToInsert[] = [
    'page_identifier' => 'contact',
    'schema_type' => 'BreadcrumbList',
    'schema_json' => json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => 'http://localhost:8000'
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Contact Studio',
                'item' => 'http://localhost:8000/contact'
            ]
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
];

// 6. Schemas for each individual Service
foreach ($services as $svc) {
    $slug = $svc['slug'];
    $title = $svc['title'];
    $desc = $svc['short_description'];
    $faqs = json_decode($svc['faqs_json'], true) ?: [];

    // Service Schema (add for services that do not already have it)
    $schemasToInsert[] = [
        'page_identifier' => $slug,
        'schema_type' => 'Service',
        'schema_json' => json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'serviceType' => $title,
            'name' => $title . ' - Ecomshoot Commercial Photography',
            'provider' => [
                '@type' => 'Organization',
                'name' => 'Ecomshoot',
                'url' => 'http://localhost:8000'
            ],
            'url' => 'http://localhost:8000/service/' . $slug,
            'description' => $desc,
            'areaServed' => 'Global',
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => $title . ' Packages',
                'itemListElement' => [
                    [
                        '@type' => 'Offer',
                        'itemOffered' => [
                            '@type' => 'Service',
                            'name' => 'Standard ' . $title,
                            'description' => $desc
                        ]
                    ]
                ]
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    ];

    // FAQPage Schema
    if (!empty($faqs)) {
        $mainEntities = [];
        foreach ($faqs as $f) {
            if (!empty($f['question']) && !empty($f['answer'])) {
                $mainEntities[] = [
                    '@type' => 'Question',
                    'name' => trim($f['question']),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => trim($f['answer'])
                    ]
                ];
            }
        }

        if (!empty($mainEntities)) {
            $schemasToInsert[] = [
                'page_identifier' => $slug,
                'schema_type' => 'FAQPage',
                'schema_json' => json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => $mainEntities
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ];
        }
    }

    // BreadcrumbList Schema
    $schemasToInsert[] = [
        'page_identifier' => $slug,
        'schema_type' => 'BreadcrumbList',
        'schema_json' => json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => 'http://localhost:8000'
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Services',
                    'item' => 'http://localhost:8000/services'
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $title,
                    'item' => 'http://localhost:8000/service/' . $slug
                ]
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    ];
}

// Now validate and insert/update all schemas in DB
$inserted = 0;
$updated = 0;
foreach ($schemasToInsert as $item) {
    // Validate JSON first
    $validation = SchemaManager::validateJSON($item['schema_json']);
    if (!$validation['valid']) {
        echo "VALIDATION FAILED for {$item['page_identifier']} ({$item['schema_type']}): {$validation['error']}\n";
        continue;
    }

    // Check if exists
    $checkStmt = $db->prepare("SELECT id FROM `schemas` WHERE page_identifier = :page AND schema_type = :stype LIMIT 1");
    $checkStmt->execute([
        'page' => $item['page_identifier'],
        'stype' => $item['schema_type']
    ]);
    $existingId = $checkStmt->fetchColumn();

    if ($existingId) {
        $uStmt = $db->prepare("UPDATE `schemas` SET schema_json = :json, is_active = 1 WHERE id = :id");
        $uStmt->execute([
            'json' => $item['schema_json'],
            'id' => $existingId
        ]);
        $updated++;
    } else {
        $iStmt = $db->prepare("INSERT INTO `schemas` (page_identifier, schema_type, schema_json, is_active) VALUES (:page, :stype, :json, 1)");
        $iStmt->execute([
            'page' => $item['page_identifier'],
            'stype' => $item['schema_type'],
            'json' => $item['schema_json']
        ]);
        $inserted++;
    }
}

echo "Schemas inserted: $inserted, updated: $updated\n";

$totalActive = $db->query("SELECT count(*) FROM `schemas` WHERE is_active = 1")->fetchColumn();
echo "Total Active Schemas in DB: $totalActive\n";

