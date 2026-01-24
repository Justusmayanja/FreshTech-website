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
<!-- Global Header with Cart Badge -->
<header class="w-full bg-white shadow sticky top-0 z-40">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="h-16 flex items-center justify-between">
      <a href="/index.html" class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#00D4FF] to-[#FF6B00] text-white font-bold grid place-items-center">FT</div>
        <div class="font-extrabold text-[#0A1A2F]">PulseTech Solutions</div>
      </a>
      <nav class="flex items-center gap-4">
        <a href="/services.html" class="text-sm font-semibold text-gray-700 hover:text-[#0A1A2F]">Services</a>
        <a href="/portfolio.html" class="text-sm font-semibold text-gray-700 hover:text-[#0A1A2F]">Portfolio</a>
        <a href="/shop.php" class="text-sm font-semibold text-gray-700 hover:text-[#0A1A2F]">Shop</a>
        <a href="/contact.html" class="text-sm font-semibold text-gray-700 hover:text-[#0A1A2F]">Contact</a>
        <a href="/cart.php" class="relative inline-flex items-center justify-center w-11 h-11 rounded-full border border-gray-200 hover:border-[#00D4FF] transition">
          <i class="fa-solid fa-cart-shopping text-[#0A1A2F]"></i>
          <span class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-[11px] font-bold rounded-full bg-[#00D4FF] text-[#00111F]">
            <?php echo cart_count(); ?>
          </span>
        </a>
      </nav>
    </div>
  </div>
</header>
