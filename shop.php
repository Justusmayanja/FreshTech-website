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
        $stmt = $pdo->prepare("SELECT id, name, price, sale_price, images, stock FROM products WHERE id = ? LIMIT 1");
        $stmt->execute([$pid]);
        $product = $stmt->fetch();
        if ($product) {
            $price = (float)($product['sale_price'] ?: $product['price']);
            $img = first_image_from_json($product['images']);
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
if ($pdo && ($pdo instanceof PDO)) {
    try {
        $q = $pdo->query("SELECT id, name, slug, description, price, sale_price, images, stock FROM products WHERE stock > 0 ORDER BY id DESC");
        $products = $q->fetchAll();
    } catch (Exception $e) {
        $products = [];
        $db_error = 'Unable to load products. Database connection issue.';
    }
} else {
    $db_error = 'Database is not connected. Please check your database configuration.';
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
          <a href="services/ui-ux.html">UI/UX Design</a>
          <a href="services/ecommerce.html">E-commerce Solutions</a>
        </div>
      </div>
      <a href="portfolio.html">Portfolio</a>
      <a class="active" href="/shop.php">Shop</a>
      <a href="about.html">About Us</a>
      <a href="contact.html">Contact Us</a>
      <?php
      $cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
      if ($cart_count > 0):
      ?>
        <a href="cart.php" class="cart-badge">
          <i class="fa-solid fa-shopping-cart"></i>
          <span class="badge"><?php echo $cart_count; ?></span>
        </a>
      <?php endif; ?>
      <button class="theme-toggle" data-theme-toggle>☾ / ☀</button>
    </nav>
    <div class="mobile-nav">
      <button class="theme-toggle" data-theme-toggle>☾</button>
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

    <?php if(!empty($db_error)): ?>
      <div class="alert alert-error" style="margin-bottom: 2rem; padding: 1rem; background: #f8d7da; color: #721c24; border-radius: 8px;">
        <i class="fa-solid fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($db_error); ?>
      </div>
    <?php endif; ?>

      <div class="portfolio-grid">
      <?php foreach ($products as $p):
        $price = (float)($p['sale_price'] ?: $p['price']);
        $img = first_image_from_json($p['images']);
      ?>
        <div class="portfolio-item">
          <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" onerror="this.onerror=null;this.src='images/placeholder.svg'" />
          <div class="overlay">
            <div class="badge-accent">Product</div>
            <h4><?php echo htmlspecialchars($p['name']); ?></h4>
            <p><?php echo htmlspecialchars($p['description']); ?></p>
            <div class="price" style="font-size: 1.3rem; margin: 1rem 0; color: var(--cyan);"><?php echo currency($price); ?></div>
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
        <a href="services.html">Services</a>
        <a href="portfolio.html">Portfolio</a>
        <a href="/shop.php">Shop</a>
      </div>
      <div>
        <h4>Connect</h4>
        <a href="about.html">About Us</a>
        <a href="contact.html">Contact</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 PulseTech Solutions. All rights reserved.</p>
    </div>
  </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
