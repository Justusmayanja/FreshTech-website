<?php
// Start session FIRST - before any includes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// MUST require login before anything else
require_once __DIR__ . '/includes/auth.php';
require_admin_login(); // This will redirect if not logged in

// Now we can safely require DB
require_once __DIR__ . '/includes/db.php';

// Initialize counts
$counts = [];
$counts['products'] = 0;
$counts['portfolio'] = 0;
$counts['testimonials'] = 0;
$counts['inquiries_unread'] = 0;
$counts['orders_pending'] = 0;

// Try to get counts from database if available
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $counts['products'] = (int) ($pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() ?? 0);
    } catch (Exception $e) { }
    try {
        $counts['portfolio'] = (int) ($pdo->query('SELECT COUNT(*) FROM portfolio')->fetchColumn() ?? 0);
    } catch (Exception $e) { }
    try {
        $counts['testimonials'] = (int) ($pdo->query('SELECT COUNT(*) FROM testimonials')->fetchColumn() ?? 0);
    } catch (Exception $e) { }
    try {
        $counts['inquiries_unread'] = (int) ($pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn() ?? 0);
    } catch (Exception $e) { }
    try {
        $counts['orders_pending'] = (int) ($pdo->query('SELECT COUNT(*) FROM orders WHERE status = "pending"')->fetchColumn() ?? 0);
    } catch (Exception $e) { }
}

include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-wrapper">
    <!-- Stats Cards Section -->
    <div class="stats-grid">
        <a href="/admin/products/" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Products</div>
                    <div class="stat-value"><?= $counts['products'] ?></div>
                    <div class="stat-change">Active items</div>
                </div>
            </div>
        </a>

        <a href="/admin/portfolio/" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Portfolio</div>
                    <div class="stat-value"><?= $counts['portfolio'] ?></div>
                    <div class="stat-change">Projects</div>
                </div>
            </div>
        </a>

        <a href="/admin/testimonials/" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Testimonials</div>
                    <div class="stat-value"><?= $counts['testimonials'] ?></div>
                    <div class="stat-change">Client reviews</div>
                </div>
            </div>
        </a>

        <a href="/admin/messages/" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Messages</div>
                    <div class="stat-value"><?= $counts['inquiries_unread'] ?></div>
                    <div class="stat-change">Unread inquiries</div>
                </div>
            </div>
        </a>

        <a href="/admin/orders/" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #ff6b6b 0%, #ff4757 100%);">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Orders</div>
                    <div class="stat-value" style="<?= $counts['orders_pending'] > 0 ? 'color: #ff4757; font-weight: 700;' : '' ?>"><?= $counts['orders_pending'] ?></div>
                    <div class="stat-change">Pending orders</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Why Choose Section -->
    <div class="why-choose-section">
        <h2>Why Choose PulseTech Admin?</h2>
        <p class="section-subtitle">Our comprehensive admin portal provides everything you need to manage your digital business efficiently. From product management to customer insights, we've got you covered.</p>
    </div>

    <!-- Feature Cards Grid -->
    <div class="feature-grid">
        <!-- Products Management -->
        <div class="feature-card">
            <div class="feature-badge">PRODUCTS</div>
            <div class="feature-image" style="background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);"></div>
            <div class="feature-content">
                <h3>Product Management</h3>
                <p>Manage your digital products and services catalog efficiently. Add, edit, and organize products with ease.</p>
                <div class="feature-tags">
                    <span class="tag">Inventory Tracking</span>
                    <span class="tag">Price Management</span>
                </div>
                <a href="/admin/products/index.php" class="feature-btn">Manage Products</a>
            </div>
        </div>

        <!-- Portfolio Management -->
        <div class="feature-card">
            <div class="feature-badge">PORTFOLIO</div>
            <div class="feature-image" style="background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);"></div>
            <div class="feature-content">
                <h3>Portfolio Showcase</h3>
                <p>Display your best work and successful projects. Keep your portfolio updated with stunning visual presentations.</p>
                <div class="feature-tags">
                    <span class="tag">Project Gallery</span>
                    <span class="tag">Case Studies</span>
                </div>
                <a href="/admin/portfolio/index.php" class="feature-btn">Manage Portfolio</a>
            </div>
        </div>

        <!-- Services Management -->
        <div class="feature-card">
            <div class="feature-badge">SERVICES</div>
            <div class="feature-image" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);"></div>
            <div class="feature-content">
                <h3>Service Offerings</h3>
                <p>Manage your service catalog including UI/UX design, web development, and graphic branding with comprehensive tools.</p>
                <div class="feature-tags">
                    <span class="tag">Service Categories</span>
                    <span class="tag">Pricing Plans</span>
                </div>
                <a href="/admin/services/index.php" class="feature-btn">Manage Services</a>
            </div>
        </div>

        <!-- Order Management -->
        <div class="feature-card">
            <div class="feature-badge">ADMIN TOOLS</div>
            <div class="feature-image" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);"></div>
            <div class="feature-content">
                <h3>Order Management</h3>
                <p>Track and process customer orders efficiently with our comprehensive order management system. Stay organized and never miss a sale.</p>
                <div class="feature-tags">
                    <span class="tag">Order Tracking</span>
                    <span class="tag">Customer Management</span>
                </div>
                <a href="/admin/orders/index.php" class="feature-btn">View Orders</a>
            </div>
        </div>

        <!-- Messages & Inquiries -->
        <div class="feature-card">
            <div class="feature-badge">ADMIN TOOLS</div>
            <div class="feature-image" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
            <div class="feature-content">
                <h3>Customer Insights</h3>
                <p>Understand your customers and their preferences with comprehensive customer inquiries and behavior tracking.</p>
                <div class="feature-tags">
                    <span class="tag">Message Analytics</span>
                    <span class="tag">Lead Tracking</span>
                </div>
                <a href="/admin/messages/index.php" class="feature-btn">View Messages</a>
            </div>
        </div>

        <!-- Security & Access -->
        <div class="feature-card">
            <div class="feature-badge">ADMIN TOOLS</div>
            <div class="feature-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
            <div class="feature-content">
                <h3>Secure Access</h3>
                <p>Protected admin portal with role-based access control. Your business data is secure with enterprise-grade security.</p>
                <div class="feature-tags">
                    <span class="tag">Secure Login</span>
                    <span class="tag">Role-Based Access</span>
                </div>
                <a href="/admin/logout.php" class="feature-btn">Logout</a>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-wrapper {
    padding: 0;
    max-width: 100%;
}

