<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/config.php';

if (!function_exists('cart_count')) {
    function cart_count(): int {
        $count = 0;
        if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $count += (int)($item['quantity'] ?? 0);
            }
        }
        return $count;
    }
}

if (!function_exists('currency')) {
    function currency($amount): string {
        return 'UGX ' . number_format((float)$amount, 0);
    }
}
?>
<!-- Public Site Header -->
<header>
  <div class="container navbar">
    <div class="logo">
      <img src="/images/PulseTech__2_-removebg-preview.png" alt="PulseTech Solutions logo" class="logo-image" />
      <div>
        <div>PulseTech Solutions</div>
        <div class="subtle">Bringing innovation to grow your business</div>
      </div>
    </div>
    <nav class="nav-links">
      <a href="/index.html">Home</a>
      <div class="dropdown">
        <a href="/services.html">Services ▼</a>
        <div class="dropdown-menu">
          <a href="/services/website-development.html">Website Development</a>
          <a href="/services/graphic-branding.html">Graphic Design & Branding</a>
          <a href="/services/ui-ux.html">Systems & App Development</a>
          <a href="/services/ecommerce.html">E-commerce Solutions</a>
        </div>
      </div>
      <a href="/portfolio.html">Portfolio</a>
      <a href="/shop.php">Shop</a>
      <a href="/about.php">About Us</a>
      <a href="/contact.php">Contact Us</a>
      <a href="/cart.php" class="cart-icon">
        <i class="fa-solid fa-cart-shopping"></i>
        <span class="cart-badge"><?php echo cart_count(); ?></span>
      </a>
    </nav>
    <div class="mobile-nav">
      <button class="menu-toggle" data-menu-toggle>☰</button>
    </div>
  </div>
</header>
