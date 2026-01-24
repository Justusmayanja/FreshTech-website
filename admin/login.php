<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_admin_logged_in()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';
if (!isset($pdo) || !($pdo instanceof PDO)) {
  $error = 'Database is not connected. Please check your database configuration.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM admins WHERE username = :u LIMIT 1');
        $stmt->execute(['u' => $username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            admin_login($admin);
            $redirect = $_SESSION['redirect_after_login'] ?? '/admin/dashboard.php';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid credentials.';
        }
    }
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login — PulseTech</title>
  <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body class="login-page">
  <main class="login-box">
    <h1>PulseTech Admin</h1>
    <?php if ($error): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="">
      <label>Username<input name="username" required></label>
      <label>Password<input type="password" name="password" required></label>
      <button type="submit">Sign in</button>
    </form>
  </main>
</body>
</html>

