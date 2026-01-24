<?php
// CLI helper: php admin/create_admin.php username password
if (php_sapi_name() !== 'cli') {
    echo "Run from CLI: php create_admin.php username password\n";
    exit(1);
}

require_once __DIR__ . '/../db.php';

$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];
if ($argc < 3) {
    echo "Usage: php create_admin.php username password\n";
    exit(1);
}

$username = trim($argv[1]);
$password = $argv[2];

if ($username === '' || $password === '') {
    echo "Username and password must be non-empty\n";
    exit(1);
}

try {
    $stmt = $pdo->prepare('SELECT id FROM admins WHERE username = :u LIMIT 1');
    $stmt->execute(['u' => $username]);
    if ($stmt->fetch()) {
        echo "Admin user '$username' already exists.\n";
        exit(0);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO admins (username, password_hash, role, created_at) VALUES (:u, :h, :r, NOW())');
    $insert->execute(['u' => $username, 'h' => $hash, 'r' => 'admin']);
    echo "Created admin user '$username' successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
