<?php
// MUST send headers FIRST before any output
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Destroy session
session_start();
$_SESSION = array();

// Delete session cookie
setcookie('PHPSESSID', '', time() - 3600, '/');
setcookie(session_name(), '', time() - 3600, '/');
setcookie('admin_logged_in', '', time() - 3600, '/');
setcookie('admin_id', '', time() - 3600, '/');
setcookie('admin_username', '', time() - 3600, '/');

session_destroy();

// Now output HTML after ALL headers
?><!DOCTYPE html>
<html>
<head>
    <title>Session Cleared</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .message { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 500px; }
        .success { color: #28a745; font-weight: bold; font-size: 16px; }
        a { color: #ff9800; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="message">
        <p class="success">✓ Session cleared successfully!</p>
        <p>All cookies and session data have been removed.</p>
        <p><a href="/admin/">Go to Admin Login →</a></p>
    </div>
</body>
</html>
