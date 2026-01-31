<?php
require_once __DIR__ . '/db.php';

try {
    if ($pdo) {
        // Check if products table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
        $exists = $stmt->fetch();
        
        if ($exists) {
            echo "✓ Products table exists\n";
            
            // Count products
            $count = $pdo->query('SELECT COUNT(*) as cnt FROM products')->fetch();
            echo "Total products: " . $count['cnt'] . "\n";
            
            if ($count['cnt'] > 0) {
                echo "\nProducts:\n";
                $products = $pdo->query('SELECT id, name, category, price_ugx, image_url FROM products')->fetchAll();
                foreach ($products as $p) {
                    echo "  - " . $p['name'] . " (" . $p['category'] . ") - UGX " . number_format($p['price_ugx']) . " - Image: " . $p['image_url'] . "\n";
                }
            }
        } else {
            echo "✗ Products table does not exist\n";
            echo "Table will be created automatically when visiting admin/shop/\n";
        }
    } else {
        echo "✗ Database connection failed\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
