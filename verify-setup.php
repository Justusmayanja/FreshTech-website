<?php
/**
 * PulseTech Database & System Verification
 * Visit: http://localhost:5500/verify-setup.php
 */
session_start();
require_once __DIR__ . '/config/config.php';

$status = [
    'database' => ['passed' => false, 'message' => ''],
    'tables' => ['passed' => false, 'message' => '', 'count' => 0],
    'admin' => ['passed' => false, 'message' => ''],
    'products' => ['passed' => false, 'message' => '', 'count' => 0],
    'services' => ['passed' => false, 'message' => '', 'count' => 0],
    'portfolio' => ['passed' => false, 'message' => '', 'count' => 0],
    'blog' => ['passed' => false, 'message' => '', 'count' => 0],
    'contact' => ['passed' => false, 'message' => '', 'count' => 0],
    'orders' => ['passed' => false, 'message' => '', 'count' => 0],
];

// Test 1: Database Connection
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $status['database']['passed'] = true;
        $status['database']['message'] = '✓ MySQL database connected';
    } else {
        $reason = isset($dbError) ? (' — ' . htmlspecialchars($dbError)) : '';
        $status['database']['message'] = '✗ Database connection failed' . $reason;
    }
} catch (Exception $e) {
    $status['database']['message'] = '✗ ' . $e->getMessage();
}

// Test 2: Check Tables
if ($status['database']['passed']) {
    try {
        $tables = ['admins', 'products', 'services', 'portfolio', 'blog_posts', 'contact_messages', 'orders', 'order_items'];
        $result = $pdo->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = 'freshtech_db'");
        $count = $result->fetch()['cnt'];
        $status['tables']['count'] = $count;
        $status['tables']['passed'] = $count >= 8;
        $status['tables']['message'] = ($count >= 8 ? '✓' : '✗') . ' Found ' . $count . '/8 tables';
    } catch (Exception $e) {
        $status['tables']['message'] = '✗ ' . $e->getMessage();
    }
}

// Test 3: Check Admin
if ($status['database']['passed']) {
    try {
        $admin = $pdo->query("SELECT COUNT(*) as cnt FROM admins WHERE username = 'admin'")->fetch();
        $status['admin']['passed'] = ($admin['cnt'] > 0);
        $status['admin']['message'] = ($admin['cnt'] > 0 ? '✓ Admin user found' : '✗ Admin user not found');
    } catch (Exception $e) {
        $status['admin']['message'] = '✗ ' . $e->getMessage();
    }
}

// Test 4-8: Check Data in Tables (only if DB connected)
$tables = [
    'products' => 'products',
    'services' => 'services',
    'portfolio' => 'portfolio',
    'blog' => 'blog_posts',
    'contact' => 'contact_messages',
    'orders' => 'orders'
];

if ($status['database']['passed']) {
    foreach ($tables as $key => $table) {
        try {
            $result = $pdo->query("SELECT COUNT(*) as cnt FROM $table");
            $count = $result->fetch()['cnt'];
            $status[$key]['count'] = $count;
            $status[$key]['passed'] = ($count > 0);
            $status[$key]['message'] = ($count > 0 ? '✓' : '⚠') . ' ' . $count . ' records found';
        } catch (Exception $e) {
            $status[$key]['message'] = '✗ ' . $e->getMessage();
        }
    }
} else {
    foreach (array_keys($tables) as $key) {
        $status[$key]['passed'] = false;
        $status[$key]['message'] = 'DB not connected';
        $status[$key]['count'] = 0;
    }
}

