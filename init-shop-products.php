<?php
// Initialize shop products from shopping folder images
require_once __DIR__ . '/db.php';

echo "=== Shop Products Initialization ===\n\n";

if (!$pdo) {
    echo "❌ Database connection failed. Please start MySQL service.\n";
    echo "Run: net start mysql\n";
    exit(1);
}

echo "✓ Database connected\n";

// Create products table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255),
        description TEXT,
        price_ugx DECIMAL(10, 2),
        old_price_ugx DECIMAL(10, 2),
        image_url VARCHAR(500),
        category VARCHAR(100),
        stock_quantity INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ Products table ready\n";
} catch (Exception $e) {
    echo "❌ Error creating table: " . $e->getMessage() . "\n";
    exit(1);
}

// Product descriptions mapping
$product_descriptions = [
    'headphones' => 'High-quality wireless headphones with excellent sound and comfort',
    'earbuds' => 'Premium wireless earbuds with noise cancellation and long battery life',
    'watches' => 'Feature-rich smart watch with fitness tracking and notifications',
    'holders' => 'Durable phone/device holder for convenient viewing and protection',
    'power bank' => 'Portable high-capacity power bank for fast charging on the go',
    'chargers' => 'Fast charger compatible with all devices - efficient and reliable'
];

// Categorize products
function categorize_product($filename) {
    $lower = strtolower($filename);
    
    if (strpos($lower, 'charger') !== false) return 'chargers';
    if (strpos($lower, 'head') !== false) return 'headphones';
    if (strpos($lower, 'pod') !== false) return 'earbuds';
    if (strpos($lower, 'watch') !== false) return 'watches';
    if (strpos($lower, 'holder') !== false) return 'holders';
    if (strpos($lower, 'power bank') !== false || strpos($lower, 'power') !== false) return 'power bank';
    
    return 'accessories';
}

// Check current products
$count = $pdo->query("SELECT COUNT(*) as cnt FROM products")->fetch();
echo "\nCurrent products in database: " . $count['cnt'] . "\n";

if ($count['cnt'] > 0) {
    echo "\n⚠️  Products already exist. Delete them first? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim(strtolower($line)) === 'y') {
        $pdo->exec("DELETE FROM products");
        echo "✓ Existing products deleted\n";
    } else {
        echo "Keeping existing products.\n";
        exit(0);
    }
}

// Create products from shopping folder images
$uploads_dir = __DIR__ . '/uploads';
$product_files = ['head1.jpg', 'head2.jpg', 'head3.jpg', 'holder3.jpg', 'iphone holder.jpg', 
                 'pod1.jpg', 'pod2.jpg', 'power bank.jpg', 'watch1.jpg', 'watch2.jpg', 'watch3.jpg'];

echo "\n=== Creating Products ===\n";

$created = 0;
foreach ($product_files as $file) {
    $filepath = $uploads_dir . '/' . $file;
    
    if (!file_exists($filepath)) {
        echo "⚠️  File not found: $file\n";
        continue;
    }
    
    $category = categorize_product($file);
    $name = ucfirst(str_replace(['.jpg', '.jpeg'], '', str_replace('_', ' ', $file)));
    $description = $product_descriptions[$category] ?? 'Quality tech accessory for your devices';
    
    // Set prices based on category
    $prices = [
        'headphones' => 180000,
        'earbuds' => 150000,
        'watches' => 350000,
        'holders' => 45000,
        'power bank' => 120000,
        'chargers' => 95000,
        'accessories' => 50000
    ];
    
    $price = $prices[$category] ?? 50000;
    $old_price = $price * 1.4; // 40% discount
    $slug = strtolower(str_replace(' ', '-', $name));
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, slug, image_url, category, price_ugx, old_price_ugx, stock_quantity, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $file, $category, $price, $old_price, 20, $description]);
        echo "✓ Created: $name ($category) - UGX " . number_format($price) . "\n";
        $created++;
    } catch (Exception $e) {
        echo "❌ Error creating $name: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Summary ===\n";
echo "Total products created: $created\n";

// Show final count
$final_count = $pdo->query("SELECT COUNT(*) as cnt FROM products")->fetch();
echo "Products in database: " . $final_count['cnt'] . "\n";

echo "\n✅ Done! Visit http://localhost:5500/shop.php to see your products.\n";
