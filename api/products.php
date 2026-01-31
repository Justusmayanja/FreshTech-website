<?php
// api/products.php — PulseTech API to serve Featured Products as JSON

header('Content-Type: application/json; charset=utf-8');

// Include the connection (go up one folder)
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Database not connected',
                'error'   => isset($dbError) ? $dbError : null,
            ]);
            exit;
        }
        // Fetch all products from shop, newest first (same as shop.php)
        $stmt = $pdo->query("SELECT id, name, slug, description, price_ugx, old_price_ugx, image_url, category, stock_quantity FROM products ORDER BY id DESC LIMIT 3");
        $products = $stmt->fetchAll();

        // Process image URLs - extract first image if it's a JSON array
        foreach ($products as &$product) {
            if (!empty($product['image_url'])) {
                $imageUrl = $product['image_url'];
                
                // Try to decode as JSON
                $arr = json_decode($imageUrl, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($arr) && !empty($arr)) {
                    $first = $arr[0] ?? '';
                    if ($first) {
                        if (preg_match('~^https?://~i', $first)) {
                            $product['image_url'] = $first;
                        } else {
                            $product['image_url'] = '/uploads/' . ltrim($first, '/');
                        }
                    }
                } elseif (!preg_match('~^https?://~i', $imageUrl)) {
                    // If it's a filename, add /uploads/ prefix
                    $product['image_url'] = '/uploads/' . ltrim($imageUrl, '/');
                }
            } else {
                $product['image_url'] = '/images/placeholder.svg';
            }
        }
        unset($product);

        // If no products in database, return sample products
        if (empty($products)) {
            $products = [
                [
                    'id' => 1,
                    'name' => 'Premium Wireless Earbuds',
                    'description' => 'High-quality sound with noise cancellation',
                    'price_ugx' => 450000,
                    'old_price_ugx' => 600000,
                    'discount' => '25% Off',
                    'rating' => 5,
                    'image_url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=260&fit=crop'
                ],
                [
                    'id' => 2,
                    'name' => 'USB-C Fast Charger',
                    'description' => 'Quick charge your devices in minutes',
                    'price_ugx' => 95000,
                    'old_price_ugx' => null,
                    'discount' => null,
                    'rating' => 4,
                    'image_url' => 'https://images.unsplash.com/photo-1591496694519-5d381e16f382?w=400&h=260&fit=crop'
                ],
                [
                    'id' => 3,
                    'name' => 'Portable Power Bank 20000mAh',
                    'description' => 'Charge on the go with high capacity',
                    'price_ugx' => 180000,
                    'old_price_ugx' => 250000,
                    'discount' => '28% Off',
                    'rating' => 5,
                    'image_url' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=400&h=260&fit=crop'
                ],
                [
                    'id' => 4,
                    'name' => 'Phone Screen Protector',
                    'description' => 'Tempered glass protection for all devices',
                    'price_ugx' => 35000,
                    'old_price_ugx' => null,
                    'discount' => null,
                    'rating' => 4,
                    'image_url' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400&h=260&fit=crop'
                ],
                [
                    'id' => 5,
                    'name' => 'Mechanical Keyboard RGB',
                    'description' => 'Professional gaming and typing experience',
                    'price_ugx' => 385000,
                    'old_price_ugx' => 520000,
                    'discount' => '26% Off',
                    'rating' => 5,
                    'image_url' => 'https://images.unsplash.com/photo-1587829191301-4a171dcb549e?w=400&h=260&fit=crop'
                ],
                [
                    'id' => 6,
                    'name' => 'Wireless Mouse',
                    'description' => 'Precision tracking with long battery life',
                    'price_ugx' => 125000,
                    'old_price_ugx' => null,
                    'discount' => null,
                    'rating' => 4,
                    'image_url' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=400&h=260&fit=crop'
                ]
            ];
        }

        echo json_encode([
            'status'  => 'success',
            'data'    => $products,
            'count'   => count($products)
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Database query failed'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Only GET allowed here']);
}