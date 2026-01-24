<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/auth.php';
// simple base path for assets — adapt if your site runs in a subfolder
$assetBase = '/admin/assets';
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin — PulseTech</title>
  <link rel="stylesheet" href="<?= htmlspecialchars($assetBase) ?>/css/admin.css">
</head>
<body class="admin-root">
  <div class="admin-layout">
    <aside class="sidebar">
      <div class="brand">PulseTech Admin</div>
      <nav>
        <a href="/admin/dashboard.php">Dashboard</a>
        <a href="/admin/products/index.php">Products</a>
        <a href="/admin/portfolio/index.php">Portfolio</a>
        <a href="/admin/testimonials/index.php">Testimonials</a>
        <a href="/admin/brands/index.php">Brands</a>
        <a href="/admin/inquiries/index.php">Inquiries</a>
        <a href="/admin/logout.php">Logout</a>
      </nav>
    </aside>
    <main class="main">
      <header class="topbar">
        <div class="top-left">Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Guest') ?></div>
      </header>
      <section class="content">
<?php
require_once __DIR__ . '/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' - ' : ''; ?>FreshTech Admin</title>
    
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
    
    <style>
        [data-theme="dark"] {
            color-scheme: dark;
        }
        
        .sidebar-closed main {
            margin-left: 0;
        }
        
        .sidebar-open aside {
            transform: translateX(0);
        }
        
        .sidebar-open .sidebar-overlay {
            display: block;
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

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 bg-white shadow h-16 z-10 md:ml-64">
        <div class="flex items-center justify-between h-full px-6">
            <!-- Mobile Menu Button & Logo -->
            <div class="flex items-center gap-4">
                <button id="toggleSidebar" class="md:hidden p-2 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-bars text-xl text-navy-ft"></i>
                </button>
                <div class="hidden md:flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-ft to-orange-ft flex items-center justify-center">
                        <i class="fas fa-rocket text-white"></i>
                    </div>
                    <span class="font-bold text-navy-ft">FreshTech</span>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex items-center gap-4">
                <!-- Theme Toggle -->
                <button id="themeToggle" class="p-2 hover:bg-gray-100 rounded-lg transition" title="Toggle Dark Mode">
                    <i class="fas fa-sun text-orange-ft text-lg"></i>
                </button>
                
                <!-- User Menu -->
                <div class="relative group">
                    <button class="flex items-center gap-2 p-2 hover:bg-gray-100 rounded-lg transition">
                        <div class="w-8 h-8 rounded-full bg-cyan-ft flex items-center justify-center text-white text-sm font-bold">
                            <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?>
                        </div>
                        <span class="hidden sm:inline text-sm font-medium text-gray-700"><?php echo sanitize($_SESSION['admin_name'] ?? $_SESSION['admin_username'] ?? 'Admin'); ?></span>
                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 mt-0 w-48 bg-white rounded-lg shadow-lg hidden group-hover:block z-50 border border-gray-200">
                        <a href="/admin/logout.php" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-lg m-2 transition">
                            <i class="fas fa-sign-out-alt mr-2 text-orange-ft"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="md:ml-64 mt-16 p-6">
        <!-- Breadcrumb -->
        <?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
        <div class="mb-6 text-sm text-gray-600 flex items-center gap-2">
            <a href="/admin/dashboard.php" class="hover:text-cyan-ft transition">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <?php foreach ($breadcrumb as $link => $label): ?>
                <span>/</span>
                <?php if ($link): ?>
                    <a href="<?php echo sanitize($link); ?>" class="hover:text-cyan-ft transition"><?php echo sanitize($label); ?></a>
                <?php else: ?>
                    <span><?php echo sanitize($label); ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
