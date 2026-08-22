-- Create Database
CREATE DATABASE IF NOT EXISTS `inventory_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `inventory_management`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_code` VARCHAR(50) NOT NULL UNIQUE,
  `product_name` VARCHAR(150) NOT NULL,
  `category_id` INT NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  `minimum_stock` INT NOT NULL DEFAULT 10,
  `supplier` VARCHAR(100) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_products_categories`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Seeding Default Admin User
-- Password: admin123
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `password`, `full_name`) 
VALUES (1, 'admin', '$2y$10$Fv.OXYDGPB1oKvyR1ubKjOZqyAM3rNg4NZXlnJUrvgvCsGGCu7zQu', 'Administrator')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- --------------------------------------------------------
-- Seeding Initial Categories
-- --------------------------------------------------------
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Electronics', 'Devices, gadgets, and computational accessories.'),
(2, 'Grocery', 'Daily food items, beverages, and household consumables.'),
(3, 'Clothing', 'Apparel, footwear, and accessory garments.'),
(4, 'Stationery', 'Office supplies, books, pens, and paper products.'),
(5, 'Home & Kitchen', 'Furniture, appliances, cooking utensils, and home decor.')
ON DUPLICATE KEY UPDATE `name`=`name`;

-- --------------------------------------------------------
-- Seeding 10 Sample Products
-- --------------------------------------------------------
INSERT INTO `products` (`product_code`, `product_name`, `category_id`, `description`, `price`, `quantity`, `minimum_stock`, `supplier`) VALUES
-- Electronics
('ELEC-001', 'Logitech MX Master 3S Mouse', 1, 'Ergonomic wireless mouse with silent clicks and 8K DPI tracking.', 99.99, 15, 5, 'Logitech Distributor'),
('ELEC-002', 'Dell UltraSharp 27 Monitor', 1, '27-inch 4K USB-C Hub Monitor with IPS Panel.', 389.00, 3, 5, 'Dell Retail Solutions'), -- Low Stock (Qty 3 <= Min 5)
('ELEC-003', 'USB-C Cable 2m Braided', 1, 'High-speed data transfer and charging cable.', 12.50, 0, 10, 'Anker Inc.'), -- Out of Stock (Qty 0)

-- Grocery
('GROC-001', 'Organic Rolled Oats 1kg', 2, '100% whole grain organic rolled oats.', 4.50, 50, 15, 'Whole Foods Co.'),
('GROC-002', 'Pure Maple Syrup 500ml', 2, 'Grade A organic Canadian maple syrup.', 15.99, 8, 10, 'Maple Farms Ltd.'), -- Low Stock (Qty 8 <= Min 10)

-- Clothing
('CLOT-001', 'Classic Cotton T-Shirt L', 3, '100% combed cotton crew neck t-shirt in white.', 19.99, 45, 10, 'Apparel Source'),
('CLOT-002', 'Denim Jacket Navy M', 3, 'Premium denim jacket with button closures.', 59.99, 12, 5, 'Fashion House'),

-- Stationery
('STAT-001', 'A5 Dotted Journal Black', 4, 'Hardcover journal with 160 pages of 120gsm paper.', 14.99, 25, 5, 'Paper Goods Co.'),
('STAT-002', 'Gel Pen Pack of 12 Black', 4, 'Fine point smooth gel ink pens.', 8.99, 4, 10, 'Pilot Supplies Ltd.'), -- Low Stock (Qty 4 <= Min 10)

-- Home & Kitchen
('HOME-001', 'Stainless Steel Water Bottle', 5, 'Double-walled vacuum insulated bottle (750ml).', 24.99, 30, 8, 'Hydro Peak'),
('HOME-002', 'Digital Kitchen Scale', 5, 'High precision digital scale with tare function.', 18.50, 2, 5, 'Kitchen Gizmos')
ON DUPLICATE KEY UPDATE `product_code`=`product_code`;
