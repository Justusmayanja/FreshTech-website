<?php
session_start();
require_once __DIR__ . '/config/config.php';

// Initialize cart if needed
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Helpers
function currency($amount) { return 'UGX ' . number_format((float)$amount, 0); }
function first_image_from_json($imagesJson) {
    $fallback = '/images/placeholder.svg';
    if (!$imagesJson) return $fallback;
    $arr = json_decode($imagesJson, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($arr) || empty($arr)) return $fallback;
    $first = $arr[0] ?? '';
    if (!$first) return $fallback;
    if (preg_match('~^https?://~i', $first)) return $first;
    return '/uploads/' . ltrim($first, '/');
}

// Handle Add to Cart
$cart_message = '';
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && $pdo && ($pdo instanceof PDO)) {
  $pid = (int)($_POST['product_id'] ?? 0);
  if ($pid > 0) {
    $stmt = $pdo->prepare("SELECT id, name, price_ugx, old_price_ugx, image_url, stock_quantity FROM products WHERE id = ? LIMIT 1");
    $stmt->execute([$pid]);
    $product = $stmt->fetch();
    if ($product) {
      $price = (float)($product['price_ugx']);
      $img = $product['image_url'] ?: '/images/placeholder.svg';
            if (isset($_SESSION['cart'][$pid])) {
                $_SESSION['cart'][$pid]['quantity'] += 1;
            } else {
                $_SESSION['cart'][$pid] = [
                  'id' => (int)$product['id'],
                  'name' => $product['name'],
                  'price' => $price,
                  'image' => $img,
                  'quantity' => 1,
                ];
            }
            $cart_message = 'Product added to cart!';
        }
    }
}

// Fetch products
$products = [];
$db_error = '';
$categories = [];
if ($pdo && ($pdo instanceof PDO)) {
  try {
    $q = $pdo->query("SELECT id, name, slug, description, price_ugx, old_price_ugx, image_url, category, stock_quantity FROM products WHERE stock_quantity > 0 ORDER BY category, id DESC");
    $products = $q->fetchAll();
    
    // Get unique categories for filter buttons
    foreach ($products as $p) {
      if (!empty($p['category']) && !in_array($p['category'], $categories)) {
        $categories[] = $p['category'];
      }
    }
    sort($categories);
  } catch (Exception $e) {
    $products = [];
    // Database error - use fallback sample data
  }
}

