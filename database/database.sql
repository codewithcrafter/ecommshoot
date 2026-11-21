-- ============================================================================
-- ECOMSHOOT DATABASE SCHEMA & SEED DATA
-- Professional E-Commerce Product Photography & Creative Production Agency
-- ============================================================================

-- Drop existing tables to ensure clean initialization
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `blog_tag_relations`;
DROP TABLE IF EXISTS `blog_tags`;
DROP TABLE IF EXISTS `blogs`;
DROP TABLE IF EXISTS `blog_categories`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `pages`;
DROP TABLE IF EXISTS `schemas`;
DROP TABLE IF EXISTS `seo_metadata`;
DROP TABLE IF EXISTS `media`;
DROP TABLE IF EXISTS `markdown_documents`;
DROP TABLE IF EXISTS `contact_submissions`;
DROP TABLE IF EXISTS `website_settings`;
DROP TABLE IF EXISTS `admins`;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- Table: admins
-- ----------------------------------------------------------------------------
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'editor') NOT NULL DEFAULT 'admin',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `last_login_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: pages
-- ----------------------------------------------------------------------------
CREATE TABLE `pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `file_name` VARCHAR(100) NOT NULL,
  `headline` VARCHAR(255) NULL,
  `subheadline` TEXT NULL,
  `content` LONGTEXT NULL,
  `status` ENUM('published', 'draft') DEFAULT 'published',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: services
-- ----------------------------------------------------------------------------
CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `file_name` VARCHAR(100) NOT NULL,
  `h1_heading` VARCHAR(255) NOT NULL,
  `short_description` TEXT NOT NULL,
  `introduction` LONGTEXT NOT NULL,
  `benefits_json` LONGTEXT NULL,
  `features_json` LONGTEXT NULL,
  `industries_json` LONGTEXT NULL,
  `process_json` LONGTEXT NULL,
  `faqs_json` LONGTEXT NULL,
  `related_slugs` VARCHAR(255) NULL,
  `featured_image` VARCHAR(255) NULL,
  `badge` VARCHAR(50) DEFAULT 'Popular',
  `display_order` INT DEFAULT 0,
  `status` ENUM('published', 'draft') DEFAULT 'published',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: blog_categories
-- ----------------------------------------------------------------------------
CREATE TABLE `blog_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: blogs
-- ----------------------------------------------------------------------------
CREATE TABLE `blogs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `excerpt` TEXT NOT NULL,
  `content` LONGTEXT NOT NULL,
  `content_format` ENUM('markdown', 'html') DEFAULT 'markdown',
  `featured_image` VARCHAR(255) NULL,
  `author_name` VARCHAR(100) NOT NULL DEFAULT 'Ecomshoot Creative Studio',
  `author_role` VARCHAR(100) DEFAULT 'Commercial Photography Director',
  `status` ENUM('published', 'draft') DEFAULT 'published',
  `faqs_json` LONGTEXT NULL,
  `internal_links_json` LONGTEXT NULL,
  `read_time` VARCHAR(20) DEFAULT '5 min read',
  `published_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_blogs_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: blog_tags
-- ----------------------------------------------------------------------------
CREATE TABLE `blog_tags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `slug` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: blog_tag_relations
-- ----------------------------------------------------------------------------
CREATE TABLE `blog_tag_relations` (
  `blog_id` INT NOT NULL,
  `tag_id` INT NOT NULL,
  PRIMARY KEY (`blog_id`, `tag_id`),
  CONSTRAINT `fk_btr_blog` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_btr_tag` FOREIGN KEY (`tag_id`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: media
-- ----------------------------------------------------------------------------
CREATE TABLE `media` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `file_name` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_size` INT NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `alt_text` VARCHAR(255) NULL,
  `caption` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: seo_metadata
-- ----------------------------------------------------------------------------
CREATE TABLE `seo_metadata` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `page_identifier` VARCHAR(100) NOT NULL UNIQUE,
  `meta_title` VARCHAR(255) NOT NULL,
  `meta_description` TEXT NOT NULL,
  `canonical_url` VARCHAR(255) NULL,
  `og_title` VARCHAR(255) NULL,
  `og_description` TEXT NULL,
  `og_image` VARCHAR(255) NULL,
  `twitter_title` VARCHAR(255) NULL,
  `twitter_description` TEXT NULL,
  `twitter_image` VARCHAR(255) NULL,
  `robots_meta` VARCHAR(50) DEFAULT 'index, follow',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: schemas
-- ----------------------------------------------------------------------------
CREATE TABLE `schemas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `schema_type` VARCHAR(100) NOT NULL,
  `page_identifier` VARCHAR(100) NOT NULL DEFAULT 'all',
  `schema_json` LONGTEXT NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: markdown_documents
-- ----------------------------------------------------------------------------
CREATE TABLE `markdown_documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `content` LONGTEXT NOT NULL,
  `status` ENUM('draft', 'published') DEFAULT 'draft',
  `related_type` VARCHAR(50) DEFAULT 'blog',
  `related_id` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: contact_submissions
-- ----------------------------------------------------------------------------
CREATE TABLE `contact_submissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `company_name` VARCHAR(150) NULL,
  `service_required` VARCHAR(150) NOT NULL,
  `message` LONGTEXT NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: website_settings
-- ----------------------------------------------------------------------------
CREATE TABLE `website_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` LONGTEXT NULL,
  `setting_group` VARCHAR(50) DEFAULT 'general',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- Admins
INSERT INTO `admins` (`id`, `name`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'Lead Administrator', 'admin@ecomshoot.com', '$2y$12$UnD1oS9O1MRx.iHJkU5zDe2PTyrYSgBjS3dNhUFXpds7RGPZEdzPe', 'admin', 'active'),
(2, 'Creative Editor', 'editor@ecomshoot.com', '$2y$12$UnD1oS9O1MRx.iHJkU5zDe2PTyrYSgBjS3dNhUFXpds7RGPZEdzPe', 'editor', 'active');

-- Website Settings
INSERT INTO `website_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'Ecomshoot', 'general'),
('site_tagline', 'Premium E-Commerce Product Photography & Creative Production Agency', 'general'),
('site_email', 'contact@ecomshoots.in', 'contact'),
('site_phone', '+1 (800) 555-SHOOT', 'contact'),
('site_address', 'Studio 4B, 742 Creative Boulevard, New York, NY 10013', 'contact'),
('site_hours', 'Monday – Friday: 9:00 AM – 6:30 PM IST', 'contact'),
('site_base_url', 'https://www.ecomshoots.in', 'general'),
('social_instagram', 'https://instagram.com/ecomshoot', 'social'),
('social_linkedin', 'https://linkedin.com/company/ecomshoot', 'social'),
('social_pinterest', 'https://pinterest.com/ecomshoot', 'social'),
('social_youtube', 'https://youtube.com/@ecomshoot', 'social'),
('llms_company_summary', 'Ecomshoot is a premier commercial product photography studio and post-production house serving direct-to-consumer (D2C) brands, high-growth Shopify merchants, Amazon enterprise sellers, and international fashion retailers. We specialize in studio-grade white background catalog photography, lifestyle editorial staging, high-fashion apparel model shoots, micro-detailed luxury jewellery imagery, 360-degree interactive product spins, and pixel-precise commercial retouching.', 'seo');

-- Pages
INSERT INTO `pages` (`id`, `title`, `slug`, `file_name`, `headline`, `subheadline`, `content`, `status`) VALUES
(1, 'Home', 'home', 'index.php', 'Visual Assets Engineered to Multiply E-Commerce Conversions', 'We photograph, direct, and retouch high-converting product imagery that elevates brand perception across Shopify, Amazon, retail channels, and paid acquisition.', 'Welcome to Ecomshoot. We operate state-of-the-art commercial production facilities outfitted with medium-format digital capture systems, calibrated Broncolor strobe arrays, motorized turners, and in-house luxury stylists.', 'published'),
(2, 'About Us', 'about', 'about.php', 'Artistry Meets E-Commerce Conversion Science', 'Founded by seasoned commercial photographers and e-commerce growth operators, Ecomshoot bridges pure aesthetic refinement with data-backed marketplace performance.', 'At Ecomshoot, we treat every SKU as an investment asset. Our team has shot and delivered over 250,000 commercial product images for high-growth direct-to-consumer brands and global lifestyle powerhouses.', 'published'),
(3, 'Our Services', 'services', 'services.php', 'Complete Visual Production Solutions for Modern Brands', 'From compliant Amazon white-background packshots to immersive seasonal lifestyle campaign shoots, explore our specialized commercial photography services.', 'We offer end-to-end studio production, art direction, prop fabrication, professional model casting, high-precision lighting setups, and pixel-accurate retouching under one unified workflow.', 'published'),
(4, 'Insights & Studio Journal', 'blog', 'blog.php', 'E-Commerce Photography Intelligence & Visual Strategy', 'Expert perspectives, technical studio breakdowns, lighting masterclasses, and marketplace compliance guides written by our production leads.', 'Discover actionable insights on improving your catalog click-through rates, optimizing return-on-ad-spend with creative assets, and adhering to strict retailer specifications.', 'published'),
(5, 'Contact Studio', 'contact', 'contact.php', 'Start Your Production with Ecomshoot', 'Book your dedicated studio shoot, request a custom volume rate card, or send sample products for a complimentary test shot session.', 'Our production coordinators respond within 24 business hours with detailed project scopes, timelines, sample shot lists, and transparent production pricing.', 'published');

