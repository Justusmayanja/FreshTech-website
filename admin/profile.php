<?php
$pageTitle = 'Profile Settings';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';

$success = $error = '';
$admin_id = $_SESSION['admin_id'] ?? 0;

// Check if database connection exists
if (!$pdo) {
    $error = 'Database connection failed. Please ensure MySQL is running and the database exists.';
    $admin = [
        'username' => $_SESSION['admin_username'] ?? 'Admin',
        'role' => 'admin',
        'email' => $_SESSION['admin_email'] ?? 'admin@pulsetech.com',
        'created_at' => date('Y-m-d H:i:s')
    ];
} else {
    // Fetch current admin data
    try {
        $stmt = $pdo->prepare("SELECT username, role, created_at FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            header('Location: /admin/logout.php');
            exit;
        }

        // Set email from session or use default
        $admin['email'] = $_SESSION['admin_email'] ?? 'admin@pulsetech.com';
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
        $admin = [
            'username' => $_SESSION['admin_username'] ?? 'Admin',
            'role' => 'admin',
            'email' => $_SESSION['admin_email'] ?? 'admin@pulsetech.com',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    
    if (!$pdo) {
        $error = 'Database connection failed. Cannot update profile.';
    } elseif (empty($username)) {
        $error = 'Username is required';
    } else {
        try {
            // Check if username already exists for another admin
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
            $stmt->execute([$username, $admin_id]);
            
            if ($stmt->fetch()) {
                $error = 'Username already exists';
            } else {
                $stmt = $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?");
                if ($stmt->execute([$username, $admin_id])) {
                    $_SESSION['admin_username'] = $username;
                    $success = 'Profile updated successfully!';
                    $admin['username'] = $username;
                } else {
                    $error = 'Failed to update profile';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<main class="md:ml-64 pt-24 min-h-screen bg-gray-50">
    <div class="p-6 max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-cyan-ft to-cyan-600 flex items-center justify-center">
                    <i class="fas fa-user text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
                    <p class="text-gray-600">Manage your personal information</p>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Profile Information Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-cyan-ft to-cyan-600 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center border-2 border-white/50">
                        <span class="text-4xl font-bold text-white">
                            <?php echo strtoupper(substr($admin['username'], 0, 1)); ?>
                        </span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($admin['username']); ?></h2>
                        <p class="text-white/90"><?php echo htmlspecialchars($admin['email']); ?></p>
                        <p class="text-white/70 text-sm mt-1">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Member since <?php echo date('F j, Y', strtotime($admin['created_at'])); ?>
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST" class="p-6 space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-cyan-ft mr-2"></i>Username
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?php echo htmlspecialchars($admin['username']); ?>"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft focus:border-transparent transition"
                            placeholder="Enter username"
                        >
                    </div>

                    <!-- Role (Read-only) -->
                    <div>
                        <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-shield-alt text-cyan-ft mr-2"></i>Role
                        </label>
                        <input 
                            type="text" 
                            id="role" 
                            value="<?php echo htmlspecialchars(ucfirst($admin['role'])); ?>"
                            readonly
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed"
                        >
                    </div>
                </div>

                <!-- Account Info -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-id-badge text-cyan-ft text-lg"></i>
                            <div>
                                <div class="text-gray-500 text-xs">Admin ID</div>
                                <div class="font-semibold text-gray-900">#<?php echo $admin_id; ?></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-shield-alt text-cyan-ft text-lg"></i>
                            <div>
                                <div class="text-gray-500 text-xs">Role</div>
                                <div class="font-semibold text-gray-900"><?php echo htmlspecialchars(ucfirst($admin['role'])); ?></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-envelope text-cyan-ft text-lg"></i>
                            <div>
                                <div class="text-gray-500 text-xs">Email</div>
                                <div class="font-semibold text-gray-900 text-xs truncate"><?php echo htmlspecialchars($admin['email']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 justify-end border-t pt-6">
                    <a href="/admin/dashboard.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <button 
                        type="submit" 
                        name="update_profile"
                        class="px-6 py-3 bg-gradient-to-r from-cyan-ft to-cyan-600 text-white rounded-lg hover:shadow-lg transition font-medium"
                    >
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
