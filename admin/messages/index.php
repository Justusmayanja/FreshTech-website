<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$messages = [];
$db_available = isset($pdo) && $pdo instanceof PDO;

// Handle mark as read via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_read') {
    $message_id = (int)($_POST['message_id'] ?? 0);
    if ($message_id > 0 && $db_available) {
        try {
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
            $stmt->execute([$message_id]);
            header('Location: /admin/messages/');
            exit;
        } catch (Exception $e) {
            // Handle error silently
        }
    }
}

// Handle delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $message_id = (int)($_POST['message_id'] ?? 0);
    if ($message_id > 0 && $db_available) {
        try {
            $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->execute([$message_id]);
            header('Location: /admin/messages/');
            exit;
        } catch (Exception $e) {
            // Handle error silently
        }
    }
}

// Try to fetch from database
if ($db_available) {
    try {
        $stmt = $pdo->query("SELECT id, name, email, phone, subject, message, is_read, created_at FROM contact_messages ORDER BY created_at DESC");
        $messages = $stmt->fetchAll();
    } catch (Exception $e) {
        // Database error - use fallback
        $messages = [];
    }
} else {
    // Database not available - use fallback sample data
    $messages = [
        ['id' => 1, 'name' => 'Alice Johnson', 'email' => 'alice@example.com', 'phone' => '+256752123456', 'subject' => 'Website Design Inquiry', 'message' => 'I need a professional website for my business. Can you help?', 'is_read' => 1, 'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))],
        ['id' => 2, 'name' => 'Bob Martinez', 'email' => 'bob@example.com', 'phone' => '+256752234567', 'subject' => 'Mobile App Development', 'message' => 'Looking for iOS and Android app development services.', 'is_read' => 1, 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))],
        ['id' => 3, 'name' => 'Carol Davis', 'email' => 'carol@example.com', 'phone' => '+256752345678', 'subject' => 'UI/UX Design Project', 'message' => 'Need help redesigning our application interface.', 'is_read' => 0, 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
        ['id' => 4, 'name' => 'David Wilson', 'email' => 'david@example.com', 'phone' => '', 'subject' => 'Branding Services', 'message' => 'We need a complete rebranding for our startup.', 'is_read' => 0, 'created_at' => date('Y-m-d H:i:s')],
    ];
}

// Count unread messages
$unread_count = count(array_filter($messages, function($m) { return !$m['is_read']; }));

