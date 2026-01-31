<?php
// Quick database setup script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PulseTech Database Setup ===\n\n";

// Step 1: Connect to MySQL (no database selected)
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to MySQL server\n";
} catch (PDOException $e) {
    die("✗ MySQL connection failed: " . $e->getMessage() . "\n");
}

// Step 2: Create database
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS pulsetech_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database 'pulsetech_db' created/verified\n";
} catch (PDOException $e) {
    die("✗ Failed to create database: " . $e->getMessage() . "\n");
}

// Step 3: Use the database
try {
    $pdo->exec("USE pulsetech_db");
    echo "✓ Switched to pulsetech_db\n";
} catch (PDOException $e) {
    die("✗ Failed to use database: " . $e->getMessage() . "\n");
}

// Step 4: Create admins table
try {
    $sql = "CREATE TABLE IF NOT EXISTS `admins` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(100) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `role` VARCHAR(50) DEFAULT 'admin',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "✓ Admins table created/verified\n";
} catch (PDOException $e) {
    die("✗ Failed to create admins table: " . $e->getMessage() . "\n");
}

// Step 5: Check if admin user exists
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Create default admin user
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $password, 'admin']);
        echo "✓ Default admin user created\n";
        echo "  Username: admin\n";
        echo "  Password: admin123\n";
    } else {
        echo "✓ Admin user already exists\n";
    }
} catch (PDOException $e) {
    echo "✗ Warning: " . $e->getMessage() . "\n";
}

// Step 6: Create other tables from migration
try {
    $migrationFile = __DIR__ . '/database/001_init_schema.sql';
    if (file_exists($migrationFile)) {
        $sql = file_get_contents($migrationFile);
        // Split by semicolons and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Continue even if some tables already exist
                }
            }
        }
        echo "✓ Additional tables created from migration\n";
    }
} catch (Exception $e) {
    echo "Note: " . $e->getMessage() . "\n";
}

echo "\n=== Setup Complete! ===\n";
echo "You can now access the admin panel at: http://localhost:5500/admin/\n";
echo "Login with: admin / admin123\n";
