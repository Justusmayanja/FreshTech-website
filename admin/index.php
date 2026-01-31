<?php
/**
 * CRITICAL: Admin Panel Entry Point - DO NOT REDIRECT TO DASHBOARD
 * This page ONLY displays the login form
 * Dashboard access is handled in dashboard.php which checks auth
 */

// Aggressive cache busting - prevents browser from caching redirects
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, post-check=0, pre-check=0');
header('Pragma: no-cache');
header('Expires: -1');

// Clear any old/stale session data on fresh visit
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    // ONLY keep session data if it's VERY fresh (within 30 seconds and properly set)
    if (isset($_SESSION['_admin_login_time']) && (time() - $_SESSION['_admin_login_time']) > 3600) {
        // Session is stale - destroy it
        session_destroy();
        session_start();
    }
}

// ALWAYS show login page - never redirect from /admin/
// The login.php will handle both showing the form AND processing submissions
include __DIR__ . '/login.php';
exit;
?>

