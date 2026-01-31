<?php
// Start session FIRST
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require admin login
require_once __DIR__ . '/../includes/auth.php';
require_admin_login();

// Include database and config
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

// Initialize variables
$testimonials = [];
$message = '';
$error = '';

// Try to fetch testimonials from database
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $result = $pdo->query('SELECT * FROM testimonials ORDER BY created_at DESC');
        $testimonials = $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Database not available, use sample data
    }
}

// Fallback sample testimonials
if (empty($testimonials)) {
    $testimonials = [
        [
            'id' => 1,
            'client_name' => 'Sarah Johnson',
            'company' => 'Tech Innovations Ltd',
            'rating' => 5,
            'message' => 'PulseTech delivered an exceptional website that exceeded all our expectations. Their attention to detail and professionalism was outstanding!',
            'image' => null,
            'is_approved' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ],
        [
            'id' => 2,
            'client_name' => 'Michael Chen',
            'company' => 'Digital Solutions Inc',
            'rating' => 5,
            'message' => 'The UI/UX design team at PulseTech transformed our vision into reality. Amazing work and great communication throughout the project.',
            'image' => null,
            'is_approved' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
        ],
        [
            'id' => 3,
            'client_name' => 'Emily Rodriguez',
            'company' => 'Creative Agency Pro',
            'rating' => 4,
            'message' => 'Great experience working with PulseTech. They understood our needs perfectly and delivered a solution that worked beyond expectations.',
            'image' => null,
            'is_approved' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 days'))
        ],
        [
            'id' => 4,
            'client_name' => 'David Wilson',
            'company' => 'Marketing Hub',
            'rating' => 5,
            'message' => 'Exceptional service and support. The team was responsive to all our requests and delivered the project on time.',
            'image' => null,
            'is_approved' => 0,
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ]
    ];
}

// Handle add testimonial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add' && isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO testimonials (client_name, company, rating, message, is_approved)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                htmlspecialchars($_POST['client_name']),
                htmlspecialchars($_POST['company']),
                (int)$_POST['rating'],
                htmlspecialchars($_POST['message']),
                isset($_POST['is_approved']) ? 1 : 0
            ]);
            $message = 'Testimonial added successfully!';
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $error = 'Error adding testimonial: ' . $e->getMessage();
        }
    }
    // Edit testimonial
    elseif ($_POST['action'] === 'edit' && isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->prepare('
                UPDATE testimonials SET 
                client_name = ?, company = ?, rating = ?, message = ?, is_approved = ?
                WHERE id = ?
            ');
            $stmt->execute([
                htmlspecialchars($_POST['client_name']),
                htmlspecialchars($_POST['company']),
                (int)$_POST['rating'],
                htmlspecialchars($_POST['message']),
                isset($_POST['is_approved']) ? 1 : 0,
                (int)$_POST['id']
            ]);
            $message = 'Testimonial updated successfully!';
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $error = 'Error updating testimonial: ' . $e->getMessage();
        }
    }
    // Delete testimonial
    elseif ($_POST['action'] === 'delete' && isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->prepare('DELETE FROM testimonials WHERE id = ?');
            $stmt->execute([(int)$_POST['id']]);
            $message = 'Testimonial deleted successfully!';
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $error = 'Error deleting testimonial: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="margin-top: 20px;">
    <!-- Messages -->
    <?php if ($message): ?>
        <div class="alert alert-success" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Header with Add Button -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="font-size: 32px; font-weight: 700; color: #0a1a2f; margin: 0; margin-bottom: 5px;">Testimonials Management</h1>
            <p style="color: #888; margin: 0;">Manage client testimonials and reviews</p>
        </div>
        <button onclick="openModal()" style="background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%); color: #0a1a2f; border: none; padding: 12px 28px; border-radius: 30px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.3s ease;">
            <i class="fas fa-plus"></i> Add Testimonial
        </button>
    </div>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border-left: 4px solid #00d4ff;">
            <div style="font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Total Testimonials</div>
            <div style="font-size: 32px; font-weight: 700; color: #0a1a2f;"><?= count($testimonials) ?></div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border-left: 4px solid #00d4ff;">
            <div style="font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Approved</div>
            <div style="font-size: 32px; font-weight: 700; color: #0a1a2f;"><?= count(array_filter($testimonials, fn($t) => $t['is_approved'] == 1)) ?></div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border-left: 4px solid #f5576c;">
            <div style="font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Pending</div>
            <div style="font-size: 32px; font-weight: 700; color: #0a1a2f;"><?= count(array_filter($testimonials, fn($t) => $t['is_approved'] != 1)) ?></div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border-left: 4px solid #764ba2;">
            <div style="font-size: 12px; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">Avg Rating</div>
            <div style="font-size: 32px; font-weight: 700; color: #0a1a2f;">
                <?php 
                    $avgRating = !empty($testimonials) ? array_sum(array_column($testimonials, 'rating')) / count($testimonials) : 0;
                    echo number_format($avgRating, 1);
                ?>
                <span style="font-size: 16px; color: #ffc107;">★</span>
            </div>
        </div>
    </div>

    <!-- Testimonials Grid -->
    <div style="display: grid; gap: 20px;">
        <?php foreach ($testimonials as $testimonial): ?>
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05);">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #0a1a2f;">
                                <?= htmlspecialchars($testimonial['client_name']) ?>
                            </h3>
                            <?php if ($testimonial['is_approved']): ?>
                                <span style="background: #d4edda; color: #155724; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Approved</span>
                            <?php else: ?>
                                <span style="background: #fff3cd; color: #856404; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Pending</span>
                            <?php endif; ?>
                        </div>
                        <p style="margin: 0; color: #888; font-size: 14px;"><?= htmlspecialchars($testimonial['company']) ?></p>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="editTestimonial(<?= htmlspecialchars(json_encode($testimonial)) ?>)" style="background: #f0f7ff; color: #00d4ff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 12px; transition: all 0.3s ease;">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button onclick="deleteTestimonial(<?= $testimonial['id'] ?>)" style="background: #ffe6e6; color: #c00; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 12px; transition: all 0.3s ease;">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <span style="color: <?= $i < $testimonial['rating'] ? '#ffc107' : '#ddd'; ?>; font-size: 16px;">★</span>
                    <?php endfor; ?>
                </div>

                <p style="margin: 0; color: #666; line-height: 1.6; font-size: 14px;">
                    "<?= htmlspecialchars($testimonial['message']) ?>"
                </p>

                <p style="margin: 12px 0 0; color: #999; font-size: 12px;">
                    <?= formatDate($testimonial['created_at']) ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($testimonials)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 12px; margin-top: 20px;">
            <i class="fas fa-star" style="font-size: 48px; color: #ddd; margin-bottom: 16px; display: block;"></i>
            <h3 style="font-size: 20px; color: #999; margin: 0 0 8px;">No testimonials yet</h3>
            <p style="color: #bbb; margin: 0;">Start collecting client feedback and testimonials</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal for Add/Edit -->
