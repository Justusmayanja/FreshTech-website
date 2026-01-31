<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$db_available = isset($pdo) && $pdo instanceof PDO;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: /admin/products/index.php'); 
    exit; 
}

if (!verify_csrf($_POST['_csrf'] ?? '')) { 
    $_SESSION['flash_error'] = 'Invalid CSRF'; 
    header('Location: /admin/products/index.php'); 
    exit; 
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { 
    header('Location: /admin/products/index.php'); 
    exit; 
}

if (!$db_available) {
    $_SESSION['flash_error'] = 'Database unavailable. Cannot delete product at this time.';
    header('Location: /admin/products/index.php');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute(['id'=>$id]);
    $_SESSION['flash_success'] = 'Product deleted.';
} catch (Exception $e) {
    $_SESSION['flash_error'] = 'Failed to delete product.';
}

header('Location: /admin/products/index.php');
exit;