-- ----------------------------------------------------------------------------
-- SEED SERVICES (All 9 Dedicated Services)
-- ----------------------------------------------------------------------------

INSERT INTO `services` (`id`, `title`, `slug`, `file_name`, `h1_heading`, `short_description`, `introduction`, `benefits_json`, `features_json`, `industries_json`, `process_json`, `faqs_json`, `related_slugs`, `featured_image`, `badge`, `display_order`, `status`) VALUES
(1, 'Product Photography', 'product-photography', 'product-photography.php',
'Commercial Product Photography Services Engineered for Maximum Conversion',
'High-definition commercial product photography that captures physical textures, authentic color tonality, and structural form to turn casual store visitors into loyal buyers.',
'Product photography at Ecomshoot is built around commercial conversion science. When prospective buyers browse online, their perception of product quality, durability, and brand prestige is dictated almost entirely by visual fidelity. Our purpose-built commercial studios combine Phase One 150MP digital backs, calibrated Broncolor Para parabolic reflectors, and precision motorized turntables to capture every angle with tactile realism.',
'[{"title":"38% Higher Conversion Rate","description":"Listings with ultra-high resolution, multi-angle imagery consistently outperform standard catalog shots in checkout conversion.","icon":"trending-up"},{"title":"Drastic Return Reduction","description":"True-to-life color calibration and millimeter-accurate texture rendering eliminate customer surprises and lower costly product returns.","icon":"shield-check"},{"title":"Omnichannel Asset Delivery","description":"Receive pixel-perfect exports pre-formatted for Shopify, Amazon main image compliance, Instagram shoppable ads, and print collateral.","icon":"layers"}]',
'[{"title":"100+ Megapixel Medium Format Capture","description":"Every shot delivers incredible crop latitude and microscopic detail resolution for pinch-to-zoom capabilities."},{"title":"Calibrated Color Profiles","description":"Hardware-calibrated workflow with X-Rite ColorChecker charts guaranteeing 99.8% spectral color accuracy."},{"title":"Comprehensive Angle Sequences","description":"Standard 7-angle coverage including primary hero, 45-degree isometric, rear packaging, bottom base, and critical close-ups."},{"title":"Multi-Layer Drop Shadows","description":"Natural drop shadows and reflective surface treatment meticulously crafted during in-house digital post-production."}]',
'[{"name":"Consumer Electronics & Gadgets","description":"Matte finishes, metallic surfaces, OLED displays, and port ergonomics photographed without specular glare."},{"name":"Cosmetics & Skincare","description":"Glass bottles, iridescent formulas, droplet textures, and apothecary packaging captured with pristine soft lighting."},{"name":"Home Goods & Kitchenware","description":"Stainless steel, ceramics, and textiles captured with balanced fill light to emphasize material warmth and craft."},{"name":"Packaged Goods (CPG)","description":"Label legibility, nutrition panel sharpness, and structural carton rigidity aligned with retail inventory standards."}]',
'[{"step":"01","title":"Product Ingestion & Spec Audit","description":"Products arrive at our secure studio, inspected under 5000K daylight bulbs, logged into inventory, and steamed/prepped."},{"step":"02","title":"Art Direction & Lighting Architecture","description":"Our master lighting technicians construct customized light modifiers tailored specifically to your product reflectivity."},{"step":"03","title":"Tethered Capture & Live Proofing","description":"Shooting tethered to Capture One Pro with live client web proofing links for instant real-time feedback and shot sign-off."},{"step":"04","title":"Master Retouching & Compliance QA","description":"Dust removal, scratch elimination, color correction against physical references, and standardized web optimization."}]',
'[{"question":"What camera systems and lighting do you employ?","answer":"We shoot predominantly on Phase One IQ4 150MP medium format systems and Sony Alpha 61MP bodies paired with prime tilt-shift lenses and Broncolor studio strobes."},{"question":"How do we ship products to your studio?","answer":"Once you book a production run, our logistics team issues pre-addressed shipping tags and an intake packing manifest. We store items in climate-controlled lockers and safely return or donate them upon project completion."},{"question":"What turnaround times can we expect?","answer":"Standard studio turnaround is 5 to 7 business days from receipt of goods. Expedited 48-hour turnarounds are available for critical launch windows."},{"question":"Do you provide Amazon compliant white background images?","answer":"Yes, all hero assets can be delivered on pure sRGB #FFFFFF white background with 85%+ product frame occupancy matching Amazon, Walmart, and Target specifications."}]',
'ecommerce-product-photography,white-background-product-photography,product-photo-editing-retouching',
'assets/images/services/product-photography.jpg', 'Core Service', 1, 'published'),

(2, 'E-Commerce Product Photography', 'ecommerce-product-photography', 'ecommerce-product-photography.php',
'High-Volume E-Commerce Product Photography for High-Velocity Digital Brands',
'Scalable, catalog-ready product photography optimized for Shopify Plus merchants, Amazon FBA power sellers, and multi-channel retailers requiring uncompromising consistency.',
'Running a high-velocity e-commerce operation requires more than just pretty pictures—it demands absolute lighting consistency across thousands of SKUs, predictable delivery schedules, and assets formatted specifically for modern digital retail engines. Ecomshoot delivers enterprise-level catalog photography workflows that allow fast-moving consumer brands to launch hundreds of product variations without compromising brand aesthetic or visual cohesion.',
'[{"title":"Unwavering Catalog Uniformity","description":"Exact lighting angles, camera heights, and perspective ratios maintained across thousands of SKUs for flawless category pages.","icon":"grid"},{"title":"Marketplace Compliance Guarantee","description":"100% compliant with Amazon Vendor Central, Walmart Marketplace, Google Shopping, and eBay merchant guidelines.","icon":"check-circle"},{"title":"Rapid SKU Turnaround","description":"Streamlined production pipelines capable of shooting and retouching up to 500 SKUs per week without quality degradation.","icon":"zap"}]',
'[{"title":"Automated Angle Matching","description":"Precision optical calibration rigs ensure every shoe, bottle, or box sits at the identical focal tilt on your category grid."},{"title":"Batch Color Normalization","description":"Digital master palettes matched to pantone codes so colorway variations display with absolute harmony side-by-side."},{"title":"Multi-Format Web Exports","description":"Optimized WebP, high-res JPEG, and transparent PNG assets with custom naming conventions keyed directly to your store SKU codes."},{"title":"Cloud Proofing Portal","description":"Self-serve brand dashboard where your merchandising team can annotate, approve, and download assets 24/7."}]',
'[{"name":"Fashion & Footwear Retailers","description":"Consistent front, back, profile, and sole perspectives across diverse footwear silhouettes and apparel lines."},{"name":"Supplements & Nutraceuticals","description":"Pill bottles, tubs, blister packs, and ingredient badges shot with crisp label typography and premium metallic caps."},{"name":"Consumer Hardware & Tools","description":"Durable tools and technical appliances captured from functional angles showcasing ergonomic grips and control interfaces."},{"name":"Subscription Box Brands","description":"Unboxing flat lays, bundled product kits, and hero collection shots designed to maximize perceived subscription value."}]',
'[{"step":"01","title":"SKU Matrix & Style Guide Alignment","description":"We ingest your master product spreadsheet, naming conventions, and brand visual guidelines into our ERP production board."},{"step":"02","title":"Assembly-Line Studio Staging","description":"Products move through specialized stations: unboxing, lint removal, ultrasonic cleaning, staging, and precision mounting."},{"step":"03","title":"Calibrated Batch Photography","description":"Rig-locked camera axes guarantee that whether SKU #1 or SKU #500 is in the bay, framing and lighting match precisely."},{"step":"04","title":"Automated Delivery & Cloud Ingestion","description":"Assets are formatted, renamed to your exact SKU parameters, compressed for instant web loading, and uploaded to your cloud storage."}]',
'[{"question":"How do you ensure lighting remains identical when we send products months apart?","answer":"Every client has a proprietary Digital Lighting Recipe document recording exact strobe power settings, light modifier distances, focal lengths, camera heights, and color balance profiles."},{"question":"Can you handle hundreds of color variations?","answer":"Yes, our production facility features automated batch workflows designed specifically for high-count SKU matrices and seasonal collection drops."},{"question":"Do you deliver files with custom SKU filenames?","answer":"Yes! Simply provide your SKU spreadsheet, and our asset management script renames all final renders to your exact specification (e.g., SKU_01_HERO.webp, SKU_02_ANGLE.webp)."},{"question":"Are these photos ready for Shopify and Amazon?","answer":"Every deliverable is pre-scaled to square 2000x2000px or 3000x3000px at 72DPI with sRGB color space, ready for immediate platform upload."}]',
'product-photography,white-background-product-photography,lifestyle-product-photography',
'assets/images/services/ecommerce-product-photography.jpg', 'High Volume', 2, 'published'),

