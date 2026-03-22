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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (Change password in production! Default is 'admin123')
INSERT IGNORE INTO `admins` (`username`, `password_hash`, `email`) VALUES
('fallsorigin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@fallsorigincoffee.com');

-- --------------------------------------------------------
-- Table: products
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `origin` varchar(100) NOT NULL DEFAULT 'Ethiopia',
  `type` enum('coffee','equipment','merch') NOT NULL DEFAULT 'coffee',
  `price` decimal(10,2) NOT NULL,
  `weight` varchar(50) NOT NULL DEFAULT '340g',
  `description` text NOT NULL,
  `image_url` varchar(255) NOT NULL DEFAULT 'assets/img/product_front.png',
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial products
INSERT IGNORE INTO `products` (`id`, `name`, `origin`, `price`, `weight`, `description`, `image_url`, `stock_quantity`, `is_active`) VALUES
(1, 'Yirgacheffe', 'Ethiopia', 28.00, '340g', 'Bright floral notes with a distinct lemony acidity and silk-like body.', 'assets/img/yirgacheffe.png', 100, 1),
(2, 'Sidamo', 'Ethiopia', 26.00, '340g', 'Deep berry-like flavors with a smooth chocolate finish and medium body.', 'assets/img/sidamo.png', 100, 1),
(3, 'Guji', 'Ethiopia', 32.00, '340g', 'Complex jasmine aroma with notes of sweet peach and a clean honey finish.', 'assets/img/guji.png', 100, 1);

-- --------------------------------------------------------
-- Table: orders
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` varchar(20) NOT NULL, -- e.g., ORD-2026-XXXX
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('New','Accepted','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'New',
  `payment_method` varchar(50) DEFAULT 'Card',
  `payment_status` enum('Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  `payment_intent_id` varchar(100) DEFAULT NULL,
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
