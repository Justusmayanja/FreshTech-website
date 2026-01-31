<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$products = [];
$q = trim($_GET['q'] ?? '');
$db_available = isset($pdo) && $pdo instanceof PDO;

// Handle delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    if ($product_id > 0 && $db_available) {
        try {
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product && !empty($product['image'])) {
                $imagePath = __DIR__ . '/../uploads/' . $product['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $del = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $del->execute([$product_id]);
            header('Location: /admin/products/');
            exit;
        } catch (Exception $e) {
            // Error handled silently
        }
    }
}

// Handle form submission (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && $db_available) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $product_id = (int)($_POST['product_id'] ?? 0);
    
    if (!empty($name) && $price > 0) {
        try {
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
            
            if ($product_id > 0) {
                if (!empty($image)) {
                    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price_ugx = ?, category = ?, image = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $category, $image, $product_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price_ugx = ?, category = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $category, $product_id]);
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price_ugx, category, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $description, $price, $category, $image]);
            }
            
            header('Location: /admin/products/');
            exit;
        } catch (Exception $e) {
            // Error handled silently
        }
    }
}

// Try to fetch from database
if ($db_available) {
    try {
        $sql = 'SELECT * FROM products';
        if ($q !== '') {
            $sql .= ' WHERE name LIKE :q OR description LIKE :q';
            $stmt = $pdo->prepare($sql . ' ORDER BY id DESC');
            $stmt->execute(['q' => "%$q%"]);
        } else {
            $stmt = $pdo->query($sql . ' ORDER BY id DESC');
        }
        $products = $stmt->fetchAll();
    } catch (Exception $e) {
        $products = [];
    }
} else {
    // Use fallback sample data
    $all_products = [
        ['id' => 1, 'name' => 'Web Development Package', 'price_ugx' => 2500000, 'rating' => 4.8, 'description' => 'Complete web development service', 'category' => 'Web', 'image' => '', 'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))],
        ['id' => 2, 'name' => 'UI/UX Design Service', 'price_ugx' => 1800000, 'rating' => 4.9, 'description' => 'Professional UI/UX design', 'category' => 'Design', 'image' => '', 'created_at' => date('Y-m-d H:i:s', strtotime('-20 days'))],
        ['id' => 3, 'name' => 'Logo Design', 'price_ugx' => 800000, 'rating' => 4.7, 'description' => 'Custom logo design', 'category' => 'Branding', 'image' => '', 'created_at' => date('Y-m-d H:i:s', strtotime('-15 days'))],
        ['id' => 4, 'name' => 'Mobile App Development', 'price_ugx' => 5000000, 'rating' => 4.6, 'description' => 'Native and cross-platform apps', 'category' => 'Mobile', 'image' => '', 'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))],
        ['id' => 5, 'name' => 'SEO Optimization', 'price_ugx' => 1200000, 'rating' => 4.5, 'description' => 'Search engine optimization', 'category' => 'SEO', 'image' => '', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))],
    ];
    
    // Filter by search
    if ($q !== '') {
        $products = array_filter($all_products, function($p) use ($q) {
            return stripos($p['name'], $q) !== false || stripos($p['description'], $q) !== false;
        });
    } else {
        $products = $all_products;
    }
}

$pageTitle = "Products";
include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-4xl font-bold text-navy-ft">Products</h1>
        <p class="text-gray-600 mt-1">Manage your product catalog</p>
    </div>
    <button onclick="openAddModal()" class="px-6 py-3 bg-cyan-ft text-navy-ft rounded-lg font-bold hover:bg-opacity-90 transition">
        <i class="fas fa-plus mr-2"></i>Add Product
    </button>
</div>

<!-- Search Bar -->
<div class="mb-6 flex gap-3">
    <form method="get" class="flex-1 flex gap-2">
        <input type="text" name="q" placeholder="Search products by name or description..." value="<?php echo htmlspecialchars($q); ?>" class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft">
        <button type="submit" class="px-6 py-2 bg-cyan-ft text-navy-ft rounded-lg font-semibold hover:bg-opacity-90 transition">
            <i class="fas fa-search mr-2"></i>Search
        </button>
    </form>
    <?php if ($q !== ''): ?>
        <a href="/admin/products/" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
            <i class="fas fa-times mr-2"></i>Clear
        </a>
    <?php endif; ?>
