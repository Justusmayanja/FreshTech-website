<?php
/**
 * PulseTech Setup - Initialize Database Tables
 * Run once to create orders and order_items tables
 */
require_once __DIR__ . '/config/config.php';

if (!$pdo) {
    die('Database connection failed.');
}

try {
    // Create orders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
      id INT AUTO_INCREMENT PRIMARY KEY,
      order_number VARCHAR(40) NOT NULL UNIQUE,
      customer_name VARCHAR(120) NOT NULL,
      email VARCHAR(160) NOT NULL,
      phone VARCHAR(40) NOT NULL,
      address VARCHAR(255) NOT NULL,
      notes TEXT NULL,
      payment_method ENUM('mobile_money','cod') NOT NULL DEFAULT 'mobile_money',
      transaction_id VARCHAR(120) NULL,
      total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      status ENUM('pending','processing','shipped','delivered','cancelled','completed') NOT NULL DEFAULT 'pending',
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX (status),
      INDEX (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Create order_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
      id INT AUTO_INCREMENT PRIMARY KEY,
      order_id INT NOT NULL,
      product_id INT NOT NULL,
      product_name VARCHAR(200) NOT NULL,
      quantity INT NOT NULL,
      unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      CONSTRAINT fk_orderitems_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
      INDEX (order_id),
      INDEX (product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo '<html><head><title>Setup Complete</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-50 p-10"><div class="max-w-2xl mx-auto bg-white border rounded-xl p-8"><div class="text-green-600 text-lg font-bold mb-4">✓ Database tables created successfully!</div><p class="text-gray-700 mb-4">The following tables are ready:</p><ul class="list-disc list-inside text-gray-700 mb-6"><li><strong>orders</strong> - Stores customer orders</li><li><strong>order_items</strong> - Stores line items for each order</li></ul><div class="space-y-3"><a href="/shop.php" class="inline-block px-5 py-2.5 bg-[#00D4FF] text-[#00111F] font-bold rounded-lg hover:brightness-110">Go to Shop</a></div></div></body></html>';
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo '<html><head><title>Tables Exist</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-50 p-10"><div class="max-w-2xl mx-auto bg-white border rounded-xl p-8"><div class="text-blue-600 text-lg font-bold mb-4">ℹ Tables already exist</div><p class="text-gray-700 mb-6">The database is ready to use.</p><a href="/shop.php" class="inline-block px-5 py-2.5 bg-[#00D4FF] text-[#00111F] font-bold rounded-lg hover:brightness-110">Go to Shop</a></div></body></html>';
    } else {
        echo '<html><head><title>Error</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-50 p-10"><div class="max-w-2xl mx-auto bg-white border rounded-xl p-8"><div class="text-red-600 text-lg font-bold mb-4">✗ Error creating tables</div><p class="text-gray-700">' . htmlspecialchars($e->getMessage()) . '</p></div></body></html>';
    }
}
?>
