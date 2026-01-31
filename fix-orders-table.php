<?php
require_once __DIR__ . '/db.php';

if (!$pdo) {
    die("No database connection available.\n");
}

echo "Checking for orders table...\n\n";

try {
    $result = $pdo->query("SHOW TABLES LIKE 'orders'");
    
    if ($result->rowCount() > 0) {
        echo "✓ Orders table EXISTS\n\n";
        echo "Columns in orders table:\n";
        echo "------------------------\n";
        
        $cols = $pdo->query("DESCRIBE orders");
        foreach ($cols as $col) {
            echo "  " . str_pad($col['Field'], 20) . " " . $col['Type'] . "\n";
        }
    } else {
        echo "✗ Orders table DOES NOT exist\n\n";
        echo "Creating orders table from migration file...\n";
        
        // Run the migration
        $sqlFile = __DIR__ . '/database/003_orders_schema.sql';
        $sql = file_get_contents($sqlFile);
        
        // Remove DELIMITER commands and split properly
        $sql = preg_replace('/DELIMITER.*/i', '', $sql);
        
        // Execute the SQL
        $pdo->exec($sql);
        
        echo "✓ Orders table created successfully!\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
