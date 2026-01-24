-- PulseTech Solutions Database Migration
-- Migration: 001_init_schema
-- Description: Initial database schema setup with all core tables
-- Date: 2025-01-24

-- ============================================
-- ADMINS TABLE - User authentication
-- ============================================
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `role` VARCHAR(50) DEFAULT 'admin',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PRODUCTS TABLE - E-commerce products
-- ============================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE,
  `description` TEXT DEFAULT NULL,
  `price_ugx` DECIMAL(12, 2) NOT NULL,
  `old_price_ugx` DECIMAL(12, 2) DEFAULT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `sku` VARCHAR(100) UNIQUE DEFAULT NULL,
  `stock_quantity` INT DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `discount` VARCHAR(50) DEFAULT NULL,
  `rating` DECIMAL(3, 2) DEFAULT 5.00,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_category` (`category`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ORDERS TABLE - Customer orders
-- ============================================
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) NOT NULL,
  `customer_phone` VARCHAR(50) DEFAULT NULL,
  `total_amount` DECIMAL(12, 2) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'pending',
  `payment_method` VARCHAR(100) DEFAULT NULL,
  `shipping_address` TEXT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_order_number` (`order_number`),
  KEY `idx_status` (`status`),
  KEY `idx_customer_email` (`customer_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ORDER_ITEMS TABLE - Products in orders
-- ============================================
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(12, 2) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(12, 2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PORTFOLIO TABLE - Project showcase
-- ============================================
CREATE TABLE IF NOT EXISTS `portfolio` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE,
  `client_name` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `year` SMALLINT DEFAULT NULL,
  `technologies` VARCHAR(255) DEFAULT NULL,
  `link_url` VARCHAR(255) DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_category` (`category`),
  KEY `idx_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TESTIMONIALS TABLE - Client reviews
-- ============================================
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `quote` TEXT NOT NULL,
  `author_name` VARCHAR(255) NOT NULL,
  `author_role` VARCHAR(255) DEFAULT NULL,
  `company` VARCHAR(255) DEFAULT NULL,
  `rating` TINYINT DEFAULT 5,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SERVICES TABLE - Service offerings
-- ============================================
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(100) DEFAULT NULL,
  `features` JSON DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_active` (`is_active`),
  KEY `idx_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BLOG_POSTS TABLE - Blog articles
-- ============================================
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE,
  `content` LONGTEXT DEFAULT NULL,
  `excerpt` TEXT DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `author` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `tags` VARCHAR(255) DEFAULT NULL,
  `is_published` TINYINT(1) DEFAULT 0,
  `published_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_published` (`is_published`),
  KEY `idx_category` (`category`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CONTACT_MESSAGES TABLE - Contact form submissions
-- ============================================
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `replied_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_email` (`email`),
  KEY `idx_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INQUIRIES TABLE - Service inquiries (legacy)
-- ============================================
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BRANDS TABLE - Client/Brand logos
-- ============================================
CREATE TABLE IF NOT EXISTS `brands` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `logo_url` VARCHAR(255) DEFAULT NULL,
  `website_url` VARCHAR(255) DEFAULT NULL,
  `display_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MIGRATIONS TABLE - Track applied migrations
-- ============================================
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `migration` VARCHAR(255) NOT NULL UNIQUE,
  `batch` INT NOT NULL,
  `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial migration record
INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('001_init_schema', 1);
