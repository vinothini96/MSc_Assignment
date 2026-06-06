-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2026 at 07:45 AM
-- Server version: 8.0.45
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `saree_shop_db`
--
CREATE DATABASE IF NOT EXISTS saree_shop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saree_shop_db;
-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `full_name`, `created_at`) VALUES
(1, 'admin', 'admin@elegancesarees.com', '$2y$10$ooQGlTIvP7kYc9EIbh7HIuNdu.F7u8quIaIMCxXpz1v59xws1Ew2K', 'Store Administrator', '2026-05-27 15:29:52');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `subtitle`, `image`, `link_url`, `discount_text`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Festive Collection 2026', 'Up to 30% off on silk sarees', 'hero_banner_1.jpg', 'shop.php?category=silk-sarees', '30% OFF', 1, 1, '2026-05-27 15:29:52'),
(2, 'Bridal Season Sale', 'Exclusive bridal sarees', 'hero_banner_2.jpg', 'shop.php?category=bridal-sarees', 'FLAT Rs.2000 OFF', 1, 2, '2026-05-27 15:29:52'),
(3, 'New Arrivals', 'Fresh designer drapes', 'hero_banner_3.jpg', 'shop.php?filter=new', 'NEW', 1, 3, '2026-05-27 15:29:52');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(1, 1, 9, 2, '2026-05-28 16:39:29');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `status`, `created_at`) VALUES
(1, 'Silk Sarees', 'silk-sarees', 'Luxurious pure silk sarees for every occasion.', NULL, 'active', '2026-05-27 15:29:52'),
(2, 'Cotton Sarees', 'cotton-sarees', 'Comfortable cotton sarees for daily wear.', NULL, 'active', '2026-05-27 15:29:52'),
(3, 'Designer Sarees', 'designer-sarees', 'Trendy designer collections from top labels.', NULL, 'active', '2026-05-27 15:29:52'),
(4, 'Bridal Sarees', 'bridal-sarees', 'Stunning bridal sarees for your special day.', NULL, 'active', '2026-05-27 15:29:52'),
(5, 'Banarasi Sarees', 'banarasi-sarees', 'Traditional Banarasi weave masterpieces.', NULL, 'active', '2026-05-27 15:29:52');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_points`
--

CREATE TABLE `loyalty_points` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `points` int NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loyalty_points`
--

