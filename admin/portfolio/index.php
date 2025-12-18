<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$portfolio_items = [];
$action = $_GET['action'] ?? '';
$item_id = $_GET['id'] ?? '';

// Handle delete
if ($action == 'delete' && !empty($item_id)) {
    try {
        // Get image to delete
        $stmt = $db->prepare("SELECT image FROM portfolio WHERE id = ?");
        $stmt->execute([(int)$item_id]);
        $item = $stmt->fetch();
        
        if ($item && !empty($item['image'])) {
            $imagePath = __DIR__ . '/../uploads/' . $item['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $del = $db->prepare("DELETE FROM portfolio WHERE id = ?");
        $del->execute([(int)$item_id]);
        redirect('portfolio/');
    } catch (Exception $e) {
        // Handle error
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $item_id = (int)($_POST['item_id'] ?? 0);
    
    if (!empty($title)) {
        try {
            // Handle image upload
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $file = $_FILES['image'];
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed) && $file['size'] <= 5000000) {
                    $image = time() . '_' . uniqid() . '.' . $ext;
                    $uploadPath = __DIR__ . '/../uploads/' . $image;
                    
                    if (!is_dir(__DIR__ . '/../uploads/')) {
                        mkdir(__DIR__ . '/../uploads/', 0755, true);
                    }
                    
                    move_uploaded_file($file['tmp_name'], $uploadPath);
                }
            }
            
            if ($item_id > 0) {
                // Update
                if (!empty($image)) {
                    $stmt = $db->prepare("UPDATE portfolio SET title = ?, description = ?, url = ?, image = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $url, $image, $item_id]);
                } else {
                    $stmt = $db->prepare("UPDATE portfolio SET title = ?, description = ?, url = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $url, $item_id]);
                }
            } else {
                // Create
                $stmt = $db->prepare("INSERT INTO portfolio (title, description, url, image, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $description, $url, $image]);
            }
            
            redirect('portfolio/');
        } catch (Exception $e) {
            // Handle error
        }
    }
}

try {
    $stmt = $db->query("SELECT id, title, description, url, image, created_at FROM portfolio ORDER BY created_at DESC");
    $portfolio_items = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error
}

$pageTitle = "Portfolio";
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-4xl font-bold text-navy-ft">Portfolio</h1>
        <p class="text-gray-600 mt-1">Showcase your work and projects</p>
    </div>
    <button onclick="openAddModal()" class="px-6 py-3 bg-cyan-ft text-navy-ft rounded-lg font-bold hover:bg-opacity-90 transition">
        <i class="fas fa-plus mr-2"></i>Add Portfolio Item
    </button>
</div>

<!-- Portfolio Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($portfolio_items)): ?>
        <div class="col-span-full py-12 text-center bg-white rounded-xl border">
            <i class="fas fa-image text-5xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 text-lg">No portfolio items yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($portfolio_items as $item): ?>
            <div class="bg-white rounded-xl shadow-md border overflow-hidden hover:shadow-lg transition group">
                <!-- Portfolio Image -->
                <?php if (!empty($item['image'])): ?>
                    <div class="w-full h-48 bg-gray-100 overflow-hidden flex items-center justify-center group-hover:opacity-75 transition">
                        <img src="/admin/uploads/<?php echo sanitize($item['image']); ?>" alt="<?php echo sanitize($item['title']); ?>" class="w-full h-full object-cover">
                    </div>
                <?php else: ?>
                    <div class="w-full h-48 bg-gray-100 flex items-center justify-center group-hover:opacity-75 transition">
                        <i class="fas fa-image text-gray-400 text-4xl"></i>
                    </div>
                <?php endif; ?>
                
                <!-- Item Info -->
                <div class="p-4">
                    <h3 class="font-bold text-navy-ft text-lg"><?php echo sanitize($item['title']); ?></h3>
                    <p class="text-sm text-gray-600 mt-2"><?php echo sanitize(substr($item['description'], 0, 100)); ?></p>
                    
                    <?php if (!empty($item['url'])): ?>
                        <a href="<?php echo sanitize($item['url']); ?>" target="_blank" class="text-cyan-ft font-semibold text-sm mt-2 inline-block hover:underline">
                            <i class="fas fa-external-link-alt mr-1"></i>View Project
                        </a>
                    <?php endif; ?>
                    
                    <p class="text-xs text-gray-500 mt-3">Added: <?php echo formatDate($item['created_at']); ?></p>
                    
                    <!-- Actions -->
                    <div class="flex gap-2 mt-4">
                        <button onclick="editItem(<?php echo (int)$item['id']; ?>)" class="flex-1 px-3 py-2 bg-cyan-ft text-navy-ft rounded-lg font-semibold hover:bg-opacity-90 transition text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        <a href="?action=delete&id=<?php echo (int)$item['id']; ?>" onclick="return confirm('Delete this item?')" class="flex-1 px-3 py-2 bg-red-100 text-red-700 rounded-lg font-semibold hover:bg-red-200 transition text-sm text-center">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="portfolioModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white">
            <h2 id="modalTitle" class="text-2xl font-bold">Add Portfolio Item</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-navy-ft">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <form id="portfolioForm" method="POST" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="item_id" id="item_id" value="">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Project Title *</label>
                    <input type="text" name="title" id="title" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Project URL</label>
                    <input type="url" name="url" id="url" placeholder="https://example.com" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Project Image</label>
                    <input type="file" name="image" id="image" accept="image/*" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft">
                    <p class="text-xs text-gray-500 mt-1">Max 5MB. Formats: JPG, PNG, GIF, WebP</p>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 px-4 py-3 bg-cyan-ft text-navy-ft rounded-lg font-bold hover:bg-opacity-90 transition">
                    <i class="fas fa-save mr-2"></i>Save Item
                </button>
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Portfolio Item';
    document.getElementById('portfolioForm').reset();
    document.getElementById('item_id').value = '';
    document.getElementById('portfolioModal').classList.remove('hidden');
}

function editItem(id) {
    document.getElementById('modalTitle').textContent = 'Edit Portfolio Item';
    document.getElementById('item_id').value = id;
    document.getElementById('portfolioModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('portfolioModal').classList.add('hidden');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
