<?php
/**
 * Graphic Asset Generator for Ecomshoot
 * Generates vector graphics and preview assets for services and portfolio
 */

$services = [
    'product-photography' => ['Commercial Product Photography', '#1E3A8A', '#2563EB', '#F97316', 'Studio Medium Format Capture'],
    'ecommerce-product-photography' => ['E-Commerce Product Photography', '#0F172A', '#1E293B', '#3B82F6', 'Multi-SKU Catalog Uniformity'],
    'lifestyle-product-photography' => ['Lifestyle Product Photography', '#4C1D95', '#7C3AED', '#EDE9FE', 'Staged Contextual Environments'],
    'fashion-product-photography' => ['Fashion & Apparel Photography', '#831843', '#DB2777', '#FCE7F3', '3D Ghost Mannequin & Model'],
    'creative-product-photography' => ['Creative & Advertising Photography', '#7C2D12', '#EA580C', '#FEF3C7', 'Splashes, Levitation & Art Direction'],
    'jewellery-product-photography' => ['Jewellery & Luxury Photography', '#064E3B', '#059669', '#FBBF24', 'Micro Macro Focus-Stacking'],
    'product-photo-editing-retouching' => ['Photo Editing & Retouching', '#1E1B4B', '#4F46E5', '#C7D2FE', 'Frequency Separation & Vector Clipping'],
    'white-background-product-photography' => ['White Background Packshots', '#1E293B', '#3B82F6', '#FFFFFF', 'Pure RGB #FFFFFF Amazon Compliant'],
    'professional-product-photography' => ['Professional Commercial Shoots', '#090D16', '#1E3A8A', '#FBBF24', 'Turnkey Production & Direction']
];

foreach ($services as $slug => $info) {
    [$title, $c1, $c2, $accent, $subtitle] = $info;
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600" width="800" height="600">
  <defs>
    <linearGradient id="bg_$slug" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="$c1" />
      <stop offset="100%" stop-color="$c2" />
    </linearGradient>
    <pattern id="pat_$slug" width="50" height="50" patternUnits="userSpaceOnUse">
      <circle cx="25" cy="25" r="1.5" fill="rgba(255,255,255,0.1)" />
    </pattern>
  </defs>
  <rect width="800" height="600" fill="url(#bg_$slug)" />
  <rect width="800" height="600" fill="url(#pat_$slug)" />
  
  <!-- Studio Lighting Ring & Aperture Graphic -->
  <g transform="translate(400, 240)">
    <circle r="140" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="30" />
    <circle r="110" fill="none" stroke="$accent" stroke-width="3" stroke-dasharray="10 8" opacity="0.8" />
    <circle r="85" fill="rgba(0,0,0,0.25)" stroke="rgba(255,255,255,0.4)" stroke-width="2" />
    
    <!-- Camera Lens Blades -->
    <path d="M -40 -30 L 30 -60 L 50 -10 Z" fill="rgba(255,255,255,0.2)" />
    <path d="M 30 -60 L 60 20 L 10 30 Z" fill="rgba(255,255,255,0.15)" />
    <path d="M 60 20 L -10 60 L -30 20 Z" fill="rgba(255,255,255,0.2)" />
    <path d="M -10 60 L -60 -10 L -20 -30 Z" fill="rgba(255,255,255,0.25)" />
    
    <circle r="30" fill="$accent" opacity="0.9" />
    <circle r="10" fill="#FFFFFF" />
  </g>

  <!-- Typography -->
  <rect x="100" y="420" width="600" height="130" rx="12" fill="rgba(15, 23, 42, 0.65)" stroke="rgba(255,255,255,0.15)" />
  <text x="400" y="465" fill="#FFFFFF" font-family="Space Grotesk, Arial, sans-serif" font-size="24" font-weight="700" text-anchor="middle">$title</text>
  <text x="400" y="505" fill="$accent" font-family="DM Sans, Arial, sans-serif" font-size="16" font-weight="500" text-anchor="middle" letter-spacing="1.5">$subtitle</text>
  <text x="400" y="532" fill="rgba(255,255,255,0.7)" font-family="DM Sans, Arial, sans-serif" font-size="13" text-anchor="middle">ECOMSHOOT STUDIO PRODUCTION</text>
</svg>
SVG;
    file_put_contents("assets/images/services/{$slug}.svg", $svg);
    file_put_contents("assets/images/services/{$slug}.jpg", $svg);
}

