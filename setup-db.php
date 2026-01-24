<?php
echo "=== PulseTech Database Setup ===\n\n";

// Step 1: Test MySQL connection
try {
    $mysql = new PDO('mysql:host=localhost', 'root', '');
    echo "[✓] MySQL server is running.\n";
} catch (PDOException $e) {
    echo "[✗] MySQL Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 2: Check if database exists
try {
    $result = $mysql->query("SHOW DATABASES LIKE 'pulsetech_db'")->fetch();
    if ($result) {
        echo "[✓] Database 'pulsetech_db' exists.\n";
    } else {
        echo "[!] Database 'pulsetech_db' not found. Creating...\n";
        $mysql->exec("CREATE DATABASE pulsetech_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "[✓] Database 'pulsetech_db' created.\n";
    }
} catch (PDOException $e) {
    echo "[✗] Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Connect to pulsetech_db
try {
    $pdo = new PDO('mysql:host=localhost;dbname=pulsetech_db;charset=utf8mb4', 'root', '');
    echo "[✓] Connected to pulsetech_db.\n";
} catch (PDOException $e) {
    echo "[✗] Connection Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 4: Check if tables exist
$tables = ['products', 'orders', 'order_items', 'admins', 'services', 'portfolio', 'blog_posts', 'contact_messages'];
$missing_tables = [];

foreach ($tables as $table) {
    $result = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    if (!$result) {
        $missing_tables[] = $table;
    }
}

if (empty($missing_tables)) {
    echo "[✓] All required tables exist.\n";
} else {
    echo "[!] Missing tables: " . implode(', ', $missing_tables) . "\n";
    echo "[!] Importing pulsetech_db.sql...\n";
    
    $sql_file = __DIR__ . '/database/freshtech_db.sql';
    if (file_exists($sql_file)) {
        $sql = file_get_contents($sql_file);
        $statements = array_filter(array_map('trim', explode(';', $sql)), function($s) {
            return !empty($s) && strpos($s, '--') !== 0;
        });
        
        foreach ($statements as $statement) {
            try {
                $pdo->exec($statement . ';');
            } catch (PDOException $e) {
                echo "[!] Warning: " . $e->getMessage() . "\n";
            }
        }
        echo "[✓] Database schema imported.\n";
    } else {
        echo "[✗] pulsetech_db.sql not found at $sql_file\n";
    }
}

// Step 5: Verify connection works with a query
try {
    $count = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    echo "[✓] Products table has $count products.\n";
} catch (PDOException $e) {
    echo "[!] Error querying products: " . $e->getMessage() . "\n";
}

echo "\n=== Setup Complete ===\n";
echo "Your site should now connect to the database successfully.\n";
