<?php
/**
 * PulseTech Seeder Runner
 * Executes seed SQL files regardless of prior migrations
 *
 * Usage:
 *  php run-seeders.php                // seed without resetting tables
 *  php run-seeders.php --reset        // truncate tables, then seed
 */

const DB_HOST = 'localhost';
const DB_NAME = 'pulsetech_db';
const DB_USER = 'root';
const DB_PASS = '';
const SEED_FILES = [__DIR__ . '/database/002_seed_sample_data.sql'];

// CLI flags
$reset = in_array('--reset', $argv ?? []);
$verbose = in_array('--verbose', $argv ?? []);

function connect(): PDO {
    return new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}

function execFile(PDO $pdo, string $path): void {
    global $verbose;
    if (!file_exists($path)) {
        throw new RuntimeException("Seed file not found: $path");
    }
    $sql = file_get_contents($path);
    $len = is_string($sql) ? strlen($sql) : 0;
    if ($verbose) {
        echo "      File size: $len bytes\n";
        if ($len > 0) {
            echo "      Preview: " . substr($sql, 0, 120) . "\n";
        } else {
            echo "      [!] Empty or unreadable SQL file content\n";
        }
    }
    // Split into statements by semicolon; ignore comments/empty
    $parts = array_map('trim', explode(';', (string)$sql));
    if ($verbose) {
        echo "      Split parts: " . count($parts) . "\n";
        foreach ($parts as $i => $pp) {
            $preview = substr($pp, 0, 120);
            $preview = str_replace(["\n", "\r"], ['\\n', '\\r'], $preview);
            $hasInsert = stripos($pp, 'INSERT') !== false ? 'INSERT' : '';
            echo sprintf("        [%d]%s '%s'\n", $i, $hasInsert ? " (contains $hasInsert)" : '', $preview);
        }
    }
    $stmts = [];
    foreach ($parts as $idx => $p) {
        if ($p === '') continue;
        // Remove comment lines and empty lines
        $lines = preg_split('/\r?\n/', $p);
        $cleanLines = [];
        foreach ($lines as $line) {
            $lt = ltrim($line);
            if ($lt === '') continue;
            if (str_starts_with($lt, '--')) continue;
            if (str_starts_with($lt, '/*')) continue;
            $cleanLines[] = $line;
        }
        $clean = trim(implode("\n", $cleanLines));
        if ($verbose) {
            $cprev = str_replace(["\n", "\r"], ['\\n', '\\r'], substr($clean, 0, 120));
            echo sprintf("        [clean %d] '%s'\n", $idx, $cprev);
        }
        if ($clean === '') continue;
        $stmts[] = $clean;
    }
    echo "      Parsed statements: " . count($stmts) . "\n";
    foreach ($stmts as $statement) {
        try {
            $affected = $pdo->exec($statement . ';');
            // Optional: echo per-table insert counts when obvious
            if (preg_match('/^INSERT\s+/i', $statement)) {
                // crude table name extraction
                if (preg_match('/INSERT\s+IGNORE\s+INTO\s+`?(\w+)`?/i', $statement, $m) ||
                    preg_match('/INSERT\s+INTO\s+`?(\w+)`?/i', $statement, $m)) {
                    $tbl = $m[1] ?? 'unknown';
                    echo sprintf("      [+] %s rows inserted into %s\n", (int)$affected, $tbl);
                }
            }
        } catch (Throwable $e) {
            echo "      [!] Statement failed: " . $e->getMessage() . "\n";
            // continue on error to try remaining statements
        }
    }
}

function truncateForReset(PDO $pdo): void {
    // Order matters due to foreign keys
    $tables = [
        'order_items',
        'orders',
        'products',
        'portfolio',
        'testimonials',
        'services',
        'blog_posts',
        'contact_messages',
        'inquiries',
        'brands'
    ];
    foreach ($tables as $t) {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        $pdo->exec("TRUNCATE TABLE `$t`");
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    }
}

function countTable(PDO $pdo, string $table): int {
    return (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}

// ─────────────────────────────────────────────────────────────
// Run
// ─────────────────────────────────────────────────────────────

printf("\nPulseTech Seeder Runner (DB: %s)\n\n", DB_NAME);

try {
    $pdo = connect();
    echo "[✓] Connected\n";

    if ($reset) {
        echo "[!] Reset requested: truncating seeded tables...\n";
        truncateForReset($pdo);
        echo "[✓] Tables truncated\n";
    }

    foreach (SEED_FILES as $f) {
        echo "[→] Seeding from " . basename($f) . "...\n";
        execFile($pdo, $f);
        echo "[✓] Done: " . basename($f) . "\n";
    }

    // Summary counts
    $summary = [
        'products' => countTable($pdo, 'products'),
        'services' => countTable($pdo, 'services'),
        'portfolio' => countTable($pdo, 'portfolio'),
        'testimonials' => countTable($pdo, 'testimonials'),
        'brands' => countTable($pdo, 'brands'),
    ];

    echo "\nSeed Summary:\n";
    foreach ($summary as $table => $count) {
        printf("  %-14s %5d\n", $table . ':', $count);
    }

    echo "\n[✓] Seeding complete.\n\n";
    exit(0);
} catch (Throwable $e) {
    echo "\n[✗] Seeder error: " . $e->getMessage() . "\n";
    exit(1);
}
