<?php
/**
 * PulseTech Solutions - Site Configuration
 * Shared between public site and admin panel
 */

// Site Configuration (dynamic base URL)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:5500';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/') ?: '/';
$basePath = rtrim($scriptDir, '/');
if ($basePath === '/') { $basePath = ''; }
define('SITE_URL', $scheme . '://' . $host . $basePath);
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
define('FROM_EMAIL', 'hello@pulsetechsolutions.com');
define('FROM_NAME', 'PulseTech Solutions');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Kampala');

// Database disabled in this environment
$pdo = null;
$dbConnected = false;

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
