<?php
require_once __DIR__ . '/db.php';

// Auto-run this migration when checkout is accessed
if (!$pdo) {
    return; // No database connection
}

try {
    // ========== ORDERS TABLE ==========
    $result = $pdo->query("SHOW TABLES LIKE 'orders'");
    
    if ($result->rowCount() === 0) {
        // Create orders table from scratch
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
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
            is_viewed TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_order_number (order_number),
            INDEX idx_customer_email (email),
            INDEX idx_is_viewed (is_viewed),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // Table exists, check and add missing columns
        $existingColumns = [];
        $columnsResult = $pdo->query("DESCRIBE orders");
        foreach ($columnsResult as $col) {
            $existingColumns[] = $col['Field'];
        }
        
        // Add missing columns one by one
        if (!in_array('email', $existingColumns)) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN email VARCHAR(255) NOT NULL DEFAULT '' AFTER customer_name");
        }
        if (!in_array('phone', $existingColumns)) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN phone VARCHAR(20) AFTER email");
        }
        if (!in_array('address', $existingColumns)) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN address TEXT AFTER phone");
        }
        if (!in_array('notes', $existingColumns)) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN notes TEXT AFTER address");
        }
        if (!in_array('payment_method', $existingColumns)) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN payment_method ENUM('mobile_money', 'cod') DEFAULT 'mobile_money' AFTER notes");
        }
        if (!in_array('transaction_id', $existingColumns)) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN transaction_id VARCHAR(255) AFTER payment_method");
        }
        if (!in_array('total_amount', $existingColumns)) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN total_amount DECIMAL(15, 2) NOT NULL DEFAULT 0 AFTER transaction_id");
        }
        if (!in_array('status', $existingColumns)) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending' AFTER total_amount");
        }
        if (!in_array('is_viewed', $existingColumns)) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN is_viewed TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
            $pdo->exec("ALTER TABLE orders ADD INDEX idx_is_viewed (is_viewed)");
        }
    }
    
    // ========== ORDER_ITEMS TABLE ==========
    $itemsResult = $pdo->query("SHOW TABLES LIKE 'order_items'");
    
    if ($itemsResult->rowCount() === 0) {
        // Create order_items table from scratch
        $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } else {
        // Table exists, check and add missing columns
        $existingItemColumns = [];
        $itemColumnsResult = $pdo->query("DESCRIBE order_items");
        foreach ($itemColumnsResult as $col) {
            $existingItemColumns[] = $col['Field'];
        }
        
        // Add missing columns
        if (!in_array('order_id', $existingItemColumns)) {
            $pdo->exec("ALTER TABLE order_items ADD COLUMN order_id INT NOT NULL AFTER id");
        }
        if (!in_array('product_id', $existingItemColumns)) {
            $pdo->exec("ALTER TABLE order_items ADD COLUMN product_id INT NOT NULL AFTER order_id");
        }
        if (!in_array('product_name', $existingItemColumns)) {
            $pdo->exec("ALTER TABLE order_items ADD COLUMN product_name VARCHAR(255) NOT NULL AFTER product_id");
        }
        if (!in_array('quantity', $existingItemColumns)) {
            $pdo->exec("ALTER TABLE order_items ADD COLUMN quantity INT NOT NULL DEFAULT 1 AFTER product_name");
        }
        if (!in_array('unit_price', $existingItemColumns)) {
            $pdo->exec("ALTER TABLE order_items ADD COLUMN unit_price DECIMAL(15, 2) NOT NULL AFTER quantity");
        }
        if (!in_array('subtotal', $existingItemColumns)) {
            $pdo->exec("ALTER TABLE order_items ADD COLUMN subtotal DECIMAL(15, 2) NOT NULL DEFAULT 0 AFTER unit_price");
        }
    }
    
} catch (Exception $e) {
    // Log error for debugging
    error_log("Orders table setup error: " . $e->getMessage());
}
?>