// Fallback sample products if database is not connected or no products found
if (empty($products)) {
  $products = [
    [
      'id' => 1,
      'name' => 'Wireless Headphones',
      'slug' => 'wireless-headphones-1',
      'description' => 'High-quality wireless headphones with excellent sound and comfort',
      'price_ugx' => 180000,
      'old_price_ugx' => 252000,
      'image_url' => 'head1.jpg',
      'category' => 'Headphones',
      'stock_quantity' => 20
    ],
    [
      'id' => 2,
      'name' => 'Premium Headphones',
      'slug' => 'premium-headphones',
      'description' => 'High-quality wireless headphones with excellent sound and comfort',
      'price_ugx' => 180000,
      'old_price_ugx' => 252000,
      'image_url' => 'head2.jpg',
      'category' => 'Headphones',
      'stock_quantity' => 20
    ],
    [
      'id' => 3,
      'name' => 'Bluetooth Headphones',
      'slug' => 'bluetooth-headphones',
      'description' => 'High-quality wireless headphones with excellent sound and comfort',
      'price_ugx' => 180000,
      'old_price_ugx' => 252000,
      'image_url' => 'head3.jpg',
      'category' => 'Headphones',
      'stock_quantity' => 20
    ],
    [
      'id' => 4,
      'name' => 'Phone Holder',
      'slug' => 'phone-holder',
      'description' => 'Durable phone/device holder for convenient viewing and protection',
      'price_ugx' => 45000,
      'old_price_ugx' => 63000,
      'image_url' => 'holder3.jpg',
      'category' => 'Holders',
      'stock_quantity' => 20
    ],
    [
      'id' => 5,
      'name' => 'iPhone Holder',
      'slug' => 'iphone-holder',
      'description' => 'Durable phone/device holder for convenient viewing and protection',
      'price_ugx' => 45000,
      'old_price_ugx' => 63000,
      'image_url' => 'iphone holder.jpg',
      'category' => 'Holders',
      'stock_quantity' => 20
    ],
    [
      'id' => 6,
      'name' => 'Wireless Earbuds Pro',
      'slug' => 'wireless-earbuds-pro',
      'description' => 'Premium wireless earbuds with noise cancellation and long battery life',
      'price_ugx' => 150000,
      'old_price_ugx' => 210000,
      'image_url' => 'pod1.jpg',
      'category' => 'Earbuds',
      'stock_quantity' => 20
    ],
    [
      'id' => 7,
      'name' => 'AirPods Style Earbuds',
      'slug' => 'airpods-style-earbuds',
      'description' => 'Premium wireless earbuds with noise cancellation and long battery life',
      'price_ugx' => 150000,
      'old_price_ugx' => 210000,
      'image_url' => 'pod2.jpg',
      'category' => 'Earbuds',
      'stock_quantity' => 20
    ],
    [
      'id' => 8,
      'name' => 'Portable Power Bank',
      'slug' => 'portable-power-bank',
      'description' => 'Portable high-capacity power bank for fast charging on the go',
      'price_ugx' => 120000,
      'old_price_ugx' => 168000,
      'image_url' => 'power bank.jpg',
      'category' => 'Power Banks',
      'stock_quantity' => 20
    ],
    [
      'id' => 9,
      'name' => 'Smart Watch Series 1',
      'slug' => 'smart-watch-1',
      'description' => 'Feature-rich smart watch with fitness tracking and notifications',
      'price_ugx' => 350000,
      'old_price_ugx' => 490000,
      'image_url' => 'watch1.jpg',
      'category' => 'Smartwatches',
      'stock_quantity' => 20
    ],
    [
      'id' => 10,
      'name' => 'Smart Watch Series 2',
      'slug' => 'smart-watch-2',
      'description' => 'Feature-rich smart watch with fitness tracking and notifications',
      'price_ugx' => 350000,
      'old_price_ugx' => 490000,
      'image_url' => 'watch2.jpg',
      'category' => 'Smartwatches',
      'stock_quantity' => 20
    ],
    [
      'id' => 11,
      'name' => 'Smart Watch Series 3',
      'slug' => 'smart-watch-3',
      'description' => 'Feature-rich smart watch with fitness tracking and notifications',
      'price_ugx' => 350000,
      'old_price_ugx' => 490000,
      'image_url' => 'watch3.jpg',
      'category' => 'Smartwatches',
      'stock_quantity' => 20
    ]
  ];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shop | PulseTech Solutions</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
<header>
  <div class="container navbar">
    <div class="logo">
      <div class="mark">FT</div>
      <div>
        <div>PulseTech Solutions</div>
        <small class="subtle">Digital Agency • Tech Store</small>
      </div>
    </div>
    <nav class="nav-links">
      <a href="index.html">Home</a>
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
      <a class="active" href="/shop.php">Shop</a>
      <a href="about.php">About Us</a>
      <a href="contact.php">Contact Us</a>
      <?php
      $cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
      if ($cart_count > 0):
      ?>
        <a href="cart.php" class="cart-badge">
          <i class="fa-solid fa-shopping-cart"></i>
          <span class="badge"><?php echo $cart_count; ?></span>
        </a>
      <?php endif; ?>
    </nav>
    <div class="mobile-nav">
      <button class="menu-toggle" data-menu-toggle>☰</button>
    </div>
  </div>
</header>

<main>
  <section class="hero" style="background: linear-gradient(rgba(10, 26, 47, 0.85), rgba(10, 26, 47, 0.85)), url('https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=1600&h=600&fit=crop') center/cover; color: white; padding: 100px 0;">
    <div class="container">
      <div class="badge">Premium Tech Accessories</div>
      <h1>Shop Our Collection</h1>
      <p>Curated selection of phone cases, chargers, earbuds, smart watches, and tech gadgets. Quality products at competitive prices.</p>
    </div>
  </section>

  <section class="section">
    <div class="container">

    <?php if($cart_message): ?>
      <div class="alert alert-success" style="margin-bottom: 2rem; padding: 1rem; background: #d4edda; color: #155724; border-radius: 8px;">
        <i class="fa-solid fa-check-circle mr-2"></i><?php echo $cart_message; ?>
      </div>
    <?php endif; ?>

      <!-- Category Filters + Search Bar (Same Line) -->
      <div style="display: flex; justify-content: space-between; align-items: center; gap: 2rem; margin-bottom: 2rem; flex-wrap: wrap;">
        
        <!-- Filter Buttons -->
        <div class="filters" style="margin-bottom: 0; display: flex; gap: 8px; flex-wrap: wrap;">
          <button type="button" class="filter-btn active" data-filter="all">All Products</button>
          <?php 
            $displayed_cats = array_slice($categories, 0, 3);
            foreach ($displayed_cats as $cat): 
          ?>
            <button type="button" class="filter-btn" data-filter="<?php echo htmlspecialchars(strtolower($cat)); ?>"><?php echo htmlspecialchars($cat); ?></button>
          <?php endforeach; ?>
        </div>

        <!-- Search Bar -->
        <input 
          type="text" 
          id="product-search" 
          placeholder="🔍 Search..." 
          style="padding: 10px 14px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--card-bg); color: var(--text); font-size: 0.95rem; min-width: 200px;"
        />
      </div>

      <p id="search-results" style="margin-bottom: 1.5rem; font-size: 0.85rem; color: var(--gray-400); height: 18px;"></p>

      <div class="portfolio-grid shop-grid" id="products-grid">
        <?php foreach ($products as $p):
          $price = (float)($p['price_ugx']);
          $old = $p['old_price_ugx'];
          // Use uploads folder for images (where admin saves them)
          $img = !empty($p['image_url']) ? 'uploads/' . basename($p['image_url']) : 'images/placeholder.svg';
          $category_attr = !empty($p['category']) ? strtolower($p['category']) : 'uncategorized';
        ?>
          <div class="portfolio-item" data-category="<?php echo htmlspecialchars($category_attr); ?>" data-name="<?php echo htmlspecialchars(strtolower($p['name'])); ?>">
          <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" onerror="this.onerror=null;this.src='images/placeholder.svg'" />
          <div class="overlay">
            <div class="badge-accent">Product</div>
            <h4><?php echo htmlspecialchars($p['name']); ?></h4>
            <p><?php echo htmlspecialchars($p['description']); ?></p>
            <div class="price" style="font-size: 1.3rem; margin: 1rem 0; color: var(--cyan);">
              <?php echo currency($price); ?>
              <?php if ($old): ?>
                <span style="color: #94a3b8; text-decoration: line-through; font-size: 0.9rem; margin-left: 8px;">
                  <?php echo currency((float)$old); ?>
                </span>
              <?php endif; ?>
            </div>
            <form method="POST" action="shop.php">
              <input type="hidden" name="add_to_cart" value="1" />
              <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>" />
              <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fa-solid fa-cart-plus"></i> Add to Cart
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
      </div>

    </div>
  </section>
</main>

<footer>
  <div class="container">
    <div class="footer-grid">
      <div>
        <h4>PulseTech Solutions</h4>
        <p>Premium digital agency & tech store</p>
      </div>
      <div>
        <h4>Explore</h4>
        <div class="grid">
          <a href="index.html">Home</a>
          <a href="services.html">Services</a>
          <a href="portfolio.html">Portfolio</a>
          <a href="/shop.php">Shop</a>
          <a href="about.php">About Us</a>
          <a href="contact.html">Contact Us</a>
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
  </div>
</footer>

<script src="js/main.clean.js"></script>
<script src="/js/image-modal.js"></script>
<style>
/* Product image sizing based on filter state */
.shop-grid .portfolio-item img {
  height: 260px;
  transition: height 0.3s ease;
}

/* Normal grid layout - 4 columns */
.shop-grid {
  grid-template-columns: repeat(4, 1fr);
}

/* When filtering by specific category (not all), make items and images smaller */
body.category-filtered .shop-grid {
  grid-template-columns: repeat(6, 1fr);
}

body.category-filtered .shop-grid .portfolio-item:not(.hidden) img {
  height: 180px;
}

/* Responsive grid */
@media (max-width: 1400px) {
  .shop-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  body.category-filtered .shop-grid {
    grid-template-columns: repeat(5, 1fr);
  }
}

@media (max-width: 1024px) {
  .shop-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  body.category-filtered .shop-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 768px) {
  .shop-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  body.category-filtered .shop-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  [style*="display: flex"][style*="justify-content: space-between"] {
    flex-direction: column;
    align-items: stretch !important;
  }
  #product-search {
    min-width: 100% !important;
  }
}

