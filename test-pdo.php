<?php
echo "Available PDO drivers:\n";
print_r(PDO::getAvailableDrivers());

echo "\n\nMySQL driver present? " . (in_array('mysql', PDO::getAvailableDrivers()) ? 'YES' : 'NO') . "\n";

// Quick connection test (uses XAMPP default root/empty)
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "Connection to MySQL successful!\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
