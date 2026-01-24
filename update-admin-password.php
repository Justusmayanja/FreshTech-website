<?php
/**
 * Update admin password safely (uses project PDO in db.php)
 *
 * Usage:
 *   php update-admin-password.php admin NewStrongPassword!
 */

require_once __DIR__ . '/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "[✗] Database connection failed: " . ($dbError ?? 'unknown') . "\n");
    exit(1);
}

[$script, $username, $newPassword] = $argv + [null, null, null];
if (!$username || !$newPassword) {
    fwrite(STDERR, "Usage: php update-admin-password.php <username> <newPassword>\n");
    exit(2);
}

try {
    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('UPDATE admins SET password_hash = :h WHERE username = :u');
    $stmt->execute(['h' => $hash, 'u' => $username]);

    if ($stmt->rowCount() === 0) {
        // If admin not found, create one
        $ins = $pdo->prepare('INSERT INTO admins (username, password_hash, name, email, role, is_active) VALUES (:u, :h, :n, :e, :r, 1)');
        $ins->execute([
            'u' => $username,
            'h' => $hash,
            'n' => 'Admin User',
            'e' => 'admin@pulsetech.local',
            'r' => 'admin',
        ]);
        echo "[+] Created admin '$username' and set new password.\n";
    } else {
        echo "[✓] Updated password for '$username'.\n";
    }

    echo "You can now log in at /admin/login.php with: \n  Username: $username\n  Password: (the one you set)\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "[✗] Error: " . $e->getMessage() . "\n");
    exit(1);
}
