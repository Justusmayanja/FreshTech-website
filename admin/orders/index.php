<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$orders = [];
$status_filter = $_GET['status'] ?? '';
$filtered_orders = [];
$order_details = [];

// Handle status update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    
    if ($order_id > 0 && in_array($new_status, ['pending', 'processing', 'completed', 'cancelled']) && isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute([$new_status, $order_id]);
            header('Location: /admin/orders/');
            exit;
        } catch (Exception $e) {}
    }
}

// Handle mark as viewed via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_viewed') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    if ($order_id > 0 && isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->prepare('UPDATE orders SET is_viewed = 1 WHERE id = ?');
            $stmt->execute([$order_id]);
            header('Location: /admin/orders/');
            exit;
        } catch (Exception $e) {}
    }
}

// Try to fetch from database
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $query = "SELECT id, order_number, customer_name, email AS customer_email, phone AS customer_phone, address AS shipping_address, payment_method, total_amount, status, is_viewed, created_at FROM orders";
        
        if (!empty($status_filter) && in_array($status_filter, ['pending', 'processing', 'completed', 'cancelled'])) {
            $stmt = $pdo->prepare($query . " WHERE status = ? ORDER BY created_at DESC");
            $stmt->execute([$status_filter]);
        } else {
            $stmt = $pdo->query($query . " ORDER BY created_at DESC");
        }
        
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Database error - use fallback
        $orders = [];
    }
} else {
    // Database not available - use fallback sample data
    $orders = [
        ['id' => 1, 'order_number' => 'FT-20260124-A1B2C3', 'customer_name' => 'John Doe', 'customer_email' => 'john@example.com', 'customer_phone' => '+256701234567', 'shipping_address' => '123 Main St, Kampala', 'payment_method' => 'mobile_money', 'total_amount' => 2500000, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))],
        ['id' => 2, 'order_number' => 'FT-20260127-D4E5F6', 'customer_name' => 'Jane Smith', 'customer_email' => 'jane@example.com', 'customer_phone' => '+256702234567', 'shipping_address' => '456 Oak Ave, Kampala', 'payment_method' => 'cod', 'total_amount' => 3500000, 'status' => 'completed', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))],
        ['id' => 3, 'order_number' => 'FT-20260128-G7H8I9', 'customer_name' => 'Bob Wilson', 'customer_email' => 'bob@example.com', 'customer_phone' => '+256703234567', 'shipping_address' => '789 Pine Rd, Kampala', 'payment_method' => 'mobile_money', 'total_amount' => 1800000, 'status' => 'processing', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
        ['id' => 4, 'order_number' => 'FT-20260129-J0K1L2', 'customer_name' => 'Alice Johnson', 'customer_email' => 'alice@example.com', 'customer_phone' => '+256704234567', 'shipping_address' => '321 Elm St, Kampala', 'payment_method' => 'mobile_money', 'total_amount' => 4200000, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))],
    ];
}

// Filter orders by status
$filtered_orders = $orders;
if (!empty($status_filter) && in_array($status_filter, ['pending', 'processing', 'completed', 'cancelled'])) {
    $filtered_orders = array_filter($orders, function($o) use ($status_filter) {
        return $o['status'] === $status_filter;
    });
}

