<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

$connected = isset($pdo) && ($pdo instanceof PDO);

echo json_encode([
    'connected' => $connected,
    'error' => $connected ? null : ($dbError ?? 'not connected'),
]);