(3, 'Lifestyle Product Photography', 'lifestyle-product-photography', 'lifestyle-product-photography.php',
'Contextual Lifestyle Product Photography That Evokes Emotion and Ignites Aspiration',
'Immersive in-context photography that showcases your product in real-world environments, connecting deeply with target demographics and skyrocketing social engagement.',
'While clean studio shots prove what your product looks like, lifestyle photography reveals what life looks like with your product. Consumers do not just buy physical objects; they buy identities, aesthetics, and aspirations. Ecomshoot creates compelling contextual lifestyle imagery that places your merchandise into tastefully curated architectural interiors, vibrant outdoor vistas, and human-centric daily rituals.',
'[{"title":"Emotional Brand Resonance","description":"Create narrative scenes that establish an instant emotional bond between your audience and your brand universe.","icon":"heart"},{"title":"Dramatic Social & Ad Performance","description":"Lifestyle imagery consistently drives 2.4x higher click-through rates on Meta ads, TikTok Spark ads, and Pinterest feeds.","icon":"bar-chart"},{"title":"Contextual Scale & Ergonomics","description":"Naturally communicate product size, everyday utility, and tactile handling in realistic, relatable environments.","icon":"eye"}]',
'[{"title":"Architectural Studio Sets & Locations","description":"Access to luxury residential loft sets, Scandinavian kitchens, brutalist bathrooms, and curated outdoor shooting locations."},{"title":"Professional Prop Styling","description":"In-house prop stylists who source high-end botanicals, ceramics, vintage textures, and artisanal accents that reinforce brand prestige."},{"title":"Talent Casting & Model Coordination","description":"Full casting management for hand models, lifestyle talent, and micro-influencers with comprehensive commercial usage buyouts."},{"title":"Natural Light Simulation","description":"Specialized high-CRI continuous lighting rigs that mimic warm golden hour sunshine or crisp morning daylight with total repeatability."}]',
'[{"name":"Wellness & Self-Care","description":"Bath soak rituals, aromatherapy diffusers, and meditation accessories staged in serene, spa-inspired sanctuary settings."},{"name":"Specialty Food & Beverage","description":"Cocktail mixology, pour shots, cutting board vignettes, and morning coffee rituals bathed in authentic culinary atmosphere."},{"name":"Luxury Bedding & Home Living","description":"Plush draped linens, layered bedroom lighting, and modern coffee table displays conveying sensory comfort."},{"name":"Pet Accessories & Care","description":"Modern dog beds, leather leashes, and organic pet nutrition staged in stylish, modern canine-friendly homes."}]',
'[{"step":"01","title":"Creative Concept & Moodboarding","description":"We collaborate with your marketing team to establish visual narratives, color palettes, prop styling references, and talent requirements."},{"step":"02","title":"Set Construction & Prop Sourcing","description":"Our set designers curate authentic interior spaces, tactile surfaces (marble, raw oak, travertine), and complementary styling pieces."},{"step":"03","title":"Art-Directed Production Shoot","description":"Under the guidance of our Creative Director, we capture expansive scene establishing shots alongside close-up contextual interactions."},{"step":"04","title":"Editorial Color Grading & Retouching","description":"Final images receive bespoke filmic or modern clean color grading tailored to match your brand lookbook guidelines."}]',
'[{"question":"Do you provide the models and props, or do we?","answer":"We handle everything! We source the props, book the models, manage usage releases, and construct the sets. If you have specific brand props you wish to include, you are welcome to send them alongside your products."},{"question":"Can we choose between indoor and outdoor locations?","answer":"Yes, we have our own fully staged studio kitchen, living room, and bathroom sets, and we also execute location permits for beaches, luxury apartments, and urban streets."},{"question":"Are model commercial usage rights included?","answer":"Yes, all lifestyle imagery produced with our contracted talent includes unlimited global digital commercial usage rights."},{"question":"How many finalized lifestyle setups do we get?","answer":"Our custom lifestyle packages are tailored to your needs, typically providing 3 to 5 fully staged scenes yielding 15 to 30 unique hero and vignette assets."}]',
'creative-product-photography,fashion-product-photography,product-photography',
'assets/images/services/lifestyle-product-photography.jpg', 'Storytelling', 3, 'published'),

(4, 'Fashion & Apparel Product Photography', 'fashion-product-photography', 'fashion-product-photography.php',
'Runway-Grade Fashion & Apparel Photography for Contemporary Clothing Labels',
'From architectural ghost mannequin executions and pin-sharp flat lays to dynamic live-model editorial lookbooks, we present fashion with elegance and drape integrity.',
'Apparel is one of the most demanding categories in digital commerce. Fabrics shift, wrinkles appear under unflattering light, colors drift between silk and denim, and consumers demand to understand the exact drape, hemline movement, and fit before committing to purchase. Ecomshoot brings editorial runway discipline to commercial apparel photography, ensuring your silhouettes look sculpted and luxurious.',
'[{"title":"Accurate Fit & Fabric Drape","description":"Showcase real textile weight, seam tension, and tailored fit so shoppers can visualize how the garment wears on their body.","icon":"maximize"},{"title":"3D Depth with Ghost Mannequin","description":"Present garments with invisible 3D volume, revealing interior linings, neck labels, and structural seams without distracting mannequins.","icon":"layers"},{"title":"High Garment Turnaround","description":"High-throughput steaming, pin-dressing, and lighting configurations designed for seasonal fashion collection rollouts.","icon":"refresh-cw"}]',
'[{"title":"3D Ghost Mannequin Magic","description":"Multi-shot composite method seamlessly joining front, interior collar, and hem into a realistic three-dimensional garment form."},{"title":"Editorial On-Model Lookbooks","description":"Full-length, walking motion, detail stitching, and candid model expressions directed by veteran fashion photographers."},{"title":"Precision Tabletop Flat Lays","description":"Meticulously pinned and styled flat lay compositions celebrating minimalist aesthetics and layered apparel outfits."},{"title":"Fabric Macro Zoom Captures","description":"Extreme close-ups highlighting cashmere weaves, technical moisture-wicking knits, leather grain, and zipper pull hardware."}]',
'[{"name":"Contemporary Ready-to-Wear","description":"Blazers, dresses, trousers, and trench coats styled to showcase premium tailoring and luxurious silhouette flow."},{"name":"Athletic & Performance Wear","description":"Compression garments, breathable mesh panels, dynamic seam placement, and reflective accents lit to convey energy."},{"name":"Intimates & Loungewear","description":"Delicate lace textures, seamless silk borders, and soft organic cottons photographed with soft, flattering wrap-around light."},{"name":"Footwear & Leather Accessories","description":"Handcrafted boots, structured leather handbags, and belts presented with structural integrity and authentic sheen."}]',
'[{"step":"01","title":"Steam, Prep & Garment Inspection","description":"Every garment is professionally steamed, lint-rolled, inspected for loose threads, and prepared with precision dressmaker pins."},{"step":"02","title":"Silhouette Sculpting & Fitting","description":"Garments are fitted to specialized modular mannequins or live talent, ensuring natural contouring and zero tension bunching."},{"step":"03","title":"Softbox & Rim Light Calibration","description":"We craft diffuse key lighting paired with delicate rim highlights that carve out the fabric texture from the backdrop."},{"step":"04","title":"Composite Stitching & Color Alignment","description":"Our digital artists composite ghost necklines, remove support pins, smooth minor fabric tension lines, and calibrate textile color."}]',
'[{"question":"What is the difference between ghost mannequin and flat lay?","answer":"Ghost mannequin gives garments a hollow-man 3D shape as if worn by an invisible person, ideal for jackets, blazers, and shirts. Flat lay presents garments styled flat on a table, ideal for tees, casual knitwear, and accessories."},{"question":"Can we hire diverse models through Ecomshoot?","answer":"Yes, we have relationships with premier model agencies representing diverse ethnicities, body types, and aesthetics. We manage castings, bookings, and release agreements."},{"question":"How do you handle difficult reflective fabrics like satin or vinyl?","answer":"Our studio uses polarized lighting filters and large scrim diffusers that control harsh highlights while maintaining the lustrous character of satin, velvet, and coated technical materials."},{"question":"Do you steam the garments prior to photography?","answer":"Yes! Professional industrial-grade fabric steaming and lint preparation are standard parts of our intake process at no extra fee."}]',
'product-photo-editing-retouching,lifestyle-product-photography,ecommerce-product-photography',
'assets/images/services/fashion-product-photography.jpg', 'Top Pick', 4, 'published'),

