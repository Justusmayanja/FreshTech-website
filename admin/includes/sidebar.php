<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Ensure orders table has required columns
require_once __DIR__ . '/../../ensure-orders-table.php';

// Get unviewed orders count
$pendingOrdersCount = 0;
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $pendingOrdersCount = (int) ($pdo->query('SELECT COUNT(*) FROM orders WHERE is_viewed = 0')->fetchColumn() ?? 0);
    } catch (Exception $e) {
        try {
            $pendingOrdersCount = (int) ($pdo->query('SELECT COUNT(*) FROM orders WHERE status = "pending"')->fetchColumn() ?? 0);
        } catch (Exception $e2) { }
    }
}

// Get unread messages count
$unreadMessagesCount = 0;
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $unreadMessagesCount = (int) ($pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn() ?? 0);
    } catch (Exception $e) { }
}
?>

<!-- Logo -->
<div class="p-6 border-b border-navy-ft/20">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-cyan-ft to-orange-ft flex items-center justify-center">
            <i class="fas fa-rocket text-white text-xl"></i>
        </div>
        <div>
            <div class="font-bold text-white text-lg">PulseTech</div>
            <div class="text-cyan-ft text-xs font-semibold">Admin</div>
        </div>
    </div>
</div>

<!-- Navigation Menu -->
<nav class="flex-1 p-4 space-y-2">
    <!-- Main -->
    <div class="text-xs uppercase tracking-widest text-cyan-ft font-bold mt-6 mb-3 px-3">Main</div>
    
    <a href="/admin/dashboard.php" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentPage == 'dashboard.php' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-chart-line w-5"></i>
        <span>Dashboard</span>
    </a>

    <!-- Content Management -->
    <div class="text-xs uppercase tracking-widest text-cyan-ft font-bold mt-6 mb-3 px-3">Management</div>
    
    <a href="/admin/orders/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentDir == 'orders' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-shopping-cart w-5"></i>
        <span>Orders</span>
        <?php if ($pendingOrdersCount > 0): ?>
            <span class="ml-auto bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center animate-pulse">
                <?= $pendingOrdersCount > 9 ? '9+' : $pendingOrdersCount ?>
            </span>
        <?php endif; ?>
    </a>
    
    <a href="/admin/aboutus/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentDir == 'aboutus' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-info-circle w-5"></i>
        <span>About Us</span>
    </a>
    
    <a href="/admin/portfolio/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentDir == 'portfolio' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-image w-5"></i>
        <span>Portfolio</span>
    </a>
    
    <a href="/admin/services/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentDir == 'services' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-cog w-5"></i>
        <span>Services</span>
    </a>
    
    <a href="/admin/shop/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentDir == 'shop' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-store w-5"></i>
        <span>Shop</span>
    </a>
    
    <a href="/admin/messages/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentDir == 'messages' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-envelope w-5"></i>
        <span>Messages</span>
        <?php if ($unreadMessagesCount > 0): ?>
            <span class="ml-auto bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center animate-pulse">
                <?= $unreadMessagesCount > 9 ? '9+' : $unreadMessagesCount ?>
            </span>
        <?php endif; ?>
    </a>
    
    <a href="/admin/testimonials/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentDir == 'testimonials' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-star w-5"></i>
        <span>Testimonials</span>
    </a>

    <!-- Tools -->
    <div class="text-xs uppercase tracking-widest text-cyan-ft font-bold mt-6 mb-3 px-3">Tools</div>
    
    <a href="/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 text-gray-300 hover:bg-white/10">
        <i class="fas fa-external-link w-5"></i>
        <span>View Site</span>
    </a>
</nav>

<!-- Footer -->
<div class="p-4 border-t border-navy-ft/20 text-gray-400 text-xs text-center">
    <p>FreshTech Solutions</p>
    <p class="text-cyan-ft">© <?php echo date('Y'); ?></p>
</div>
