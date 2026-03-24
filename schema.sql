-- Falls Origin Coffee E-commerce Schema
-- Designed for MySQL / MariaDB on Namecheap Shared Hosting

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table: admins
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (Username: admin@fallscoffee.ca / Password: FallsCoffee#2026)
INSERT IGNORE INTO `admins` (`username`, `password_hash`, `email`) VALUES
('admin@fallscoffee.ca', '$2y$10$Vg4VW.nvdy5tNUJ8ykFZJOmiJHJGkxrZYu6D0cQPW82kmPQVZXLZq', 'admin@fallscoffee.ca');

-- --------------------------------------------------------
-- Table: products
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `origin` varchar(100) NOT NULL DEFAULT 'Ethiopia',
  `type` enum('coffee','equipment','merch') NOT NULL DEFAULT 'coffee',
  `price` decimal(10,2) NOT NULL,
  `weight` varchar(50) NOT NULL DEFAULT '340g',
  `description` text NOT NULL,
  `tasting_notes` varchar(255) DEFAULT NULL,
  `brewing_suggestions` text DEFAULT NULL,
  `origin_story` text DEFAULT NULL,
  `image_url` varchar(255) NOT NULL DEFAULT 'assets/img/product_front.png',
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial products
INSERT IGNORE INTO `products` (`id`, `name`, `slug`, `origin`, `price`, `weight`, `description`, `tasting_notes`, `image_url`, `stock_quantity`, `is_active`, `is_featured`) VALUES
(1, 'Yirgacheffe', 'yirgacheffe', 'Ethiopia', 28.00, '340g', 'Bright floral notes with a distinct lemony acidity and silk-like body.', 'Jasmine, Bergamot, Blueberry', 'assets/img/yirgacheffe.png', 100, 1, 1),
(2, 'Sidamo', 'sidamo', 'Ethiopia', 26.00, '340g', 'Deep berry-like flavors with a smooth chocolate finish and medium body.', 'Dark Chocolate, Blackberry, Maple', 'assets/img/sidamo.png', 100, 1, 0),
(3, 'Guji', 'guji', 'Ethiopia', 32.00, '340g', 'Complex jasmine aroma with notes of sweet peach and a clean honey finish.', 'Peach, Honey, Jasmine', 'assets/img/guji.png', 100, 1, 0);

-- --------------------------------------------------------
-- Table: orders
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` varchar(20) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `customer_email` varchar(150) DEFAULT NULL, -- kept for compatibility
  `customer_phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL, -- kept for compatibility
  `total` decimal(10,2) NOT NULL,
  `status` enum('New','Accepted','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'New',
  `payment_method` varchar(50) DEFAULT 'Card',
  `payment_status` enum('Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  `payment_intent_id` varchar(100) DEFAULT NULL,
  `tracking_number` varchar(150) DEFAULT NULL,
  `carrier` varchar(100) DEFAULT NULL,
  `eta` varchar(100) DEFAULT NULL,
  `delivery_signature` varchar(255) DEFAULT NULL,
  `items` longtext DEFAULT NULL,
  `review_email_sent` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: order_items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(20) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: tracking_details
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tracking_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(20) NOT NULL,
  `carrier` varchar(100) NOT NULL,
  `tracking_number` varchar(150) NOT NULL,
  `tracking_url` varchar(255) DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: reviews
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `rating` int(1) NOT NULL DEFAULT 5,
  `comment` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Single Origin', 'single-origin'),
(2, 'Blends', 'blends'),
(3, 'Limited Edition', 'limited-edition');

-- --------------------------------------------------------
-- Table: admin_audit_logs
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_user` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: contact_messages
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Unread','Read','Replied') NOT NULL DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: settings
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('store_status', 'active'),
('currency', 'CAD');

COMMIT;
