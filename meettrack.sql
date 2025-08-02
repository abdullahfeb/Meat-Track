-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 25, 2025
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `meattrack`
--

CREATE DATABASE IF NOT EXISTS meattrack;
USE meattrack;

--
-- Table structure for table `condition_monitoring`
--

CREATE TABLE `condition_monitoring` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `storage_location_id` INT(11) NOT NULL,
  `temperature` DECIMAL(5,2) NOT NULL,
  `humidity` DECIMAL(5,2) NOT NULL,
  `remarks` TEXT,
  `recorded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `storage_location_id` (`storage_location_id`),
  CONSTRAINT `condition_monitoring_storage_location` FOREIGN KEY (`storage_location_id`) REFERENCES `storage_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `condition_monitoring` (`id`, `storage_location_id`, `temperature`, `humidity`, `remarks`, `recorded_at`) VALUES
(2, 2, 5.00, 5.00, NULL, '2025-05-06 19:54:28'),
(3, 2, 50.00, 80.00, NULL, '2025-05-06 19:54:55'),
(4, 1, 4.00, 70.00, NULL, '2025-05-06 19:55:19');

--
-- Table structure for table `dismissed_alerts`
--

CREATE TABLE `dismissed_alerts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `alert_identifier` VARCHAR(255) NOT NULL,
  `dismissed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_alert` (`user_id`, `alert_identifier`),
  CONSTRAINT `dismissed_alerts_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `dismissed_alerts` (`id`, `user_id`, `alert_identifier`, `dismissed_at`) VALUES
(1, 1, 'inv_5', '2025-05-06 21:40:56'),
(2, 1, 'inv_6', '2025-05-06 21:40:59'),
(3, 1, 'inv_3', '2025-05-06 21:41:00'),
(4, 1, 'inv_1', '2025-05-06 21:41:01'),
(5, 1, 'inv_2', '2025-05-06 21:41:01'),
(6, 1, 'cond_4', '2025-05-06 21:41:02'),
(7, 1, 'cond_3', '2025-05-06 21:41:03'),
(8, 1, 'cond_2', '2025-05-06 21:41:04'),
(9, 1, 'inv_8', '2025-05-06 21:41:06');

--
-- Table structure for table `distribution`
--

CREATE TABLE `distribution` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `delivery_id` VARCHAR(50) NOT NULL,
  `destination` VARCHAR(255) NOT NULL,
  `scheduled_datetime` DATETIME NOT NULL,
  `vehicle` VARCHAR(100) DEFAULT NULL,
  `driver` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'in_transit', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
  `completed_at` DATETIME DEFAULT NULL,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `delivery_id` (`delivery_id`),
  CONSTRAINT `distribution_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `distribution` (`id`, `delivery_id`, `destination`, `scheduled_datetime`, `vehicle`, `driver`, `status`, `completed_at`, `created_by`, `created_at`) VALUES
(1, 'DL-2025-04-23-784', 'Dhaka', '2025-04-23 18:39:00', 'Truck', 'Sadaf', 'delivered', '2025-04-23 22:42:56', 1, '2025-04-23 16:40:00'),
(2, 'DL-2025-04-23-436', 'Dhaka', '2025-04-23 18:42:00', 'Truck', 'Sadaf', 'cancelled', NULL, 1, '2025-04-23 16:42:48'),
(3, 'DL-2025-04-26-091', 'Dhaka', '2025-04-26 17:07:00', 'Truck', 'Sadaf', 'delivered', '2025-04-26 21:08:04', 1, '2025-04-26 15:07:56');

--
-- Table structure for table `distribution_items`
--

CREATE TABLE `distribution_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `distribution_id` INT(11) NOT NULL,
  `inventory_id` INT(11) NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `distribution_id` (`distribution_id`),
  KEY `inventory_id` (`inventory_id`),
  CONSTRAINT `distribution_items_distribution_id` FOREIGN KEY (`distribution_id`) REFERENCES `distribution` (`id`) ON DELETE CASCADE,
  CONSTRAINT `distribution_items_inventory_id` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `distribution_items` (`id`, `distribution_id`, `inventory_id`, `quantity`) VALUES
