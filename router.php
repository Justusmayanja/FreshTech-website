<?php
// Router for PHP built-in server (robust against missing QUERY_STRING)

// Normalize request path safely
$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';

// Map to filesystem path
$requested_file = __DIR__ . $path;

// Handle /admin route
if (preg_match('#^/admin/?$#', $path)) {
    $_SERVER['REQUEST_URI'] = '/admin/index.php';
    include __DIR__ . '/admin/index.php';
    exit;
}

// Handle /admin/* routes
if (preg_match('#^/admin/#', $path)) {
    return false; // Let the server serve the file normally
}

// If it's a real file or directory, serve it
if (is_file($requested_file) || is_dir($requested_file)) {
    return false;
}

// Otherwise serve index.html for the public site
include __DIR__ . '/index.html';
?>
