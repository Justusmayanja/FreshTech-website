<?php
session_start();
require_once __DIR__ . '/config/config.php';

// Check database connection
if (!$pdo) {
    die('❌ Database connection failed!');
}

echo '<html><head><title>Debug</title><script src="https://cdn.tailwindcss.com"></script><style>body{font-family:monospace}</style></head><body class="bg-gray-50 p-10">';
echo '<div class="max-w-4xl mx-auto">';

// Check products table
try {
    $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    echo "<p class='text-lg mb-4'>✓ Database Connected | <strong>$count products</strong> in database</p>";
    
    if ($count == 0) {
        echo '<div class="bg-yellow-100 border border-yellow-400 p-4 rounded mb-4">';
        echo '<p class="font-bold text-yellow-800">No products found! Adding sample products...</p>';
        
        // Add sample products
        $products = [
            ['Phone Case Leather', 'Premium leather protective case', 45000, 35000, '["https://images.unsplash.com/photo-1519046904884-53103b34b206?w=400&h=300&fit=crop"]', 50],
            ['USB-C Charger 65W', 'Fast charging USB-C charger', 120000, 95000, '["https://images.unsplash.com/photo-1625948515291-69613efd103f?w=400&h=300&fit=crop"]', 30],
            ['Wireless Earbuds Pro', 'Premium wireless earbuds with ANC', 280000, 220000, '["https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=300&fit=crop"]', 25],
            ['Smart Watch Ultra', 'Advanced fitness smartwatch', 350000, 280000, '["https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=300&fit=crop"]', 15],
            ['Phone Screen Protector', 'Tempered glass screen protection', 25000, 18000, '["https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400&h=300&fit=crop"]', 100],
            ['Portable Power Bank 20K', '20000mAh portable charger', 180000, 145000, '["https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=400&h=300&fit=crop"]', 40],
        ];
        
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, sale_price, images, stock) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($products as $p) {
            $stmt->execute($p);
        }
        
        echo '<p class="text-green-800 mt-2">✓ Added ' . count($products) . ' sample products</p>';
        echo '</div>';
    }
    
    // List products
    $result = $pdo->query("SELECT id, name, price, sale_price, stock FROM products LIMIT 10");
    echo '<table class="w-full border-collapse border border-gray-300 bg-white">';
    echo '<tr class="bg-gray-200"><th class="border p-2 text-left">ID</th><th class="border p-2 text-left">Name</th><th class="border p-2 text-right">Price</th><th class="border p-2 text-right">Sale Price</th><th class="border p-2 text-right">Stock</th></tr>';
    foreach ($result as $row) {
        echo '<tr>';
        echo '<td class="border p-2">' . $row['id'] . '</td>';
        echo '<td class="border p-2">' . htmlspecialchars($row['name']) . '</td>';
        echo '<td class="border p-2 text-right">' . $row['price'] . '</td>';
        echo '<td class="border p-2 text-right">' . $row['sale_price'] . '</td>';
        echo '<td class="border p-2 text-right">' . $row['stock'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
} catch (Exception $e) {
    echo '<p class="text-red-600">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// Check session cart
echo '<div class="mt-10 p-4 bg-blue-50 border border-blue-200 rounded">';
echo '<p class="font-bold mb-2">Session Cart Contents:</p>';
echo '<pre>' . htmlspecialchars(print_r($_SESSION['cart'] ?? [], true)) . '</pre>';
echo '</div>';

// Navigation
echo '<div class="mt-6 space-x-3">';
echo '<a href="shop.php" class="inline-block px-4 py-2 bg-blue-500 text-white rounded">Go to Shop</a>';
echo '<a href="cart.php" class="inline-block px-4 py-2 bg-green-500 text-white rounded">Go to Cart</a>';
echo '</div>';

echo '</div></body></html>';
?>
