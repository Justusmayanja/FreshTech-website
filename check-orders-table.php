<?php
require_once __DIR__ . '/config/config.php';

echo "Checking orders table...\n";

try {
    // Try to describe the orders table
    $stmt = $pdo->query('DESCRIBE orders');
    echo "Orders table exists. Columns:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Orders table does not exist or has errors.\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nCreating orders tables from migration...\n";
    
    try {
        // Read and execute the migration file
        $sql = file_get_contents(__DIR__ . '/database/003_orders_schema.sql');
        
        // Split by delimiter and execute each statement
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue;
            }
            
            // Skip DELIMITER commands
            if (stripos($statement, 'DELIMITER') !== false) {
                continue;
            }
            
            // Execute the statement
            try {
                $pdo->exec($statement);
            } catch (Exception $se) {
                // Ignore errors for DROP and CREATE TRIGGER if they already exist
                if (stripos($se->getMessage(), 'already exists') === false && 
                    stripos($se->getMessage(), "doesn't exist") === false) {
                    echo "Warning: " . $se->getMessage() . "\n";
                }
            }
        }
        
        echo "\nTables created successfully!\n";
        
        // Verify
        $stmt = $pdo->query('DESCRIBE orders');
        echo "\nVerifying orders table columns:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
        
    } catch (Exception $e2) {
        echo "Failed to create tables: " . $e2->getMessage() . "\n";
    }
}

echo "\nDone!\n";
