<?php
require_once __DIR__ . '/includes/auth.php';
require_admin_login();
require_once __DIR__ . '/includes/db.php';

// Counts
$counts = [];
try {
    $counts['products'] = (int) ($pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() ?? 0);
} catch (Exception $e) { $counts['products'] = 0; }
try { $counts['portfolio'] = (int) ($pdo->query('SELECT COUNT(*) FROM portfolio')->fetchColumn() ?? 0); } catch (Exception $e) { $counts['portfolio'] = 0; }
try { $counts['testimonials'] = (int) ($pdo->query('SELECT COUNT(*) FROM testimonials')->fetchColumn() ?? 0); } catch (Exception $e) { $counts['testimonials'] = 0; }
try { $counts['inquiries_unread'] = (int) ($pdo->query('SELECT COUNT(*) FROM inquiries WHERE is_read = 0')->fetchColumn() ?? 0); } catch (Exception $e) { $counts['inquiries_unread'] = 0; }

include __DIR__ . '/includes/header.php';
?>
<h2>Dashboard</h2>
<div class="cards">
  <div class="card">Products<br><strong><?= $counts['products'] ?></strong></div>
  <div class="card">Portfolio<br><strong><?= $counts['portfolio'] ?></strong></div>
  <div class="card">Testimonials<br><strong><?= $counts['testimonials'] ?></strong></div>
  <div class="card">Unread Inquiries<br><strong><?= $counts['inquiries_unread'] ?></strong></div>
</div>
<div class="quick-links">
  <a class="btn" href="/admin/products/index.php">Manage Products</a>
  <a class="btn" href="/admin/portfolio/index.php">Manage Portfolio</a>
  <a class="btn" href="/admin/inquiries/index.php">View Inquiries</a>
</div>

<?php include __DIR__ . '/includes/footer.php';
<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$stats = getDashboardStats($db);
$recentOrders = [];
$recentMessages = [];

try {
    // Recent 5 orders
    $stmt = $db->query("SELECT id, customer_name, status, total_amount, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
    $recentOrders = $stmt->fetchAll();
} catch (Exception $e) {
    // Table might not exist
}

try {
    // Recent 5 messages
    $stmt = $db->query("SELECT id, name, email, subject, is_read, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5");
    $recentMessages = $stmt->fetchAll();
} catch (Exception $e) {
    // Table might not exist
}

$pageTitle = "Dashboard";
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-4xl font-bold text-navy-ft mb-2">Dashboard</h1>
    <p class="text-gray-600">Welcome back, <?php echo sanitize($_SESSION['admin_name'] ?? 'Administrator'); ?>!</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Orders -->
    <div class="bg-white rounded-xl shadow-md border-l-4 border-l-cyan-ft hover:shadow-lg transition">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-600 font-semibold">Total Orders</h3>
                <i class="fas fa-shopping-cart text-cyan-ft text-2xl opacity-80"></i>
            </div>
            <div class="text-4xl font-bold text-navy-ft"><?php echo number_format($stats['total_orders']); ?></div>
            <p class="text-sm text-gray-500 mt-2">All time orders</p>
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="bg-white rounded-xl shadow-md border-l-4 border-l-orange-ft hover:shadow-lg transition">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-600 font-semibold">Pending Orders</h3>
                <i class="fas fa-clock text-orange-ft text-2xl opacity-80"></i>
            </div>
            <div class="text-4xl font-bold text-navy-ft"><?php echo number_format($stats['pending_orders']); ?></div>
            <p class="text-sm text-gray-500 mt-2">Need attention</p>
        </div>
    </div>

    <!-- New Messages -->
    <div class="bg-white rounded-xl shadow-md border-l-4 border-l-blue-500 hover:shadow-lg transition">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-600 font-semibold">New Messages</h3>
                <i class="fas fa-envelope text-blue-500 text-2xl opacity-80"></i>
            </div>
            <div class="text-4xl font-bold text-navy-ft"><?php echo number_format($stats['new_messages']); ?></div>
            <p class="text-sm text-gray-500 mt-2">Unread messages</p>
        </div>
    </div>

    <!-- Total Products -->
    <div class="bg-white rounded-xl shadow-md border-l-4 border-l-green-500 hover:shadow-lg transition">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-600 font-semibold">Total Products</h3>
                <i class="fas fa-box text-green-500 text-2xl opacity-80"></i>
            </div>
            <div class="text-4xl font-bold text-navy-ft"><?php echo number_format($stats['total_products']); ?></div>
            <p class="text-sm text-gray-500 mt-2">In catalog</p>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="bg-white rounded-xl shadow-md border mb-8">
    <div class="p-6 border-b flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-navy-ft">Recent Orders</h2>
            <p class="text-sm text-gray-600 mt-1">Latest 5 orders</p>
        </div>
        <a href="/admin/orders/" class="px-4 py-2 bg-cyan-ft text-navy-ft rounded-lg font-semibold hover:bg-opacity-90 transition">
            <i class="fas fa-arrow-right mr-2"></i>View All
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Order ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Customer</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Amount</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 opacity-50"></i>
                            <p>No orders yet</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-semibold text-navy-ft">#<?php echo (int)$order['id']; ?></td>
                            <td class="px-6 py-4 text-sm"><?php echo sanitize($order['customer_name']); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    <?php 
                                    $statusColor = 'bg-gray-100 text-gray-800';
                                    if ($order['status'] == 'pending') $statusColor = 'bg-orange-100 text-orange-800';
                                    if ($order['status'] == 'completed') $statusColor = 'bg-green-100 text-green-800';
                                    if ($order['status'] == 'cancelled') $statusColor = 'bg-red-100 text-red-800';
                                    echo $statusColor;
                                    ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold">$<?php echo number_format((float)$order['total_amount'], 2); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo formatDate($order['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Messages Table -->
<div class="bg-white rounded-xl shadow-md border">
    <div class="p-6 border-b flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-navy-ft">Recent Messages</h2>
            <p class="text-sm text-gray-600 mt-1">Latest contact form submissions</p>
        </div>
        <a href="/admin/messages/" class="px-4 py-2 bg-cyan-ft text-navy-ft rounded-lg font-semibold hover:bg-opacity-90 transition">
            <i class="fas fa-arrow-right mr-2"></i>View All
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Message ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Email</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Subject</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentMessages)): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-3xl mb-2 opacity-50"></i>
                            <p>No messages yet</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentMessages as $msg): ?>
                        <tr class="border-b hover:bg-gray-50 transition <?php echo !$msg['is_read'] ? 'bg-blue-50' : ''; ?>">
                            <td class="px-6 py-4 text-sm font-semibold text-navy-ft">#<?php echo (int)$msg['id']; ?></td>
                            <td class="px-6 py-4 text-sm"><?php echo sanitize($msg['name']); ?></td>
                            <td class="px-6 py-4 text-sm text-cyan-ft"><?php echo sanitize($msg['email']); ?></td>
                            <td class="px-6 py-4 text-sm"><?php echo sanitize($msg['subject']); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $msg['is_read'] ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo $msg['is_read'] ? 'Read' : 'New'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo formatDate($msg['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php include __DIR__ . '/includes/footer.php'; ?>
