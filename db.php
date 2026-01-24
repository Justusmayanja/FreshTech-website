<?php
// db.php — PulseTech Solutions secure connection (PDO — modern & safe 2025+)

$host     = 'localhost';          // don't change
$dbname   = 'pulsetech_db';       // must match what you created
$username = 'root';               // default XAMPP
$password = '';                   // empty on XAMPP localhost

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // errors become exceptions — easier to find bugs
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // rows come as nice arrays (not objects)
    PDO::ATTR_EMULATE_PREPARES   => false,                   // real prepared statements — prevents SQL injection
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    // Optional during testing: uncomment to confirm
    // echo "Connected to PulseTech DB!";
} catch (PDOException $e) {
    // Do not terminate; expose a generic flag and capture error for diagnostics
    $pdo = null;
    // For diagnostics pages (e.g., verify-setup.php), read $dbError
    $dbError = $e->getMessage();
}