(5, 'Creative & Advertising Product Photography', 'creative-product-photography', 'creative-product-photography.php',
'Avant-Garde Creative & Advertising Photography That Stops The Infinite Scroll',
'Concept-driven visual campaigns that disrupt mundane feeds, leveraging bold color theory, kinetic splash capture, levitation techniques, and bespoke set design.',
'In an era of hyper-saturated social feeds and ubiquitous digital ads, conventional product shots often blend into visual noise. Creative product photography is the antidote. At Ecomshoot, we develop boundary-pushing artistic concepts that fuse fine-art sensibilities with commercial performance targets. We build floating compositions, orchestrate high-speed liquid splashes, engineer dramatic shadows, and build surreal color-blocked worlds.',
'[{"title":"Viral Feed-Stopping Power","description":"Bold compositions and cinematic lighting designed specifically to slash cost-per-click and halt user thumb scrolling.","icon":"zap"},{"title":"Unforgettable Brand Identity","description":"Carve out a distinct, non-replicable aesthetic territory that positions your brand as a fearless category leader.","icon":"award"},{"title":"Multi-Campaign Versatility","description":"Versatile creative assets ideal for billboard displays, web hero headers, press releases, and viral digital ad creatives.","icon":"sparkles"}]',
'[{"title":"High-Speed Fluid & Splash Capture","description":"1/10,000s flash duration captures exploding water droplets, suspended milk splashes, and floating botanical ingredients."},{"title":"Magnetic Levitation & Suspension","description":"Zero-gravity staging where products, packaging caps, and raw ingredients float mid-air with realistic lighting cast."},{"title":"Bespoke Architectural Sets","description":"Custom fabricated acrylic blocks, raw stone pedestals, geometric arches, and color-matched backdrops."},{"title":"Prismatic & Gobo Shadow Effects","description":"Optical projection masks creating tropical palm shadows, window slats, and kaleidoscopic reflections across product surfaces."}]',
'[{"name":"Indie Fragrance & Perfumery","description":"Crystal flacons surrounded by raw incense resin, botanical flowers, and cinematic mist capturing scent notes visually."},{"name":"Functional Beverages & Spirits","description":"Ice condensation beads, dynamic pouring liquid spirals, and fresh botanical garnishes glowing in atmospheric bar light."},{"name":"Clean Cosmetics & Skincare","description":"Smears of velvety creams, clay masks, and pipette serum drops suspended above smooth ceramic tiles."},{"name":"Designer Tech & Wearables","description":"Minimalist monolithic pedestals, neon edge-lighting, and architectural concrete forms celebrating futuristic design."}]',
'[{"step":"01","title":"Concept Pitch & Visual Treatment","description":"Our creative director develops 3 distinct visual treatments with sketches, color swatches, ingredient lists, and lighting diagrams."},{"step":"02","title":"Physical Set Construction & Prop Fabrication","description":"Our artisan makers cut acrylics, curate authentic botanicals, and build custom rigs to suspend components securely."},{"step":"03","title":"High-Speed Capture & Lighting Direction","description":"Using Broncolor Scoro power packs with ultra-short flash duration, we trigger synchronized bursts capturing split-second movement."},{"step":"04","title":"Fine Art Composite & Retouching","description":"We combine multi-exposure lighting brackets, blend optimal splash formations, and paint in hand-crafted shadow gradations."}]',
'[{"question":"Can you create realistic liquid splash and drip effects?","answer":"Absolutely. We specialize in high-speed splash photography using physical liquids, glycerin droplets for lasting dew, and synchronized flash triggers to freeze movement with tack-sharp clarity."},{"question":"How involved can our team be in the creative direction?","answer":"You can be as hands-on or hands-off as you prefer! We can develop complete concepts from scratch for your approval, or execute your agency moodboard down to the exact millimeter."},{"question":"Are props and ingredients included in the project rate?","answer":"Standard studio props, colored backgrounds, and geometric pedestals are included. Specialty botanicals, exotic fruits, or custom acrylic sets are quoted transparently at cost."},{"question":"Can creative assets be adapted into short looping video clips?","answer":"Yes! During our creative photography setups, we frequently capture synchronized slow-motion video and cinemagraph loops for TikTok and Instagram Reels."}]',
'lifestyle-product-photography,product-photography,jewellery-product-photography',
'assets/images/services/creative-product-photography.jpg', 'Award Winning', 5, 'published'),

(6, 'Jewellery & Luxury Product Photography', 'jewellery-product-photography', 'jewellery-product-photography.php',
'Micro-Precision Luxury & Fine Jewellery Photography Capturing Diamond Fire and Precious Sheen',
'Specialized macro focus-stacking photography designed to eliminate unwanted reflections, reveal diamond facet scintillation, and render precious metals with breathtaking brilliance.',
'Fine jewellery and luxury timepieces represent the pinnacle of commercial photographic difficulty. Highly reflective metals act like mirrors, diamonds scatter light into fiery chromatic aberrations, and miniature proportions require microscopic depth of field. At Ecomshoot, our dedicated luxury studio is equipped with customized diffusers, motorized focus rails, and cleanroom air filters that eliminate the dust particles invisible to the human eye.',
'[{"title":"Sublime Diamond Scintillation","description":"Controlled sparklers and polarized light reveal diamond fire, crystal facet clarity, and internal brilliance without muddying shadows.","icon":"sparkles"},{"title":"True Metallic Lustre","description":"18K yellow gold, rose gold, platinum, and sterling silver rendered with accurate warmth, mirror sheen, and zero color contamination.","icon":"shield"},{"title":"Edge-to-Edge Razor Sharpness","description":"Automated 30-to-80 frame focus-stacking composites ensure the entire ring band, prong, and gemstone are in tack-sharp focus.","icon":"crosshair"}]',
'[{"title":"Macro Focus Stacking Technology","description":"Cognisys motorized linear rails move the camera in 20-micron increments, capturing dozens of focal planes stitched seamlessly."},{"title":"Custom Diffuse Light Cones","description":"Proprietary 360-degree diffusion enclosures that wrap precious rings in immaculate, seamless reflection-free illumination."},{"title":"Micro-Retouching & Gem Polish","description":"Pixel-level post-production removing microscopic micro-scratches, aligning prong symmetry, and brightening diamond tables."},{"title":"Shadow & Mirror Floor Treatments","description":"Options for floating transparent backgrounds, subtle gradient drop shadows, or pristine high-gloss mirror reflections."}]',
'[{"name":"Diamond Engagement Rings","description":"Solitaires, halo settings, and pave bands photographed to celebrate stone clarity, carat presence, and delicate prongs."},{"name":"Luxury Swiss Timepieces","description":"Intricate dial guilloche patterns, sapphire crystal antireflective coatings, bezel fluting, and caseback movements."},{"name":"Fine Gold & Silver Necklaces","description":"Delicate chains arranged with graceful serpentine geometry, free of tangles, kinks, or uneven link shadows."},{"name":"Gemstone Earrings & Bracelets","description":"Emerald, ruby, sapphire, and pearl pieces lit to balance vibrant saturated color with transparent internal light play."}]',
'[{"step":"01","title":"Cleanroom Ultrasonic Preparation","description":"Jewellery is immersed in ultrasonic cleaners, wiped with lint-free optical microfiber, and handled exclusively with anti-static gloves."},{"step":"02","title":"Precision Rigging & Articulation","description":"Rings and pendants are mounted on micro-thin invisible monofilament or proprietary museum-grade mounting putty."},{"step":"03","title":"Motorized 50-Slice Focus Stacking","description":"Our tethered macro lens records 30 to 80 consecutive exposures as the rail shifts focus from the front stone tip to the rear band."},{"step":"04","title":"Focus Stacking & High-End Retouching","description":"Helicon Focus compiles the sharpest pixels from every slice; our senior retouchers hand-polish every facet and contour."}]',
'[{"question":"Why is focus stacking necessary for jewellery?","answer":"At macro magnification, the physical depth of field of even an f/16 lens is less than 1 millimeter. Focus stacking captures dozens of images across the product depth and stitches them into a single frame where every detail is in focus."},{"question":"How do you secure valuable high-ticket merchandise?","answer":"Our studio maintains biometric security, 24/7 video surveillance, a dual-combination safe, and comprehensive commercial inland marine insurance covering valuable goods in transit and on premises."},{"question":"Can you accurately capture the difference between 14k and 18k yellow gold?","answer":"Yes, our color-managed pipeline uses custom ICC camera calibration profiles that capture subtle differences between gold karat purities and rose gold alloys."},{"question":"Do you shoot 360-degree interactive jewellery spins?","answer":"Yes, we provide 360-degree spin photography captured on motorized indexing tables, outputting smooth HTML5/GIF interactive widgets."}]',
'product-photography,product-photo-editing-retouching,creative-product-photography',
'assets/images/services/jewellery-product-photography.jpg', 'Luxury Tier', 6, 'published'),

