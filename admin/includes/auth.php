<?php
// admin/includes/auth.php — session, auth and CSRF helpers
// MUST be called before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function is_admin_logged_in(): bool {
    $logged_in = !empty($_SESSION['admin_logged_in']) && 
                 !empty($_SESSION['admin_id']) &&
                 !empty($_SESSION['admin_username']);
    return $logged_in;
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        // Redirect to /admin/ which will show login form with clean URL
        header('Location: /admin/', true, 302);
        exit;
    }
}

function admin_login($adminRow) {
    // $adminRow should be array with id, username, role
    session_regenerate_id(true);
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $adminRow['id'];
    $_SESSION['admin_username'] = $adminRow['username'];
    $_SESSION['admin_role'] = $adminRow['role'] ?? 'admin';
}

function admin_logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']);
    }
    session_destroy();
}

// Basic CSRF token helpers
function csrf_token() {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['_csrf_token'];
}

function verify_csrf($token): bool {

    return !empty($token) && hash_equals($_SESSION['_csrf_token'] ?? '', $token);
}
