<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$orders = [];
$status_filter = $_GET['status'] ?? '';

try {
    $query = "SELECT id, customer_name, email, status, total_amount, created_at FROM orders";
    
    if (!empty($status_filter)) {
        $stmt = $db->prepare($query . " WHERE status = ? ORDER BY created_at DESC");
        $stmt->execute([$status_filter]);
    } else {
        $stmt = $db->query($query . " ORDER BY created_at DESC");
    }
    
    $orders = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error
}

$pageTitle = "Orders";
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-4xl font-bold text-navy-ft">Orders</h1>
        <p class="text-gray-600 mt-1">Manage all customer orders</p>
    </div>
</div>

<!-- Filter Bar -->
<div class="bg-white rounded-xl shadow-md border p-4 mb-6 flex flex-wrap gap-4 items-center">
    <a href="/admin/orders/" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?php echo empty($status_filter) ? 'bg-cyan-ft text-navy-ft' : 'bg-gray-200 hover:bg-gray-300'; ?>">
        <i class="fas fa-list mr-2"></i>All Orders
    </a>
    <a href="/admin/orders/?status=pending" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?php echo $status_filter == 'pending' ? 'bg-orange-ft text-white' : 'bg-gray-200 hover:bg-gray-300'; ?>">
        <i class="fas fa-clock mr-2"></i>Pending
    </a>
    <a href="/admin/orders/?status=completed" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?php echo $status_filter == 'completed' ? 'bg-green-500 text-white' : 'bg-gray-200 hover:bg-gray-300'; ?>">
        <i class="fas fa-check mr-2"></i>Completed
    </a>
    <a href="/admin/orders/?status=cancelled" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?php echo $status_filter == 'cancelled' ? 'bg-red-500 text-white' : 'bg-gray-200 hover:bg-gray-300'; ?>">
        <i class="fas fa-times mr-2"></i>Cancelled
    </a>
</div>

<!-- Orders Table -->
<div class="bg-white rounded-xl shadow-md border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Order ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Customer</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Email</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Amount</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3 opacity-50 block"></i>
                            <p class="text-lg">No orders found</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-semibold text-navy-ft">#<?php echo (int)$order['id']; ?></td>
                            <td class="px-6 py-4 text-sm font-medium"><?php echo sanitize($order['customer_name']); ?></td>
                            <td class="px-6 py-4 text-sm text-cyan-ft"><?php echo sanitize($order['email']); ?></td>
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
                            <td class="px-6 py-4 text-sm">
                                <button onclick="viewOrder(<?php echo (int)$order['id']; ?>)" class="text-cyan-ft hover:text-navy-ft font-semibold transition">
                                    <i class="fas fa-eye mr-1"></i>View
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white">
            <h2 class="text-2xl font-bold">Order Details</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-navy-ft">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="orderContent" class="p-6">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-cyan-ft text-3xl"></i>
                <p class="mt-2">Loading...</p>
            </div>
        </div>
    </div>
</div>

<script>
function viewOrder(id) {
    const modal = document.getElementById('orderModal');
    const content = document.getElementById('orderContent');
    
    modal.classList.remove('hidden');
    
    // In a real app, you'd fetch order details via AJAX
    content.innerHTML = `
        <div class="text-center">
            <p class="text-lg font-semibold text-navy-ft">Order #${id}</p>
            <p class="text-gray-600 mt-2">View full order details in a separate page or modal</p>
            <p class="text-sm text-gray-500 mt-4">Note: Implement detailed order view with items, customer info, and status update options</p>
        </div>
    `;
}

function closeModal() {
    document.getElementById('orderModal').classList.add('hidden');
}

// Close modal on escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
