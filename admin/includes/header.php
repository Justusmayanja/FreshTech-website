<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>PulseTech Admin</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'cyan-ft': '#00D4FF',
                        'navy-ft': '#0A1A2F',
                        'orange-ft': '#FF6B00'
                    }
                }
            }
        };
    </script>
    
    <!-- Flowbite -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [data-theme="dark"] {
            color-scheme: dark;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900" data-theme="light">
    <!-- Sidebar Overlay (Mobile) -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed left-0 top-0 h-screen w-64 bg-navy-ft text-white transform -translate-x-full transition-transform duration-300 z-40 md:translate-x-0 md:z-20 overflow-y-auto">
        <?php include __DIR__ . '/sidebar.php'; ?>
    </aside>

    <!-- Header with Cyan Navbar -->
    <header class="fixed top-0 left-0 right-0 bg-gradient-to-r from-cyan-ft to-cyan-600 shadow-lg z-10 md:ml-64">
        <div class="h-24 px-6 flex flex-col justify-center">
            <h1 class="text-2xl font-bold text-navy-ft">Welcome to PulseTech Admin Portal</h1>
            <p class="text-sm text-navy-ft font-medium opacity-90">Professional business management for your digital innovation company</p>
        </div>

        <!-- Navbar Controls (Right Side) -->
        <div class="absolute top-6 right-6 flex items-center gap-4">
            <!-- Theme Toggle -->
            <button id="themeToggle" class="p-2 hover:bg-cyan-500 rounded-lg transition text-navy-ft" title="Toggle Dark Mode">
                <i class="fas fa-sun text-lg"></i>
            </button>
            
            <!-- User Menu -->
            <div class="relative" x-data="{ open: false }">
                <button 
                    @click="open = !open" 
                    @click.away="open = false"
                    class="flex items-center gap-2 p-2 hover:bg-cyan-500 rounded-lg transition"
                >
                    <div class="w-8 h-8 rounded-full bg-navy-ft flex items-center justify-center text-white text-sm font-bold">
                        <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?>
                    </div>
                    <span class="hidden sm:inline text-sm font-medium text-navy-ft"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                    <i class="fas fa-chevron-down text-xs text-navy-ft transition-transform" :class="{ 'rotate-180': open }"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div 
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50"
                    style="display: none;"
                >
                    <!-- User Info Header -->
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></p>
                        <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'admin@pulsetech.com'); ?></p>
                    </div>
                    
                    <!-- Menu Items -->
                    <div class="py-1">
                        <a href="/admin/profile.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-cyan-50 transition">
                            <div class="w-8 h-8 rounded-full bg-cyan-50 flex items-center justify-center">
                                <i class="fas fa-user text-cyan-ft text-sm"></i>
                            </div>
                            <div>
                                <div class="font-medium">Profile Settings</div>
                                <div class="text-xs text-gray-500">Update your information</div>
                            </div>
                        </a>
                        
                        <a href="/admin/account.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 transition">
                            <div class="w-8 h-8 rounded-full bg-orange-50 flex items-center justify-center">
                                <i class="fas fa-cog text-orange-ft text-sm"></i>
                            </div>
                            <div>
                                <div class="font-medium">Account Settings</div>
                                <div class="text-xs text-gray-500">Security & password</div>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Logout -->
                    <div class="border-t border-gray-100 py-1">
                        <a href="/admin/logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                            <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center">
                                <i class="fas fa-sign-out-alt text-red-600 text-sm"></i>
                            </div>
                            <div>
                                <div class="font-medium">Sign Out</div>
                                <div class="text-xs text-red-500">End your session</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="md:ml-64 mt-24 p-6">
        <!-- Breadcrumb -->
        <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
        <div class="mb-6 text-sm text-gray-600 flex items-center gap-2">
            <a href="/admin/dashboard.php" class="hover:text-cyan-ft transition">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <?php foreach ($breadcrumb as $link => $label): ?>
                <span>/</span>
                <?php if ($link): ?>
                    <a href="<?php echo htmlspecialchars($link); ?>" class="hover:text-cyan-ft transition"><?php echo htmlspecialchars($label); ?></a>
                <?php else: ?>
                    <span><?php echo htmlspecialchars($label); ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
