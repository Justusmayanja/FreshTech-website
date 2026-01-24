-- PulseTech Solutions Database Migration
-- Migration: 002_seed_sample_data
-- Description: Insert sample data for testing
-- Date: 2025-01-24

-- ============================================
-- SEED ADMIN USER
-- ============================================
INSERT IGNORE INTO `admins` (`username`, `password_hash`, `name`, `email`, `role`) VALUES 
('admin', '$2y$10$5lUqrxs8N6U9v2Z7K8H6M.zN8Q7P9R6K5L4M3N2O1P0Q9R8S7T6U5', 'Admin User', 'admin@pulsetech.local', 'admin');

-- ============================================
-- SEED PRODUCTS
-- ============================================
INSERT IGNORE INTO `products` (`name`, `slug`, `description`, `price_ugx`, `old_price_ugx`, `category`, `stock_quantity`, `is_featured`, `discount`, `rating`) VALUES
('65W GaN Fast Charger', '65w-gan-charger', 'Dual USB-C, compact, travel ready', 165000, 185000, 'chargers', 50, 1, '10% OFF', 4.50),
('Crystal ANC Earbuds', 'crystal-anc-earbuds', '42h battery life, clear calls, active noise cancellation', 340000, 380000, 'earbuds', 30, 1, 'Bundle', 5.00),
('Pulse Smart Watch', 'pulse-smartwatch', 'AMOLED display, fitness tracking, NFC payments', 490000, 520000, 'wearables', 20, 1, NULL, 4.80),
('USB-C Hub Pro', 'usb-c-hub-pro', '7-in-1 multiport hub with ethernet and HDMI', 220000, NULL, 'accessories', 45, 0, NULL, 4.20),
('Wireless Keyboard', 'wireless-keyboard', 'Mechanical switches, RGB backlight, silent mode', 180000, 200000, 'peripherals', 60, 0, '10% OFF', 4.60);

-- ============================================
-- SEED SERVICES
-- ============================================
INSERT IGNORE INTO `services` (`title`, `slug`, `description`, `icon`, `display_order`, `is_active`) VALUES
('Website Development', 'website-development', 'High-performance sites built with modern frameworks. SEO optimized and lightning fast.', 'fas fa-globe', 1, 1),
('Graphic Design & Branding', 'graphic-branding', 'Logo design, brand guidelines, and visual assets tailored to your market.', 'fas fa-palette', 2, 1),
('UI/UX Design', 'ui-ux-design', 'User-centered design systems and interactive prototypes that convert.', 'fas fa-pencil-ruler', 3, 1),
('E-commerce Solutions', 'ecommerce-solutions', 'Shopify and headless commerce with localized payments and fulfillment.', 'fas fa-shopping-cart', 4, 1);

-- ============================================
-- SEED PORTFOLIO ITEMS
-- ============================================
INSERT IGNORE INTO `portfolio` (`title`, `slug`, `client_name`, `category`, `description`, `year`, `technologies`) VALUES
('Center for Tomorrow', 'center-for-tomorrow', 'NGO Kampala', 'web', 'Community impact platform with donor journey optimization', 2024, 'Next.js, Tailwind CSS'),
('Julie Crafts', 'julie-crafts', 'Julie Crafts', 'ecommerce', 'E-commerce store for traditional crafts with Kampala checkout', 2024, 'Shopify, Liquid'),
('Kampala Health Platform', 'kampala-health', 'Kampala Medical', 'web', 'Patient portal with clinic directories and appointment bookings', 2023, 'React, Firebase'),
('Kla Streetwear', 'kla-streetwear', 'Kla Collective', 'branding', 'Bold visual identity system for clothing collective', 2024, 'Figma, Adobe Suite'),
('Gadget Hub Kampala', 'gadget-hub', 'Gadget Hub', 'ecommerce', 'Phone and accessories shop with localized payment integration', 2023, 'WooCommerce, PHP'),
('Nile Artisan Market', 'nile-artisan', 'Nile Arts', 'branding', 'Complete brand kit and landing page for artisan collective', 2023, 'Webflow');

-- ============================================
-- SEED TESTIMONIALS
-- ============================================
INSERT IGNORE INTO `testimonials` (`quote`, `author_name`, `author_role`, `company`, `rating`, `is_active`) VALUES
('PulseTech transformed our online presence. Their attention to detail and understanding of our market was exceptional.', 'Sarah Mutesi', 'CEO', 'Julie Crafts', 5, 1),
('The team delivered our project on time and exceeded expectations. Highly recommend!', 'David Kabuye', 'Founder', 'Kla Streetwear', 5, 1),
('Professional, responsive, and truly talented designers. Worth every shilling.', 'Jane Namata', 'Marketing Manager', 'Kampala Health', 4, 1),
('Best investment we made for our startup. The branding and website are perfect.', 'Brian Nakimuli', 'Co-founder', 'TechStart Kampala', 5, 1);

-- ============================================
-- SEED BRANDS
-- ============================================
INSERT IGNORE INTO `brands` (`name`, `logo_url`, `display_order`, `is_active`) VALUES
('Julie Crafts', '/images/brands/julie-crafts.png', 1, 1),
('Kla Streetwear', '/images/brands/kla-streetwear.png', 2, 1),
('Kampala Health', '/images/brands/kampala-health.png', 3, 1),
('Gadget Hub', '/images/brands/gadget-hub.png', 4, 1);

-- Update migration record
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('002_seed_sample_data', 1) ON DUPLICATE KEY UPDATE `batch` = VALUES(`batch`);
