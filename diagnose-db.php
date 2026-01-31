<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Database Connection Test ===\n\n";

$host = 'localhost';
$dbname = 'pulsetech_db';
$username = 'root';
$password = '';

// Test 1: Can we connect to MySQL at all?
echo "Test 1: Connecting to MySQL server...\n";
try {
    $pdo_test = new PDO("mysql:host=$host", $username, $password);
    echo "✓ MySQL server connection successful\n\n";
} catch (PDOException $e) {
    die("✗ Cannot connect to MySQL: " . $e->getMessage() . "\n");
}

// Test 2: Does the database exist?
echo "Test 2: Checking if database exists...\n";
try {
    $stmt = $pdo_test->query("SHOW DATABASES LIKE 'pulsetech_db'");
    $exists = $stmt->fetch();
    if ($exists) {
        echo "✓ Database 'pulsetech_db' exists\n\n";
    } else {
        echo "✗ Database 'pulsetech_db' does NOT exist\n";
        echo "Creating database...\n";
        $pdo_test->exec("CREATE DATABASE pulsetech_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✓ Database created\n\n";
    }
} catch (PDOException $e) {
    die("✗ Database check failed: " . $e->getMessage() . "\n");
}

// Test 3: Can we connect to the specific database?
echo "Test 3: Connecting to pulsetech_db...\n";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to pulsetech_db\n\n";
} catch (PDOException $e) {
    die("✗ Cannot connect to pulsetech_db: " . $e->getMessage() . "\n");
}

// Test 4: Check if admins table exists
echo "Test 4: Checking admins table...\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
    $exists = $stmt->fetch();
    if ($exists) {
        echo "✓ Admins table exists\n";
        $count = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        echo "  Found $count admin users\n\n";
    } else {
        echo "✗ Admins table does NOT exist\n";
        echo "Creating admins table...\n";
        
        $sql = "CREATE TABLE `admins` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `role` VARCHAR(50) DEFAULT 'admin',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $pdo->exec($sql);
        echo "✓ Admins table created\n";
        
        // Create default admin
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $password_hash, 'admin']);
        echo "✓ Default admin user created (username: admin, password: admin123)\n\n";
    }
} catch (PDOException $e) {
    echo "✗ Table check failed: " . $e->getMessage() . "\n\n";
}

echo "=== All Tests Complete ===\n";
echo "Your database should now be ready!\n";
echo "Try accessing: http://localhost:5500/admin/profile.php\n";
