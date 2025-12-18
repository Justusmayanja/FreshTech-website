-- FreshTech Solutions Database
-- Complete SQL schema with sample data

CREATE DATABASE IF NOT EXISTS freshtech_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE freshtech_db;

-- =====================================================
-- 1. ADMINS TABLE
-- =====================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    last_login DATETIME,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin account: admin / FreshTech2025!
INSERT INTO admins (username, password, email, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@freshtechsolutions.com', 'Admin User');

-- =====================================================
-- 2. CONTACT MESSAGES TABLE
-- =====================================================
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    service VARCHAR(50),
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample contact messages
INSERT INTO contact_messages (full_name, email, phone, service, message, status, ip_address) VALUES
('John Smith', 'john@example.com', '+1 (555) 123-4567', 'website', 'I need a new website for my startup. Can we schedule a call?', 'unread', '192.168.1.1'),
('Sarah Johnson', 'sarah@company.com', '+1 (555) 987-6543', 'branding', 'Looking for a complete brand identity package. What are your rates?', 'read', '192.168.1.2'),
('Mike Chen', 'mike@tech.com', NULL, 'accessories', 'Do you have iPhone 15 Pro cases in stock?', 'replied', '192.168.1.3');

-- =====================================================
-- 3. ORDERS TABLE
-- =====================================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    shipping_address TEXT NOT NULL,
    billing_address TEXT,
    items JSON NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    tax DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    tracking_number VARCHAR(100),
    notes TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample orders
INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, shipping_address, items, subtotal, shipping_cost, tax, total, payment_method, payment_status, order_status) VALUES
('ORD-2025-0001', 'Emily Davis', 'emily@email.com', '+1 (555) 111-2222', '123 Main St, San Francisco, CA 94102', '[{"product_id":1,"name":"Impact MagSafe Case","price":32.00,"quantity":1},{"product_id":4,"name":"65W GaN Fast Charger","price":44.00,"quantity":1}]', 76.00, 5.00, 6.84, 87.84, 'Credit Card', 'paid', 'shipped'),
('ORD-2025-0002', 'Robert Taylor', 'robert@company.com', '+1 (555) 333-4444', '456 Oak Ave, Los Angeles, CA 90001', '[{"product_id":7,"name":"Crystal ANC Earbuds","price":89.00,"quantity":2}]', 178.00, 0.00, 16.02, 194.02, 'PayPal', 'paid', 'delivered'),
('ORD-2025-0003', 'Lisa Anderson', 'lisa@email.com', '+1 (555) 555-6666', '789 Pine Rd, Austin, TX 78701', '[{"product_id":10,"name":"Pulse Smart Watch","price":129.00,"quantity":1}]', 129.00, 5.00, 12.06, 146.06, 'Credit Card', 'pending', 'processing');

-- =====================================================
-- 4. PORTFOLIO TABLE
-- =====================================================
CREATE TABLE portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    client_name VARCHAR(100),
    project_date DATE,
    featured_image VARCHAR(255),
    gallery_images JSON,
    live_url VARCHAR(255),
    technologies JSON,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample portfolio projects