(1, 1, 1, 2.00),
(2, 2, 2, 25.00),
(3, 3, 6, 20.00);

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `batch_number` VARCHAR(50) NOT NULL,
  `meat_type_id` INT(11) NOT NULL,
  `cut_type` VARCHAR(100) DEFAULT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `processing_date` DATE NOT NULL,
  `expiry_date` DATE NOT NULL,
  `storage_location_id` INT(11) NOT NULL,
  `quality_notes` TEXT DEFAULT NULL,
  `status` ENUM('good', 'near_expiry', 'spoiled', 'reserved', 'distributed') NOT NULL DEFAULT 'good',
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `batch_number` (`batch_number`),
  KEY `meat_type_id` (`meat_type_id`),
  KEY `storage_location_id` (`storage_location_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `inventory_meat_type_id` FOREIGN KEY (`meat_type_id`) REFERENCES `meat_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_storage_location_id` FOREIGN KEY (`storage_location_id`) REFERENCES `storage_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `inventory` (`id`, `batch_number`, `meat_type_id`, `cut_type`, `quantity`, `processing_date`, `expiry_date`, `storage_location_id`, `quality_notes`, `status`, `created_by`, `created_at`) VALUES
(1, 'MT-2025-04-23-022', 1, 'Loin', 3.00, '2025-04-23', '2025-04-30', 2, 'A Grade', 'spoiled', 1, '2025-04-23 16:39:16'),
(2, 'MT-2025-04-23-063', 1, 'Loin', 25.00, '2025-04-23', '2025-04-30', 3, 'A+', 'spoiled', 1, '2025-04-23 16:41:47'),
(3, 'MT-2025-04-23-840', 2, 'Breast', 25.00, '2025-04-23', '2025-04-29', 4, 'b+', 'spoiled', 1, '2025-04-23 16:48:39'),
(5, 'MT-2025-04-23-400', 4, NULL, 10.00, '2025-04-23', '2025-04-24', 1, 'b+', 'spoiled', 1, '2025-04-23 16:50:49'),
(6, 'MT-2025-04-25-176', 2, NULL, 30.00, '2025-04-25', '2025-04-25', 1, NULL, 'spoiled', 1, '2025-04-25 11:10:43'),
(8, 'MT-2025-05-06-845', 1, NULL, 100.00, '2025-05-06', '2025-05-07', 3, 'A+', 'good', 1, '2025-05-06 20:37:29');

--
-- Table structure for table `loss_records`
--

CREATE TABLE `loss_records` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `inventory_id` INT(11) DEFAULT NULL,
  `meat_type_id` INT(11) NOT NULL,
  `stage` ENUM('slaughter', 'processing', 'storage', 'handling', 'transport', 'retail') NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `reason` TEXT NOT NULL,
  `action_taken` TEXT DEFAULT NULL,
  `created_by` INT NOT NULL,
  `recorded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inventory_id` (`inventory_id`),
  KEY `meat_type_id` (`meat_type_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `loss_records_inventory_id` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE SET NULL,
  CONSTRAINT `loss_records_meat_type_id` FOREIGN KEY (`meat_type_id`) REFERENCES `meat_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loss_records_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `loss_records` (`id`, `inventory_id`, `meat_type_id`, `stage`, `quantity`, `reason`, `action_taken`, `created_by`, `recorded_at`) VALUES
(1, 6, 1, 'processing', 50.00, 'spoilage', 'yes', 1, '2025-05-06 19:36:11'),
(2, 3, 2, 'storage', 15.00, 'Expired', 'yes', 1, '2025-05-06 19:45:44');

--
-- Table structure for table `meat_types`
--

CREATE TABLE `meat_types` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `default_expiry_days` INT(11) NOT NULL DEFAULT 7,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `meat_types` (`id`, `name`, `default_expiry_days`) VALUES
(1, 'Beef', 7),
(2, 'Chicken', 5),
(3, 'Pork', 7),
(4, 'Lamb', 7),
(5, 'Turkey', 5);

--
-- Table structure for table `spoilage`
--

CREATE TABLE `spoilage` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `inventory_id` INT(11) DEFAULT NULL,
  `batch_number` VARCHAR(50) NOT NULL,
  `meat_type_id` INT(11) NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `processing_date` DATE NOT NULL,
  `storage_location_id` INT(11) NOT NULL,
  `reason` ENUM('temperature fluctuation', 'expired', 'contamination', 'improper handling', 'packaging failure', 'other') NOT NULL,
  `disposal_method` ENUM('incineration', 'landfill', 'rendering', 'composting', 'other') NOT NULL,
  `recorded_by` INT(11) NOT NULL,
  `recorded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inventory_id` (`inventory_id`),
  KEY `meat_type_id` (`meat_type_id`),
  KEY `storage_location_id` (`storage_location_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `spoilage_inventory_id` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE SET NULL,
  CONSTRAINT `spoilage_meat_type_id` FOREIGN KEY (`meat_type_id`) REFERENCES `meat_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `spoilage_storage_location_id` FOREIGN KEY (`storage_location_id`) REFERENCES `storage_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `spoilage_recorded_by` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `spoilage` (`id`, `inventory_id`, `batch_number`, `meat_type_id`, `quantity`, `processing_date`, `storage_location_id`, `reason`, `disposal_method`, `recorded_by`, `recorded_at`) VALUES
(1, 5, 'MT-2025-04-23-400', 1, 10.00, '2025-04-23', 1, 'expired', 'incineration', 1, '2025-05-06 21:01:56');

--
-- Table structure for table `storage_locations`
--

CREATE TABLE `storage_locations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `temperature_range` VARCHAR(50) NOT NULL,
  `capacity_kg` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `storage_locations` (`id`, `name`, `temperature_range`, `capacity_kg`) VALUES
(1, 'Cold Room 1', '0-4°C', 1000.00),
(2, 'Cold Room 2', '0-4°C', 1000.00),
(3, 'Freezer 1', '-18°C', 800.00),
(4, 'Freezer 2', '-18°C', 800.00);

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `expiry_alerts` TINYINT(1) DEFAULT 1,
  `expiry_days_before` INT(11) DEFAULT 3,
  `max_temp` DECIMAL(5,1) DEFAULT 4.0,
  `min_temp` DECIMAL(5,1) DEFAULT 0.0,
  `max_humidity` INT(11) DEFAULT 80,
  `min_humidity` INT(11) DEFAULT 65,
  `monitoring_alerts` TINYINT(1) DEFAULT 1,
  `email_alerts` TINYINT(1) DEFAULT 1,
  `email_recipients` TEXT DEFAULT NULL,
  `sms_alerts` TINYINT(1) DEFAULT 0,
  `sms_recipients` TEXT DEFAULT NULL,
  `spoilage_alert_threshold` INT(11) DEFAULT 5,
  `inventory_low_threshold` INT(11) DEFAULT 10,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `system_settings` (`id`, `expiry_alerts`, `expiry_days_before`, `max_temp`, `min_temp`, `max_humidity`, `min_humidity`, `monitoring_alerts`, `email_alerts`, `email_recipients`, `sms_alerts`, `sms_recipients`, `spoilage_alert_threshold`, `inventory_low_threshold`) VALUES
(1, 1, 3, 4.0, 0.0, 80, 65, 1, 1, 'admin@meattrack.com,manager@meattrack.com', 0, NULL, 5, 10);

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'manager', 'supervisor', 'operator', 'viewer') NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 'Admin User', 'admin@meattrack.com', '$2y$10$0G6xK1zq6mY7y3Z8z9A1qO2x3y4z5A6B7C8D9E0F1G2H3I4J5K6L', 'admin', 'active', NULL, '2025-04-23 16:31:47'),
(2, 'Fahim Mahtab', 'fahimmahtab40@gmail.com', '$2y$10$p3SdGga/SocgHIvTMEI6V.4h1kAjxq/It4BvIjX7HPakE7igUkHRO', 'manager', 'active', NULL, '2025-04-23 16:43:55'),
(3, 'Abdullah', 'abdullah55@gmail.com', '$2y$10$4froNn.qYksKwvdMk3S6xOuY6Cll749UfHbnppNgdYhv3KqCQp0E2', 'manager', 'active', NULL, '2025-04-23 17:00:13'),
(4, 'Sadaf', 'sadaf55@gmail.com', '$2y$10$4JRJywg/EME41hP6K2BH9Orjb7u2B4ll6sPbDn/nIFDlrTLbSs6sq', 'viewer', 'active', NULL, '2025-04-23 17:02:16');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;