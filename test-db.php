<?php
require_once __DIR__ . '/db.php';

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException('PDO not initialized');
    }
    $count = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    echo "Connected! Found $count products in database.\n";

    $products = $pdo->query('SELECT id, name, price_ugx FROM products LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSample products:\n";
    foreach ($products as $p) {
        echo "- {$p['name']}: UGX {$p['price_ugx']}\n";
    }
} catch (Throwable $e) {
    echo 'DB Error: ' . $e->getMessage() . "\n";
}
