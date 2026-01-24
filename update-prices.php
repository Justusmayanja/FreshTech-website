<?php
require_once __DIR__ . '/config/config.php';

// Update all products to include price in description
try {
    $products = $pdo->query("SELECT id, name, description, price, sale_price FROM products")->fetchAll();
    
    $stmt = $pdo->prepare("UPDATE products SET description = ? WHERE id = ?");
    
    foreach ($products as $p) {
        $price = (float)($p['sale_price'] ?: $p['price']);
        $original = $p['price'];
        $priceText = "Price: UGX " . number_format($price, 0);
        
        // Only add price if not already in description
        if (strpos($p['description'], 'Price') === false) {
            $newDesc = $p['description'] . " | " . $priceText;
        } else {
            $newDesc = $p['description'];
        }
        
        $stmt->execute([$newDesc, $p['id']]);
    }
    
    echo '<html><head><title>Update Complete</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-50 p-10">';
    echo '<div class="max-w-2xl mx-auto bg-white p-8 rounded shadow">';
    echo '<p class="text-lg text-green-600 font-bold mb-4">✓ Prices added to all ' . count($products) . ' product descriptions!</p>';
    echo '<a href="shop.php" class="inline-block px-4 py-2 bg-blue-500 text-white rounded">Go to Shop</a>';
    echo '</div></body></html>';
    
} catch (Exception $e) {
    echo '<p style="color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
