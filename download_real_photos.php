<?php
/**
 * ECOMSHOOT - Real Photography Asset Ingestion Script
 * Downloads high-resolution, commercial-grade photography from curated Unsplash collections
 */

$images = [
    // Hero & General
    'assets/images/hero/hero-main.jpg' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1200&h=900&q=85',
    'assets/images/hero/hero-commercial.jpg' => 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=800&h=600&q=85',
    
    // Services (All 9 Core Services)
    'assets/images/services/product-photography.jpg' => 'https://images.unsplash.com/photo-1526947425960-945c6e72858f?auto=format&fit=crop&w=1000&h=700&q=85', // Skincare cosmetics
    'assets/images/services/ecommerce-product-photography.jpg' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&h=700&q=85', // Red Nike sneaker catalog
    'assets/images/services/lifestyle-product-photography.jpg' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1000&h=700&q=85', // Contextual home lifestyle
    'assets/images/services/fashion-product-photography.jpg' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1000&h=700&q=85', // Fashion apparel styling
    'assets/images/services/creative-product-photography.jpg' => 'https://images.unsplash.com/photo-1550572017-edd951aa8f72?auto=format&fit=crop&w=1000&h=700&q=85', // Splash & vibrant advertising
    'assets/images/services/jewellery-product-photography.jpg' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=1000&h=700&q=85', // Fine diamond ring macro
    'assets/images/services/product-photo-editing-retouching.jpg' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=1000&h=700&q=85', // High-end headphones detail
    'assets/images/services/white-background-product-photography.jpg' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=1000&h=700&q=85', // Pure white background medicine/bottle
    'assets/images/services/professional-product-photography.jpg' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=1000&h=700&q=85', // Pro camera lens & studio rig
    
    // Portfolio Items
    'assets/images/portfolio/portfolio-1.jpg' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=1000&h=750&q=85', // Luxury perfume bottle
    'assets/images/portfolio/portfolio-2.jpg' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=1000&h=750&q=85', // Matte wireless headphones
    'assets/images/portfolio/portfolio-3.jpg' => 'https://images.unsplash.com/photo-1603561591411-07134e71a2a9?auto=format&fit=crop&w=1000&h=750&q=85', // Diamond ring macro
    'assets/images/portfolio/portfolio-4.jpg' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=1000&h=750&q=85', // Ceramic cup coffee
    'assets/images/portfolio/portfolio-5.jpg' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=1000&h=750&q=85', // Tailored suit jacket
    'assets/images/portfolio/portfolio-6.jpg' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=1000&h=750&q=85', // Cold glass cocktail splash
    'assets/images/portfolio/portfolio-7.jpg' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=1000&h=750&q=85', // Designer leather handbag
    'assets/images/portfolio/portfolio-8.jpg' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=1000&h=750&q=85', // Serum dropper bottle
    
    // About & Studio Behind The Scenes
    'assets/images/about/studio-bay.jpg' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?auto=format&fit=crop&w=1000&h=700&q=85', // Studio lighting setup
    'assets/images/about/camera-rig.jpg' => 'https://images.unsplash.com/photo-1502982720700-bfff97f2ecac?auto=format&fit=crop&w=1000&h=700&q=85', // Camera lens setup
    'assets/images/about/retouching-desk.jpg' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=1000&h=700&q=85', // Digital workstation

    // Team Portraits
    'assets/images/about/team-marcus.jpg' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&h=600&q=85',
    'assets/images/about/team-elena.jpg' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=600&h=600&q=85',
    'assets/images/about/team-david.jpg' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=600&h=600&q=85',

    // Testimonial Avatars
    'assets/images/testimonials/avatar-1.jpg' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=200&h=200&q=85',
    'assets/images/testimonials/avatar-2.jpg' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&h=200&q=85',
    'assets/images/testimonials/avatar-3.jpg' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=200&h=200&q=85'
];

foreach ($images as $targetFile => $url) {
    $dir = dirname($targetFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    echo "Downloading {$targetFile}... ";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && strlen($data) > 2000) {
        file_put_contents($targetFile, $data);
        echo "OK (" . round(strlen($data) / 1024, 1) . " KB)\n";
    } else {
        echo "FAILED (HTTP {$httpCode})\n";
    }
}

echo "Image download batch completed.\n";

