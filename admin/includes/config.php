<?php
/**
 * FreshTech Solutions - Admin Panel
 * Database Configuration & Connection
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database credentials — keep in sync with /db.php and migrations
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pulsetech_db');
define('DB_CHARSET', 'utf8mb4');

// Company info
define('COMPANY_NAME', 'FreshTech Solutions');
define('ADMIN_URL', '/admin/');
define('SITE_URL', '/');

// Security
define('SESSION_NAME', 'freshtech_admin');
define('SESSION_TIMEOUT', 3600); // 1 hour

$db = null;
$db_error = null;

try {
    // Create PDO connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $db = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    $db_error = "Database connection failed: " . $e->getMessage();
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . ADMIN_URL . "login.php");
        exit;
    }
}

function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function getCurrentAdmin() {
    global $db;
    if (!isLoggedIn() || $db === null) return null;
    
    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

function redirect($path) {
    header("Location: " . ADMIN_URL . $path);
    exit;
}

function formatDate($date) {
    return date('M d, Y - H:i', strtotime($date));
}

function getDashboardStats($db) {
    $stats = [
        'total_orders' => 0,
        'pending_orders' => 0,
        'new_messages' => 0,
        'total_products' => 0
    ];
    
    try {
        // Total orders
        $stmt = $db->query("SELECT COUNT(*) as count FROM orders");
        $result = $stmt->fetch();
        $stats['total_orders'] = $result['count'] ?? 0;
        
        // Pending orders
        $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
        $result = $stmt->fetch();
        $stats['pending_orders'] = $result['count'] ?? 0;
        
        // New messages
        $stmt = $db->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
        $result = $stmt->fetch();
        $stats['new_messages'] = $result['count'] ?? 0;
        
        // Total products
        $stmt = $db->query("SELECT COUNT(*) as count FROM products");
        $result = $stmt->fetch();
        $stats['total_products'] = $result['count'] ?? 0;
    } catch (Exception $e) {
        // Tables might not exist yet
    }
    
    return $stats;
}
// Do not close PHP tag to avoid accidental output
