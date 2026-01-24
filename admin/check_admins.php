<?php
// Quick checker: lists admins
require_once __DIR__ . '/../db.php';
try {
    $stmt = $pdo->query('SELECT id, username, created_at FROM admins ORDER BY id');
    $rows = $stmt->fetchAll();
    if (!$rows) {
        echo "No admin users found.\n";
        exit(0);
    }
    foreach ($rows as $r) {
        echo "ID: {$r['id']}  Username: {$r['username']}  Created: {$r['created_at']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