</div>

<!-- Products Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
    <?php if (empty($products)): ?>
        <div class="col-span-full py-12 text-center bg-white rounded-xl border">
            <i class="fas fa-inbox text-5xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 text-lg">No products found. Click "Add Product" to get started.</p>
        </div>
    <?php else: ?>
        <?php foreach ($products as $prod): ?>
            <div class="bg-white rounded-xl shadow-md border overflow-hidden hover:shadow-lg transition">
                <!-- Product Image -->
                <?php if (!empty($prod['image'])): ?>
                    <div class="w-full h-48 bg-gray-100 overflow-hidden flex items-center justify-center">
                        <img src="/admin/uploads/<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="w-full h-full object-cover">
                    </div>
                <?php else: ?>
                    <div class="w-full h-48 bg-gradient-to-br from-cyan-ft to-blue-500 flex items-center justify-center">
                        <i class="fas fa-cube text-white text-4xl"></i>
                    </div>
                <?php endif; ?>
                
                <!-- Product Info -->
                <div class="p-4">
                    <h3 class="font-bold text-navy-ft text-lg"><?php echo htmlspecialchars($prod['name']); ?></h3>
                    <p class="text-sm text-gray-600 mt-1">
                        <?php if (!empty($prod['category'])): ?>
                            <span class="inline-block px-2 py-1 bg-gray-100 rounded text-xs font-medium"><?php echo htmlspecialchars($prod['category']); ?></span>
                        <?php endif; ?>
                    </p>
                    <p class="text-2xl font-bold text-cyan-ft mt-3">UGX <?php echo number_format((int)($prod['price_ugx'] ?? 0)); ?></p>
                    
                    <?php if (!empty($prod['rating'])): ?>
                        <div class="mt-2">
                            <span class="text-sm text-yellow-500">
                                <i class="fas fa-star"></i> <?php echo $prod['rating']; ?>/5.0
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <p class="text-xs text-gray-500 mt-2">Added: <?php echo formatDate($prod['created_at']); ?></p>
                    
                    <!-- Actions -->
                    <div class="flex gap-2 mt-4">
                        <button onclick="editProduct(<?php echo (int)$prod['id']; ?>)" class="flex-1 px-3 py-2 bg-cyan-ft text-navy-ft rounded-lg font-semibold hover:bg-opacity-90 transition text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        <form method="POST" style="flex: 1; display: flex;" onsubmit="return confirm('Delete this product?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="product_id" value="<?php echo (int)$prod['id']; ?>">
                            <button type="submit" class="flex-1 px-3 py-2 bg-red-100 text-red-700 rounded-lg font-semibold hover:bg-red-200 transition text-sm">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="productModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white">
            <h2 id="modalTitle" class="text-2xl font-bold text-navy-ft">Add Product</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-navy-ft transition">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <form id="productForm" method="POST" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="product_id" id="product_id" value="">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Product Name *</label>
                    <input type="text" name="name" id="name" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Price (UGX) *</label>
                        <input type="number" name="price" id="price" required step="1" min="0" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                        <input type="text" name="category" id="category" placeholder="e.g., Web, Design, Mobile" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Product Image</label>
                    <input type="file" name="image" id="image" accept="image/*" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft">
                    <p class="text-xs text-gray-500 mt-1">Max 5MB. Formats: JPG, PNG, GIF, WebP</p>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 px-4 py-3 bg-cyan-ft text-navy-ft rounded-lg font-bold hover:bg-opacity-90 transition">
                    <i class="fas fa-save mr-2"></i>Save Product
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
    document.getElementById('modalTitle').textContent = 'Add Product';
    document.getElementById('productForm').reset();
    document.getElementById('product_id').value = '';
    document.getElementById('productModal').classList.remove('hidden');
}

function editProduct(id) {
    document.getElementById('modalTitle').textContent = 'Edit Product #' + id;
    document.getElementById('product_id').value = id;
    document.getElementById('productModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('productModal').classList.add('hidden');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
