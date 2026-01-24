<?php
// api/products.php — PulseTech API to serve Featured Products as JSON

header('Content-Type: application/json; charset=utf-8');

// Include the connection (go up one folder)
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    try {
        if (!isset($pdo) || !($pdo instanceof PDO)) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Database not connected',
                'error'   => isset($dbError) ? $dbError : null,
            ]);
            exit;
        }
        // Fetch all products, newest first
        $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
        $products = $stmt->fetchAll();  // gets array of rows

        echo json_encode([
            'status'  => 'success',
            'data'    => $products,           // the actual products array
            'count'   => count($products)
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Database query failed'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Only GET allowed here']);
}