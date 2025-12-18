<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$services = [];
$action = $_GET['action'] ?? '';
$service_id = $_GET['id'] ?? '';

// Handle delete
if ($action == 'delete' && !empty($service_id)) {
    try {
        $del = $db->prepare("DELETE FROM services WHERE id = ?");
        $del->execute([(int)$service_id]);
        redirect('services/');
    } catch (Exception $e) {
        // Handle error
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fa-star');
    $service_id = (int)($_POST['service_id'] ?? 0);
    
    if (!empty($name)) {
        try {
            if ($service_id > 0) {
                // Update
                $stmt = $db->prepare("UPDATE services SET name = ?, description = ?, icon = ? WHERE id = ?");
                $stmt->execute([$name, $description, $icon, $service_id]);
            } else {
                // Create
                $stmt = $db->prepare("INSERT INTO services (name, description, icon, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$name, $description, $icon]);
            }
            
            redirect('services/');
        } catch (Exception $e) {
            // Handle error
        }
    }
}

try {
    $stmt = $db->query("SELECT id, name, description, icon, created_at FROM services ORDER BY created_at DESC");
    $services = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error
}

$pageTitle = "Services";
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-4xl font-bold text-navy-ft">Services</h1>
        <p class="text-gray-600 mt-1">Manage your service offerings</p>
    </div>
    <button onclick="openAddModal()" class="px-6 py-3 bg-cyan-ft text-navy-ft rounded-lg font-bold hover:bg-opacity-90 transition">
        <i class="fas fa-plus mr-2"></i>Add Service
    </button>
</div>

<!-- Services Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($services)): ?>
        <div class="col-span-full py-12 text-center bg-white rounded-xl border">
            <i class="fas fa-cog text-5xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 text-lg">No services yet. Click "Add Service" to get started.</p>
        </div>
    <?php else: ?>
        <?php foreach ($services as $service): ?>
            <div class="bg-white rounded-xl shadow-md border overflow-hidden hover:shadow-lg transition">
                <!-- Service Icon -->
                <div class="p-6 bg-gradient-to-br from-cyan-ft to-orange-ft flex items-center justify-center h-32">
                    <i class="fas <?php echo sanitize($service['icon']); ?> text-white text-5xl"></i>
                </div>
                
                <!-- Service Info -->
                <div class="p-4">
                    <h3 class="font-bold text-navy-ft text-lg"><?php echo sanitize($service['name']); ?></h3>
                    <p class="text-sm text-gray-600 mt-2"><?php echo sanitize(substr($service['description'], 0, 100)); ?></p>
                    <p class="text-xs text-gray-500 mt-3">
                        <i class="fas fa-code mr-1"></i>Icon: <?php echo sanitize($service['icon']); ?>
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Added: <?php echo formatDate($service['created_at']); ?></p>
                    
                    <!-- Actions -->
                    <div class="flex gap-2 mt-4">
                        <button onclick="editService(<?php echo (int)$service['id']; ?>)" class="flex-1 px-3 py-2 bg-cyan-ft text-navy-ft rounded-lg font-semibold hover:bg-opacity-90 transition text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        <a href="?action=delete&id=<?php echo (int)$service['id']; ?>" onclick="return confirm('Delete this service?')" class="flex-1 px-3 py-2 bg-red-100 text-red-700 rounded-lg font-semibold hover:bg-red-200 transition text-sm text-center">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="serviceModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white">
            <h2 id="modalTitle" class="text-2xl font-bold">Add Service</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-navy-ft">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <form id="serviceForm" method="POST" class="p-6">
            <input type="hidden" name="service_id" id="service_id" value="">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Service Name *</label>
                    <input type="text" name="name" id="name" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft" placeholder="e.g., Web Design">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft" placeholder="Describe this service..."></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Font Awesome Icon</label>
                    <div class="flex gap-2">
                        <input type="text" name="icon" id="icon" value="fa-star" class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-ft" placeholder="fa-star">
                        <div class="px-4 py-2 border rounded-lg bg-gray-50 flex items-center">
                            <i id="iconPreview" class="fas fa-star text-2xl text-cyan-ft"></i>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Use Font Awesome classes (e.g., fa-star, fa-rocket, fa-briefcase)</p>
                    <a href="https://fontawesome.com/icons" target="_blank" class="text-cyan-ft text-xs font-semibold hover:underline">View Icon List</a>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 px-4 py-3 bg-cyan-ft text-navy-ft rounded-lg font-bold hover:bg-opacity-90 transition">
                    <i class="fas fa-save mr-2"></i>Save Service
                </button>
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const iconInput = document.getElementById('icon');
const iconPreview = document.getElementById('iconPreview');

if (iconInput) {
    iconInput.addEventListener('input', () => {
        const classes = iconInput.value.split(' ').map(c => c.trim()).filter(c => c);
        iconPreview.className = 'fas ' + classes.join(' ') + ' text-2xl text-cyan-ft';
    });
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Service';
    document.getElementById('serviceForm').reset();
    document.getElementById('service_id').value = '';
    document.getElementById('icon').value = 'fa-star';
    document.getElementById('iconPreview').className = 'fas fa-star text-2xl text-cyan-ft';
    document.getElementById('serviceModal').classList.remove('hidden');
}

function editService(id) {
    document.getElementById('modalTitle').textContent = 'Edit Service';
    document.getElementById('service_id').value = id;
    document.getElementById('serviceModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('serviceModal').classList.add('hidden');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