/* Hero Section */
.dashboard-hero {
    display: none;
}

.hero-content {
    display: none;
}

.hero-badge {
    display: none;
}

.dashboard-hero h1 {
    display: none;
}

.hero-subtitle {
    display: none;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
    padding: 0 0 40px;
}

.stat-card-link {
    text-decoration: none;
    color: inherit;
    cursor: pointer;
    transition: all 0.3s ease;
}

.stat-card-link:hover .stat-card {
    transform: translateY(-6px);
    box-shadow: 0 12px 32px rgba(0, 212, 255, 0.2);
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 28px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
}

.stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
    flex-shrink: 0;
}

.stat-icon i {
    font-size: 28px;
}

.stat-info {
    flex: 1;
}

.stat-label {
    font-size: 13px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 36px;
    font-weight: 700;
    color: #0a1a2f;
    line-height: 1;
    margin-bottom: 6px;
}

.stat-change {
    font-size: 13px;
    color: #00d4ff;
    font-weight: 500;
}

/* Why Choose Section */
.why-choose-section {
    text-align: center;
    padding: 40px 0 30px;
    max-width: 800px;
    margin: 0 auto;
}

.why-choose-section h2 {
    font-size: 32px;
    color: #0a1a2f;
    margin: 0 0 15px;
    font-weight: 700;
}

.section-subtitle {
    font-size: 16px;
    color: #666;
    line-height: 1.7;
}

/* Feature Grid */
.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
    padding: 30px 0 60px;
}

.feature-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
    position: relative;
}

.feature-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15);
}

.feature-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    background: rgba(255, 255, 255, 0.95);
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    color: #0a1a2f;
    z-index: 1;
}

.feature-image {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.feature-content {
    padding: 28px;
}

.feature-content h3 {
    font-size: 22px;
    color: #0a1a2f;
    margin: 0 0 12px;
    font-weight: 700;
}

.feature-content p {
    font-size: 14px;
    color: #666;
    line-height: 1.7;
    margin-bottom: 20px;
}

.feature-tags {
    display: flex;
    gap: 10px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.tag {
    background: #f0f7ff;
    color: #00d4ff;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.feature-btn {
    display: inline-block;
    background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
    color: #0a1a2f;
    padding: 12px 28px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.feature-btn:hover {
    transform: translateX(4px);
    box-shadow: 0 8px 20px rgba(0, 212, 255, 0.3);
}

@media (max-width: 768px) {
    .dashboard-hero {
        padding: 40px 20px;
    }
    
    .dashboard-hero h1 {
        font-size: 28px;
    }
    
    .stats-grid,
    .feature-grid {
        padding-left: 20px;
        padding-right: 20px;
        grid-template-columns: 1fr;
    }
    
    .why-choose-section {
        padding: 30px 20px;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