INSERT INTO portfolio (title, slug, category, description, client_name, project_date, featured_image, technologies, is_featured, is_active) VALUES
('NovaPay Fintech Platform', 'novapay-fintech-platform', 'website', 'Responsive SaaS site with custom CMS. +38% demo signups in 60 days.', 'NovaPay Inc', '2024-11-15', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=540&h=360&fit=crop', '["Next.js", "Sanity CMS", "Tailwind CSS"]', TRUE, TRUE),
('ShiftLabs AI Rebrand', 'shiftlabs-ai-rebrand', 'branding', 'Complete visual identity: logo suite, color system, brand guidelines.', 'ShiftLabs', '2024-10-20', 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=540&h=360&fit=crop', '["Adobe Illustrator", "Figma"]', TRUE, TRUE),
('VeloCommerce Shop', 'velocommerce-shop', 'ecommerce', 'Headless Shopify store, 1.2s LCP, optimized checkout flow.', 'VeloCommerce', '2024-09-10', 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=540&h=360&fit=crop', '["Shopify", "Next.js", "Stripe"]', FALSE, TRUE);

-- =====================================================
-- 5. PRODUCTS TABLE
-- =====================================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    sku VARCHAR(50) UNIQUE,
    description TEXT,
    short_description VARCHAR(500),
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2),
    stock_quantity INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 5,
    featured_image VARCHAR(255),
    gallery_images JSON,
    specifications JSON,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    rating DECIMAL(2,1) DEFAULT 0.0,
    review_count INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    meta_title VARCHAR(255),
    meta_description VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample products
INSERT INTO products (name, slug, sku, description, short_description, category, price, sale_price, stock_quantity, featured_image, is_featured, is_active, rating, review_count) VALUES
('Impact MagSafe Case', 'impact-magsafe-case', 'PHN-CASE-001', 'Premium protective case with MagSafe compatibility. Drop-tested from 10 feet. Available in Cyan/Charcoal colors for iPhone 15 and 14.', 'Cyan/Charcoal • iPhone 15/14', 'cases', 38.00, 32.00, 150, 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=400&h=260&fit=crop', TRUE, TRUE, 5.0, 124),
('Crystal Clear Case', 'crystal-clear-case', 'PHN-CASE-002', 'Ultra-thin transparent case that showcases your phone. Scratch-resistant and yellowing-proof material.', 'Ultra-thin • All models', 'cases', 24.00, NULL, 200, 'https://images.unsplash.com/photo-1511707267537-b85faf00021e?w=400&h=260&fit=crop', FALSE, TRUE, 4.0, 89),
('Premium Leather Case', 'premium-leather-case', 'PHN-CASE-003', 'Genuine leather case with aged finish. Develops unique patina over time.', 'Genuine leather • Aged finish', 'cases', 49.00, NULL, 75, 'https://images.unsplash.com/photo-1589872657893-75f4c7a60944?w=400&h=260&fit=crop', TRUE, TRUE, 5.0, 56),
('65W GaN Fast Charger', '65w-gan-fast-charger', 'CHG-001', 'Compact GaN technology charger with dual USB-C ports. 65W power delivery for laptops and phones.', 'Dual USB-C • Travel ready', 'chargers', 44.00, NULL, 300, 'https://images.unsplash.com/photo-1591496694519-5d381e16f382?w=400&h=260&fit=crop', TRUE, TRUE, 5.0, 312),
('Crystal ANC Earbuds', 'crystal-anc-earbuds', 'AUD-001', 'Premium wireless earbuds with active noise cancellation. 42-hour total battery life with charging case.', '42h battery • Clear calls • ANC', 'audio', 89.00, NULL, 250, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=260&fit=crop', TRUE, TRUE, 5.0, 467),
('Pulse Smart Watch', 'pulse-smart-watch', 'WAT-001', 'AMOLED display smartwatch with fitness tracking, NFC payments, and 7-day battery life.', 'AMOLED • Fitness • NFC Pay', 'watches', 129.00, NULL, 180, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=260&fit=crop', TRUE, TRUE, 4.0, 234);

-- =====================================================
-- 6. SERVICES TABLE
-- =====================================================
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    icon VARCHAR(10),
    short_description VARCHAR(500),
    full_description TEXT,
    features JSON,
    pricing_tiers JSON,
    delivery_time VARCHAR(50),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    meta_title VARCHAR(255),
    meta_description VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample services
INSERT INTO services (title, slug, icon, short_description, full_description, features, delivery_time, is_featured, is_active) VALUES
('Website Development', 'website-development', 'W', 'High-performance sites in Webflow/Next.js with lightning speed and SEO baked in.', 'We build high-performance websites with modern frameworks, optimized for speed, SEO, and user experience. From landing pages to full SaaS platforms.', '["Custom CMS integration", "Core Web Vitals optimized", "Mobile-first responsive", "Analytics & tracking setup"]', '2-4 weeks', TRUE, TRUE),
('Graphic Design & Branding', 'graphic-branding', 'G', 'Logo suites, brand guidelines, and sales assets with a premium, future-ready look.', 'Logo design, visual systems, and brand guidelines that make you stand out. From concept sketches to final deliverables.', '["Logo suite + variations", "Brand guidelines", "Marketing materials", "Social media templates"]', '2-3 weeks', TRUE, TRUE),
('UI/UX Design', 'ui-ux-design', 'U', 'Product design systems, conversion-focused flows, and prototypes that test fast.', 'Research-driven design that converts. From wireframes to interactive prototypes, we craft digital experiences optimized for usability and conversion.', '["User research & personas", "Wireframes & flows", "Interactive prototypes", "Design system libraries"]', '3-4 weeks', TRUE, TRUE),
('E-commerce Solutions', 'ecommerce-solutions', 'E', 'Shopify/Headless commerce, optimized product pages, and streamlined checkout.', 'Complete online stores on Shopify, WooCommerce, or headless commerce. Optimized checkout flows and conversion funnels.', '["Shopify/WooCommerce setup", "Product page optimization", "Payment gateway integration", "Inventory & shipping"]', '3-6 weeks', TRUE, TRUE);

-- =====================================================
-- 7. BLOG POSTS TABLE
-- =====================================================
CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255),
    category VARCHAR(50),
    tags JSON,
    author_id INT,
    status ENUM('draft', 'published', 'scheduled') DEFAULT 'draft',
    published_at DATETIME,
    views INT DEFAULT 0,
    reading_time INT,
    is_featured BOOLEAN DEFAULT FALSE,
    meta_title VARCHAR(255),
    meta_description VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample blog posts
INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, category, tags, author_id, status, published_at, reading_time, is_featured) VALUES
('Next.js 14 Performance Guide', 'nextjs-14-performance-guide', 'Master server components, streaming, and partial prerendering for lightning-fast sites.', '<h2>Introduction to Next.js 14</h2><p>Next.js 14 brings revolutionary performance improvements...</p>', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=540&h=300&fit=crop', 'Web Design', '["Next.js", "Performance", "React"]', 1, 'published', '2025-12-08 10:00:00', 8, TRUE),
('Building a Brand Identity in 2025', 'building-brand-identity-2025', 'Step-by-step framework: from discovery to delivering a complete brand system.', '<h2>Why Brand Identity Matters</h2><p>Your brand is more than a logo...</p>', 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=540&h=300&fit=crop', 'Branding', '["Branding", "Design", "Strategy"]', 1, 'published', '2025-12-05 14:00:00', 10, TRUE),
('Best USB-C Chargers of 2025', 'best-usbc-chargers-2025', 'We tested 20 GaN chargers. Here are the fastest, safest, and most portable options.', '<h2>Testing Methodology</h2><p>We tested 20 different USB-C chargers...</p>', 'https://images.unsplash.com/photo-1591496694519-5d381e16f382?w=540&h=300&fit=crop', 'Tech Reviews', '["Tech", "Reviews", "Chargers"]', 1, 'published', '2025-12-03 09:00:00', 6, FALSE);

-- =====================================================
-- 8. SETTINGS TABLE
-- =====================================================
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'text',
    category VARCHAR(50) DEFAULT 'general',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Site settings
INSERT INTO settings (setting_key, setting_value, setting_type, category, description) VALUES
('site_name', 'FreshTech Solutions', 'text', 'general', 'Website name'),
('site_tagline', 'Professional Websites, Stunning Branding & Premium Tech Accessories', 'text', 'general', 'Website tagline'),
('site_logo', '/assets/logo.png', 'image', 'general', 'Site logo URL'),
('contact_email', 'hello@freshtechsolutions.com', 'email', 'contact', 'Main contact email'),
('contact_phone', '+1 (555) 123-4567', 'text', 'contact', 'Contact phone number'),
('contact_address', 'San Francisco, CA, United States', 'textarea', 'contact', 'Physical address'),
('social_instagram', 'https://instagram.com/freshtech', 'url', 'social', 'Instagram URL'),
('social_facebook', 'https://facebook.com/freshtech', 'url', 'social', 'Facebook URL'),
('social_twitter', 'https://twitter.com/freshtech', 'url', 'social', 'Twitter URL'),
('social_linkedin', 'https://linkedin.com/company/freshtech', 'url', 'social', 'LinkedIn URL'),
('primary_color', '#00D4FF', 'color', 'design', 'Primary brand color (cyan)'),
('secondary_color', '#0A1A2F', 'color', 'design', 'Secondary brand color (dark navy)'),
('accent_color', '#FF6B00', 'color', 'design', 'Accent color (orange)'),
('free_shipping_threshold', '50.00', 'number', 'ecommerce', 'Free shipping on orders over'),
('tax_rate', '9.00', 'number', 'ecommerce', 'Tax rate percentage'),
('currency', 'USD', 'text', 'ecommerce', 'Currency code'),
('currency_symbol', '$', 'text', 'ecommerce', 'Currency symbol');

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================
CREATE INDEX idx_contact_status ON contact_messages(status);
CREATE INDEX idx_contact_created ON contact_messages(created_at DESC);
CREATE INDEX idx_orders_number ON orders(order_number);
CREATE INDEX idx_orders_status ON orders(order_status);
CREATE INDEX idx_orders_created ON orders(created_at DESC);
CREATE INDEX idx_portfolio_active ON portfolio(is_active, is_featured);
CREATE INDEX idx_portfolio_category ON portfolio(category);
CREATE INDEX idx_products_active ON products(is_active);
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_featured ON products(is_featured);
CREATE INDEX idx_services_active ON services(is_active);
CREATE INDEX idx_blog_status ON blog_posts(status);
CREATE INDEX idx_blog_published ON blog_posts(published_at DESC);

-- =====================================================
-- COMPLETED SUCCESSFULLY
-- =====================================================