(7, 'Product Photo Editing & Retouching', 'product-photo-editing-retouching', 'product-photo-editing-retouching.php',
'Commercial Product Photo Editing, Clipping & High-End Digital Retouching',
'Transform raw studio captures into museum-quality digital assets with pixel-perfect clipping paths, frequency separation, scratch removal, and master color balancing.',
'Behind every breath-taking commercial image lies world-class digital post-production. Even the most carefully managed studio shoot encounters microscopic manufacturing imperfections, dust particles, uneven fabric seams, or minor packaging dents. Ecomshoot operates an in-house team of master digital retouchers who elevate good photographs into extraordinary commercial assets that sell.',
'[{"title":"Flawless Surface Perfection","description":"Eliminate manufacturing blemishes, seam glue, scratches, and micro-dust while preserving the authentic natural texture.","icon":"check-circle"},{"title":"100% Hand-Drawn Clipping Paths","description":"Precise pen-tool vector paths ensuring crisp, razor-sharp edges without the jagged halo artifacts common in automated AI cutouts.","icon":"scissors"},{"title":"Accurate Color Correction","description":"Calibrate digital color values against Pantone swatches and physical samples for flawless cross-screen color accuracy.","icon":"sliders"}]',
'[{"title":"Advanced Frequency Separation","description":"Separates color tone from high-frequency surface texture, allowing smooth lighting adjustments without blurring details."},{"title":"Dynamic Multi-Shadow Creation","description":"Hand-crafted drop shadows, ambient contact shadows, and realistic surface reflections tailored to match client guidelines."},{"title":"Ghost Mannequin Neck Jointing","description":"Seamlessly joining front and back interior garment collar photographs into a clean, uniform 3D apparel presentation."},{"title":"Label & Artwork Replacement","description":"Digitally swapping language labels, correcting foil stamping alignment, or updating packaging claims without reshooting."}]',
'[{"name":"High-End Consumer Electronics","description":"Screen replacement, matte plastic smoothing, brushed aluminum sheen restoration, and optical glare removal."},{"name":"Footwear & Athletic Gear","description":"Sole cleaning, shape contouring, foam texture balancing, and removal of manufacturing injection-mold seams."},{"name":"Apparel & Fashion Houses","description":"Wrinkle relaxation, symmetry refinement, collar stiffening, and removal of stray threads or hanger indentations."},{"name":"Bottles, Glassware & Perfume","description":"Liquid clarity enhancement, condensation rendering, symmetrical glass contouring, and internal bubble correction."}]',
'[{"step":"01","title":"Raw Conversion & Color Profiling","description":"Tethered 16-bit RAW captures are converted in Capture One with custom tone curves that maximize dynamic range preservation."},{"step":"02","title":"Pen Tool Vector Clipping & Masking","description":"Retouchers draft hand-drawn vector paths along every product edge, ensuring smooth transitions even on intricate shapes."},{"step":"03","title":"Surface Cleanup & Texture Sculpting","description":"Using frequency separation and cloning, we hand-clean every surface artifact while safeguarding authentic material grain."},{"step":"04","title":"Shadow Rendering & Multi-Format Export","description":"Custom ambient contact shadows are applied, followed by automated multi-resolution web exports and QA checklist signoff."}]',
'[{"question":"Do you use automated AI background removal tools?","answer":"Never for client deliverables. While AI tools are improving, they produce soft, blurry, or jagged edges and clip crucial product fringes. We hand-draw vector clipping paths using Photoshop Pen Tool for museum-grade precision."},{"question":"Can you edit photos that our team shot in-house?","answer":"Yes! Many brands send us their existing in-house RAW or TIFF image catalogs for color grading, background removal, and high-end retouching."},{"question":"Can you change product colors digitally to avoid shooting every variation?","answer":"Yes, our colorway alteration service allows you to shoot one hero SKU and generate 20 photorealistic colorway variants, saving thousands in production costs."},{"question":"What formats do you deliver?","answer":"We deliver layered PSD files (upon request), transparent PNGs, high-resolution TIFFs for print, and optimized WebP/JPEG files for web."}]',
'product-photography,white-background-product-photography,fashion-product-photography',
'assets/images/services/product-photo-editing-retouching.jpg', 'Studio Essential', 7, 'published'),

(8, 'White Background Product Photography', 'white-background-product-photography', 'white-background-product-photography.php',
'Pristine White Background Product Photography Built for Global E-Commerce Marketplaces',
'Pure RGB #255,255,255 white background imagery engineered to pass Amazon, Walmart, and Google Shopping compliance algorithms on the very first upload.',
'Pure white background photography—frequently referred to as the e-commerce packshot—is the indisputable backbone of digital retail. Leading marketplaces like Amazon, Walmart, Target, and eBay enforce strict technical requirements: pure RGB (255,255,255) white backgrounds, zero extraneous props, 85% frame occupancy, and natural grounding shadows. Ecomshoot has perfected this format, delivering pristine catalog images that pass automated compliance algorithms effortlessly.',
'[{"title":"Instant Marketplace Compliance","description":"Engineered strictly to Amazon Seller Central, Walmart Marketplace, and Google Merchant Center specifications.","icon":"shield-check"},{"title":"Focus on Pure Product Essence","description":"Zero background distractions or cluttered sets—putting 100% of customer cognitive focus on your product craftsmanship.","icon":"target"},{"title":"Effortless Web Integration","description":"Blends seamlessly into any website layout, category grid, or digital promotional banner with zero visible framing boxes.","icon":"layout"}]',
'[{"title":"Pure RGB (255, 255, 255) Guarantee","description":"True pure white backdrops verified through automated digital threshold tests to eliminate muddy gray or dirty white halos."},{"title":"Natural Ambient Drop Shadows","description":"Retained or digitally painted natural contact shadows that anchor the product so it does not appear to float unnaturally."},{"title":"Consistent Frame Filling Ratios","description":"Every product is scaled to occupy exactly 85% of the frame bounds, establishing a uniform visual rhythm on product listing pages."},{"title":"Pinch-to-Zoom Dynamic Detail","description":"High-resolution capture ensuring crisp 2000px+ zoom capability so buyers can examine stitching, textures, and fine print."}]',
'[{"name":"Amazon FBA & Vendor Brands","description":"Guaranteed zero listing suppressions with main hero images meeting all 9 Amazon image style guidelines."},{"name":"D2C Shopify & BigCommerce Stores","description":"Immaculate category grid consistency that elevates store credibility and gives independent brands an enterprise feel."},{"name":"B2B Wholesale & Line Sheets","description":"Crisp, clear product views designed for digital buyer portals, printed catalogs, and trade show brochures."},{"name":"Pharmaceutical & Health Products","description":"Sterile, clinical purity that communicates medical safety, regulatory compliance, and pristine hygienic standards."}]',
'[{"step":"01","title":"Specular Backlight Calibration","description":"We light the background independently from the subject using calibrated strip boxes to prevent light wrap and edge bleed."},{"step":"02","title":"Subject Key & Fill Balancing","description":"Dedicated front key lights carve out shape and material depth while soft fills illuminate subtle brand logos."},{"step":"03","title":"White Point Threshold Verification","description":"Digital inspection confirms that background pixels register at pure RGB 255, 255, 255 while product highlights remain unclipped."},{"step":"04","title":"Shadow Preservation & Sizing","description":"Realistic ground shadow is isolated, product centered with standardized margins, and exported in high-res format."}]',
'[{"question":"Will these images pass Amazon requirements?","answer":"Yes, 100% guaranteed. We adhere strictly to Amazon guidelines: pure white background (RGB 255,255,255), 85%+ product frame filling, no watermarks, no text overlays, and high resolution (3000x3000px) enabling zoom."},{"question":"What happens if my product itself is white?","answer":"Photographing white products on a white background is our specialty! We sculpt the edges of the white product with subtle dark reflection cards and precise rim lighting, ensuring the silhouette stands out clearly without blending into the backdrop."},{"question":"Can we choose between floating white or white with natural shadow?","answer":"Yes, we offer pure cutouts with no shadow, natural grounding contact shadows, or delicate soft drop shadows based on your website aesthetic."},{"question":"How quickly can you shoot 50 products on white?","answer":"For standard consumer items, a 50-SKU white background batch is typically shot, retouched, and delivered within 4 to 6 business days."}]',
'ecommerce-product-photography,product-photography,product-photo-editing-retouching',
'assets/images/services/white-background-product-photography.jpg', 'Marketplace Must', 8, 'published'),

