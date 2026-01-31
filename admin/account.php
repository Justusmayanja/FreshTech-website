<?php
$pageTitle = 'Account Settings';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';

$success = $error = '';
$admin_id = $_SESSION['admin_id'] ?? 0;

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!$pdo) {
        $error = 'Database connection failed. Please ensure MySQL is running and the database exists.';
    } elseif (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        try {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
            $stmt->execute([$admin_id]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$admin || !password_verify($current_password, $admin['password_hash'])) {
                $error = 'Current password is incorrect';
            } else {
                // Update password
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                
                if ($stmt->execute([$hashed, $admin_id])) {
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'Failed to update password';
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
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-orange-ft to-red-600 flex items-center justify-center">
                    <i class="fas fa-cog text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Account Settings</h1>
                    <p class="text-gray-600">Manage your account security and preferences</p>
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

        <!-- Change Password Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-orange-ft to-red-600 p-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-lock"></i>
                    Change Password
                </h2>
                <p class="text-white/90 text-sm mt-1">Keep your account secure with a strong password</p>
            </div>

            <form method="POST" class="p-6 space-y-6">
                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-key text-orange-ft mr-2"></i>Current Password
                    </label>
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-ft focus:border-transparent transition"
                        placeholder="Enter your current password"
                    >
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-lock text-orange-ft mr-2"></i>New Password
                        </label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            required
                            minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-ft focus:border-transparent transition"
                            placeholder="Minimum 6 characters"
                        >
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-lock text-orange-ft mr-2"></i>Confirm New Password
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                            minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-ft focus:border-transparent transition"
                            placeholder="Re-enter new password"
                        >
                    </div>
                </div>

                <!-- Password Requirements -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2 text-sm">Password Requirements:</h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li><i class="fas fa-check text-blue-600 mr-2"></i>At least 6 characters long</li>
                        <li><i class="fas fa-check text-blue-600 mr-2"></i>Mix of letters and numbers recommended</li>
                        <li><i class="fas fa-check text-blue-600 mr-2"></i>Avoid common passwords</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 justify-end border-t pt-6">
                    <a href="/admin/dashboard.php" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <button 
                        type="submit" 
                        name="change_password"
                        class="px-6 py-3 bg-gradient-to-r from-orange-ft to-red-600 text-white rounded-lg hover:shadow-lg transition font-medium"
                    >
                        <i class="fas fa-save mr-2"></i>Change Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Security Tips -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-shield-alt text-cyan-ft"></i>
                Security Tips
            </h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="flex gap-3">
                    <div class="w-10 h-10 rounded-full bg-cyan-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-lightbulb text-cyan-ft"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Use Strong Passwords</h4>
                        <p class="text-gray-600 text-xs mt-1">Combine uppercase, lowercase, numbers, and symbols</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-sync text-orange-ft"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Change Regularly</h4>
                        <p class="text-gray-600 text-xs mt-1">Update your password every 3-6 months</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-secret text-green-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Keep it Private</h4>
                        <p class="text-gray-600 text-xs mt-1">Never share your password with anyone</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm">Avoid Reuse</h4>
                        <p class="text-gray-600 text-xs mt-1">Don't use the same password across sites</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