// Fetch order items for modal (if database available)
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->prepare('SELECT order_id, product_id, product_name, quantity, unit_price AS price, subtotal FROM order_items WHERE order_id = ? ORDER BY id ASC');
        foreach ($orders as $order) {
            $stmt->execute([$order['id']]);
            $order_details[$order['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {}
}

$pageTitle = "Orders";
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container" style="margin-top: 20px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: #0a1a2f; margin: 0; margin-bottom: 5px;">Orders Management</h1>
            <p style="color: #888; margin: 0;">Track and manage all customer orders</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div style="background: white; border-radius: 12px; padding: 16px; margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
        <a href="/admin/orders/" style="padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; background: <?= empty($status_filter) ? 'linear-gradient(135deg, #00d4ff 0%, #0099cc 100%); color: #0a1a2f;' : '#f0f0f0; color: #666;' ?>">
            <i class="fas fa-list"></i> All Orders
        </a>
        <a href="/admin/orders/?status=pending" style="padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; background: <?= $status_filter == 'pending' ? '#ff9500; color: white;' : '#f0f0f0; color: #666;' ?>">
            <i class="fas fa-clock"></i> Pending
        </a>
        <a href="/admin/orders/?status=processing" style="padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; background: <?= $status_filter == 'processing' ? '#3b82f6; color: white;' : '#f0f0f0; color: #666;' ?>">
            <i class="fas fa-spinner"></i> Processing
        </a>
        <a href="/admin/orders/?status=completed" style="padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; background: <?= $status_filter == 'completed' ? '#10b981; color: white;' : '#f0f0f0; color: #666;' ?>">
            <i class="fas fa-check"></i> Completed
        </a>
        <a href="/admin/orders/?status=cancelled" style="padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; background: <?= $status_filter == 'cancelled' ? '#ef4444; color: white;' : '#f0f0f0; color: #666;' ?>">
            <i class="fas fa-times"></i> Cancelled
        </a>
    </div>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border-left: 4px solid #00d4ff;">
            <div style="font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Total Orders</div>
            <div style="font-size: 32px; font-weight: 700; color: #0a1a2f;"><?= count($orders) ?></div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border-left: 4px solid #ff9500;">
            <div style="font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Pending</div>
            <div style="font-size: 32px; font-weight: 700; color: #0a1a2f;"><?= count(array_filter($orders, fn($o) => $o['status'] === 'pending')) ?></div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border-left: 4px solid #3b82f6;">
            <div style="font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Processing</div>
            <div style="font-size: 32px; font-weight: 700; color: #0a1a2f;"><?= count(array_filter($orders, fn($o) => $o['status'] === 'processing')) ?></div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border-left: 4px solid #10b981;">
            <div style="font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Total Revenue</div>
            <div style="font-size: 32px; font-weight: 700; color: #0a1a2f;">UGX <?= number_format((int)array_sum(array_column($orders, 'total_amount'))) ?></div>
        </div>
    </div>

    <!-- Orders Table -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #f8f9fa; border-bottom: 1px solid #e0e0e0;">
                <tr>
                    <th style="padding: 16px; text-align: left; font-size: 13px; font-weight: 600; color: #666; text-transform: uppercase;">Order #</th>
                    <th style="padding: 16px; text-align: left; font-size: 13px; font-weight: 600; color: #666; text-transform: uppercase;">Customer</th>
                    <th style="padding: 16px; text-align: left; font-size: 13px; font-weight: 600; color: #666; text-transform: uppercase;">Email</th>
                    <th style="padding: 16px; text-align: left; font-size: 13px; font-weight: 600; color: #666; text-transform: uppercase;">Amount</th>
                    <th style="padding: 16px; text-align: left; font-size: 13px; font-weight: 600; color: #666; text-transform: uppercase;">Status</th>
                    <th style="padding: 16px; text-align: left; font-size: 13px; font-weight: 600; color: #666; text-transform: uppercase;">Date</th>
                    <th style="padding: 16px; text-align: left; font-size: 13px; font-weight: 600; color: #666; text-transform: uppercase;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($filtered_orders)): ?>
                    <tr style="border-bottom: 1px solid #e0e0e0;">
                        <td colspan="7" style="padding: 40px; text-align: center; color: #999;">
                            <i class="fas fa-inbox" style="font-size: 48px; color: #ddd; margin-bottom: 16px; display: block;"></i>
                            <p style="margin: 0; font-size: 16px;">No orders found</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($filtered_orders as $order): ?>
                        <tr style="border-bottom: 1px solid #e0e0e0; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                            <td style="padding: 16px; font-weight: 600; color: #0a1a2f;"><?= htmlspecialchars($order['order_number'] ?? '#' . $order['id']) ?></td>
                            <td style="padding: 16px; color: #333;"><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td style="padding: 16px; color: #00d4ff; font-size: 14px;"><?= htmlspecialchars($order['customer_email']) ?></td>
                            <td style="padding: 16px; font-weight: 600; color: #0a1a2f;">UGX <?= number_format((int)$order['total_amount']) ?></td>
                            <td style="padding: 16px;">
                                <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; 
                                <?php
                                    $statusColor = 'background: #f0f0f0; color: #666;';
                                    if ($order['status'] === 'pending') $statusColor = 'background: #fff3cd; color: #856404;';
                                    if ($order['status'] === 'processing') $statusColor = 'background: #cfe2ff; color: #084298;';
                                    if ($order['status'] === 'completed') $statusColor = 'background: #d1e7dd; color: #0f5132;';
                                    if ($order['status'] === 'cancelled') $statusColor = 'background: #f8d7da; color: #842029;';
                                    echo $statusColor;
                                ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 16px; color: #666; font-size: 14px;"><?= formatDate($order['created_at']) ?></td>
                            <td style="padding: 16px;">
                                <button onclick="viewOrder(<?= htmlspecialchars(json_encode($order)) ?>)" style="background: #f0f7ff; color: #00d4ff; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 12px; transition: all 0.3s ease; margin-right: 8px;">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (empty($filtered_orders) && !empty($orders)): ?>
        <div style="text-align: center; padding: 40px 20px; color: #999; margin-top: 20px;">
            <i class="fas fa-filter" style="font-size: 36px; color: #ddd; margin-bottom: 12px; display: block;"></i>
            <p style="margin: 0;">No orders match the selected filter</p>
        </div>
    <?php endif; ?>
</div>

<!-- Order Details Modal -->
<div id="orderModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e0e0e0;">
            <h2 id="modalTitle" style="font-size: 24px; color: #0a1a2f; margin: 0; font-weight: 700;">Order Details</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; color: #999; cursor: pointer;">×</button>
        </div>

        <div id="orderContent">
            <div style="text-align: center; padding: 40px 20px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 36px; color: #00d4ff; margin-bottom: 12px; display: block;"></i>
                <p style="color: #999; margin: 0;">Loading order details...</p>
            </div>
        </div>
    </div>
</div>

<script>
function viewOrder(order) {
    const modal = document.getElementById('orderModal');
    const content = document.getElementById('orderContent');
    const title = document.getElementById('modalTitle');
    
    title.textContent = 'Order #' + (order.order_number || order.id);
    modal.style.display = 'flex';

    // Mark order as viewed
    fetch('/admin/orders/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'mark_viewed', order_id: order.id })
    }).catch(() => {});
    
    // Get order items from server
    fetch('/admin/orders/get-order-details.php?id=' + order.id)
        .then(r => r.json())
        .then(data => {
            const items = data.items || [];
            const itemsHtml = items.length > 0 
                ? items.map(item => `
                    <tr style="border-bottom: 1px solid #e0e0e0;">
                        <td style="padding: 12px; text-align: left;">${item.product_name}</td>
                        <td style="padding: 12px; text-align: center;">${item.quantity}</td>
                        <td style="padding: 12px; text-align: right;">UGX ${new Intl.NumberFormat().format(Math.round(item.price))}</td>
                        <td style="padding: 12px; text-align: right; font-weight: 600;">UGX ${new Intl.NumberFormat().format(Math.round(item.subtotal))}</td>
                    </tr>
                `).join('')
                : '<tr><td colspan="4" style="padding: 20px; text-align: center; color: #999;">No items in this order</td></tr>';
            
            content.innerHTML = `
                <div style="space-y: 16px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <p style="color: #888; font-size: 12px; text-transform: uppercase; font-weight: 600; margin: 0 0 4px;">Order Number</p>
                            <p style="color: #0a1a2f; font-weight: 700; font-size: 16px; margin: 0;">${order.order_number}</p>
                        </div>
                        <div>
                            <p style="color: #888; font-size: 12px; text-transform: uppercase; font-weight: 600; margin: 0 0 4px;">Status</p>
                            <select onchange="updateOrderStatus(${order.id}, this.value)" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-weight: 600;">
                                <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                                <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div style="background: #f8f9fa; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                        <h3 style="color: #0a1a2f; margin: 0 0 12px; font-size: 14px; font-weight: 600;">Customer Information</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 14px;">
                            <div>
                                <p style="color: #888; margin: 0; font-size: 12px; text-transform: uppercase; font-weight: 600;">Name</p>
                                <p style="color: #0a1a2f; margin: 4px 0 0; font-weight: 600;">${order.customer_name}</p>
                            </div>
                            <div>
                                <p style="color: #888; margin: 0; font-size: 12px; text-transform: uppercase; font-weight: 600;">Email</p>
                                <p style="color: #00d4ff; margin: 4px 0 0; font-weight: 600;">${order.customer_email}</p>
                            </div>
                            <div>
                                <p style="color: #888; margin: 0; font-size: 12px; text-transform: uppercase; font-weight: 600;">Phone</p>
                                <p style="color: #0a1a2f; margin: 4px 0 0; font-weight: 600;">${order.customer_phone || 'N/A'}</p>
                            </div>
                            <div>
                                <p style="color: #888; margin: 0; font-size: 12px; text-transform: uppercase; font-weight: 600;">Payment Method</p>
                                <p style="color: #0a1a2f; margin: 4px 0 0; font-weight: 600;">${order.payment_method === 'mobile_money' ? 'Mobile Money' : 'Cash on Delivery'}</p>
                            </div>
                        </div>
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #ddd;">
                            <p style="color: #888; margin: 0; font-size: 12px; text-transform: uppercase; font-weight: 600;">Delivery Address</p>
                            <p style="color: #0a1a2f; margin: 4px 0 0; font-weight: 600;">${order.shipping_address || 'N/A'}</p>
                        </div>
                    </div>

                    <h3 style="color: #0a1a2f; margin: 0 0 12px; font-size: 14px; font-weight: 600;">Order Items</h3>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                        <thead style="background: #f8f9fa; border-bottom: 2px solid #e0e0e0;">
                            <tr>
                                <th style="padding: 12px; text-align: left; font-weight: 600; color: #666; font-size: 13px;">Product</th>
                                <th style="padding: 12px; text-align: center; font-weight: 600; color: #666; font-size: 13px;">Qty</th>
                                <th style="padding: 12px; text-align: right; font-weight: 600; color: #666; font-size: 13px;">Unit Price</th>
                                <th style="padding: 12px; text-align: right; font-weight: 600; color: #666; font-size: 13px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>

                    <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e8eaef 100%); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 16px; font-weight: 600; color: #0a1a2f;">Total Amount:</span>
                            <span style="font-size: 24px; font-weight: 700; color: #00d4ff;">UGX ${new Intl.NumberFormat().format(Math.round(order.total_amount))}</span>
                        </div>
                    </div>

                    <p style="color: #999; font-size: 12px; margin: 0;">Order placed: ${new Date(order.created_at).toLocaleString()}</p>
                </div>
            `;
        })
        .catch(e => {
            content.innerHTML = '<p style="color: #c00; padding: 20px; text-align: center;">Error loading order details</p>';
        });
}

function updateOrderStatus(orderId, newStatus) {
    if (!confirm('Update order status to "' + newStatus + '"?')) return;
    
    fetch('/admin/orders/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_status&order_id=${orderId}&status=${newStatus}`
    })
    .then(r => r.text())
    .then(() => {
        // Reload the page to update the order list and sidebar badge
        window.location.reload();
    })
    .catch(e => {
        alert('Failed to update order status');
    });
}

function closeModal() {
    document.getElementById('orderModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('orderModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Close modal on escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    #orderModal > div {
        width: 95% !important;
    }
    
    table {
        font-size: 12px !important;
    }
    
    table th, table td {
        padding: 10px !important;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