(9, 'Professional Product Photography', 'professional-product-photography', 'professional-product-photography.php',
'End-to-End Professional Commercial Product Photography for Established Enterprises',
'Full-service creative production combining strategic creative direction, medium format camera hardware, master lighting, set styling, and dedicated project management.',
'Professional product photography is a strategic investment in enterprise brand equity. When global brands launch landmark products, refresh legacy catalog architectures, or roll out omnichannel advertising campaigns, they need a production partner capable of handling intricate logistics, strict visual guidelines, and uncompromising image quality. Ecomshoot provides end-to-end studio solutions for forward-thinking brand teams.',
'[{"title":"Dedicated Senior Creative Director","description":"A seasoned production director leads your project from concept moodboards to lighting architecture and final quality signoff.","icon":"user-check"},{"title":"Enterprise Turnkey Production","description":"We coordinate everything: shipping manifests, prep stations, prop fabrication, talent casting, proofing, and cloud archiving.","icon":"briefcase"},{"title":"Unmatched Commercial Quality","description":"State-of-the-art camera systems, precision color calibration, and master retouching that satisfy billboard and print standards.","icon":"gem"}]',
'[{"title":"Bespoke Production Roadmaps","description":"Every client receives a custom call sheet, minute-by-minute shooting timetable, and collaborative proofing portal."},{"title":"Phase One 150MP Digital Backs","description":"The gold standard in commercial photography, delivering dynamic range, optical acuity, and crop flexibility beyond 35mm cameras."},{"title":"Master Color Temperature Management","description":"Continuous spectrophotometer monitoring guaranteeing color temperatures remain locked to 5600K throughout the shoot."},{"title":"Secure Long-Term Asset Vault","description":"All RAW captures and layered master PSDs archived in geo-redundant encrypted cloud storage for 3 years at no extra charge."}]',
'[{"name":"Enterprise Consumer Electronics","description":"High-end smart home ecosystems, audio gear, and laptops captured across commercial hero shots and exploded feature sets."},{"name":"Heritage Spirits & Distilleries","description":"Heavy glass bottles, rich amber liquid tones, embossed foil labels, and dramatic mahogany bar environments."},{"name":"Designer Furniture & Lighting","description":"Large-scale studio sets accommodating oversized modular sofas, dining suites, and architectural lighting fixtures."},{"name":"Omnichannel Retail Consortia","description":"Large retail chains requiring synchronized seasonal visual rollouts across thousands of digital and print touchpoints."}]',
'[{"step":"01","title":"Discovery Call & Technical Scoping","description":"We review your project objectives, brand guidelines, channel destinations, file requirements, and delivery milestones."},{"step":"02","title":"Call Sheet & Pre-Production Deck","description":"Our team prepares a comprehensive pre-production book outlining lighting diagrams, talent, props, and shot-by-shot schedules."},{"step":"03","title":"Multi-Set Studio Execution","description":"Shooting across multiple concurrent bays with dedicated digital techs monitoring focus, color targets, and live client proofing streams."},{"step":"04","title":"Enterprise Master Retouching & Delivery","description":"Layered master PSDs, CMYK print files, sRGB web exports, and multi-crop variations packaged and delivered via private cloud."}]',
'[{"question":"What distinguishes your professional commercial service from basic catalog shots?","answer":"Our professional service is full-scale commercial production: medium-format cameras, a dedicated creative director, customized lighting for each material texture, comprehensive art direction, and hand-crafted post-production suitable for billboards, television, and global campaigns."},{"question":"Can our brand representatives attend the shoot in person?","answer":"Yes! You are welcome to attend in person at our New York studio suite, which features a private client lounge, high-speed fiber WiFi, and dedicated viewing monitors. Alternatively, join via our interactive live remote tethering stream."},{"question":"Do you handle multi-location or oversized product shoots?","answer":"Yes, our studio space includes a drive-in cyclorama bay suitable for large furniture, fitness equipment, and commercial appliances, as well as on-location mobile production units."},{"question":"How are commercial licensing rights handled?","answer":"All deliverables come with perpetual, worldwide, royalty-free commercial usage rights across digital, print, and retail advertising channels."}]',
'product-photography,ecommerce-product-photography,creative-product-photography',
'assets/images/services/professional-product-photography.jpg', 'Enterprise', 9, 'published');

-- ----------------------------------------------------------------------------
-- SEED BLOG CATEGORIES
-- ----------------------------------------------------------------------------
INSERT INTO `blog_categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'E-Commerce Strategy', 'ecommerce-strategy', 'Conversion optimization, marketplace tactics, and visual merchandising strategies for online brands.'),
(2, 'Studio Lighting & Tech', 'studio-lighting-tech', 'In-depth lighting setups, camera gear breakdowns, and technical masterclasses from our studio leads.'),
(3, 'Marketplace Compliance', 'marketplace-compliance', 'Rules, guidelines, and compliance blueprints for Amazon, Walmart, Google Shopping, and Shopify.'),
(4, 'Post-Production & Retouching', 'post-production-retouching', 'Behind the scenes of frequency separation, clipping paths, shadow architecture, and color calibration.');

-- ----------------------------------------------------------------------------
-- SEED BLOG POSTS (With rich Markdown content)
-- ----------------------------------------------------------------------------
INSERT INTO `blogs` (`id`, `category_id`, `title`, `slug`, `excerpt`, `content`, `content_format`, `featured_image`, `author_name`, `author_role`, `status`, `faqs_json`, `internal_links_json`, `read_time`, `published_at`) VALUES
(1, 1, 'How High-Resolution Product Photography Directly Boosts Shopify Conversion Rates',
'how-product-photography-boosts-shopify-conversions',
'Discover the empirical connection between visual resolution, multi-angle imagery, and customer checkout conversion rates on modern Shopify stores.',
'# The Visual Science of E-Commerce Conversion

In physical retail, purchasing decisions are tactile. Shoppers run their fingertips across the grain of a leather wallet, test the spring resistance of a sports shoe, examine the weight of a watch, and inspect stitching up close. 

In digital commerce, **every single one of those sensory touchpoints must be translated through light and pixels.**

When a potential buyer lands on your Shopify product page, they are fundamentally making an assessment of risk. Is this product legitimate? Does the material feel cheap? Will the color match my expectations when the parcel arrives?

## The Three Visual Pillars of High-Converting Stores

### 1. Optical Resolution and Micro-Texture Rendering
Low-resolution images act like a foggy shop window. If an online shopper attempts to pinch-to-zoom on your hero image and encounters blocky artifacts or blurry edges, their subconscious registers lower product quality. 

Using medium-format camera systems, we capture over 100 million pixels of raw visual data. This allows users to zoom into:
- The organic weave of linen apparel
- The brushed bevel of metallic consumer tech
- The matte or gloss coat of cosmetics packaging

### 2. Standardized Multi-Angle Sequences
One hero photograph is never enough to overcome purchase hesitation. Our empirical testing with high-volume D2C clients demonstrates that moving from **2 images to 7 standardized angles** increases checkout completion by an average of **34%**.

Recommended 7-shot sequence:
1. **Hero Shot**: 45-degree isometric view on clean neutral ground
2. **Direct Front**: Straight-on profile showing balanced proportions
3. **Side & Silhouette**: Revealing product depth and slimness
4. **Back & Details**: Showing closure mechanisms, ports, or rear details
5. **Macro Close-Up**: Extreme zoom of critical hardware or branding
6. **In-Hand / Human Scale**: Contextual shot demonstrating true physical dimensions
7. **Lifestyle In-Context**: Inspiring aspirational usage in real environments

### 3. Absolute Color Fidelity
Mismatched colors between the screen and the physical product are the number one cause of e-commerce apparel and home goods returns. By utilizing hardware-calibrated X-Rite ColorChecker targets and custom ICC profiles, Ecomshoot locks color deviation ($\Delta E$) below 1.5, well below the threshold of human visual perception.

## Key Takeaway
Investing in professional studio imagery is not a decorative marketing expense—it is a foundational conversion engine that pays dividends on every single paid ad dollar sent to your store.',
'markdown', 'assets/images/portfolio/portfolio-1.jpg', 'Marcus Vance', 'Head of E-Commerce Strategy', 'published',
'[{"question":"What image resolution is optimal for Shopify?","answer":"Shopify recommends square images between 2048 x 2048 pixels and 4472 x 4472 pixels for smooth pinch-to-zoom without overwhelming mobile bandwidth."},{"question":"How many images should each product listing have?","answer":"We recommend a minimum of 5 to 7 images per SKU to satisfy all customer risk assessments."}]',
'[{"anchor":"Ecommerce Product Photography","url":"ecommerce-product-photography.php"},{"anchor":"Product Photo Editing & Retouching","url":"product-photo-editing-retouching.php"}]',
'6 min read', '2026-10-15 09:30:00'),

(2, 2, 'Mastering the Art of Pure White Background Photography: Studio Setup & Lighting',
'mastering-white-background-product-photography-lighting',
'A technical masterclass into achieving true RGB 255,255,255 white background photographs without washing out product edges or losing specular highlights.',
'# The Myth of Overexposing the Backdrop

Many amateur photographers assume that achieving a pure white background simply means blasting a white paper backdrop with maximum strobe power. 

**This is the fastest way to ruin a product photo.**

When you overwhelm your background with excessive light, that light bounces back toward the camera lens, wrapping around the edges of your product. This phenomenon—known as **specular edge bleed** or flare—washes out delicate silhouettes, washes away fine details, and introduces a milky gray veil over your contrast.

## The Balanced Four-Light Studio Architecture

To achieve a true pure white (RGB 255, 255, 255) background while preserving tack-sharp product boundaries, you must decouple background illumination from subject lighting entirely.

| Light Role | Modifier | Purpose | Recommended Power Ratio |
| :--- | :--- | :--- | :--- |
| **Background Left** | Stripbox with Grid | Even white distribution | +1.5 stops over key |
| **Background Right** | Stripbox with Grid | Eliminates shadow falloff | +1.5 stops over key |
| **Main Key Light** | Octabox with Diffuser | Shapes product contours | Base exposure (f/11) |
| **Fill / Rim** | Silver Reflector / Strip | Opens dark shadows | -1.5 stops under key |

### Step 1: Distance Is Your Protective Shield
Place your product at least **6 to 8 feet in front of the white backdrop**. The inverse-square law dictates that light intensity diminishes exponentially with distance. Increasing the physical gap between your product and the background guarantees that backdrop bounce cannot contaminate your subject.

### Step 2: The Black Card Flagging Technique
When photographing shiny or light-colored items (such as white cosmetic bottles or silver jewellery) against a white backdrop, the edges can blend invisibly into the background.

The solution is simple yet transformative: **Black foam-core flags**. 
By placing narrow black strips just outside the camera frame on either side of the product, the glossy edges reflect the black card, carving out crisp, razor-sharp dark boundary lines that define the shape with absolute precision.

### Step 3: Measuring with the Digital Eyedropper
Never trust your naked eye or an uncalibrated monitor. In Capture One or Photoshop:
- Inspect the four corners of your background canvas with the color sampler.
- The numbers must read **R: 255, G: 255, B: 255**.
- Inspect the outer rim of your product. If the edge pixels jump from 255 directly to 230 without gradual antialiasing, your backlight was too intense.

## Conclusion
True studio packshot excellence is born from control, not raw power. With proper distance, deliberate flagging, and balanced light ratios, pure white photography becomes a reproducible science.',
'markdown', 'assets/images/portfolio/portfolio-2.jpg', 'Elena Rostova', 'Master Studio Lighting Director', 'published',
'[{"question":"Why does my white background look grayish after uploading?","answer":"If the camera raw value is even slightly below 255 (e.g. 250, 250, 250), it will display as a dingy off-white box against pure white web pages."},{"question":"Should I use continuous LED or strobe lighting for white backgrounds?","answer":"High-output studio strobes with fast flash durations are superior for product photography because they easily overpower ambient room light and freeze any micro-vibrations."}]',
'[{"anchor":"White Background Product Photography","url":"white-background-product-photography.php"},{"anchor":"Product Photography Services","url":"product-photography.php"}]',
'8 min read', '2026-10-22 14:15:00'),

(3, 3, 'Amazon Product Image Guidelines 2026: Complete Compliance & Best Practices',
'amazon-product-image-guidelines-compliance-handbook',
'Stay ahead of Amazon algorithm updates and seller central image standards to prevent listing suppression and capture the Buy Box.',
'# The Critical Role of Image Compliance on Amazon

On Amazon, your main hero image (known internally as `MAIN`) is the single most critical asset in your entire seller account. It determines your **Search Impressions to Click-Through Rate (CTR)** on the search results page.

However, Amazon employs strict computer-vision algorithms that automatically scan and suppress listings that fail to meet strict style guide criteria. Having your best-selling ASIN suppressed during a holiday sales peak can cost tens of thousands of dollars in lost revenue.

## Amazon MAIN Image Mandatory Rules

Here is the authoritative checklist your primary image must satisfy:

1. **Pure White Background**: Must be pure sRGB white (`#FFFFFF` or RGB 255, 255, 255).
2. **85% Frame Occupancy**: The product must fill at least 85% of the total image canvas area. Excess dead white border space will trigger listing warnings.
3. **No Added Text, Badges, or Watermarks**: Graphics claiming "Best Seller", "100% Organic", sale percentages, or brand logos not physically part of the product are strictly prohibited on the MAIN image.
4. **Physical Product Only**: No illustrations, 3D mockups, or vector sketches. Must be a genuine photograph of the physical item.
5. **No Extraneous Accessories**: Do not display items that are not included in the purchase box. If you sell a camera lens, do not photograph the camera body on the MAIN image unless it is part of the bundle.
6. **High Resolution for Zoom**: Minimum dimension of 1600 pixels on the longest side (Ecomshoot standards deliver 3000 x 3000 pixels at 1:1 square ratio for maximum pinch-to-zoom utility).

