<?php
session_start();
require_once __DIR__ . '/../../db.php';

// Initialize products table if it doesn't exist
if ($pdo) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255),
            description TEXT,
            price_ugx DECIMAL(10, 2),
            old_price_ugx DECIMAL(10, 2),
            image_url VARCHAR(500),
            category VARCHAR(100),
            stock_quantity INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
    } catch (Exception $e) {
        // Table might already exist
    }
}

// Product descriptions mapping by category
$product_descriptions = [
    'headphones' => 'High-quality wireless headphones with excellent sound and comfort',
    'earbuds' => 'Premium wireless earbuds with noise cancellation and long battery life',
    'watches' => 'Feature-rich smart watch with fitness tracking and notifications',
    'holders' => 'Durable phone/device holder for convenient viewing and protection',
    'power bank' => 'Portable high-capacity power bank for fast charging on the go',
    'chargers' => 'Fast charger compatible with all devices - efficient and reliable'
];

// Get product categories from filename
function categorize_product($filename) {
    $lower = strtolower($filename);
    
    if (strpos($lower, 'charger') !== false) return 'chargers';
    if (strpos($lower, 'head') !== false) return 'headphones';
    if (strpos($lower, 'pod') !== false) return 'earbuds';
    if (strpos($lower, 'watch') !== false) return 'watches';
    if (strpos($lower, 'holder') !== false) return 'holders';
    if (strpos($lower, 'power bank') !== false || strpos($lower, 'power') !== false) return 'power bank';
    
    return 'accessories';
}

// Get description based on category
function get_description($category) {
    global $product_descriptions;
    return $product_descriptions[$category] ?? 'Quality tech accessory for your devices';
}

$products = [];
$q = trim($_GET['q'] ?? '');
$category_filter = $_GET['category'] ?? '';
$db_available = isset($pdo) && $pdo instanceof PDO;

// Handle delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    if ($product_id > 0 && $db_available) {
        try {
            $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product && !empty($product['image_url'])) {
                $imagePath = __DIR__ . '/../../uploads/' . basename($product['image_url']);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $del = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $del->execute([$product_id]);
            header('Location: /admin/shop/');
            exit;
        } catch (Exception $e) {
            // Error handled silently
        }
    }
}

// Auto-populate products from shopping folder images (first visit)
if ($db_available) {
    try {
        $count = $pdo->query("SELECT COUNT(*) as cnt FROM products")->fetch();
        if ($count['cnt'] == 0) {
            // Get list of image files from uploads folder
            $uploads_dir = __DIR__ . '/../../uploads';
            if (is_dir($uploads_dir)) {
                $files = scandir($uploads_dir);
                $product_files = ['head1.jpg', 'head2.jpg', 'head3.jpg', 'holder3.jpg', 'iphone holder.jpg', 
                                 'pod1.jpg', 'pod2.jpg', 'power bank.jpg', 'watch1.jpg', 'watch2.jpg', 'watch3.jpg'];
                
                foreach ($product_files as $file) {
                    if (in_array($file, $files)) {
                        $category = categorize_product($file);
                        $name = ucfirst(str_replace(['.jpg', '.jpeg'], '', str_replace('_', ' ', $file)));
                        $description = get_description($category);
                        
                        // Set prices based on category
                        $prices = [
                            'headphones' => 180000,
                            'earbuds' => 150000,
                            'watches' => 350000,
                            'holders' => 45000,
                            'power bank' => 120000,
                            'chargers' => 95000,
                            'accessories' => 50000
                        ];
                        
                        $price = $prices[$category] ?? 50000;
                        $old_price = $price * 1.4; // 40% discount
                        $slug = strtolower(str_replace(' ', '-', $name));
                        
                        try {
                            $stmt = $pdo->prepare("INSERT INTO products (name, slug, image_url, category, price_ugx, old_price_ugx, stock_quantity, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$name, $slug, $file, $category, $price, $old_price, 20, $description]);
                        } catch (Exception $e) {
                            // Product might already exist
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Error checking products
    }
}


// Handle form submission (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && $db_available) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $old_price = (int)($_POST['old_price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $product_id = (int)($_POST['product_id'] ?? 0);
    
    if (!empty($name) && $price > 0) {
        try {
            $image_url = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $file = $_FILES['image'];
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed) && $file['size'] <= 5000000) {
                    $image = time() . '_' . uniqid() . '.' . $ext;
                    $uploadPath = __DIR__ . '/../../uploads/' . $image;
                    
                    if (!is_dir(__DIR__ . '/../../uploads/')) {
                        mkdir(__DIR__ . '/../../uploads/', 0755, true);
                    }
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        $image_url = $image;
                    }
                }
            }
            
            if ($product_id > 0) {
                // Update
                if (!empty($image_url)) {
                    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price_ugx = ?, old_price_ugx = ?, category = ?, stock_quantity = ?, image_url = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $old_price, $category, $stock, $image_url, $product_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price_ugx = ?, old_price_ugx = ?, category = ?, stock_quantity = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $old_price, $category, $stock, $product_id]);
                }
            } else {
                // Create
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price_ugx, old_price_ugx, category, stock_quantity, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $description, $price, $old_price, $category, $stock, $image_url]);
            }
            
            header('Location: /admin/shop/');
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
        $params = [];
        $conditions = [];
        
        if ($q !== '') {
            $conditions[] = '(name LIKE :q OR description LIKE :q)';
            $params['q'] = "%$q%";
        }
        
        if ($category_filter !== '') {
            $conditions[] = 'category = :cat';
            $params['cat'] = $category_filter;
        }
        
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        
        $sql .= ' ORDER BY id DESC';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
    } catch (Exception $e) {
        $products = [];
    }
}