$pageTitle = "Messages";
include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-4xl font-bold text-navy-ft">Contact Messages</h1>
        <p class="text-gray-600 mt-1">Manage customer contact form submissions</p>
    </div>
    <?php if ($unread_count > 0): ?>
        <div class="px-6 py-3 bg-blue-100 text-blue-800 rounded-lg font-bold">
            <i class="fas fa-bell mr-2"></i><?php echo $unread_count; ?> New Message<?php echo $unread_count > 1 ? 's' : ''; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow border p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Total Messages</p>
                <p class="text-2xl font-bold text-navy-ft"><?php echo count($messages); ?></p>
            </div>
            <i class="fas fa-envelope text-3xl text-cyan-ft opacity-20"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow border p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Unread</p>
                <p class="text-2xl font-bold text-blue-600"><?php echo $unread_count; ?></p>
            </div>
            <i class="fas fa-envelope-open text-3xl text-blue-400 opacity-20"></i>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow border p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Read</p>
                <p class="text-2xl font-bold text-green-600"><?php echo count($messages) - $unread_count; ?></p>
            </div>
            <i class="fas fa-check-circle text-3xl text-green-400 opacity-20"></i>
        </div>
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
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Phone</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Subject</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3 opacity-50 block"></i>
                            <p class="text-lg">No messages yet</p>
                            <p class="text-sm text-gray-400 mt-2">Messages from the contact form will appear here</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <tr data-message-id="<?php echo (int)$msg['id']; ?>" class="border-b hover:bg-gray-50 transition <?php echo !$msg['is_read'] ? 'bg-blue-50' : ''; ?>">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <?php if (!$msg['is_read']): ?>
                                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                    <?php endif; ?>
                                    <span class="text-sm font-semibold"><?php echo htmlspecialchars($msg['name']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-cyan-ft">
                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="hover:underline">
                                    <?php echo htmlspecialchars($msg['email']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php if (!empty($msg['phone'])): ?>
                                    <a href="tel:<?php echo htmlspecialchars($msg['phone']); ?>" class="hover:underline">
                                        <?php echo htmlspecialchars($msg['phone']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php 
                                $subject = htmlspecialchars($msg['subject']);
                                echo strlen($subject) > 40 ? substr($subject, 0, 40) . '...' : $subject;
                                ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="message-status px-3 py-1 rounded-full text-xs font-semibold <?php echo $msg['is_read'] ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo $msg['is_read'] ? 'Read' : 'New'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo formatDate($msg['created_at']); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <button onclick='viewMessage(<?php echo json_encode($msg, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="text-cyan-ft hover:text-navy-ft font-semibold transition">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </button>
                                    <?php if (!$msg['is_read']): ?>
                                        <form method="POST" class="mark-read-form" style="display: inline;">
                                            <input type="hidden" name="action" value="mark_read">
                                            <input type="hidden" name="message_id" value="<?php echo (int)$msg['id']; ?>">
                                            <button type="submit" class="text-green-600 hover:text-green-800 font-semibold transition">
                                                <i class="fas fa-check mr-1"></i>Read
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this message?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="message_id" value="<?php echo (int)$msg['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold transition">
                                            <i class="fas fa-trash mr-1"></i>Delete
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

<!-- Message Details Modal -->
<div id="messageModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between sticky top-0 bg-white">
            <h2 class="text-2xl font-bold text-navy-ft">Message Details</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-navy-ft transition">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="messageContent" class="p-6">
            <!-- Content loaded via JS -->
        </div>
        <div class="p-6 border-t bg-gray-50 flex gap-3">
            <a id="replyEmail" href="" class="flex-1 px-4 py-3 bg-cyan-ft text-navy-ft text-center rounded-lg font-bold hover:bg-opacity-90 transition">
                <i class="fas fa-reply mr-2"></i>Reply via Email
            </a>
            <button onclick="closeModal()" class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function viewMessage(msg) {
    const modal = document.getElementById('messageModal');
    const content = document.getElementById('messageContent');
    const replyBtn = document.getElementById('replyEmail');
    
    modal.classList.remove('hidden');

    // Mark message as read in the background
    if (msg.id) {
        fetch('/admin/messages/', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'mark_read', message_id: msg.id })
        }).then(() => {
            const row = document.querySelector(`tr[data-message-id="${msg.id}"]`);
            if (row) {
                row.classList.remove('bg-blue-50');
                const statusEl = row.querySelector('.message-status');
                if (statusEl) {
                    statusEl.textContent = 'Read';
                    statusEl.classList.remove('bg-blue-100', 'text-blue-800');
                    statusEl.classList.add('bg-gray-100', 'text-gray-800');
                }
                const readForm = row.querySelector('.mark-read-form');
                if (readForm) readForm.remove();
            }
            msg.is_read = 1;
            const modalStatus = document.getElementById('modalStatus');
            if (modalStatus) {
                modalStatus.textContent = 'Read';
                modalStatus.classList.remove('bg-blue-100', 'text-blue-800');
                modalStatus.classList.add('bg-gray-100', 'text-gray-800');
            }
        }).catch(() => {});
    }
    
    const subject = 'Re: ' + (msg.subject || 'Your inquiry');
    replyBtn.href = `mailto:${msg.email}?subject=${encodeURIComponent(subject)}`;
    
    content.innerHTML = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">From</label>
                    <p class="text-lg font-semibold text-navy-ft">${msg.name || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Date</label>
                    <p class="text-gray-900">${msg.created_at || 'N/A'}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                    <a href="mailto:${msg.email}" class="text-cyan-ft font-semibold hover:underline">${msg.email || 'N/A'}</a>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Phone</label>
                    ${msg.phone ? `<a href="tel:${msg.phone}" class="text-cyan-ft font-semibold hover:underline">${msg.phone}</a>` : '<p class="text-gray-400">N/A</p>'}
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Subject</label>
                <p class="text-gray-900 font-medium">${msg.subject || 'No subject'}</p>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                <div class="bg-gray-50 p-4 rounded-lg border whitespace-pre-wrap text-gray-800">${msg.message || 'No message content'}</div>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                <span id="modalStatus" class="px-3 py-1 rounded-full text-xs font-semibold ${msg.is_read ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800'}">
                    ${msg.is_read ? 'Read' : 'New'}
                </span>
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
