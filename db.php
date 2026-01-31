<?php
// db.php — PulseTech Solutions secure connection (PDO — modern & safe 2025+)

$host     = 'localhost';          // don't change
$dbname   = 'pulsetech_db';       // must match what you created
$username = 'root';               // default XAMPP
$password = '';                   // empty on XAMPP localhost
$port     = 3306;                 // default MySQL port

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // errors become exceptions — easier to find bugs
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // rows come as nice arrays (not objects)
    PDO::ATTR_EMULATE_PREPARES   => false,                   // real prepared statements — prevents SQL injection
];

$pdo = null;
$dbError = '';

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    // Connection successful
} catch (PDOException $e) {
    // Database might not exist, try to create it
    try {
        $tempPdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password, $options);
        // Create database
        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        // Now connect to the newly created database
        $pdo = new PDO($dsn, $username, $password, $options);
    } catch (PDOException $createError) {
        $pdo = null;
        $dbError = 'Database Error: ' . $e->getMessage() . '. Please ensure MySQL is running and credentials are correct.';
    }
}