<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$order_id = (int)($_GET['id'] ?? 0);
$items = [];

if ($order_id > 0 && isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->prepare('SELECT product_id, product_name, quantity, unit_price AS price, subtotal FROM order_items WHERE order_id = ? ORDER BY id ASC');
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

echo json_encode(['items' => $items]);
?>
