<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=freshtech_db;charset=utf8mb4', 'root', '');
    $count = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    echo "Connected! Found $count products in database.\n";
    
    $products = $pdo->query('SELECT id, name, price FROM products LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSample products:\n";
    foreach ($products as $p) {
        echo "- {$p['name']}: UGX {$p['price']}\n";
    }
} catch (Exception $e) {
    echo 'DB Error: ' . $e->getMessage() . "\n";
}
