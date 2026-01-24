<?php
session_start();
require_once __DIR__ . '/config/config.php';

function currency($amount) { return 'UGX ' . number_format((float)$amount, 0); }

// Guard: cart must have items
$cart = $_SESSION['cart'] ?? [];
if (!$cart || !is_array($cart)) {
    header('Location: cart.php');
    exit;
}

// Compute totals
$items = array_values($cart);
$subtotal = 0.0;
foreach ($items as $it) {
    $subtotal += ((float)$it['price']) * ((int)$it['quantity']);
}
$total = $subtotal;

$errors = [];
$success = false;
$orderNumber = '';

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $payment_method = ($_POST['payment_method'] ?? 'mobile_money') === 'cod' ? 'cod' : 'mobile_money';
    $txn_id = trim($_POST['transaction_id'] ?? '');

    if ($name === '') $errors['full_name'] = 'Full Name is required';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid Email is required';
    if ($phone === '') $errors['phone'] = 'Phone Number is required';
    if ($address === '') $errors['address'] = 'Delivery Address is required';
    if ($payment_method === 'mobile_money' && $txn_id === '') $errors['transaction_id'] = 'Transaction ID is required for Mobile Money';

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // Create order number
            $orderNumber = 'FT-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

            // Insert order
            $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_name, email, phone, address, notes, payment_method, transaction_id, total_amount, status, created_at) VALUES (?,?,?,?,?,?,?,?,?,'pending',NOW())");
            $stmt->execute([
                $orderNumber,
                $name,
                $email,
                $phone,
                $address,
                $notes,
                $payment_method,
                $payment_method === 'mobile_money' ? $txn_id : null,
                $total,
            ]);
            $order_id = (int)$pdo->lastInsertId();

            // Insert order items & reduce stock
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (?,?,?,?,?,?)");
            $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

            foreach ($items as $it) {
                $pid = (int)$it['id'];
                $qty = (int)$it['quantity'];
                $price = (float)$it['price'];
                $sub = $qty * $price;

                $itemStmt->execute([$order_id, $pid, $it['name'], $qty, $price, $sub]);

                // Reduce stock; ensure not below zero
                $stockStmt->execute([$qty, $pid, $qty]);
                if ($stockStmt->rowCount() === 0) {
                    // rollback if stock insufficient
                    throw new Exception('Insufficient stock for product ID ' . $pid);
                }
            }

            $pdo->commit();
            $_SESSION['cart'] = [];
            header('Location: thank-you.php?order=' . urlencode($orderNumber));
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors['general'] = 'Failed to place order: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout | PulseTech Solutions</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: { cyanft: '#00D4FF', orangeft:'#FF6B00', navyft:'#0A1A2F' } } } };
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-50 text-gray-900">
  <?php include __DIR__ . '/header.php'; ?>

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-extrabold text-navyft mb-6">Checkout</h1>

    <?php if(!empty($errors['general'])): ?>
      <div class="mb-6 p-4 rounded-lg border border-red-300 bg-red-50 text-red-700 font-semibold">
        <i class="fa-solid fa-circle-exclamation mr-2"></i><?php echo htmlspecialchars($errors['general']); ?>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <form method="post" class="lg:col-span-2 bg-white border rounded-xl p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name *</label>
            <input name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2" required />
            <?php if(!empty($errors['full_name'])) echo '<p class="text-sm text-red-600 mt-1">'.htmlspecialchars($errors['full_name']).'</p>'; ?>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Email *</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2" required />
            <?php if(!empty($errors['email'])) echo '<p class="text-sm text-red-600 mt-1">'.htmlspecialchars($errors['email']).'</p>'; ?>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Number *</label>
            <input name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2" required />
            <?php if(!empty($errors['phone'])) echo '<p class="text-sm text-red-600 mt-1">'.htmlspecialchars($errors['phone']).'</p>'; ?>
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Delivery Address *</label>
            <input name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2" required />
            <?php if(!empty($errors['address'])) echo '<p class="text-sm text-red-600 mt-1">'.htmlspecialchars($errors['address']).'</p>'; ?>
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Notes (Optional)</label>
          <textarea name="notes" class="w-full border rounded-lg px-3 py-2" rows="3"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
        </div>

        <div class="border rounded-xl p-4">
          <div class="font-bold text-navyft mb-2">Payment Method</div>
          <label class="flex items-center gap-3 mb-2">
            <input type="radio" name="payment_method" value="mobile_money" <?php echo (($_POST['payment_method'] ?? 'mobile_money') === 'mobile_money') ? 'checked' : ''; ?> />
            <span>Mobile Money (MTN/Airtel)</span>
          </label>
          <label class="flex items-center gap-3">
            <input type="radio" name="payment_method" value="cod" <?php echo (($_POST['payment_method'] ?? '') === 'cod') ? 'checked' : ''; ?> />
            <span>Cash on Delivery</span>
          </label>

          <div class="mt-4 bg-cyanft/10 border border-cyanft/30 rounded-lg p-4">
            <p class="text-sm text-gray-700"><strong>For Mobile Money:</strong> Pay <span class="font-bold text-navyft"><?php echo currency($total); ?></span> to <span class="font-bold">+256 700 000 000</span> and enter your Transaction ID below.</p>
            <div class="mt-3">
              <label class="block text-sm font-semibold text-gray-700 mb-1">Transaction ID (Required for Mobile Money)</label>
              <input name="transaction_id" value="<?php echo htmlspecialchars($_POST['transaction_id'] ?? ''); ?>" class="w-full border rounded-lg px-3 py-2" />
              <?php if(!empty($errors['transaction_id'])) echo '<p class="text-sm text-red-600 mt-1">'.htmlspecialchars($errors['transaction_id']).'</p>'; ?>
            </div>
          </div>
        </div>

        <button class="w-full inline-flex items-center justify-center gap-2 bg-cyanft text-[#00111F] font-bold py-3 rounded-lg hover:brightness-110 transition">
          Place Order
        </button>
      </form>

      <aside class="bg-white border rounded-xl p-6 h-max">
        <h2 class="text-xl font-bold text-navyft mb-3">Order Summary</h2>
        <ul class="divide-y">
          <?php foreach ($items as $it): ?>
            <li class="py-2 flex items-center justify-between text-sm">
              <span><?php echo htmlspecialchars($it['name']); ?> × <?php echo (int)$it['quantity']; ?></span>
              <span class="font-semibold"><?php echo currency((float)$it['price'] * (int)$it['quantity']); ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="mt-4 flex items-center justify-between text-gray-700">
          <span>Total</span>
          <span class="text-2xl font-extrabold text-navyft"><?php echo currency($total); ?></span>
        </div>

        <!-- Floating WhatsApp for support -->
              <a href="https://wa.me/256700000000?text=Hi%20PulseTech%2C%20I%20need%20help%20with%20checkout" target="_blank" class="mt-6 w-full inline-flex items-center justify-center gap-2 bg-[#25D366] text-white font-bold py-2.5 rounded-lg hover:brightness-110 transition">
          <i class="fa-brands fa-whatsapp"></i>
          Need help? Chat on WhatsApp
        </a>
      </aside>
    </div>
  </main>

  <script>
    // Simple toggle required for transaction id based on payment method
    const pmRadios = document.querySelectorAll('input[name="payment_method"]');
    const txnInput = document.querySelector('input[name="transaction_id"]');
    const mmBox = txnInput && txnInput.closest('.bg-cyanft\\/10');
    function updateTxnVisibility() {
      const method = document.querySelector('input[name="payment_method"]:checked')?.value;
      if (!txnInput || !mmBox) return;
      if (method === 'mobile_money') {
        mmBox.classList.remove('hidden');
      } else {
        mmBox.classList.add('hidden');
      }
    }
    pmRadios.forEach(r => r.addEventListener('change', updateTxnVisibility));
    updateTxnVisibility();
  </script>
</body>
</html>
