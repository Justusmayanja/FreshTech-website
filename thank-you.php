<?php
session_start();
require_once __DIR__ . '/config/config.php';

$orderNumber = $_GET['order'] ?? '';
$order = null;
$items = [];

if ($orderNumber) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
    if ($order) {
        $it = $pdo->prepare("SELECT product_name, quantity, unit_price AS price, subtotal FROM order_items WHERE order_id = ?");
        $it->execute([(int)$order['id']]);
        $items = $it->fetchAll();
    }
}

if (!$order) {
    header('Location: /shop.php');
    exit;
}

function currency($amount) { return 'UGX ' . number_format((float)$amount, 0); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Confirmation | PulseTech Solutions</title>
  <link rel="stylesheet" href="/css/styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: { cyanft: '#00D4FF', orangeft:'#FF6B00', navyft:'#0A1A2F' } } } };
  </script>
</head>
<body class="bg-gray-50 text-gray-900">
  <?php include __DIR__ . '/header.php'; ?>

  <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white border rounded-xl p-8">
      <div class="flex items-center gap-3 mb-4">
        <i class="fa-regular fa-circle-check text-3xl text-[#00D4FF]"></i>
        <h1 class="text-2xl font-extrabold text-navyft">Thank you! Your order is confirmed.</h1>
      </div>
      <p class="text-gray-700 mb-6">Order Number: <span class="font-bold text-navyft"><?php echo htmlspecialchars($order['order_number']); ?></span></p>
      <div class="mb-6">
        <a href="/order-status.php?order=<?php echo urlencode($order['order_number']); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border font-semibold">
          <i class="fa-solid fa-receipt"></i>
          View this order anytime
        </a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="font-bold text-navyft mb-2">Customer</div>
          <p class="text-sm text-gray-700"><?php echo htmlspecialchars($order['customer_name']); ?></p>
          <p class="text-sm text-gray-700"><?php echo htmlspecialchars($order['email']); ?></p>
          <p class="text-sm text-gray-700"><?php echo htmlspecialchars($order['phone']); ?></p>
          <p class="text-sm text-gray-700"><?php echo htmlspecialchars($order['address']); ?></p>
        </div>
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="font-bold text-navyft mb-2">Payment</div>
          <p class="text-sm text-gray-700">Method: <span class="font-semibold text-navyft"><?php echo strtoupper($order['payment_method']); ?></span></p>
          <?php if($order['payment_method'] === 'mobile_money'): ?>
            <p class="text-sm text-gray-600 mt-2">We will confirm once payment is received.</p>
          <?php else: ?>
            <p class="text-sm text-gray-600 mt-2">Your order will be delivered soon. Please prepare payment on delivery.</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="mb-4">
        <div class="font-bold text-navyft mb-2">Items</div>
        <div class="bg-white border rounded-lg overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-4 py-3 text-left">Product</th>
                <th class="px-4 py-3 text-left">Qty</th>
                <th class="px-4 py-3 text-left">Price</th>
                <th class="px-4 py-3 text-left">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $it): ?>
                <tr class="border-b">
                  <td class="px-4 py-3"><?php echo htmlspecialchars($it['product_name']); ?></td>
                  <td class="px-4 py-3"><?php echo (int)$it['quantity']; ?></td>
                  <td class="px-4 py-3"><?php echo currency($it['price']); ?></td>
                  <td class="px-4 py-3 font-semibold text-navyft"><?php echo currency($it['subtotal']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="flex items-center justify-between text-gray-700">
        <span>Total</span>
        <span class="text-2xl font-extrabold text-navyft"><?php echo currency($order['total_amount']); ?></span>
      </div>

      <div class="mt-8 flex items-center gap-3">
        <a href="/shop.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-cyanft text-[#00111F] font-bold">
          <i class="fa-solid fa-arrow-left"></i>
          Continue Shopping
        </a>
        <a href="/index.html" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border font-bold">
          Home
        </a>
      </div>
    </div>
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