// Generate Portfolio Items
$portfolioItems = [
    'portfolio-1' => ['Luxury Botanical Perfume', '#1E3A8A', '#F97316', 'Liquid Splash & Macro Flare', 'Creative Commercial'],
    'portfolio-2' => ['Wireless ANC Headphones', '#0F172A', '#3B82F6', 'Matte Titanium & Audio Ergonomics', 'High-Tech Packshot'],
    'portfolio-3' => ['Diamond Solitaire Ring', '#064E3B', '#FBBF24', '60-Slice Focus Stacking Composite', 'Luxury Jewellery'],
    'portfolio-4' => ['Minimalist Ceramic Dinnerware', '#7C2D12', '#EDE9FE', 'Warm Kitchen Sunlight Staging', 'Lifestyle Editorial'],
    'portfolio-5' => ['Merino Wool Tailored Blazer', '#831843', '#F472B6', '3D Ghost Mannequin Form', 'Fashion & Apparel'],
    'portfolio-6' => ['Organic Cold-Pressed Juice', '#C2410C', '#FCD34D', 'Synchronized Water Droplet Burst', 'Beverage Advertising']
];

foreach ($portfolioItems as $slug => $info) {
    [$name, $c1, $accent, $desc, $cat] = $info;
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 650" width="900" height="650">
  <defs>
    <linearGradient id="pbg_$slug" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="$c1" />
      <stop offset="100%" stop-color="#020617" />
    </linearGradient>
  </defs>
  <rect width="900" height="650" fill="url(#pbg_$slug)" />
  
  <g transform="translate(450, 260)">
    <circle r="160" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="2" />
    <circle r="120" fill="none" stroke="$accent" stroke-width="1.5" opacity="0.6" />
    <rect x="-80" y="-80" width="160" height="160" rx="20" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.2)" stroke-width="2" transform="rotate(45)" />
    <circle r="40" fill="$accent" opacity="0.8" />
  </g>

  <!-- Editorial Card Label -->
  <rect x="80" y="470" width="740" height="130" rx="14" fill="rgba(15, 23, 42, 0.85)" stroke="rgba(255,255,255,0.1)" />
  <rect x="110" y="495" width="140" height="26" rx="6" fill="$accent" />
  <text x="180" y="512" fill="#0F172A" font-family="Space Grotesk, sans-serif" font-size="12" font-weight="700" text-anchor="middle">$cat</text>
  <text x="110" y="555" fill="#FFFFFF" font-family="Space Grotesk, sans-serif" font-size="26" font-weight="700">$name</text>
  <text x="110" y="582" fill="rgba(255,255,255,0.7)" font-family="DM Sans, sans-serif" font-size="14">$desc</text>
</svg>
SVG;
    file_put_contents("assets/images/portfolio/{$slug}.svg", $svg);
    file_put_contents("assets/images/portfolio/{$slug}.jpg", $svg);
}

// Generate Logo SVG
$logoSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 60" width="240" height="60">
  <defs>
    <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#2563EB" />
      <stop offset="100%" stop-color="#F97316" />
    </linearGradient>
  </defs>
  <!-- Camera Shutter Mark -->
  <g transform="translate(10, 8)">
    <rect x="0" y="0" width="44" height="44" rx="12" fill="url(#logoGrad)" />
    <circle cx="22" cy="22" r="14" fill="none" stroke="#FFFFFF" stroke-width="2.5" />
    <circle cx="22" cy="22" r="6" fill="#FBBF24" />
    <circle cx="32" cy="12" r="3" fill="#FFFFFF" opacity="0.9" />
  </g>
  <!-- Text Brand -->
  <text x="64" y="36" fill="#0F172A" font-family="Space Grotesk, sans-serif" font-size="25" font-weight="800" letter-spacing="-0.5">ECOM<tspan fill="#F97316">SHOOT</tspan></text>
</svg>
SVG;
file_put_contents("assets/images/logo.svg", $logoSvg);
file_put_contents("assets/images/logo.png", $logoSvg);

echo "Assets generated successfully.\n";

