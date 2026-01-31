<?php
session_start();
require_once __DIR__ . '/config/config.php';

// Initialize cart if needed
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions (remove, update quantity, clear)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_item'])) {
        $product_id = (int)$_POST['product_id'];
        unset($_SESSION['cart'][$product_id]);
        header('Location: cart.php');
        exit;
    }
    
    if (isset($_POST['update_quantity'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = max(1, (int)$_POST['quantity']);
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        }
        header('Location: cart.php');
        exit;
    }
    
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        header('Location: cart.php');
        exit;
    }
}

// Calculate totals
$items = array_values($_SESSION['cart']);
$subtotal = 0.0;
foreach ($items as $item) {
    $subtotal += ((float)$item['price']) * ((int)$item['quantity']);
}
$total = $subtotal;

function currency($amount) { return 'UGX ' . number_format((float)$amount, 0); }
function cart_count() {
    $count = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Your Cart | PulseTech Solutions</title>
  <link rel="stylesheet" href="/css/styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: { cyanft: '#00D4FF', orangeft:'#FF6B00', navyft:'#0A1A2F' } } } };
  </script>
  <style>
    body {
      background: #f9fafb !important;
      color: #111827 !important;
    }
    *,
    *::before,
    *::after {
      animation: none !important;
      transition: none !important;
    }
    .cart-static table,
    .cart-static th,
    .cart-static td {
      background: #ffffff !important;
      color: #111827 !important;
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-900">
  <?php include __DIR__ . '/header.php'; ?>

  <main class="cart-static max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-extrabold text-navyft mb-8">Shopping Cart</h1>

    <?php if (empty($items)): ?>
      <div class="bg-white border rounded-xl p-12 text-center">
        <i class="fa-solid fa-cart-shopping text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-600 text-lg mb-6">Your cart is empty</p>
        <a href="/shop.php" class="inline-flex items-center gap-2 px-6 py-3 bg-cyanft text-[#00111F] font-bold rounded-lg hover:brightness-110 transition">
          <i class="fa-solid fa-arrow-left"></i>
          Back to Shop
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2 bg-white border rounded-xl overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-gray-50 border-b">
                <tr>
                  <th class="px-6 py-3 text-sm font-semibold text-gray-700">Product</th>
                  <th class="px-6 py-3 text-sm font-semibold text-gray-700">Price</th>
                  <th class="px-6 py-3 text-sm font-semibold text-gray-700">Quantity</th>
                  <th class="px-6 py-3 text-sm font-semibold text-gray-700">Subtotal</th>
                  <th class="px-6 py-3"></th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <?php foreach ($items as $item): 
                  $item_subtotal = ((float)$item['price']) * ((int)$item['quantity']);
                ?>
                <tr>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-4">
                      <div class="w-16 h-16 rounded-lg bg-gray-200 flex-shrink-0" style="background: linear-gradient(135deg, #e5e7eb, #d1d5db);"></div>
                      <div>
                        <p class="font-bold text-navyft"><?php echo htmlspecialchars($item['name']); ?></p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 font-bold text-navyft"><?php echo currency($item['price']); ?></td>
                  <td class="px-6 py-4">
                    <form method="POST" action="cart.php" class="inline-flex items-center gap-2">
                      <input type="hidden" name="update_quantity" value="1">
                      <input type="hidden" name="product_id" value="<?php echo (int)$item['id']; ?>">
                      <input type="number" min="1" name="quantity" value="<?php echo (int)$item['quantity']; ?>" class="w-16 border-2 border-cyanft rounded px-2 py-1 text-center bg-cyanft text-[#00111F] font-bold" />
                      <button type="submit" class="px-3 py-1 text-xs bg-cyanft text-[#00111F] hover:brightness-110 rounded transition font-semibold">Update</button>
                    </form>
                  </td>
                  <td class="px-6 py-4 font-bold text-navyft text-lg"><?php echo currency($item_subtotal); ?></td>
                  <td class="px-6 py-4">
                    <form method="POST" action="cart.php" onsubmit="return confirm('Remove this item?')">
                      <input type="hidden" name="remove_item" value="1">
                      <input type="hidden" name="product_id" value="<?php echo (int)$item['id']; ?>">
                      <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="px-6 py-4 bg-gray-50 border-t flex items-center justify-between">
            <form method="POST" action="cart.php" onsubmit="return confirm('Clear entire cart?')">
              <input type="hidden" name="clear_cart" value="1">
              <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-sm">
                <i class="fa-solid fa-trash-can mr-1"></i>Clear Cart
              </button>
            </form>
            <a href="/shop.php" class="text-cyanft hover:text-[#00D4FF] font-semibold text-sm">
              <i class="fa-solid fa-arrow-left mr-1"></i>Continue Shopping
            </a>
          </div>
        </div>
        <!-- Order Summary -->
        <aside class="bg-white border rounded-xl p-6 h-fit sticky top-20">
          <h2 class="text-xl font-bold text-navyft mb-6">Order Summary</h2>
          
          <div class="space-y-3 mb-6">
            <div class="flex items-center justify-between text-gray-700">
              <span>Subtotal</span>
              <span class="font-semibold"><?php echo currency($subtotal); ?></span>
            </div>
            <div class="border-t pt-3 flex items-center justify-between text-lg font-bold text-navyft">
              <span>Total</span>
              <span><?php echo currency($total); ?></span>
            </div>
          </div>

          <a href="/checkout.php" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-cyanft text-[#00111F] font-bold rounded-lg hover:brightness-110 transition">
            <i class="fa-solid fa-arrow-right"></i>
            Proceed to Checkout
          </a>
        </aside>
      </div>
    <?php endif; ?>
  </main>
  <script>
    // Mobile menu toggle only (no theme switching for Tailwind pages)
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const navLinks = document.querySelector('.nav-links');
    if (menuToggle && navLinks) {
      menuToggle.addEventListener('click', () => {
        navLinks.classList.toggle('active');
      });
    }
  </script>
</body>
</html>
