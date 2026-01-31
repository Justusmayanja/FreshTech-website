<?php
// create-database.php - Creates the PulseTech database if it doesn't exist

$host     = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect without specifying database first
    $pdo = new PDO("mysql:host=$host", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS pulsetech_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green; font-size: 16px;'><strong>✓ Database 'pulsetech_db' created or already exists!</strong></p>";
    
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=pulsetech_db;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "<p style='color: green; font-size: 16px;'><strong>✓ Connected to database!</strong></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-size: 16px;'><strong>✗ Database Error:</strong><br>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PulseTech - Database Setup</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0891b2;
            margin-bottom: 20px;
        }
        p {
            line-height: 1.6;
            margin: 10px 0;
        }
        a {
            color: #0891b2;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
        }
        .success {
            color: #16a34a;
            padding: 15px;
            background: #f0fdf4;
            border-left: 4px solid #16a34a;
            border-radius: 4px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Database Setup Complete</h1>
        <div class="success">
            <p><strong>The database 'pulsetech_db' is ready!</strong></p>
            <p>Tables will be created automatically when you access the admin pages.</p>
        </div>
        <p><a href="/admin/aboutus/index.php">→ Go to About Us Management</a></p>
        <p><a href="/admin/dashboard.php">→ Go to Dashboard</a></p>
    </div>
</body>
</html>