## Optimizing Secondary Images (Slots 2 Through 7)

While the MAIN image has rigid legal constraints, your secondary images offer total creative freedom to build your brand story and handle customer objections:

- **Infographic Breakdown (Slot 2)**: Call out key technical dimensions, ingredients, or construction materials with elegant pointer lines.
- **Scale Comparison (Slot 3)**: Place the product next to common everyday items or in a human hand to demonstrate actual real-world size.
- **Problem vs. Solution (Slot 4)**: Visually demonstrate the pain point your product solves compared to generic alternatives.
- **Lifestyle Emotional Hook (Slots 5 & 6)**: High-production lifestyle images showing happy target demographics enjoying the product benefits.
- **Packaging & Unboxing (Slot 7)**: Showcase premium gift boxes, instructions, and bundled accessories to build unboxing anticipation.

## Partnering with Ecomshoot for Amazon Success
At Ecomshoot, our automated quality assurance script verifies every deliverable against Amazon Seller Central API requirements before the files ever reach your inbox.',
'markdown', 'assets/images/portfolio/portfolio-3.jpg', 'David Chen', 'Marketplace Compliance Lead', 'published',
'[{"question":"What happens if Amazon flags my main image?","answer":"Your listing is placed into a suppressed status, meaning it will completely disappear from search results and category navigation until a compliant image is uploaded and approved."},{"question":"Can we use props in secondary images on Amazon?","answer":"Yes! Props, infographics, text overlays, and lifestyle backgrounds are fully allowed on secondary images (slots 2 through 9)."}]',
'[{"anchor":"E-Commerce Product Photography","url":"ecommerce-product-photography.php"},{"anchor":"White Background Product Photography","url":"white-background-product-photography.php"}]',
'7 min read', '2026-10-29 11:00:00');

-- ----------------------------------------------------------------------------
-- SEED BLOG TAGS & RELATIONS
-- ----------------------------------------------------------------------------
INSERT INTO `blog_tags` (`id`, `name`, `slug`) VALUES
(1, 'Conversion Optimization', 'conversion-optimization'),
(2, 'Shopify', 'shopify'),
(3, 'Lighting Tips', 'lighting-tips'),
(4, 'Studio Techniques', 'studio-techniques'),
(5, 'Amazon FBA', 'amazon-fba'),
(6, 'Marketplace Rules', 'marketplace-rules');

INSERT INTO `blog_tag_relations` (`blog_id`, `tag_id`) VALUES
(1, 1), (1, 2),
(2, 3), (2, 4),
(3, 5), (3, 6);

-- ----------------------------------------------------------------------------
-- SEED SEO METADATA (For all pages and services)
-- ----------------------------------------------------------------------------
INSERT INTO `seo_metadata` (`page_identifier`, `meta_title`, `meta_description`, `canonical_url`, `og_title`, `og_description`, `og_image`, `twitter_title`, `twitter_description`, `twitter_image`) VALUES
('home', 'Ecomshoot | Premium E-Commerce Product Photography & Creative Production Agency',
'Elevate brand perception and multiply e-commerce conversion rates with high-end commercial product photography, pure white packshots, and immersive lifestyle visual productions.',
'https://www.ecomshoots.in/index.php',
'Ecomshoot | Premium Commercial Product Photography',
'Studio-grade commercial product photography engineered for Shopify, Amazon, and luxury retail brands.',
'assets/images/portfolio/portfolio-1.jpg',
'Ecomshoot | Premium Commercial Product Photography',
'Studio-grade commercial product photography engineered for Shopify, Amazon, and luxury retail brands.',
'assets/images/portfolio/portfolio-1.jpg'),

('about', 'About Ecomshoot | Commercial Photography Studio & Production House',
'Discover how Ecomshoot bridges fine art commercial photography and e-commerce conversion science. Meet our studio directors, lighting engineers, and master retouchers.',
'https://www.ecomshoots.in/about.php',
'About Ecomshoot | Commercial Studio & Production House',
'Meet the creative minds and technical perfectionists behind Ecomshoot.',
'assets/images/portfolio/portfolio-2.jpg',
'About Ecomshoot | Commercial Studio & Production House',
'Meet the creative minds and technical perfectionists behind Ecomshoot.',
'assets/images/portfolio/portfolio-2.jpg'),

('services', 'Commercial Photography Services | Ecomshoot Studio Catalog',
'Explore our complete suite of commercial product photography services: from Amazon white background packshots to editorial lifestyle campaigns and high-end jewellery.',
'https://www.ecomshoots.in/services.php',
'Commercial Photography Services | Ecomshoot',
'Complete studio visual production solutions for high-growth e-commerce brands.',
'assets/images/portfolio/portfolio-3.jpg',
'Commercial Photography Services | Ecomshoot',
'Complete studio visual production solutions for high-growth e-commerce brands.',
'assets/images/portfolio/portfolio-3.jpg'),

('blog', 'Insights & Photography Journal | Ecomshoot Creative Production',
'Expert perspectives, lighting setups, Amazon compliance guides, and e-commerce visual strategy from the senior photographers at Ecomshoot.',
'https://www.ecomshoots.in/blog.php',
'Insights & Photography Journal | Ecomshoot',
'E-Commerce photography intelligence, studio lighting setups, and conversion strategies.',
'assets/images/portfolio/portfolio-1.jpg',
'Insights & Photography Journal | Ecomshoot',
'E-Commerce photography intelligence, studio lighting setups, and conversion strategies.',
'assets/images/portfolio/portfolio-1.jpg'),

('contact', 'Contact Ecomshoot | Book a Studio Shoot & Request a Quote',
'Ready to elevate your product imagery? Contact our New York studio for custom rate cards, volume SKU estimates, or complimentary sample test shoots.',
'https://www.ecomshoots.in/contact.php',
'Contact Ecomshoot | Book a Studio Shoot',
'Connect with our production coordinators and schedule your product shoot today.',
'assets/images/portfolio/portfolio-2.jpg',
'Contact Ecomshoot | Book a Studio Shoot',
'Connect with our production coordinators and schedule your product shoot today.',
'assets/images/portfolio/portfolio-2.jpg'),

