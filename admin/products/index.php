<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$products = [];
$action = $_GET['action'] ?? '';
$product_id = $_GET['id'] ?? '';

// Handle delete
if ($action == 'delete' && !empty($product_id)) {
    try {
        // Get product to delete image
        $stmt = $db->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([(int)$product_id]);
        $product = $stmt->fetch();
        
        if ($product && !empty($product['image'])) {
            $imagePath = __DIR__ . '/../uploads/' . $product['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $del = $db->prepare("DELETE FROM products WHERE id = ?");
        $del->execute([(int)$product_id]);
        redirect('products/');
    } catch (Exception $e) {
        // Handle error
    }
}

// Handle form submission (add/edit)
$editingProduct = null;
if ($action == 'edit' && !empty($product_id)) {
    try {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([(int)$product_id]);
        $editingProduct = $stmt->fetch();
    } catch (Exception $e) {
        // Handle error
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $product_id = (int)($_POST['product_id'] ?? 0);
    
    if (!empty($name) && $price > 0) {
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
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        // Success
                    } else {
                        $image = '';
                    }
                }
            }
            
            if ($product_id > 0) {
                // Update
                if (!empty($image)) {
                    $stmt = $db->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, image = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $category, $image, $product_id]);
                } else {
                    $stmt = $db->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $category, $product_id]);
                }
            } else {
                // Create
                $stmt = $db->prepare("INSERT INTO products (name, description, price, category, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $description, $price, $category, $image]);
            }
            
            redirect('products/');
        } catch (Exception $e) {
            // Handle error
        }
    }
}

try {
    $stmt = $db->query("SELECT id, name, price, category, image, created_at FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error
}

$pageTitle = "Products";
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

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

<!-- Products Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($products)): ?>
        <div class="col-span-full py-12 text-center bg-white rounded-xl border">
            <i class="fas fa-inbox text-5xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 text-lg">No products yet. Click "Add Product" to get started.</p>
        </div>
    <?php else: ?>
        <?php foreach ($products as $prod): ?>
            <div class="bg-white rounded-xl shadow-md border overflow-hidden hover:shadow-lg transition">
                <!-- Product Image -->
                <?php if (!empty($prod['image'])): ?>
                    <div class="w-full h-48 bg-gray-100 overflow-hidden flex items-center justify-center">
                        <img src="/admin/uploads/<?php echo sanitize($prod['image']); ?>" alt="<?php echo sanitize($prod['name']); ?>" class="w-full h-full object-cover">
                    </div>
                <?php else: ?>
                    <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-image text-gray-400 text-4xl"></i>
                    </div>
                <?php endif; ?>
                
                <!-- Product Info -->
                <div class="p-4">
                    <h3 class="font-bold text-navy-ft text-lg"><?php echo sanitize($prod['name']); ?></h3>
                    <p class="text-sm text-gray-600 mt-1">
                        <span class="inline-block px-2 py-1 bg-gray-100 rounded text-xs"><?php echo sanitize($prod['category']); ?></span>
                    </p>
                    <p class="text-2xl font-bold text-cyan-ft mt-3">$<?php echo number_format((float)$prod['price'], 2); ?></p>
                    <p class="text-xs text-gray-500 mt-2">Added: <?php echo formatDate($prod['created_at']); ?></p>
                    
                    <!-- Actions -->
                    <div class="flex gap-2 mt-4">
                        <button onclick="editProduct(<?php echo (int)$prod['id']; ?>)" class="flex-1 px-3 py-2 bg-cyan-ft text-navy-ft rounded-lg font-semibold hover:bg-opacity-90 transition text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        <a href="?action=delete&id=<?php echo (int)$prod['id']; ?>" onclick="return confirm('Delete this product?')" class="flex-1 px-3 py-2 bg-red-100 text-red-700 rounded-lg font-semibold hover:bg-red-200 transition text-sm text-center">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="productModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white">
            <h2 id="modalTitle" class="text-2xl font-bold">Add Product</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-navy-ft">
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
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Price *</label>
                        <input type="number" name="price" id="price" required step="0.01" min="0" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                        <input type="text" name="category" id="category" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft">
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
    // In a real app, you'd fetch product data via AJAX
    document.getElementById('modalTitle').textContent = 'Edit Product';
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