// Get unique categories
$categories = [];
if ($db_available) {
    try {
        $stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $categories = [];
    }
}

// Set page title for header
$pageTitle = 'Shop Products';
$breadcrumb = ['' => 'Shop'];

// Include the header which has the complete HTML structure
include __DIR__ . '/../includes/header.php';
?>
            
            <div class="admin-content" style="padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <h1 style="margin: 0; font-size: 32px;">Shop Products</h1>
                    <button class="btn" onclick="openAddModal()" style="background: #3498db; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: bold;">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
                
                <!-- Products Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
                    <?php foreach ($products as $prod): ?>
                        <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s;">
                            <!-- Product Image -->
                            <div style="width: 100%; height: 220px; background: #f0f0f0; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                <?php if (!empty($prod['image_url'])): ?>
                                    <img src="/uploads/<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-shopping-bag" style="font-size: 60px; color: #ddd;"></i>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Product Info -->
                            <div style="padding: 20px;">
                                <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: bold; color: #333;"><?php echo htmlspecialchars($prod['name']); ?></h3>
                                
                                <div style="margin-bottom: 10px;">
                                    <span style="display: inline-block; background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <?php echo htmlspecialchars($prod['category'] ?? 'Accessories'); ?>
                                    </span>
                                </div>
                                
                                <p style="margin: 10px 0; color: #666; font-size: 13px;"><?php echo htmlspecialchars(substr($prod['description'] ?? '', 0, 50)); ?>...</p>
                                
                                <div style="margin: 15px 0; border-top: 1px solid #eee; padding-top: 10px;">
                                    <div style="font-size: 18px; font-weight: bold; color: #27ae60;">
                                        UGX <?php echo number_format((int)($prod['price_ugx'] ?? 0)); ?>
                                    </div>
                                    <?php if (!empty($prod['old_price_ugx']) && $prod['old_price_ugx'] > 0): ?>
                                        <div style="font-size: 12px; color: #999; text-decoration: line-through;">
                                            UGX <?php echo number_format((int)$prod['old_price_ugx']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="margin: 10px 0; font-size: 12px; color: #666;">
                                    <?php if ($prod['stock_quantity'] > 0): ?>
                                        <span style="color: #27ae60; font-weight: bold;">✓ In Stock (<?php echo $prod['stock_quantity']; ?>)</span>
                                    <?php else: ?>
                                        <span style="color: #e74c3c; font-weight: bold;">✗ Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Actions -->
                                <div style="display: flex; gap: 8px; margin-top: 15px;">
                                    <button class="btn" onclick="editProduct(<?php echo htmlspecialchars(json_encode($prod), ENT_QUOTES); ?>)" style="flex: 1; background: #3498db; color: white; padding: 8px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="flex: 1; display: flex;" onsubmit="return confirm('Delete this product?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$prod['id']; ?>">
                                        <button type="submit" style="flex: 1; background: #e74c3c; color: white; padding: 8px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($products)): ?>
                    <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 8px;">
                        <i class="fas fa-shopping-bag" style="font-size: 60px; color: #ddd; margin-bottom: 20px; display: block;"></i>
                        <p style="color: #999; font-size: 16px;">No products found. Click "Add Product" to get started.</p>
                    </div>
                <?php endif; ?>
            </div>
    
    <!-- Add/Edit Modal -->
    <div id="productModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); overflow-y: auto;">
        <div style="background: white; margin: 50px auto; padding: 30px; border-radius: 8px; width: 90%; max-width: 600px; box-shadow: 0 5px 20px rgba(0,0,0,0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <h2 id="modalTitle" style="margin: 0; font-size: 24px;">Add Product</h2>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">×</button>
            </div>
            
            <form id="productForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="product_id" value="">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Product Name *</label>
                    <input type="text" name="name" id="name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Description</label>
                    <textarea name="description" id="description" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Price (UGX) *</label>
                        <input type="number" name="price" id="price" required step="1000" min="0" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Old Price (UGX)</label>
                        <input type="number" name="old_price" id="old_price" step="1000" min="0" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Category</label>
                        <input type="text" name="category" id="category" placeholder="e.g., Headphones" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Stock Quantity *</label>
                        <input type="number" name="stock" id="stock" required step="1" min="0" value="20" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                </div>
                
                <div style="margin-top: 15px; margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Product Image</label>
                    <input type="file" name="image" id="image" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <small style="color: #999;">Max 5MB. Formats: JPG, PNG, GIF, WebP</small>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" style="flex: 1; background: #27ae60; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px;">
                        Save Product
                    </button>
                    <button type="button" onclick="closeModal()" style="flex: 1; background: #95a5a6; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('productForm').reset();
            document.getElementById('product_id').value = '';
            document.getElementById('productModal').style.display = 'block';
        }
        
        function editProduct(data) {
            document.getElementById('modalTitle').textContent = 'Edit Product - ' + data.name;
            document.getElementById('product_id').value = data.id;
            document.getElementById('name').value = data.name || '';
            document.getElementById('description').value = data.description || '';
            document.getElementById('price').value = data.price_ugx || '';
            document.getElementById('old_price').value = data.old_price_ugx || '';
            document.getElementById('category').value = data.category || '';
            document.getElementById('stock').value = data.stock_quantity || '';
            document.getElementById('productModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>