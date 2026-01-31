<?php
require_once __DIR__ . '/db.php';

header('Content-Type: text/plain');

if (!$pdo) {
    echo "ERROR: No database connection available.\n";
    echo "DB Error: " . ($dbError ?? 'Unknown error') . "\n";
    exit;
}

echo "Database connection: OK\n\n";
echo "Checking for orders table...\n\n";

try {
    $result = $pdo->query("SHOW TABLES LIKE 'orders'");
    
    if ($result->rowCount() > 0) {
        echo "✓ Orders table EXISTS\n\n";
        echo "Columns in orders table:\n";
        echo "------------------------\n";
        
        $cols = $pdo->query("DESCRIBE orders");
        foreach ($cols as $col) {
            echo sprintf("  %-20s %s\n", $col['Field'], $col['Type']);
        }
        
        echo "\n✓ Table structure looks good!\n";
        
    } else {
        echo "✗ Orders table DOES NOT exist\n\n";
        echo "Creating orders and order_items tables...\n";
        
        // Create orders table
        $createOrders = "CREATE TABLE IF NOT EXISTS orders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_number VARCHAR(50) NOT NULL UNIQUE,
            customer_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            notes TEXT,
            payment_method ENUM('mobile_money', 'cod') DEFAULT 'mobile_money',
            transaction_id VARCHAR(255),
            total_amount DECIMAL(15, 2) NOT NULL DEFAULT 0,
            status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_order_number (order_number),
            INDEX idx_customer_email (email),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createOrders);
        echo "✓ Orders table created\n";
        
        // Create order_items table
        $createOrderItems = "CREATE TABLE IF NOT EXISTS order_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            unit_price DECIMAL(15, 2) NOT NULL,
            subtotal DECIMAL(15, 2) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            INDEX idx_order_id (order_id),
            INDEX idx_product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createOrderItems);
        echo "✓ Order_items table created\n\n";
        
        // Verify
        $cols = $pdo->query("DESCRIBE orders");
        echo "Columns created:\n";
        foreach ($cols as $col) {
            echo sprintf("  %-20s %s\n", $col['Field'], $col['Type']);
        }
        
        echo "\n✓ Tables created successfully!\n";
    }
} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
