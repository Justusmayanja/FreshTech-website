<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>

<!-- Logo -->
<div class="p-6 border-b border-navy-ft/20">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-cyan-ft to-orange-ft flex items-center justify-center">
            <i class="fas fa-rocket text-white text-xl"></i>
        </div>
        <div>
            <div class="font-bold text-white text-lg">FreshTech</div>
            <div class="text-cyan-ft text-xs font-semibold">Solutions</div>
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
    </a>
    
    <a href="/admin/products/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentDir == 'products' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-box w-5"></i>
        <span>Products</span>
    </a>
    
    <a href="/admin/portfolio/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentDir == 'portfolio' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-image w-5"></i>
        <span>Portfolio</span>
    </a>
    
    <a href="/admin/services/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentDir == 'services' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-cog w-5"></i>
        <span>Services</span>
    </a>
    
    <a href="/admin/blog/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentDir == 'blog' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-newspaper w-5"></i>
        <span>Blog Posts</span>
    </a>
    
    <a href="/admin/messages/" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg transition duration-200 <?php echo $currentDir == 'messages' ? 'bg-cyan-ft text-navy-ft' : 'text-gray-300 hover:bg-white/10'; ?>">
        <i class="fas fa-envelope w-5"></i>
        <span>Messages</span>
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
    <p class="text-cyan-ft">© 2025</p>
</div>
