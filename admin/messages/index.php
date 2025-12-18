<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$messages = [];
$action = $_GET['action'] ?? '';
$message_id = $_GET['id'] ?? '';

// Handle mark as read
if ($action == 'mark_read' && !empty($message_id)) {
    try {
        $stmt = $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([(int)$message_id]);
        redirect('messages/');
    } catch (Exception $e) {
        // Handle error
    }
}

// Handle delete
if ($action == 'delete' && !empty($message_id)) {
    try {
        $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([(int)$message_id]);
        redirect('messages/');
    } catch (Exception $e) {
        // Handle error
    }
}

try {
    $stmt = $db->query("SELECT id, name, email, subject, message, is_read, created_at FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error
}

$pageTitle = "Messages";
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-4xl font-bold text-navy-ft">Contact Messages</h1>
        <p class="text-gray-600 mt-1">Manage customer contact form submissions</p>
    </div>
</div>

<!-- Messages Table -->
<div class="bg-white rounded-xl shadow-md border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">From</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Email</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Subject</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3 opacity-50 block"></i>
                            <p class="text-lg">No messages</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <tr class="border-b hover:bg-gray-50 transition <?php echo !$msg['is_read'] ? 'bg-blue-50' : ''; ?>">
                            <td class="px-6 py-4 text-sm font-semibold"><?php echo sanitize($msg['name']); ?></td>
                            <td class="px-6 py-4 text-sm text-cyan-ft"><?php echo sanitize($msg['email']); ?></td>
                            <td class="px-6 py-4 text-sm"><?php echo sanitize(substr($msg['subject'], 0, 50)); ?>...</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $msg['is_read'] ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo $msg['is_read'] ? 'Read' : 'New'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo formatDate($msg['created_at']); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <button onclick="viewMessage(<?php echo (int)$msg['id']; ?>, '<?php echo sanitize($msg['name']); ?>', '<?php echo sanitize($msg['email']); ?>', '<?php echo sanitize($msg['subject']); ?>', `<?php echo sanitize($msg['message']); ?>`)" class="text-cyan-ft hover:text-navy-ft font-semibold transition mr-3">
                                    <i class="fas fa-eye mr-1"></i>View
                                </button>
                                <?php if (!$msg['is_read']): ?>
                                    <a href="?action=mark_read&id=<?php echo (int)$msg['id']; ?>" class="text-green-600 hover:text-green-800 font-semibold transition mr-3">
                                        <i class="fas fa-check mr-1"></i>Read
                                    </a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?php echo (int)$msg['id']; ?>" class="text-red-600 hover:text-red-800 font-semibold transition" onclick="return confirm('Delete this message?')">
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

<!-- Message Details Modal -->
<div id="messageModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white">
            <h2 class="text-2xl font-bold">Message Details</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-navy-ft">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="messageContent" class="p-6">
            <!-- Content loaded via JS -->
        </div>
    </div>
</div>

<script>
function viewMessage(id, name, email, subject, message) {
    const modal = document.getElementById('messageModal');
    const content = document.getElementById('messageContent');
    
    modal.classList.remove('hidden');
    
    content.innerHTML = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">From</label>
                <p class="text-lg font-semibold text-navy-ft">${name}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <a href="mailto:${email}" class="text-cyan-ft font-semibold">${email}</a>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Subject</label>
                <p class="text-gray-900">${subject}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Message</label>
                <div class="bg-gray-50 p-4 rounded-lg border whitespace-pre-wrap text-gray-800">${message}</div>
            </div>
        </div>
    `;
}

function closeModal() {
    document.getElementById('messageModal').classList.add('hidden');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
