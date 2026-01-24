<?php
/**
 * PulseTech Database Migration Runner
 * Executes all pending migrations in the correct order
 * 
 * Usage: php run-migrations.php
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║   PulseTech Solutions - Database Migration Runner              ║\n";
echo "║   Database: pulsetech_db                                       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'pulsetech_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('MIGRATIONS_DIR', __DIR__ . '/database');

// Connect to database
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "[✓] Connected to database: " . DB_NAME . "\n\n";
} catch (PDOException $e) {
    echo "[✗] Database connection failed:\n";
    echo "    " . $e->getMessage() . "\n\n";
    exit(1);
}

// Ensure migrations table exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL UNIQUE,
            `batch` INT NOT NULL,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "[✓] Migrations tracking table ready\n\n";
} catch (PDOException $e) {
    echo "[✗] Failed to create migrations table:\n";
    echo "    " . $e->getMessage() . "\n\n";
    exit(1);
}

// Get list of migration files
$migration_files = glob(MIGRATIONS_DIR . '/[0-9]*_*.sql');
sort($migration_files);

if (empty($migration_files)) {
    echo "[!] No migration files found in " . MIGRATIONS_DIR . "\n";
    exit(0);
}

// Get already executed migrations
$executed = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
$executed = array_map(function($m) {
    return basename($m);
}, $executed);

// Get next batch number
$last_batch = $pdo->query("SELECT COALESCE(MAX(batch), 0) as max_batch FROM migrations")->fetch();
$next_batch = $last_batch['max_batch'] + 1;

// Run pending migrations
$pending = array_filter($migration_files, function($file) use ($executed) {
    return !in_array(basename($file), $executed);
});

if (empty($pending)) {
    echo "No pending migrations.\n";
    echo "\nExecuted migrations:\n";
    foreach ($executed as $migration) {
        echo "  ✓ " . $migration . "\n";
    }
    exit(0);
}

echo "Pending migrations to execute:\n";
foreach ($pending as $file) {
    echo "  → " . basename($file) . "\n";
}
echo "\n";

// Execute migrations
$failed = [];
$successful = [];

foreach ($pending as $file) {
    $migration_name = basename($file);
    
    try {
        $sql = file_get_contents($file);
        
        // Split by semicolon and filter empty statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($s) {
                return !empty($s) && strpos(ltrim($s), '--') !== 0;
            }
        );
        
        // Execute each statement
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement . ';');
            }
        }
        
        // Record migration
        $pdo->prepare("
            INSERT INTO migrations (migration, batch)
            VALUES (?, ?)
        ")->execute([$migration_name, $next_batch]);
        
        echo "[✓] " . $migration_name . "\n";
        $successful[] = $migration_name;
        
    } catch (Exception $e) {
        echo "[✗] " . $migration_name . "\n";
        echo "    Error: " . $e->getMessage() . "\n";
        $failed[] = [
            'migration' => $migration_name,
            'error' => $e->getMessage()
        ];
    }
}

// Summary
echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║   Migration Summary                                              ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "Successful: " . count($successful) . "\n";
foreach ($successful as $m) {
    echo "  ✓ " . $m . "\n";
}

if (!empty($failed)) {
    echo "\nFailed: " . count($failed) . "\n";
    foreach ($failed as $m) {
        echo "  ✗ " . $m['migration'] . "\n";
        echo "    " . $m['error'] . "\n";
    }
    exit(1);
} else {
    echo "\n[✓] All migrations executed successfully!\n";
    echo "\nYour database is ready to use.\n";
    exit(0);
}
