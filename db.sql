/*
SQLyog Community v13.3.0 (64 bit)
MySQL - 12.0.2-MariaDB : Database - ars_ecommerce
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`ars_ecommerce` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci */;

USE `ars_ecommerce`;

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `categories` */

insert  into `categories`(`id`,`name`,`slug`) values 
(1,'Electronics','electronics'),
(2,'Clothing','clothing'),
(3,'Home & Living','home-living');

/*Table structure for table `contact_submissions` */

DROP TABLE IF EXISTS `contact_submissions`;

CREATE TABLE `contact_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied') DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `contact_submissions` */

/*Table structure for table `coupons` */

DROP TABLE IF EXISTS `coupons`;

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `type` enum('fixed','percentage') DEFAULT 'fixed',
  `value` decimal(10,2) NOT NULL,
  `min_cart_amount` decimal(10,2) DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `coupons` */

/*Table structure for table `email_logs` */

DROP TABLE IF EXISTS `email_logs`;

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) DEFAULT NULL,
  `recipient` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `email_logs` */

insert  into `email_logs`(`id`,`queue_id`,`recipient`,`subject`,`status`,`error_message`,`sent_at`) values 
(1,1,'test@example.com','Test Email from ARS','sent',NULL,'2026-04-03 12:10:48'),
(2,2,'mind59024@gmail.com','Welcome to ARS Store, Test User!','sent',NULL,'2026-04-03 12:12:20'),
(3,3,'test@example.com','Test Email from ARS','sent',NULL,'2026-04-07 17:08:02'),
(4,4,'testuser@example.com','Welcome to ARS Store, Test User!','sent',NULL,'2026-04-07 17:08:07'),
(5,5,'mind59024@gmail.com','Reset Your ARS Store Password - Devbarat Prasad Patel','sent',NULL,'2026-04-07 17:08:12'),
(6,6,'mind59024@gmail.com','Reset Your ARS Store Password - Devbarat Prasad Patel','sent',NULL,'2026-04-07 17:08:17'),
(7,7,'mind59024@gmail.com','Order Confirmation #1','sent',NULL,'2026-04-07 17:08:22'),
(8,8,'pdewbrath@gmail.com','Welcome to ARS Store, Devbarat Prasad Patel!','sent',NULL,'2026-04-07 17:08:26'),
(9,9,'pdewbrath@gmail.com','Welcome to ARS Store, Devbarat Prasad Patel!','sent',NULL,'2026-04-07 17:08:31'),
(10,10,'pdewbrath@gmail.com','Welcome to ARS Store, Devbarat Prasad Patel!','sent',NULL,'2026-04-07 17:08:35'),
(11,11,'pdewbrath@gmail.com','Order Confirmation #2','sent',NULL,'2026-04-07 17:09:18'),
(12,12,'nepalcyberfirm@gmail.com','Welcome to ARS Store, Nepal Cyber Firm!','sent',NULL,'2026-04-08 09:39:23'),
(13,13,'nepalcyberfirm@gmail.com','Order Confirmation #1','sent',NULL,'2026-04-08 09:48:34');

/*Table structure for table `email_queue` */

DROP TABLE IF EXISTS `email_queue`;

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body_html` text NOT NULL,
  `status` enum('pending','sending','sent','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `error_message` text DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `scheduled_at` (`scheduled_at`),
  KEY `idx_email_queue_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `email_queue` */

insert  into `email_queue`(`id`,`recipient_email`,`recipient_name`,`subject`,`body_html`,`status`,`attempts`,`max_attempts`,`error_message`,`scheduled_at`,`sent_at`,`created_at`) values 
(1,'test@example.com','Test User','Test Email from ARS','<h1>Test</h1><p>This is a test email.</p>','sent',1,3,NULL,'2026-04-03 12:10:09','2026-04-03 12:10:48','2026-04-03 12:10:09'),
(2,'mind59024@gmail.com','Test User','Welcome to ARS Store, Test User!','<h1>Welcome!</h1><p>Hi Test User, thank you for registering at ARS Store. We are excited to have you!</p>','sent',1,3,NULL,'2026-04-03 06:27:10','2026-04-03 12:12:20','2026-04-03 12:12:10'),
(3,'test@example.com','Test User','Test Email from ARS','<h1>Test</h1><p>This is a test email.</p>','sent',1,3,NULL,'2026-04-03 18:20:51','2026-04-07 17:08:02','2026-04-03 18:20:51'),
(4,'testuser@example.com','Test User','Welcome to ARS Store, Test User!','<h1>Welcome!</h1><p>Hi Test User, thank you for registering at ARS Store. We are excited to have you!</p>','sent',1,3,NULL,'2026-04-03 12:36:21','2026-04-07 17:08:07','2026-04-03 18:21:21'),
(5,'mind59024@gmail.com','Devbarat Prasad Patel','Reset Your ARS Store Password - Devbarat Prasad Patel','<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;\">\n                <h2 style=\"color: #ea580c;\">Password Reset Request</h2>\n                <p>Hi <strong>Devbarat Prasad Patel</strong>,</p>\n                <p>We received a request to reset your password. Click the button below to create a new password:</p>\n                <p style=\"margin: 30px 0;\">\n                    <a href=\"http://localhost/ARS/auth/reset-password.php?token=e8f80ded63e7a1e12a7327c63c685fdaadbe820128616012264ce7a69d28df26\" style=\"background-color: #ea580c; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;\">Reset Password</a>\n                </p>\n                <p>Or copy this link: <a href=\"http://localhost/ARS/auth/reset-password.php?token=e8f80ded63e7a1e12a7327c63c685fdaadbe820128616012264ce7a69d28df26\">http://localhost/ARS/auth/reset-password.php?token=e8f80ded63e7a1e12a7327c63c685fdaadbe820128616012264ce7a69d28df26</a></p>\n                <p><small>This link expires in 1 hour. If you did not request this, please ignore this email.</small></p>\n                <hr style=\"border: none; border-top: 1px solid #eee; margin: 20px 0;\">\n                <p style=\"color: #666; font-size: 12px;\">ARS Store - Nepal</p>\n            </div>','sent',1,3,NULL,'2026-04-03 13:01:02','2026-04-07 17:08:12','2026-04-03 18:46:02'),
(6,'mind59024@gmail.com','Devbarat Prasad Patel','Reset Your ARS Store Password - Devbarat Prasad Patel','<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;\">\n                <h2 style=\"color: #ea580c;\">Password Reset Request</h2>\n                <p>Hi <strong>Devbarat Prasad Patel</strong>,</p>\n                <p>We received a request to reset your password. Click the button below to create a new password:</p>\n                <p style=\"margin: 30px 0;\">\n                    <a href=\"http://localhost/ARS/auth/reset-password.php?token=e14ba1eea82370de345c0777fd7ad2859263c1421cca1b5eb8beae6d47784b9c\" style=\"background-color: #ea580c; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;\">Reset Password</a>\n                </p>\n                <p>Or copy this link: <a href=\"http://localhost/ARS/auth/reset-password.php?token=e14ba1eea82370de345c0777fd7ad2859263c1421cca1b5eb8beae6d47784b9c\">http://localhost/ARS/auth/reset-password.php?token=e14ba1eea82370de345c0777fd7ad2859263c1421cca1b5eb8beae6d47784b9c</a></p>\n                <p><small>This link expires in 1 hour. If you did not request this, please ignore this email.</small></p>\n                <hr style=\"border: none; border-top: 1px solid #eee; margin: 20px 0;\">\n                <p style=\"color: #666; font-size: 12px;\">ARS Store - Nepal</p>\n            </div>','sent',1,3,NULL,'2026-04-03 13:03:30','2026-04-07 17:08:17','2026-04-03 18:48:30'),
(7,'mind59024@gmail.com','A.R.S','Order Confirmation #1','<h1>Thank you for your order!</h1><p>Hi A.R.S, your order #1 has been received and is being processed.</p><p>Total Amount: Rs.249</p>','sent',1,3,NULL,'2026-04-04 07:00:25','2026-04-07 17:08:22','2026-04-04 12:45:25'),
(8,'pdewbrath@gmail.com','Devbarat Prasad Patel','Welcome to ARS Store, Devbarat Prasad Patel!','<h1>Welcome!</h1><p>Hi Devbarat Prasad Patel, thank you for registering at ARS Store. We are excited to have you!</p>','sent',1,3,NULL,'2026-04-05 00:41:58','2026-04-07 17:08:26','2026-04-05 06:26:58'),
(9,'pdewbrath@gmail.com','Devbarat Prasad Patel','Welcome to ARS Store, Devbarat Prasad Patel!','<h1>Welcome!</h1><p>Hi Devbarat Prasad Patel, thank you for registering at ARS Store. We are excited to have you!</p>','sent',1,3,NULL,'2026-04-07 11:15:02','2026-04-07 17:08:31','2026-04-07 17:00:02'),
(10,'pdewbrath@gmail.com','Devbarat Prasad Patel','Welcome to ARS Store, Devbarat Prasad Patel!','<h1>Welcome!</h1><p>Hi Devbarat Prasad Patel, thank you for registering at ARS Store. We are excited to have you!</p>','sent',1,3,NULL,'2026-04-07 11:22:57','2026-04-07 17:08:35','2026-04-07 17:07:57'),
(11,'pdewbrath@gmail.com','Devbarat Prasad Patel','Order Confirmation #2','<h1>Thank you for your order!</h1><p>Hi Devbarat Prasad Patel, your order #2 has been received and is being processed.</p><p>Total Amount: Rs.249</p>','sent',1,3,NULL,'2026-04-07 11:24:13','2026-04-07 17:09:18','2026-04-07 17:09:13'),
(12,'nepalcyberfirm@gmail.com','Nepal Cyber Firm','Welcome to ARS Store, Nepal Cyber Firm!','<h1>Welcome!</h1><p>Hi Nepal Cyber Firm, thank you for registering at ARS Store. We are excited to have you!</p>','sent',1,3,NULL,'2026-04-08 03:54:17','2026-04-08 09:39:23','2026-04-08 09:39:17'),
(13,'nepalcyberfirm@gmail.com','Nepal Cyber Firm','Order Confirmation #1','<h1>Thank you for your order!</h1><p>Hi Nepal Cyber Firm, your order #1 has been received and is being processed.</p><p>Total Amount: Rs.8,994</p>','sent',1,3,NULL,'2026-04-08 04:03:29','2026-04-08 09:48:34','2026-04-08 09:48:29');

/*Table structure for table `email_templates` */

DROP TABLE IF EXISTS `email_templates`;

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content_html` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `email_templates` */

insert  into `email_templates`(`id`,`slug`,`subject`,`content_html`,`created_at`) values 
(1,'welcome_email','Welcome to ARS Store, {{name}}!','<h1>Welcome!</h1><p>Hi {{name}}, thank you for registering at ARS Store. We are excited to have you!</p>','2026-04-03 06:17:33'),
(2,'order_confirmation','Order Confirmation #{{order_id}}','<h1>Thank you for your order!</h1><p>Hi {{name}}, your order #{{order_id}} has been received and is being processed.</p><p>Total Amount: {{total}}</p>','2026-04-03 06:17:33'),
(5,'email_verification','Verify Your ARS Store Account - {{name}}','<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;\">\n                <h2 style=\"color: #ea580c;\">Welcome to ARS Store!</h2>\n                <p>Hi <strong>{{name}}</strong>,</p>\n                <p>Thank you for registering. Please verify your email address by clicking the button below:</p>\n                <p style=\"margin: 30px 0;\">\n                    <a href=\"{{verify_url}}\" style=\"background-color: #ea580c; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;\">Verify Email Address</a>\n                </p>\n                <p>Or copy this link: <a href=\"{{verify_url}}\">{{verify_url}}</a></p>\n                <p><small>This link expires in 24 hours.</small></p>\n                <hr style=\"border: none; border-top: 1px solid #eee; margin: 20px 0;\">\n                <p style=\"color: #666; font-size: 12px;\">ARS Store - Nepal</p>\n            </div>','2026-04-03 18:29:02'),
(6,'password_reset','Reset Your ARS Store Password - {{name}}','<div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;\">\n                <h2 style=\"color: #ea580c;\">Password Reset Request</h2>\n                <p>Hi <strong>{{name}}</strong>,</p>\n                <p>We received a request to reset your password. Click the button below to create a new password:</p>\n                <p style=\"margin: 30px 0;\">\n                    <a href=\"{{reset_url}}\" style=\"background-color: #ea580c; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;\">Reset Password</a>\n                </p>\n                <p>Or copy this link: <a href=\"{{reset_url}}\">{{reset_url}}</a></p>\n                <p><small>This link expires in 1 hour. If you did not request this, please ignore this email.</small></p>\n                <hr style=\"border: none; border-top: 1px solid #eee; margin: 20px 0;\">\n                <p style=\"color: #666; font-size: 12px;\">ARS Store - Nepal</p>\n            </div>','2026-04-03 18:31:51');

/*Table structure for table `order_items` */

DROP TABLE IF EXISTS `order_items`;

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `order_items` */

insert  into `order_items`(`id`,`order_id`,`product_id`,`quantity`,`price`) values 
(1,1,1,6,1499.00);

/*Table structure for table `orders` */

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `payment_method` enum('COD','eSewa','BankQR') NOT NULL,
  `payment_status` enum('Pending','Paid','Failed') DEFAULT 'Pending',
  `delivery_status` enum('Pending','Confirmed','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `current_location` varchar(255) DEFAULT 'Preparing for shipment',
  `location_updated_at` timestamp NULL DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `orders` */

insert  into `orders`(`id`,`user_id`,`total_amount`,`coupon_code`,`discount_amount`,`payment_method`,`payment_status`,`delivery_status`,`current_location`,`location_updated_at`,`transaction_id`,`payment_proof`,`address`,`notes`,`created_at`) values 
(1,2,8994.00,NULL,0.00,'COD','Pending','Confirmed','kailyaa',NULL,'','','Nepal Cyber Firm (9811144402) | nepalcyberfirm@gmail.com | Hamro Labs ,No. 13, Radhemai, Birgunj Metropolitan City Parsa District, Madhesh Province, Nepal Postal Code: 44300','','2026-04-08 09:48:29');

/*Table structure for table `product_images` */

DROP TABLE IF EXISTS `product_images`;

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `product_images` */

/*Table structure for table `product_reviews` */

DROP TABLE IF EXISTS `product_reviews`;

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `product_reviews` */

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `sku` (`sku`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `products` */

insert  into `products`(`id`,`name`,`slug`,`description`,`price`,`discount_price`,`category_id`,`stock`,`image`,`sku`,`is_featured`,`created_at`) values 
(1,'Apple ','apple-','This is an test product ',1500.00,1499.00,2,94,'products/prod_69d5d1b95bccc.jpg','APP852',1,'2026-04-08 09:40:37');

/*Table structure for table `reviews` */

DROP TABLE IF EXISTS `reviews`;

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `reviews` */

/*Table structure for table `settings` */

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `settings` */

insert  into `settings`(`id`,`setting_key`,`setting_value`) values 
(1,'store_name','ARS E-Commerce'),
(2,'store_email','support@ars.com'),
(3,'store_phone','9800000000'),
(4,'store_address','Kathmandu, Nepal'),
(5,'currency','Rs.'),
(6,'tax_percent','0'),
(7,'shipping_fee','100'),
(8,'cod_enabled','1'),
(9,'esewa_enabled','1'),
(10,'bank_qr_enabled','1');

/*Table structure for table `site_settings` */

DROP TABLE IF EXISTS `site_settings`;

CREATE TABLE `site_settings` (
  `key` varchar(100) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `site_settings` */

/*Table structure for table `user_sessions` */

DROP TABLE IF EXISTS `user_sessions`;

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_session` (`user_id`,`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `user_sessions` */

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`full_name`,`email`,`mobile`,`password`,`address`,`role`,`created_at`) values 
(1,'Admin User','admin@ars.com','9800000000','$2y$12$HyLE.XCEYhATd.NTO6HYyehUPUEHBHw.F9NZEgH8njv6/Vb.wxvv2',NULL,'admin','2026-04-07 22:33:24'),
(2,'Nepal Cyber Firm','nepalcyberfirm@gmail.com','9811144402','$2y$12$DuykQQSpV8fb2RwfFbizXe3WuqMwJF61aOQA5EGan9GSTA/Tvmeom','Hamro Labs ,No. 13, Radhemai, Birgunj Metropolitan City Parsa District, Madhesh Province, Nepal Postal Code: 44300','customer','2026-04-08 09:39:16');

/*Table structure for table `wishlist` */

DROP TABLE IF EXISTS `wishlist`;

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

/*Data for the table `wishlist` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