@media (max-width: 480px) {
  .shop-grid {
    grid-template-columns: 1fr;
  }
  body.category-filtered .shop-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
<script>
// Product Search Functionality
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('product-search');
  const searchResults = document.getElementById('search-results');
  const productItems = document.querySelectorAll('.portfolio-item');
  const filterBtns = document.querySelectorAll('.filter-btn');
  
  // Update body class when filter changes
  filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      if (this.dataset.filter === 'all') {
        document.body.classList.remove('category-filtered');
      } else {
        document.body.classList.add('category-filtered');
      }
    });
  });
  
  if (searchInput && productItems.length > 0) {
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase().trim();
      let visibleCount = 0;
      
      productItems.forEach(function(item) {
        if (item.classList.contains('hidden')) return; // Skip already filtered items
        
        const productName = item.getAttribute('data-name') || '';
        
        if (searchTerm === '' || productName.includes(searchTerm)) {
          item.style.display = '';
          visibleCount++;
        } else {
          item.style.display = 'none';
        }
      });
      
      // Update search results text
      if (searchTerm === '') {
        searchResults.textContent = '';
      } else if (visibleCount === 0) {
        searchResults.textContent = 'No products found';
        searchResults.style.color = '#e63946';
      } else {
        searchResults.textContent = visibleCount + ' product' + (visibleCount !== 1 ? 's' : '') + ' found';
        searchResults.style.color = 'var(--cyan)';
      }
    });
  }
});
</script>
</body>
</html>
