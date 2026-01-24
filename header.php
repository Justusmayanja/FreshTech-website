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
<header class="site-header" data-site-header>
  <div class="site-header__inner">
    <a href="/index.html" class="site-brand" aria-label="PulseTech Solutions home">
      <span class="site-brand__mark">FT</span>
      <span class="site-brand__text">
        <span class="site-brand__name">PulseTech Solutions</span>
        <span class="site-brand__tagline">Digital Agency & Tech Store</span>
      </span>
    </a>

    <nav class="site-nav" data-site-nav aria-label="Primary">
      <button class="site-nav__toggle" type="button" aria-expanded="false" aria-controls="site-nav-panel" aria-label="Toggle navigation" data-nav-toggle>
        <span class="site-nav__icon" aria-hidden="true"></span>
        <span class="sr-only">Toggle navigation</span>
      </button>

      <div class="site-nav__backdrop" data-nav-backdrop></div>

      <div class="site-nav__links" id="site-nav-panel" data-nav-panel role="dialog" aria-modal="true" tabindex="-1">
        <ul class="site-nav__list">
          <li><a href="/index.html">Home</a></li>
          <li><a href="/services.html">Services</a></li>
          <li><a href="/shop.php">Solutions</a></li>
          <li><a href="/portfolio.html">Portfolio</a></li>
          <li><a href="/about.html">About</a></li>
          <li><a href="/contact.html">Contact</a></li>
        </ul>
        <div class="site-nav__actions">
          <a class="site-nav__cart" href="/cart.php" aria-label="View cart">
            <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
            <span class="site-nav__badge"><?php echo cart_count(); ?></span>
          </a>
          <a class="btn btn-primary site-nav__cta" href="/contact.html">Get Started</a>
        </div>
      </div>
    </nav>
  </div>
</header>