INSERT INTO `loyalty_points` (`id`, `user_id`, `order_id`, `points`, `description`, `created_at`) VALUES
(1, 1, 1, 109, 'Points earned on order', '2026-05-27 15:29:52');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `shipping_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `district` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pincode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` enum('cod','online') COLLATE utf8mb4_unicode_ci DEFAULT 'cod',
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `discount_amount`, `shipping_address`, `city`, `district`, `pincode`, `phone`, `payment_method`, `status`, `notes`, `created_at`) VALUES
(1, 1, 'ES20260527001', 10999.00, 0.00, '12 MG Road', 'puthur', 'jaffna', '400001', '0745678900', 'cod', 'delivered', NULL, '2026-05-27 15:29:52');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 'Royal Kanjivaram Silk', 1, 10999.00, 10999.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `saree_type` enum('Silk','Cotton','Designer','Bridal','Banarasi','Chiffon','Georgette') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `stock` int DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `is_new_arrival` tinyint(1) DEFAULT '0',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `saree_type`, `price`, `discount_price`, `stock`, `image`, `is_featured`, `is_new_arrival`, `status`, `created_at`) VALUES
(1, 1, 'Royal Kanjivaram Silk', 'royal-kanjivaram-silk', 'Handwoven Kanjivaram silk with golden zari border. Perfect for weddings and festivals.', 'Silk', 12999.00, 10999.00, 25, 'silk_saree_1.jpg', 1, 0, 'active', '2026-05-27 15:29:52'),
(2, 1, 'Mysore Pure Silk Classic', 'mysore-pure-silk-classic', 'Soft Mysore silk in rich maroon with traditional motifs.', 'Silk', 8999.00, NULL, 30, 'silk_saree_1.jpg', 1, 1, 'active', '2026-05-27 15:29:52'),
(3, 2, 'Handloom Cotton Floral', 'handloom-cotton-floral', 'Breathable handloom cotton with delicate floral prints.', 'Cotton', 2499.00, 1999.00, 50, 'cotton_saree.jpg', 0, 1, 'active', '2026-05-27 15:29:52'),
(4, 2, 'Daily Wear Cotton Stripe', 'daily-wear-cotton-stripe', 'Lightweight striped cotton saree for office and casual wear.', 'Cotton', 1799.00, NULL, 40, 'cotton_saree.jpg', 0, 0, 'active', '2026-05-27 15:29:52'),
(5, 3, 'Designer Embroidered Net', 'designer-embroidered-net', 'Contemporary net saree with sequin embroidery and blouse piece.', 'Designer', 15999.00, 13999.00, 15, 'designer_saree.jpg', 1, 1, 'active', '2026-05-27 15:29:52'),
(6, 3, 'Bollywood Style Georgette', 'bollywood-style-georgette', 'Flowing georgette saree inspired by celebrity drapes.', 'Georgette', 6999.00, 5999.00, 20, 'designer_saree.jpg', 1, 0, 'active', '2026-05-27 15:29:52'),
(7, 4, 'Bridal Red Zari Masterpiece', 'bridal-red-zari-masterpiece', 'Heavy bridal saree with all-over zari work and contrast pallu.', 'Bridal', 24999.00, 21999.00, 10, 'bridal_saree.jpg', 1, 0, 'active', '2026-05-27 15:29:52'),
(8, 4, 'Bridal Pink Banarasi', 'bridal-pink-banarasi', 'Elegant pink Banarasi bridal saree with peacock motifs.', 'Bridal', 18999.00, NULL, 8, 'bridal_saree.jpg', 0, 1, 'active', '2026-05-27 15:29:52'),
(9, 5, 'Banarasi Brocade Gold', 'banarasi-brocade-gold', 'Authentic Varanasi brocade in gold and cream.', 'Banarasi', 14999.00, 12999.00, 18, 'silk_saree_1.jpg', 1, 0, 'active', '2026-05-27 15:29:52'),
(10, 5, 'Banarasi Teal Heritage', 'banarasi-teal-heritage', 'Heritage weave Banarasi in deep teal with silver zari.', 'Banarasi', 11999.00, NULL, 22, 'silk_saree_1.jpg', 0, 0, 'active', '2026-05-27 15:29:52'),
(11, 1, 'Chiffon Party Wear Pink', 'hiffon-arty-ear-ink', 'Light chiffon saree ideal for parties and receptions.', 'Chiffon', 3999.00, 3499.00, 35, 'hiffon-arty-ear-ink_1780026667.jpg', 0, 1, 'active', '2026-05-27 15:29:52'),
(12, 2, 'Organic Cotton Natural', 'organic-cotton-natural', 'Eco-friendly organic cotton in natural dyes.', 'Cotton', 2999.00, NULL, 45, 'cotton_saree.jpg', 0, 0, 'active', '2026-05-27 15:29:52'),
(14, 5, 'Silk Banarasi', 'ilk-anarasi', 'Test', 'Silk', 4500.00, 4000.00, 8, 'est-ilk_1780027165.jpg', 0, 1, 'active', '2026-05-29 03:52:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pincode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loyalty_points` int DEFAULT '0',
  `status` enum('active','blocked') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `address`, `city`, `district`, `pincode`, `loyalty_points`, `status`, `created_at`) VALUES
(1, 'Priya Kumar', 'priya@example.com', '0778082300', '$2y$10$Slwr.w8d8cPhe.4q7t8Bx.CbnR2Tcj05elqEUE792V07Yv4rX8OOi', '12 MG Road', 'Kinniya', 'Trincomalee', '400001', 150, 'active', '2026-05-27 15:29:52'),
(2, 'Vinothini', 'vinothini.s@gmail.com', '0778082394', '$2y$10$U5G0lTv.6pIYRrE4s9LtkeT/q3jy3YTONz9u.TvJrhmGPknet7HlS', '87', 'Sevenapity', 'Polonnaruwa', '54000', 0, 'active', '2026-05-28 17:35:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD CONSTRAINT `loyalty_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loyalty_points_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