// Calculate overall status
$allPassed = $status['database']['passed'] && $status['tables']['passed'] && $status['admin']['passed'] && $status['products']['count'] > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FreshTech - System Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-10">
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">FreshTech Solutions</h1>
            <p class="text-gray-600">System Verification & Setup Confirmation</p>
        </div>

        <!-- Overall Status -->
        <div class="bg-white border-2 rounded-lg p-6 mb-6 <?php echo $allPassed ? 'border-green-300' : 'border-yellow-300'; ?>">
            <div class="flex items-center gap-4">
                <div class="text-5xl <?php echo $allPassed ? 'text-green-500' : 'text-yellow-500'; ?>">
                    <i class="fa-solid <?php echo $allPassed ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-1">
                        <?php echo $allPassed ? '✓ System Ready!' : '⚠ Incomplete Setup'; ?>
                    </h2>
                    <p class="text-gray-600">
                        <?php echo $allPassed ? 'All systems are operational. You can now test the site.' : 'Some checks failed. Review the details below.'; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Verification Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Database -->
            <div class="bg-white border rounded-lg p-4">
                <h3 class="font-bold text-lg mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-database <?php echo $status['database']['passed'] ? 'text-green-500' : 'text-red-500'; ?>"></i>
                    Database Connection
                </h3>
                <p class="text-gray-700"><?php echo $status['database']['message']; ?></p>
            </div>

            <!-- Tables -->
            <div class="bg-white border rounded-lg p-4">
                <h3 class="font-bold text-lg mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-table <?php echo $status['tables']['passed'] ? 'text-green-500' : 'text-orange-500'; ?>"></i>
                    Database Tables
                </h3>
                <p class="text-gray-700"><?php echo $status['tables']['message']; ?></p>
            </div>

            <!-- Admin -->
            <div class="bg-white border rounded-lg p-4">
                <h3 class="font-bold text-lg mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-lock <?php echo $status['admin']['passed'] ? 'text-green-500' : 'text-red-500'; ?>"></i>
                    Admin User
                </h3>
                <p class="text-gray-700 mb-2"><?php echo $status['admin']['message']; ?></p>
                <?php if ($status['admin']['passed']): ?>
                    <p class="text-xs text-gray-500">
                        Username: <code class="bg-gray-100 px-2 py-1 rounded">admin</code><br>
                        Password: <code class="bg-gray-100 px-2 py-1 rounded">FreshTech2025!</code>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Sample Data -->
            <div class="bg-white border rounded-lg p-4">
                <h3 class="font-bold text-lg mb-2">
                    <i class="fa-solid fa-database text-blue-500"></i> Sample Data
                </h3>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li><?php echo $status['products']['count']; ?> Products</li>
                    <li><?php echo $status['services']['count']; ?> Services</li>
                    <li><?php echo $status['portfolio']['count']; ?> Portfolio Items</li>
                    <li><?php echo $status['blog']['count']; ?> Blog Posts</li>
                </ul>
            </div>
        </div>

        <!-- Quick Test Links -->
        <div class="bg-white border rounded-lg p-6 mb-6">
            <h3 class="font-bold text-lg mb-4">🧪 Quick Tests</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <a href="shop.php" class="block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-center">
                    <i class="fa-solid fa-shopping-cart"></i> View Shop
                </a>
                <a href="cart.php" class="block px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-center">
                    <i class="fa-solid fa-bag-shopping"></i> View Cart
                </a>
                <a href="contact.html" class="block px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 text-center">
                    <i class="fa-solid fa-envelope"></i> Contact Form
                </a>
                <a href="admin/login.php" class="block px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 text-center">
                    <i class="fa-solid fa-lock"></i> Admin Panel
                </a>
            </div>
        </div>

        <!-- Test Instructions -->
        <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-6">
            <h3 class="font-bold text-lg mb-3 text-yellow-900">📋 Next Steps</h3>
            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                <li>Click "View Shop" and verify products display correctly</li>
                <li>Click "Add to Cart" on any product and verify success message</li>
                <li>Check that cart badge in header updates</li>
                <li>Go to "View Cart" and verify product appears</li>
                <li>Test checkout flow by clicking "Proceed to Checkout"</li>
                <li>Log in to "Admin Panel" with credentials above</li>
                <li>Submit the "Contact Form" and verify message appears in admin</li>
                <li>View orders in admin panel from test checkouts</li>
            </ol>
        </div>

        <!-- Footer -->
        <div class="text-center mt-10 text-gray-600 text-sm">
            <p>Verification Status: <strong><?php echo date('Y-m-d H:i:s'); ?></strong></p>
            <p>Database: <code class="bg-gray-100 px-2 py-1 rounded">freshtech_db</code></p>
        </div>
    </div>
</body>
</html>
