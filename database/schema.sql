-- Production Schema for Falls Origin Coffee (MySQL 8.0+)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

-- Admin Users
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products Registry
CREATE TABLE IF NOT EXISTS `products` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `tasting_notes` text,
  `brewing_suggestions` text,
  `origin_story` text,
  `price` decimal(10,2) NOT NULL,
  `type` varchar(50) DEFAULT 'Single Origin',
  `origin` varchar(100) DEFAULT NULL,
  `weight` varchar(20) DEFAULT '340g',
  `image_url` varchar(255) DEFAULT NULL,
  `roast_intensity` int DEFAULT 3,
  `stock_quantity` int DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders Master
CREATE TABLE IF NOT EXISTS `orders` (
  `id` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(50) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Paid',
  `tracking_token` varchar(64) NOT NULL,
  `carrier` varchar(50) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `eta` varchar(100) DEFAULT NULL,
  `delivery_method` varchar(50) DEFAULT 'Standard',
  `delivery_signature` text DEFAULT NULL,
  `items` json NOT NULL,
  `stripe_payment_intent_id` varchar(100) DEFAULT NULL,
  `review_email_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_token` (`tracking_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Public Sentiment (Reviews)
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Taxonomy
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product Catalog
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `weight` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `tasting_notes` text DEFAULT NULL,
  `brewing_suggestions` text DEFAULT NULL,
  `origin_story` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inbox (Contact Messages)
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(20) DEFAULT 'unread',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System Audit Logs
CREATE TABLE IF NOT EXISTS `admin_audit_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_user` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial Seed Data
INSERT IGNORE INTO `products` (`id`, `name`, `description`, `price`, `type`, `origin`, `weight`, `image_url`, `roast_intensity`) VALUES
('ETH-001', 'Yirgacheffe Heirloom', 'Floral notes with citrus acidity and a clean, silky finish.', 28.50, 'Single Origin', 'Ethiopia', '340g', 'assets/img/falls-origin-premium-packaging.png', 2),
('COL-002', 'Huila Reserve', 'Deep caramel sweetness with cherry and milk chocolate undertones.', 32.00, 'Reserve', 'Colombia', '340g', 'assets/img/falls-origin-premium-packaging.png', 4);

COMMIT;
