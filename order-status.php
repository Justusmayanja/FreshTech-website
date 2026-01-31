<?php
session_start();
require_once __DIR__ . '/config/config.php';

function currency($amount) { return 'UGX ' . number_format((float)$amount, 0); }

$orderNumber = trim($_GET['order'] ?? '');
$lookupEmail = trim($_GET['email'] ?? '');
$lookupPhone = trim($_GET['phone'] ?? '');

$order = null;
$items = [];
$error = '';

if ($orderNumber !== '' && isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();

        if ($order) {
            // Optional verification if email/phone provided
            if ($lookupEmail !== '' && strcasecmp($lookupEmail, (string)$order['email']) !== 0) {
                $order = null;
                $error = 'Email does not match this order number.';
            } elseif ($lookupPhone !== '' && preg_replace('/\D+/', '', $lookupPhone) !== preg_replace('/\D+/', '', (string)$order['phone'])) {
                $order = null;
                $error = 'Phone number does not match this order number.';
            }
        } else {
            $error = 'Order not found. Please check your order number.';
        }

        if ($order) {
            $it = $pdo->prepare("SELECT product_name, quantity, unit_price AS price, subtotal FROM order_items WHERE order_id = ?");
            $it->execute([(int)$order['id']]);
            $items = $it->fetchAll();
        }
    } catch (Exception $e) {
        $error = 'Unable to load order details at the moment.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Status | PulseTech Solutions</title>
  <link rel="stylesheet" href="/css/styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: { cyanft: '#00D4FF', orangeft:'#FF6B00', navyft:'#0A1A2F' } } } };
  </script>
</head>
<body class="bg-gray-50 text-gray-900">
  <?php include __DIR__ . '/header.php'; ?>

  <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white border rounded-xl p-8">
      <div class="flex items-center gap-3 mb-6">
        <i class="fa-solid fa-receipt text-3xl text-[#00D4FF]"></i>
        <h1 class="text-2xl font-extrabold text-navyft">Find Your Order</h1>
      </div>

      <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Order Number</label>
          <input name="order" value="<?php echo htmlspecialchars($orderNumber); ?>" placeholder="FT-YYYYMMDD-XXXXXX" class="w-full border rounded-lg px-3 py-2" required />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Email (optional)</label>
          <input name="email" value="<?php echo htmlspecialchars($lookupEmail); ?>" type="email" class="w-full border rounded-lg px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Phone (optional)</label>
          <input name="phone" value="<?php echo htmlspecialchars($lookupPhone); ?>" type="tel" class="w-full border rounded-lg px-3 py-2" />
        </div>
        <div class="md:col-span-3">
          <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-cyanft text-[#00111F] font-bold">
            <i class="fa-solid fa-magnifying-glass"></i>
            View Order
          </button>
        </div>
      </form>

      <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <?php if ($order): ?>
        <div class="border rounded-lg p-4 mb-6">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <div class="text-sm text-gray-600">Order Number</div>
              <div class="text-lg font-bold text-navyft"><?php echo htmlspecialchars($order['order_number']); ?></div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Status</div>
              <div class="text-lg font-semibold text-navyft"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Total</div>
              <div class="text-lg font-bold text-navyft"><?php echo currency($order['total_amount']); ?></div>
            </div>
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
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
