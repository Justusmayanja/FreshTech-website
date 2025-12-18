<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$posts = [];
$action = $_GET['action'] ?? '';
$post_id = $_GET['id'] ?? '';

// Handle delete
if ($action == 'delete' && !empty($post_id)) {
    try {
        // Get image to delete
        $stmt = $db->prepare("SELECT image FROM blog_posts WHERE id = ?");
        $stmt->execute([(int)$post_id]);
        $post = $stmt->fetch();
        
        if ($post && !empty($post['image'])) {
            $imagePath = __DIR__ . '/../uploads/' . $post['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $del = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
        $del->execute([(int)$post_id]);
        redirect('blog/');
    } catch (Exception $e) {
        // Handle error
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $post_id = (int)($_POST['post_id'] ?? 0);
    
    if (!empty($title) && !empty($content)) {
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
            
            if ($post_id > 0) {
                // Update
                if (!empty($image)) {
                    $stmt = $db->prepare("UPDATE blog_posts SET title = ?, excerpt = ?, content = ?, image = ?, status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$title, $excerpt, $content, $image, $status, $post_id]);
                } else {
                    $stmt = $db->prepare("UPDATE blog_posts SET title = ?, excerpt = ?, content = ?, status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$title, $excerpt, $content, $status, $post_id]);
                }
            } else {
                // Create
                $stmt = $db->prepare("INSERT INTO blog_posts (title, excerpt, content, image, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->execute([$title, $excerpt, $content, $image, $status]);
            }
            
            redirect('blog/');
        } catch (Exception $e) {
            // Handle error
        }
    }
}

try {
    $stmt = $db->query("SELECT id, title, excerpt, status, image, created_at FROM blog_posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error
}

$pageTitle = "Blog Posts";
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-4xl font-bold text-navy-ft">Blog Posts</h1>
        <p class="text-gray-600 mt-1">Write and manage blog articles</p>
    </div>
    <button onclick="openAddModal()" class="px-6 py-3 bg-cyan-ft text-navy-ft rounded-lg font-bold hover:bg-opacity-90 transition">
        <i class="fas fa-plus mr-2"></i>Write Post
    </button>
</div>

<!-- Blog Posts Table -->
<div class="bg-white rounded-xl shadow-md border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Title</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Author</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-pen-fancy text-4xl mb-3 opacity-50 block"></i>
                            <p class="text-lg">No blog posts yet. Start writing!</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($post['image'])): ?>
                                        <img src="/admin/uploads/<?php echo sanitize($post['image']); ?>" alt="<?php echo sanitize($post['title']); ?>" class="w-12 h-12 rounded object-cover">
                                    <?php else: ?>
                                        <div class="w-12 h-12 rounded bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-semibold text-navy-ft"><?php echo sanitize(substr($post['title'], 0, 40)); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo sanitize($post['excerpt']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $post['status'] == 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo ucfirst($post['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo sanitize($_SESSION['admin_name'] ?? 'Admin'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo formatDate($post['created_at']); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <button onclick="editPost(<?php echo (int)$post['id']; ?>)" class="text-cyan-ft hover:text-navy-ft font-semibold transition mr-3">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <a href="?action=delete&id=<?php echo (int)$post['id']; ?>" onclick="return confirm('Delete this post?')" class="text-red-600 hover:text-red-800 font-semibold transition">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="postModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white">
            <h2 id="modalTitle" class="text-2xl font-bold">Write New Post</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-navy-ft">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <form id="postForm" method="POST" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="post_id" id="post_id" value="">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Post Title *</label>
                    <input type="text" name="title" id="title" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft" placeholder="Enter post title">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Featured Image</label>
                    <input type="file" name="image" id="image" accept="image/*" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft">
                    <p class="text-xs text-gray-500 mt-1">Max 5MB</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Excerpt</label>
                    <textarea name="excerpt" id="excerpt" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft" placeholder="Short summary (shown in lists)"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Content *</label>
                    <textarea name="content" id="content" rows="8" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft font-mono text-sm" placeholder="Write your blog post content..."></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 px-4 py-3 bg-cyan-ft text-navy-ft rounded-lg font-bold hover:bg-opacity-90 transition">
                    <i class="fas fa-save mr-2"></i>Save Post
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
    document.getElementById('modalTitle').textContent = 'Write New Post';
    document.getElementById('postForm').reset();
    document.getElementById('post_id').value = '';
    document.getElementById('status').value = 'draft';
    document.getElementById('postModal').classList.remove('hidden');
}

function editPost(id) {
    document.getElementById('modalTitle').textContent = 'Edit Post';
    document.getElementById('post_id').value = id;
    document.getElementById('postModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('postModal').classList.add('hidden');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