('product-photography', 'Commercial Product Photography Services | Ecomshoot',
'High-definition commercial product photography that captures physical textures, authentic color tonality, and structural form to turn casual store visitors into loyal buyers.',
'https://www.ecomshoots.in/product-photography.php',
'Commercial Product Photography Services | Ecomshoot',
'High-definition commercial product photography engineered for maximum checkout conversion.',
'assets/images/services/product-photography.jpg',
'Commercial Product Photography Services | Ecomshoot',
'High-definition commercial product photography engineered for maximum checkout conversion.',
'assets/images/services/product-photography.jpg'),

('ecommerce-product-photography', 'E-Commerce Product Photography Services | Ecomshoot',
'Scalable, catalog-ready product photography optimized for Shopify Plus merchants, Amazon FBA power sellers, and multi-channel retailers requiring uncompromising consistency.',
'https://www.ecomshoots.in/ecommerce-product-photography.php',
'E-Commerce Product Photography Services | Ecomshoot',
'High-volume, catalog-consistent product photography for modern digital brands.',
'assets/images/services/ecommerce-product-photography.jpg',
'E-Commerce Product Photography Services | Ecomshoot',
'High-volume, catalog-consistent product photography for modern digital brands.',
'assets/images/services/ecommerce-product-photography.jpg'),

('lifestyle-product-photography', 'Lifestyle Product Photography Services | Ecomshoot',
'Immersive in-context photography that showcases your product in real-world environments, connecting deeply with target demographics and skyrocketing social engagement.',
'https://www.ecomshoots.in/lifestyle-product-photography.php',
'Lifestyle Product Photography Services | Ecomshoot',
'Contextual lifestyle imagery that evokes emotion and drives social engagement.',
'assets/images/services/lifestyle-product-photography.jpg',
'Lifestyle Product Photography Services | Ecomshoot',
'Contextual lifestyle imagery that evokes emotion and drives social engagement.',
'assets/images/services/lifestyle-product-photography.jpg'),

('fashion-product-photography', 'Fashion & Apparel Product Photography Services | Ecomshoot',
'From architectural ghost mannequin executions and pin-sharp flat lays to dynamic live-model editorial lookbooks, we present fashion with elegance and drape integrity.',
'https://www.ecomshoots.in/fashion-product-photography.php',
'Fashion & Apparel Product Photography Services | Ecomshoot',
'Runway-grade apparel photography: 3D ghost mannequin, flat lays, and editorial lookbooks.',
'assets/images/services/fashion-product-photography.jpg',
'Fashion & Apparel Product Photography Services | Ecomshoot',
'Runway-grade apparel photography: 3D ghost mannequin, flat lays, and editorial lookbooks.',
'assets/images/services/fashion-product-photography.jpg'),

('creative-product-photography', 'Creative & Advertising Product Photography | Ecomshoot',
'Concept-driven visual campaigns that disrupt mundane feeds, leveraging bold color theory, kinetic splash capture, levitation techniques, and bespoke set design.',
'https://www.ecomshoots.in/creative-product-photography.php',
'Creative & Advertising Product Photography | Ecomshoot',
'Avant-garde creative photography designed to stop the scroll and build iconic brands.',
'assets/images/services/creative-product-photography.jpg',
'Creative & Advertising Product Photography | Ecomshoot',
'Avant-garde creative photography designed to stop the scroll and build iconic brands.',
'assets/images/services/creative-product-photography.jpg'),

('jewellery-product-photography', 'Jewellery & Luxury Product Photography Services | Ecomshoot',
'Specialized macro focus-stacking photography designed to eliminate unwanted reflections, reveal diamond facet scintillation, and render precious metals with breathtaking brilliance.',
'https://www.ecomshoots.in/jewellery-product-photography.php',
'Jewellery & Luxury Product Photography Services | Ecomshoot',
'Micro-precision macro photography capturing diamond fire and precious metal lustre.',
'assets/images/services/jewellery-product-photography.jpg',
'Jewellery & Luxury Product Photography Services | Ecomshoot',
'Micro-precision macro photography capturing diamond fire and precious metal lustre.',
'assets/images/services/jewellery-product-photography.jpg'),

('product-photo-editing-retouching', 'Product Photo Editing & High-End Retouching | Ecomshoot',
'Transform raw studio captures into museum-quality digital assets with pixel-perfect clipping paths, frequency separation, scratch removal, and master color balancing.',
'https://www.ecomshoots.in/product-photo-editing-retouching.php',
'Product Photo Editing & High-End Retouching | Ecomshoot',
'Commercial digital post-production: hand-drawn clipping paths, scratch removal, and color matching.',
'assets/images/services/product-photo-editing-retouching.jpg',
'Product Photo Editing & High-End Retouching | Ecomshoot',
'Commercial digital post-production: hand-drawn clipping paths, scratch removal, and color matching.',
'assets/images/services/product-photo-editing-retouching.jpg'),

('white-background-product-photography', 'White Background Product Photography Services | Ecomshoot',
'Pure RGB #255,255,255 white background imagery engineered to pass Amazon, Walmart, and Google Shopping compliance algorithms on the very first upload.',
'https://www.ecomshoots.in/white-background-product-photography.php',
'White Background Product Photography Services | Ecomshoot',
'Pristine pure white packshots 100% compliant with Amazon, Walmart, and Google Shopping.',
'assets/images/services/white-background-product-photography.jpg',
'White Background Product Photography Services | Ecomshoot',
'Pristine pure white packshots 100% compliant with Amazon, Walmart, and Google Shopping.',
'assets/images/services/white-background-product-photography.jpg'),

('professional-product-photography', 'Professional Commercial Product Photography | Ecomshoot',
'Full-service creative production combining strategic creative direction, medium format camera hardware, master lighting, set styling, and dedicated project management.',
'https://www.ecomshoots.in/professional-product-photography.php',
'Professional Commercial Product Photography | Ecomshoot',
'Turnkey commercial photography production for enterprise brands and leading retail agencies.',
'assets/images/services/professional-product-photography.jpg',
'Professional Commercial Product Photography | Ecomshoot',
'Turnkey commercial photography production for enterprise brands and leading retail agencies.',
'assets/images/services/professional-product-photography.jpg');

-- ----------------------------------------------------------------------------
-- SEED SCHEMAS (Independent Schema Management System)
-- ----------------------------------------------------------------------------
INSERT INTO `schemas` (`id`, `name`, `schema_type`, `page_identifier`, `schema_json`, `is_active`) VALUES
(1, 'Global Organization Schema', 'Organization', 'all',
'{\n  "@context": "https://schema.org",\n  "@type": "Organization",\n  "name": "Ecomshoot",\n  "url": "https://www.ecomshoots.in",\n  "logo": "https://www.ecomshoots.in/assets/images/logo.png",\n  "description": "Professional commercial e-commerce product photography studio and creative production company.",\n  "contactPoint": {\n    "@type": "ContactPoint",\n    "telephone": "+1-800-555-7466",\n    "contactType": "customer service",\n    "email": "contact@ecomshoots.in",\n    "availableLanguage": "en"\n  },\n  "address": {\n    "@type": "PostalAddress",\n    "streetAddress": "Studio 4B, 742 Creative Boulevard",\n    "addressLocality": "New York",\n    "addressRegion": "NY",\n    "postalCode": "10013",\n    "addressCountry": "US"\n  },\n  "sameAs": [\n    "https://instagram.com/ecomshoot",\n    "https://linkedin.com/company/ecomshoot",\n    "https://pinterest.com/ecomshoot"\n  ]\n}', 1),

(2, 'Global WebSite Schema', 'WebSite', 'home',
'{\n  "@context": "https://schema.org",\n  "@type": "WebSite",\n  "name": "Ecomshoot",\n  "url": "https://www.ecomshoots.in",\n  "potentialAction": {\n    "@type": "SearchAction",\n    "target": "https://www.ecomshoots.in/services.php?q={search_term_string}",\n    "query-input": "required name=search_term_string"\n  }\n}', 1),

(3, 'Commercial Photography Service Schema', 'Service', 'product-photography',
'{\n  "@context": "https://schema.org",\n  "@type": "Service",\n  "serviceType": "Commercial Product Photography",\n  "provider": {\n    "@type": "Organization",\n    "name": "Ecomshoot",\n    "url": "https://www.ecomshoots.in"\n  },\n  "areaServed": "Global",\n  "description": "High-definition commercial product photography engineered for maximum e-commerce conversion, multi-angle catalog capture, and calibrated color accuracy."\n}', 1),

(4, 'E-Commerce Photography FAQ Schema', 'FAQPage', 'product-photography',
'{\n  "@context": "https://schema.org",\n  "@type": "FAQPage",\n  "mainEntity": [\n    {\n      "@type": "Question",\n      "name": "What camera systems and lighting do you employ?",\n      "acceptedAnswer": {\n        "@type": "Answer",\n        "text": "We shoot predominantly on Phase One IQ4 150MP medium format systems and Sony Alpha 61MP bodies paired with prime tilt-shift lenses and Broncolor studio strobes."\n      }\n    },\n    {\n      "@type": "Question",\n      "name": "How do we ship products to your studio?",\n      "acceptedAnswer": {\n        "@type": "Answer",\n        "text": "Once you book a production run, our logistics team issues pre-addressed shipping tags and an intake packing manifest. We store items in climate-controlled lockers and safely return or donate them upon project completion."\n      }\n    }\n  ]\n}', 1);
