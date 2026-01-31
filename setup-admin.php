<?php
/**
 * Setup admin user for login
 */

require_once __DIR__ . '/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo "Database connection failed.\n";
    exit(1);
}

// Create admin user
$username = 'admin';
$password = 'admin@123';
$email = 'admin@pulsetech.local';
$name = 'Admin User';

try {
    // Check if admins table exists, if not create it
    $pdo->exec('CREATE TABLE IF NOT EXISTS `admins` (
      `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `username` VARCHAR(100) NOT NULL UNIQUE,
      `password_hash` VARCHAR(255) NOT NULL,
      `role` VARCHAR(50) DEFAULT "admin",
      `name` VARCHAR(255) DEFAULT NULL,
      `email` VARCHAR(255) DEFAULT NULL,
      `is_active` TINYINT(1) DEFAULT 1,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Delete existing admin if it exists
    $pdo->prepare('DELETE FROM admins WHERE username = ?')->execute([$username]);
    
    // Insert new admin
    $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash, role, name, email, is_active) VALUES (?, ?, ?, ?, ?, 1)');
    $stmt->execute([$username, $hash, 'admin', $name, $email]);
    
    echo "✓ Admin user created successfully!\n\n";
    echo "Login Credentials:\n";
    echo "==================\n";
    echo "Username: $username\n";
    echo "Password: $password\n";
    echo "\nAccess the admin panel at: http://localhost:5500/admin/login.php\n";
    
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
