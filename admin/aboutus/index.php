<?php
$pageTitle = 'About Us Management';
require_once __DIR__ . '/../includes/db.php';

$success = $error = '';

// Create tables if they don't exist
if ($pdo) {
    try {
        // Team members table
        $pdo->exec("CREATE TABLE IF NOT EXISTS team_members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            position VARCHAR(255) NOT NULL,
            bio TEXT,
            image_url VARCHAR(500),
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Stats table
        $pdo->exec("CREATE TABLE IF NOT EXISTS about_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stat_value VARCHAR(100) NOT NULL,
            stat_label VARCHAR(255) NOT NULL,
            display_order INT DEFAULT 0
        )");

        // About images (collage)
        $pdo->exec("CREATE TABLE IF NOT EXISTS about_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image_url VARCHAR(500) NOT NULL,
            alt_text VARCHAR(255) DEFAULT NULL,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Certifications & Partners
        $pdo->exec("CREATE TABLE IF NOT EXISTS about_partners (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            logo_url VARCHAR(500) DEFAULT NULL,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Check if stats exist, if not create defaults
        $count = $pdo->query("SELECT COUNT(*) FROM about_stats")->fetchColumn();
        if ($count == 0) {
            $defaultStats = [
                ['200+', 'Projects Shipped', 1],
                ['50+', 'Active Clients', 2],
                ['2.4s', 'Avg. Site Load Time', 3],
                ['98%', 'Client Satisfaction', 4]
            ];
            $stmt = $pdo->prepare("INSERT INTO about_stats (stat_value, stat_label, display_order) VALUES (?, ?, ?)");
            foreach ($defaultStats as $stat) {
                $stmt->execute($stat);
            }
        }

        // Default images
        $imgCount = $pdo->query("SELECT COUNT(*) FROM about_images")->fetchColumn();
        if ($imgCount == 0) {
            $defaultImages = [
                ['https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&h=440&fit=crop', 'Team strategy session', 1],
                ['https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&h=440&fit=crop', 'Developer coding a website', 2],
                ['https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600&h=440&fit=crop', 'Design desk with brand assets', 3],
                ['https://images.unsplash.com/photo-1551836022-4c4c79ecde51?w=600&h=440&fit=crop', 'UX workshop with sticky notes', 4],
                ['https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&h=440&fit=crop', 'Smartwatch and accessories', 5]
            ];
            $stmt = $pdo->prepare("INSERT INTO about_images (image_url, alt_text, display_order) VALUES (?, ?, ?)");
            foreach ($defaultImages as $img) {
                $stmt->execute($img);
            }
        }

        // Default partners
        $partnerCount = $pdo->query("SELECT COUNT(*) FROM about_partners")->fetchColumn();
        if ($partnerCount == 0) {
            $defaultPartners = [
                ['Shopify Partner', null, 1],
                ['Webflow Expert', null, 2],
                ['Meta Blueprint', null, 3],
                ['Google Partner', null, 4],
                ['Figma Community', null, 5],
                ['AWS Certified', null, 6]
            ];
            $stmt = $pdo->prepare("INSERT INTO about_partners (name, logo_url, display_order) VALUES (?, ?, ?)");
            foreach ($defaultPartners as $partner) {
                $stmt->execute($partner);
            }
        }

        // Default team members - auto-load on first visit
        $memberCount = $pdo->query("SELECT COUNT(*) FROM team_members")->fetchColumn();
        if ($memberCount == 0) {
            $defaultMembers = [
                ['Mayanja Justus', 'Founder & CEO', 'Previously led design at a YC-backed fintech. Passionate about conversion-first UX.', null, 1],
                ['Awongo Fahadi Rashid', 'Lead Developer', 'Full-stack engineer specializing in Next.js, headless commerce, and performance.', null, 2],
                ['Kamwada Alex', 'Brand Strategist', '15 years in branding for Fortune 500s. Now helping startups punch above their weight.', null, 3],
                ['Namayanja Mackline', 'E-commerce Lead', 'Built 40+ Shopify stores. Expert in checkout optimization and growth funnels.', null, 4]
            ];
            $stmt = $pdo->prepare("INSERT INTO team_members (name, position, bio, image_url, display_order) VALUES (?, ?, ?, ?, ?)");
            foreach ($defaultMembers as $member) {
                $stmt->execute($member);
            }
        }
    } catch (PDOException $e) {
        $error = 'Database setup error: ' . $e->getMessage();
    }
}

// Handle team member actions - BEFORE header to allow redirects
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    try {
        if (isset($_POST['add_member'])) {
            if (empty($_FILES['member_image']['name'])) {
                throw new Exception('Image upload is required');
            }
            $file = $_FILES['member_image'];
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed) || $file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Invalid image file type. Supported: JPG, PNG, GIF, WebP');
            }
            $filename = 'member_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $filepath = __DIR__ . '/../../uploads/' . $filename;
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to upload image');
            }
            $image_url = '/uploads/' . $filename;
            $stmt = $pdo->prepare("INSERT INTO team_members (name, position, bio, image_url, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $_POST['position'],
                $_POST['bio'],
                $image_url,
                (int)$_POST['display_order']
            ]);
            $success = 'Team member added successfully!';
            // Reload page to show new member
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        } elseif (isset($_POST['update_member'])) {
            $updateData = [
                $_POST['name'],
                $_POST['position'],
                $_POST['bio'],
                (int)$_POST['display_order'],
                (int)$_POST['member_id']
            ];
            if (!empty($_FILES['member_image']['name'])) {
                $file = $_FILES['member_image'];
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed) || $file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Invalid image file type. Supported: JPG, PNG, GIF, WebP');
                }
                $filename = 'member_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $filepath = __DIR__ . '/../../uploads/' . $filename;
                if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                    throw new Exception('Failed to upload image');
                }
                $stmt = $pdo->prepare("UPDATE team_members SET name = ?, position = ?, bio = ?, image_url = ?, display_order = ? WHERE id = ?");
                array_splice($updateData, 3, 0, '/uploads/' . $filename);
                $stmt->execute($updateData);
            } else {
                $stmt = $pdo->prepare("UPDATE team_members SET name = ?, position = ?, bio = ?, display_order = ? WHERE id = ?");
                $stmt->execute($updateData);
            }
            $success = 'Team member updated successfully!';
            // Reload page to show updated member
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        } elseif (isset($_POST['delete_member'])) {
            $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
            $stmt->execute([(int)$_POST['member_id']]);
            $success = 'Team member deleted successfully!';
            // Reload page to remove deleted member
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        } elseif (isset($_POST['update_stat'])) {
            $stmt = $pdo->prepare("UPDATE about_stats SET stat_value = ?, stat_label = ? WHERE id = ?");
            $stmt->execute([
                $_POST['stat_value'],
                $_POST['stat_label'],
                (int)$_POST['stat_id']
            ]);
            $success = 'Stat updated successfully!';
        } elseif (isset($_POST['add_image'])) {
            $image_url = $_POST['image_url'];
            if (!empty($_FILES['image_file']['name'])) {
                $file = $_FILES['image_file'];
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed) && $file['error'] === UPLOAD_ERR_OK) {
                    $filename = 'about_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $filepath = __DIR__ . '/../../uploads/' . $filename;
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $image_url = '/uploads/' . $filename;
                    } else {
                        throw new Exception('Failed to upload file');
                    }
                } else {
                    throw new Exception('Invalid file type or upload error');
                }
            }
            $stmt = $pdo->prepare("INSERT INTO about_images (image_url, alt_text, display_order) VALUES (?, ?, ?)");
            $stmt->execute([
                $image_url,
                $_POST['alt_text'],
                (int)$_POST['display_order']
            ]);
            $success = 'Image added successfully!';
        } elseif (isset($_POST['update_image'])) {
            $image_url = $_POST['image_url'];
            if (!empty($_FILES['image_file']['name'])) {
                $file = $_FILES['image_file'];
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed) && $file['error'] === UPLOAD_ERR_OK) {
                    $filename = 'about_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $filepath = __DIR__ . '/../../uploads/' . $filename;
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $image_url = '/uploads/' . $filename;
                    } else {
                        throw new Exception('Failed to upload file');
                    }
                } else {
                    throw new Exception('Invalid file type or upload error');
                }
            }
            $stmt = $pdo->prepare("UPDATE about_images SET image_url = ?, alt_text = ?, display_order = ? WHERE id = ?");
            $stmt->execute([
                $image_url,
                $_POST['alt_text'],
                (int)$_POST['display_order'],
                (int)$_POST['image_id']
            ]);
            $success = 'Image updated successfully!';
        } elseif (isset($_POST['delete_image'])) {
            $stmt = $pdo->prepare("DELETE FROM about_images WHERE id = ?");
            $stmt->execute([(int)$_POST['image_id']]);
            $success = 'Image deleted successfully!';
        } elseif (isset($_POST['add_partner'])) {
            $logo_url = $_POST['logo_url'] !== '' ? $_POST['logo_url'] : null;
            if (!empty($_FILES['logo_file']['name'])) {
                $file = $_FILES['logo_file'];
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed) && $file['error'] === UPLOAD_ERR_OK) {
                    $filename = 'partner_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $filepath = __DIR__ . '/../../uploads/' . $filename;
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $logo_url = '/uploads/' . $filename;
                    } else {
                        throw new Exception('Failed to upload logo');
                    }
                } else {
                    throw new Exception('Invalid logo file type');
                }
            }
            $stmt = $pdo->prepare("INSERT INTO about_partners (name, logo_url, display_order) VALUES (?, ?, ?)");
            $stmt->execute([
                $_POST['name'],
                $logo_url,
                (int)$_POST['display_order']
            ]);
            $success = 'Partner added successfully!';
        } elseif (isset($_POST['update_partner'])) {
            $logo_url = $_POST['logo_url'] !== '' ? $_POST['logo_url'] : null;
            if (!empty($_FILES['logo_file']['name'])) {
                $file = $_FILES['logo_file'];
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed) && $file['error'] === UPLOAD_ERR_OK) {
                    $filename = 'partner_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $filepath = __DIR__ . '/../../uploads/' . $filename;
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $logo_url = '/uploads/' . $filename;
                    } else {
                        throw new Exception('Failed to upload logo');
                    }
                } else {
                    throw new Exception('Invalid logo file type');
                }
            }
            $stmt = $pdo->prepare("UPDATE about_partners SET name = ?, logo_url = ?, display_order = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'],
                $logo_url,
                (int)$_POST['display_order'],
                (int)$_POST['partner_id']
            ]);
            $success = 'Partner updated successfully!';
        } elseif (isset($_POST['delete_partner'])) {
            $stmt = $pdo->prepare("DELETE FROM about_partners WHERE id = ?");
            $stmt->execute([(int)$_POST['partner_id']]);
            $success = 'Partner deleted successfully!';
        }
    } catch (PDOException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

    // Include header AFTER all POST processing and potential redirects
    require_once __DIR__ . '/../includes/header.php';

// Fetch team members, stats, images, partners
$teamMembers = $stats = $aboutImages = $partners = [];
if ($pdo) {
    try {
        $teamMembers = $pdo->query("SELECT * FROM team_members ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
        $stats = $pdo->query("SELECT * FROM about_stats ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
        $aboutImages = $pdo->query("SELECT * FROM about_images ORDER BY display_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $partners = $pdo->query("SELECT * FROM about_partners ORDER BY display_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log what we found
        error_log("Team Members Found: " . count($teamMembers));
        if (count($teamMembers) === 0) {
            error_log("WARNING: No team members in database. Please add team members through the admin panel.");
        }
    } catch (PDOException $e) {
        $error = 'Error fetching data: ' . $e->getMessage();
        error_log("Database Error: " . $e->getMessage());
    }
} else {
    $error = 'Database connection failed';
    error_log("Database connection is null - PDO not initialized");
}
?>

<main class="md:ml-64 pt-24 min-h-screen bg-gray-50">
    <div class="p-6">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">About Us Management</h1>
                    <p class="text-gray-600">Manage team members and statistics</p>
                </div>
                <a href="/" target="_blank" class="px-4 py-2 bg-cyan-ft text-white rounded-lg hover:bg-cyan-600 transition">
                    <i class="fas fa-external-link-alt mr-2"></i>View Public Page
                </a>
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
            <?php if (!$pdo): ?>
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg">
                    <p><strong>⚠️ Database Connection Issue</strong></p>
                    <p style="margin-top: 10px;">The database connection failed. This usually means:</p>
                    <ul style="margin: 10px 0 0 20px;">
                        <li>MySQL server is not running</li>
                        <li>The database 'pulsetech_db' doesn't exist</li>
                        <li>Connection credentials are incorrect</li>
                    </ul>
                    <p style="margin-top: 10px;"><a href="/create-database.php" style="color: #b45309; text-decoration: underline;">→ Click here to setup the database</a></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Team Members Section - FIRST -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8" id="team">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold flex items-center gap-2">
                    <i class="fas fa-users text-cyan-ft"></i>
                    Team Members
                </h2>
                <button 
                    onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-gradient-to-r from-cyan-ft to-cyan-600 text-white rounded-lg hover:shadow-lg transition font-medium"
                >
                    <i class="fas fa-plus mr-2"></i>Add Team Member
                </button>
            </div>

            <!-- Team Members Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Photo</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Name</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Position</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Bio</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Order</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($teamMembers)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-users text-4xl mb-3 opacity-50 block"></i>
                                    <p class="text-lg font-semibold">No team members yet</p>
                                    <p class="text-sm text-gray-400 mt-2">Get started by adding team members below</p>
                                    <button 
                                        onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                                        class="mt-4 px-4 py-2 bg-cyan-ft text-white rounded-lg hover:bg-cyan-600 transition"
                                    >
                                        <i class="fas fa-plus mr-2"></i>Add First Team Member
                                    </button>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($teamMembers as $member): ?>
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <?php if (!empty($member['image_url'])): ?>
                                            <img 
                                                src="<?php echo htmlspecialchars($member['image_url']); ?>" 
                                                alt="<?php echo htmlspecialchars($member['name']); ?>"
                                                class="w-12 h-12 rounded-lg object-cover"
                                                onerror="this.style.display='none'"
                                            >
                                        <?php else: ?>
                                            <div class="w-12 h-12 rounded-lg bg-gray-300 flex items-center justify-center text-gray-500">
                                                <i class="fas fa-image text-sm"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($member['name']); ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-cyan-ft font-medium"><?php echo htmlspecialchars($member['position']); ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600 line-clamp-2"><?php echo htmlspecialchars(substr($member['bio'], 0, 100)) . (strlen($member['bio']) > 100 ? '...' : ''); ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-500"><?php echo (int)$member['display_order']; ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-2">
                                            <button 
                                                onclick="editMember(<?php echo htmlspecialchars(json_encode($member)); ?>)"
                                                class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm"
                                            >
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this team member?')">
                                                <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                <button 
                                                    type="submit" 
                                                    name="delete_member"
                                                    class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm"
                                                >
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                <i class="fas fa-chart-line text-cyan-ft"></i>
                By the Numbers Statistics
            </h2>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($stats as $stat): ?>
                    <form method="POST" class="border border-gray-200 rounded-lg p-4 hover:border-cyan-ft transition">
                        <input type="hidden" name="stat_id" value="<?php echo $stat['id']; ?>">
                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Value</label>
                            <input 
                                type="text" 
                                name="stat_value" 
                                value="<?php echo htmlspecialchars($stat['stat_value']); ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft"
                                required
                            >
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Label</label>
                            <input 
                                type="text" 
                                name="stat_label" 
                                value="<?php echo htmlspecialchars($stat['stat_label']); ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft"
                                required
                            >
                        </div>
                        <button 
                            type="submit" 
                            name="update_stat"
                            class="w-full px-4 py-2 bg-cyan-ft text-white rounded-lg hover:bg-cyan-600 transition text-sm font-medium"
                        >
                            <i class="fas fa-save mr-1"></i>Update
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- About Images Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold flex items-center gap-2">
                    <i class="fas fa-images text-cyan-ft"></i>
                    About Page Images
                </h2>
                <button 
                    onclick="document.getElementById('addImageModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-gradient-to-r from-cyan-ft to-cyan-600 text-white rounded-lg hover:shadow-lg transition font-medium"
                >
                    <i class="fas fa-plus mr-2"></i>Add Image
                </button>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($aboutImages as $img): ?>
                    <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition">
                        <img 
                            src="<?php echo htmlspecialchars($img['image_url']); ?>" 
                            alt="<?php echo htmlspecialchars($img['alt_text'] ?? 'About image'); ?>"
                            class="w-full h-44 object-cover"
                            onerror="this.src='https://via.placeholder.com/800x500?text=No+Image'"
                        >
                        <div class="p-4">
                            <p class="font-semibold text-gray-800 mb-1">Alt: <?php echo htmlspecialchars($img['alt_text'] ?? ''); ?></p>
                            <p class="text-xs text-gray-500 mb-4">Order: <?php echo (int)$img['display_order']; ?></p>
                            <div class="flex gap-2">
                                <button 
                                    onclick="editImage(<?php echo htmlspecialchars(json_encode($img)); ?>)"
                                    class="flex-1 px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm"
                                >
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" class="flex-1" onsubmit="return confirm('Delete this image?')">
                                    <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                                    <button 
                                        type="submit" 
                                        name="delete_image"
                                        class="w-full px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm"
                                    >
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($aboutImages)): ?>
                    <div class="col-span-full text-center py-12 text-gray-500">
                        <i class="fas fa-images text-6xl mb-4"></i>
                        <p>No images yet. Click "Add Image" to get started!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Certifications & Partners Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold flex items-center gap-2">
                    <i class="fas fa-handshake text-cyan-ft"></i>
                    Certifications & Partners
                </h2>
                <button 
                    onclick="document.getElementById('addPartnerModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-gradient-to-r from-cyan-ft to-cyan-600 text-white rounded-lg hover:shadow-lg transition font-medium"
                >
                    <i class="fas fa-plus mr-2"></i>Add Partner
                </button>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($partners as $partner): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition">
                        <div class="flex items-center gap-3 mb-3">
                            <?php if (!empty($partner['logo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($partner['logo_url']); ?>" alt="<?php echo htmlspecialchars($partner['name']); ?>" class="w-10 h-10 object-contain" onerror="this.src='https://via.placeholder.com/80x80?text=Logo'">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                    <i class="fas fa-certificate"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($partner['name']); ?></p>
                                <p class="text-xs text-gray-500">Order: <?php echo (int)$partner['display_order']; ?></p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button 
                                onclick="editPartner(<?php echo htmlspecialchars(json_encode($partner)); ?>)"
                                class="flex-1 px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm"
                            >
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" class="flex-1" onsubmit="return confirm('Delete this partner?')">
                                <input type="hidden" name="partner_id" value="<?php echo $partner['id']; ?>">
                                <button 
                                    type="submit" 
                                    name="delete_partner"
                                    class="w-full px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-sm"
                                >
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($partners)): ?>
                    <div class="col-span-full text-center py-12 text-gray-500">
                        <i class="fas fa-handshake text-6xl mb-4"></i>
                        <p>No partners yet. Click "Add Partner" to get started!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add Member Modal -->
<div id="addMemberModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold">Add Team Member</h3>
                <button onclick="document.getElementById('addMemberModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Position *</label>
                    <input type="text" name="position" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bio *</label>
                    <textarea name="bio" rows="3" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Member Image *</label>
                    <input type="file" name="member_image" accept="image/*" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                    <p class="text-xs text-gray-500 mt-1">Supported: JPG, PNG, GIF, WebP</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                    <input type="number" name="display_order" value="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('addMemberModal').classList.add('hidden')" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" name="add_member" class="flex-1 px-6 py-3 bg-gradient-to-r from-cyan-ft to-cyan-600 text-white rounded-lg hover:shadow-lg transition">
                        <i class="fas fa-plus mr-2"></i>Add Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Image Modal -->
<div id="addImageModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold">Add About Image</h3>
                <button onclick="document.getElementById('addImageModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Image *</label>
                    <input type="file" name="image_file" accept="image/*" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                    <p class="text-xs text-gray-500 mt-1">Supported: JPG, PNG, GIF, WebP (max 5MB)</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Alt Text</label>
                    <input type="text" name="alt_text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft" placeholder="Describe the image">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                    <input type="number" name="display_order" value="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                <input type="hidden" name="image_url" value="">
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('addImageModal').classList.add('hidden')" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" name="add_image" class="flex-1 px-6 py-3 bg-gradient-to-r from-cyan-ft to-cyan-600 text-white rounded-lg hover:shadow-lg transition">
                        <i class="fas fa-plus mr-2"></i>Add Image
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Image Modal -->
<div id="editImageModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold">Edit About Image</h3>
                <button onclick="document.getElementById('editImageModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="image_id" id="edit_image_id">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Upload New Image (optional)</label>
                    <input type="file" name="image_file" accept="image/*" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Alt Text</label>
                    <input type="text" name="alt_text" id="edit_alt_text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                    <input type="number" name="display_order" id="edit_image_order" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                <input type="hidden" name="image_url" id="edit_image_url" value="">
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('editImageModal').classList.add('hidden')" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" name="update_image" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg transition">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Partner Modal -->
<div id="addPartnerModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold">Add Partner</h3>
                <button onclick="document.getElementById('addPartnerModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                    <input type="text" name="name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Logo (optional)</label>
                    <input type="file" name="logo_file" accept="image/*" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                    <p class="text-xs text-gray-500 mt-1">Supported: JPG, PNG, GIF, WebP, SVG</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                    <input type="number" name="display_order" value="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                <input type="hidden" name="logo_url" value="">
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('addPartnerModal').classList.add('hidden')" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" name="add_partner" class="flex-1 px-6 py-3 bg-gradient-to-r from-cyan-ft to-cyan-600 text-white rounded-lg hover:shadow-lg transition">
                        <i class="fas fa-plus mr-2"></i>Add Partner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Partner Modal -->
<div id="editPartnerModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold">Edit Partner</h3>
                <button onclick="document.getElementById('editPartnerModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="partner_id" id="edit_partner_id">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                    <input type="text" name="name" id="edit_partner_name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Upload New Logo (optional)</label>
                    <input type="file" name="logo_file" accept="image/*" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep current logo</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                    <input type="number" name="display_order" id="edit_partner_order" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                <input type="hidden" name="logo_url" id="edit_partner_logo" value="">
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('editPartnerModal').classList.add('hidden')" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" name="update_partner" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg transition">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Member Modal -->
<div id="editMemberModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold">Edit Team Member</h3>
                <button onclick="document.getElementById('editMemberModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="member_id" id="edit_member_id">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Position *</label>
                    <input type="text" name="position" id="edit_position" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bio *</label>
                    <textarea name="bio" id="edit_bio" rows="3" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Upload New Image (optional)</label>
                    <input type="file" name="member_image" accept="image/*" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Display Order</label>
                    <input type="number" name="display_order" id="edit_display_order" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-ft">
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('editMemberModal').classList.add('hidden')" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" name="update_member" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg transition">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editMember(member) {
    document.getElementById('edit_member_id').value = member.id;
    document.getElementById('edit_name').value = member.name;
    document.getElementById('edit_position').value = member.position;
    document.getElementById('edit_bio').value = member.bio;
    document.getElementById('edit_image_url').value = member.image_url;
    document.getElementById('edit_display_order').value = member.display_order;
    document.getElementById('editMemberModal').classList.remove('hidden');
}

function editImage(image) {
    document.getElementById('edit_image_id').value = image.id;
    document.getElementById('edit_image_url').value = image.image_url;
    document.getElementById('edit_alt_text').value = image.alt_text || '';
    document.getElementById('edit_image_order').value = image.display_order;
    document.getElementById('editImageModal').classList.remove('hidden');
}

function editPartner(partner) {
    document.getElementById('edit_partner_id').value = partner.id;
    document.getElementById('edit_partner_name').value = partner.name;
    document.getElementById('edit_partner_logo').value = partner.logo_url || '';
    document.getElementById('edit_partner_order').value = partner.display_order;
    document.getElementById('editPartnerModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