<div id="testimonialModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h2 id="modalTitle" style="font-size: 24px; color: #0a1a2f; margin: 0 0 24px; font-weight: 700;">Add Testimonial</h2>
        
        <form method="POST">
            <input type="hidden" id="action" name="action" value="add">
            <input type="hidden" id="testId" name="id">

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #0a1a2f; margin-bottom: 8px;">Client Name *</label>
                <input type="text" id="clientName" name="client_name" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #0a1a2f; margin-bottom: 8px;">Company</label>
                <input type="text" id="company" name="company" style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #0a1a2f; margin-bottom: 8px;">Rating *</label>
                <select id="rating" name="rating" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                    <option value="">Select rating...</option>
                    <option value="5">★★★★★ - Excellent</option>
                    <option value="4">★★★★☆ - Very Good</option>
                    <option value="3">★★★☆☆ - Good</option>
                    <option value="2">★★☆☆☆ - Fair</option>
                    <option value="1">★☆☆☆☆ - Poor</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #0a1a2f; margin-bottom: 8px;">Testimonial Message *</label>
                <textarea id="message" name="message" required rows="4" style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box; resize: vertical;"></textarea>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" id="isApproved" name="is_approved" style="cursor: pointer;">
                    <span style="color: #0a1a2f; font-weight: 600;">Approve testimonial</span>
                </label>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeModal()" style="background: #f0f0f0; color: #0a1a2f; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600;">Cancel</button>
                <button type="submit" style="background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%); color: #0a1a2f; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600;">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" id="deleteId" name="id">
</form>

<script>
function openModal() {
    document.getElementById('action').value = 'add';
    document.getElementById('modalTitle').textContent = 'Add Testimonial';
    document.getElementById('clientName').value = '';
    document.getElementById('company').value = '';
    document.getElementById('rating').value = '';
    document.getElementById('message').value = '';
    document.getElementById('isApproved').checked = false;
    document.getElementById('testimonialModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('testimonialModal').style.display = 'none';
}

function editTestimonial(testimonial) {
    document.getElementById('action').value = 'edit';
    document.getElementById('modalTitle').textContent = 'Edit Testimonial';
    document.getElementById('testId').value = testimonial.id;
    document.getElementById('clientName').value = testimonial.client_name;
    document.getElementById('company').value = testimonial.company;
    document.getElementById('rating').value = testimonial.rating;
    document.getElementById('message').value = testimonial.message;
    document.getElementById('isApproved').checked = testimonial.is_approved == 1;
    document.getElementById('testimonialModal').style.display = 'flex';
}

function deleteTestimonial(id) {
    if (confirm('Are you sure you want to delete this testimonial?')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Close modal when clicking outside
document.getElementById('testimonialModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.alert {
    border: 1px solid;
    border-radius: 8px;
    padding: 12px 20px;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

button:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

input[type="text"],
input[type="email"],
textarea,
select {
    font-family: inherit;
}

@media (max-width: 768px) {
    #testimonialModal {
        padding: 0 20px !important;
    }
    
    #testimonialModal > div {
        width: 100% !important;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
