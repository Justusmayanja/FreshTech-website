<?php
/**
 * FreshTech Solutions - Database Configuration
 * Shared between public site and admin panel
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'freshtech_db');
define('DB_USER', 'root');  // Change this in production
define('DB_PASS', '');      // Change this in production
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'http://localhost:5500');  // Change to your domain
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Security Settings
define('SESSION_LIFETIME', 7200); // 2 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// Email Configuration (for order notifications)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com'); // Change this
define('SMTP_PASS', 'your-app-password');     // Change this
define('FROM_EMAIL', 'hello@freshtechsolutions.com');
define('FROM_NAME', 'FreshTech Solutions');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('America/Los_Angeles');

// Database Connection
$pdo = null;
$dbConnected = false;

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    $dbConnected = true;
} catch(PDOException $e) {
    // Log error but don't die - allow login page to still show
    $dbError = "Database connection failed: " . $e->getMessage();
    // Optionally log to file instead of showing
    // error_log($dbError);
}

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    session_start();
}
