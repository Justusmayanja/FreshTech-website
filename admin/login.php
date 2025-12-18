<?php
require_once __DIR__ . '/includes/config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

// Check database connection first
if ($db_error) {
    $error = 'Database not configured. Please set up MySQL first. ' . $db_error;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else if (!$db) {
        $error = 'Database connection unavailable';
    } else {
        try {
            $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? AND status = 'active'");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['name'];
                
                // Update last login
                $update = $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                $update->execute([$admin['id']]);
                
                redirect('dashboard.php');
            } else {
                $error = 'Invalid username or password';
            }
        } catch (Exception $e) {
            $error = 'Login error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - FreshTech Solutions</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0A1A2F 0%, #1a3a52 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo & Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-cyan-ft bg-opacity-20 border-2 border-cyan-ft mb-4">
                <i class="fas fa-rocket text-cyan-ft text-2xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">FreshTech</h1>
            <p class="text-cyan-ft font-semibold">Admin Panel</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="h-1 bg-gradient-to-r from-cyan-ft to-orange-ft"></div>
            
            <div class="p-8">
                <!-- Error Message -->
                <?php if (!empty($error)): ?>
                    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                        <div class="flex items-center text-red-700">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span><?php echo sanitize($error); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-navy-ft mb-2">
                            <i class="fas fa-user mr-2"></i>Username
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            autofocus
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-cyan-ft focus:border-transparent transition"
                            placeholder="admin"
                        >
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-navy-ft mb-2">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                class="w-full px-4 py-3 pr-12 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-cyan-ft focus:border-transparent transition"
                                placeholder="••••••••"
                            >
                            <button 
                                type="button" 
                                id="togglePassword"
                                class="absolute right-4 top-3.5 text-gray-500 hover:text-navy-ft transition"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full py-3 mt-6 bg-gradient-to-r from-cyan-ft to-orange-ft text-white font-bold rounded-lg hover:shadow-lg transition duration-300 flex items-center justify-center"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </button>
                </form>

                <!-- Footer Info -->
                <div class="mt-8 pt-6 border-t border-gray-200 text-center text-sm text-gray-600">
                    <p><strong>Demo Credentials:</strong></p>
                    <p>Username: <code class="bg-gray-100 px-2 py-1 rounded">admin</code></p>
                    <p>Password: <code class="bg-gray-100 px-2 py-1 rounded">FreshTech2025!</code></p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-white text-sm mt-6 opacity-75">
            FreshTech Solutions © 2025
        </p>
    </div>

    <script>
        // Password visibility toggle
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const icon = toggleBtn.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
