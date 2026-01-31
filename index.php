<?php
require_once __DIR__ . '/config/config.php';

// Fetch featured products from database (limit to 3, same order as shop page)
$featured_products = [];
if ($pdo && ($pdo instanceof PDO)) {
    try {
        // First try with stock_quantity > 0, if empty try without that filter
        $stmt = $pdo->query("SELECT id, name, slug, description, price_ugx, old_price_ugx, image_url, category, stock_quantity FROM products ORDER BY id DESC LIMIT 3");
        $featured_products = $stmt->fetchAll();
    } catch (Exception $e) {
        $featured_products = [];
    }
}

// Helper function to get first image
function get_product_image($image_url) {
    if (!$image_url) return '/images/placeholder.svg';
    
    // Check if it's JSON array
    $arr = json_decode($image_url, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($arr) && !empty($arr)) {
        $first = $arr[0] ?? '';
        if ($first) {
            if (preg_match('~^https?://~i', $first)) return $first;
            return '/uploads/' . ltrim($first, '/');
        }
    }
    
    // If it's a direct URL
    if (preg_match('~^https?://~i', $image_url)) return $image_url;
    
    // Otherwise it's a filename
    return '/uploads/' . ltrim($image_url, '/');
}

// Helper function for currency
function format_currency($amount) {
    return 'UGX ' . number_format((float)$amount, 0);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PulseTech Solutions | Digital Agency & Tech Accessories</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="home-page">
<header>
  <div class="container navbar">
    <div class="logo">
      <img src="images/PulseTech__2_-removebg-preview.png" alt="PulseTech Solutions logo" class="logo-image" />
      <div>
        <div>PulseTech Solutions</div>
        <div class="subtle">Bringing innovation to grow your business</div>
      </div>
    </div>
    <nav class="nav-links">
      <a class="active" href="index.php">Home</a>
      <div class="dropdown">
        <a href="services.html">Services ▼</a>
        <div class="dropdown-menu">
          <a href="services/website-development.html">Website Development</a>
          <a href="services/graphic-branding.html">Graphic Design & Branding</a>
          <a href="services/ui-ux.html">Systems & App Development</a>
          <a href="services/ecommerce.html">E-commerce Solutions</a>
        </div>
      </div>
      <a href="portfolio.html">Portfolio</a>
      <a href="/shop.php">Shop</a>
      <a href="about.php">About Us</a>
      <a href="contact.php">Contact Us</a>
    </nav>
    <div class="mobile-nav">
      <button class="menu-toggle" data-menu-toggle>☰</button>
    </div>
  </div>
</header>

<main>
  <section class="hero">
    <div class="container hero-grid">
      <div>
        <div class="badge">Premium • Fast • Future-ready</div>
        <h1>PulseTech Solutions</h1>
        <p class="subtle" style="margin: 4px 0 12px 0; color: #2d9cdb;">Bringing innovation to grow your business.</p>
        <p>Professional Websites, Systems & App Development, Stunning Branding & Premium Tech Accessories. We blend agency-grade design with custom software solutions and a curated tech shop for a unified digital presence.</p>
        <div class="btn-row">
          <a class="btn btn-primary" href="portfolio.html">View Our Work</a>
          <a class="btn btn-outline" href="/shop.php">Shop Now</a>
        </div>
        <div class="chip-row" style="margin-top:16px;">
          <span class="chip">Websites in 3 weeks</span>
          <span class="chip">Custom Software Solutions</span>
          <span class="chip">Premium device-ready mockups</span>
        </div>
      </div>
      <div class="hero-device">
        <div class="ribbon">Current Style</div>
        <div class="device-stack">
          <div class="device macbook"><img src="images/computer2.jpg" alt="PulseTech laptop showcase" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;" /></div>
          <div class="device ipad"><img src="images/comp3.jpg" alt="PulseTech tablet showcase" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;" /></div>
          <div class="device iphone"><img src="images/computer1.jpg" alt="PulseTech mobile showcase" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;" /></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-header">
        <div>
          <div class="tag-label">Services</div>
          <h2>Full-stack creative + commerce</h2>
          <p>Design, build, and launch digital experiences for Kampala and across Uganda with measurable ROI.</p>
        </div>
        <a class="btn btn-ghost" href="services.html">Explore Services</a>
      </div>
      <div class="grid grid-4">
        <div class="card">
          <div class="service-icon"><i class="fas fa-globe"></i></div>
          <h4>Website Development</h4>
          <p>High-performance sites in Webflow/Next.js with lightning speed and SEO baked in for Uganda-first audiences.</p>
          <div class="chip-row"><span class="chip">CMS</span><span class="chip">SEO</span><span class="chip">Analytics</span></div>
        </div>
        <div class="card">
          <div class="service-icon"><i class="fas fa-palette"></i></div>
          <h4>Graphic Design & Branding</h4>
          <p>Logo suites, brand guidelines, and sales assets with a premium look tailored to Kampala and Ugandan markets.</p>
          <div class="chip-row"><span class="chip">Logos</span><span class="chip">Brand Kits</span><span class="chip">Print</span></div>
        </div>
        <div class="card">
          <div class="service-icon"><i class="fas fa-code"></i></div>
          <h4>Systems & App Development</h4>
          <p>Custom software solutions, mobile apps, and backend systems built with scalable architecture for enterprise and startup needs.</p>
          <div class="chip-row"><span class="chip">Mobile Apps</span><span class="chip">Backend Systems</span><span class="chip">APIs</span></div>
        </div>
        <div class="card">
          <div class="service-icon"><i class="fas fa-shopping-cart"></i></div>
          <h4>E-commerce Solutions</h4>
          <p>Shopify/Headless commerce with localized payments and fulfillment for Kampala.</p>
          <div class="chip-row"><span class="chip">Shopify</span><span class="chip">Headless</span><span class="chip">Payments</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section" style="background: linear-gradient(135deg, var(--navy-2), var(--navy));">
    <div class="container">
      <div class="section-header">
        <div>
          <div class="tag-label">Featured Products</div>
          <h2>Latest from our shop</h2>
          <p>Check out our newest products and accessories.</p>
        </div>
        <a class="btn btn-ghost" href="/shop.php">Browse Shop</a>
      </div>
      <div class="carousel" id="featured-products-container">
        <?php if (!empty($featured_products)): ?>
          <?php foreach ($featured_products as $product): ?>
            <div class="card product">
              <?php if (!empty($product['old_price_ugx']) && $product['old_price_ugx'] > $product['price_ugx']): ?>
                <span class="badge-sale">Sale</span>
              <?php endif; ?>
              <img src="<?= htmlspecialchars(get_product_image($product['image_url'])) ?>" alt="<?= htmlspecialchars($product['name']) ?>" />
              <h4><?= htmlspecialchars($product['name']) ?></h4>
              <p class="subtle"><?= htmlspecialchars(substr($product['description'] ?? '', 0, 80)) ?><?= strlen($product['description'] ?? '') > 80 ? '...' : '' ?></p>
              <div class="price-row">
                <span class="price"><?= format_currency($product['price_ugx']) ?></span>
                <?php if (!empty($product['old_price_ugx']) && $product['old_price_ugx'] > $product['price_ugx']): ?>
                  <span class="old-price" style="text-decoration: line-through; color: #666;"><?= format_currency($product['old_price_ugx']) ?></span>
                <?php endif; ?>
              </div>
              <a href="/shop.php#product-<?= $product['id'] ?>" class="btn btn-primary">View Details</a>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-header">
        <div>
          <div class="tag-label">Portfolio</div>
          <h2>Launch-ready case studies</h2>
          <p>Six recent builds across web, branding, and e-commerce.</p>
        </div>
        <a class="btn btn-ghost" href="portfolio.html">See All</a>
      </div>
      <div class="portfolio-grid">
        <div class="portfolio-item" data-category="web" id="center-for-tomorrow">
          <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=540&h=360&fit=crop" alt="Center for Tomorrow NGO site" />
          <div class="overlay"><h4>Center for Tomorrow</h4><p>NGO website for community impact stories and donor journeys.</p></div>
        </div>
        <div class="portfolio-item" data-category="ecommerce" id="julie-crafts">
          <img src="images/computer2.jpg" alt="Julie Crafts e-commerce" />
          <div class="overlay"><h4>Julie Crafts</h4><p>Traditional crafts store with streamlined Kampala checkout.</p></div>
        </div>
        <div class="portfolio-item" data-category="web" id="kampala-health">
          <img src="images/comp3.jpg" alt="Kampala health platform" />
          <div class="overlay"><h4>Kampala Health Platform</h4><p>Patient portal UX with clinic directories and bookings.</p></div>
        </div>
        <div class="portfolio-item" data-category="branding">
          <img src="images/computer1.jpg" alt="Kla Streetwear branding" />
          <div class="overlay"><h4>Kla Streetwear</h4><p>Bold visual identity for Kampala clothing collective.</p></div>
        </div>
        <div class="portfolio-item" data-category="ecommerce">
          <img src="https://images.unsplash.com/photo-1561070791-2526d30994b5?w=540&h=360&fit=crop" alt="Device shop" />
          <div class="overlay"><h4>Gadget Hub Kampala</h4><p>Phone and accessories shop with localized payments.</p></div>
        </div>
        <div class="portfolio-item" data-category="branding">
          <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=540&h=360&fit=crop" alt="Artisan market" />
          <div class="overlay"><h4>Nile Artisan Market</h4><p>Brand kit and landing page for artisan collective.</p></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section" style="background: linear-gradient(135deg, var(--navy-2), var(--navy));">
    <div class="container">
      <div class="section-header">
        <div>
          <div class="tag-label">Testimonials</div>
          <h2>Trusted by founders & teams</h2>
        </div>
      </div>
      <div class="testimonials">
        <div class="testimonial">
          <p>"PulseTech delivered a donor‍friendly NGO website that highlights our impact stories and community programs, helping us reach more people in need."</p>
          <strong><a href="https://centerfortomorrow.org" style="color: var(--cyan); text-decoration: underline;">Center for Tomorrow</a> — NGO</strong>
        </div>
        <div class="testimonial">
          <p>"They built a secure, easy‑to‑use loan application platform for our clients. Applications, approvals, and updates now run smoothly online."</p>
          <strong>Jonakee Holdings Limited — Money Lending</strong>
        </div>
        <div class="testimonial">
          <p>"Our traditional crafts store now sells online with beautiful product pages and a fast, simple checkout tailored to Kampala shoppers."</p>
          <strong><a href="https://juliecrafts.com" style="color: var(--cyan); text-decoration: underline;">Julie Crafts</a> — Online Artisan Store</strong>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-header">
        <div>
          <div class="tag-label">Brands</div>
          <h2>Selected clients</h2>
        </div>
      </div>
      <div class="client-logos">
        <div class="logo-tile">NovaPay</div>
        <div class="logo-tile">BlueOrbit</div>
        <div class="logo-tile">PulseHealth</div>
        <div class="logo-tile">ShiftLabs</div>
        <div class="logo-tile">VeloCommerce</div>
        <div class="logo-tile">CloudMesa</div>
      </div>
    </div>
  </section>

  <section class="section" style="background: var(--gray-100);">
    <div class="container">
      <div class="section-header">
        <div>
          <div class="tag-label">Instagram</div>
          <h2>Live feed</h2>
          <p>Device mockups, brand drops, and product shoots.</p>
        </div>
        <a class="btn btn-ghost" href="#">Follow @freshtech</a>
      </div>
      <div class="grid grid-3">
        <div class="card">MacBook UI mockup</div>
        <div class="card">iPhone hero shot</div>
        <div class="card">Brand palette preview</div>
        <div class="card">Earbuds lifestyle</div>
        <div class="card">Packaging design</div>
        <div class="card">Smart watch closeup</div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-header">
        <div>
          <div class="tag-label">Ready?</div>
          <h2>Launch your site + store together</h2>
          <p>One partner for design, development, and accessories.</p>
        </div>
        <div class="btn-row">
          <a class="btn btn-primary" href="contact.html">Book a Call</a>
          <a class="btn btn-outline" href="/shop.php">Shop Accessories</a>
        </div>
      </div>
    </div>
  </section>
</main>

<footer class="footer">
  <div class="container footer-grid">
    <div>
      <div class="logo">
        <img src="images/PulseTech__2_-removebg-preview.png" alt="PulseTech Solutions logo" class="logo-image" />
        <div>PulseTech Solutions © 2025</div>
      </div>
      <p class="subtle">PulseTech Solutions is a leading digital agency based in Kampala, Uganda. We specialize in web development, systems & app development, branding, and e-commerce solutions while offering premium phone and computer accessories to elevate your digital experience.</p>
    </div>
    <div>
      <h4>Explore</h4>
      <div class="grid">
        <a href="index.php">Home</a>
        <a href="services.html">Services</a>
        <a href="portfolio.html">Portfolio</a>
        <a href="/shop.php">Shop</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact Us</a>
      
      </div>
    </div>
    <div>
      <h4>Contact Us</h4>
      <div class="grid">
        <p class="subtle" style="margin: 0 0 8px 0;">📍 Kampala, Uganda</p>
        <p class="subtle" style="margin: 0 0 8px 0;">📧 hello@pulsetechsolutions.com</p>
        <p class="subtle" style="margin: 0 0 16px 0;">📱 +256752895268</p>
      </div>
      <div class="social-row">
        <a class="social" href="https://www.instagram.com" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        <a class="social" href="https://www.linkedin.com" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        <a class="social" href="https://twitter.com" aria-label="X (Twitter)"><i class="fa-brands fa-x-twitter"></i></a>
        <a class="social" href="https://www.tiktok.com" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
      </div>
    </div>
  </div>
</footer>

<script src="js/main.clean.js"></script>
<script src="js/image-modal.js"></script>
</body>
</html>
