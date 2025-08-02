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

-- This file is now redundant as `meattrack.sql` contains the full schema.
-- Use this file only for initial database setup or migrations if needed.

INSERT INTO system_settings (id, expiry_alerts, expiry_days_before, max_temp, min_temp, max_humidity, min_humidity, monitoring_alerts, email_alerts, email_recipients, sms_alerts, sms_recipients, spoilage_alert_threshold, inventory_low_threshold)
VALUES (1, 1, 3, 4.0, 0.0, 80, 65, 1@@@

System: The response was cut off mid-sentence. Here's the continuation and completion of the `init.sql` file, incorporating the necessary changes to align with the updated `meattrack.sql` schema and addressing the issues identified earlier:

```sql
, email_alerts, email_recipients, sms_alerts, sms_recipients, spoilage_alert_threshold, inventory_low_threshold)
VALUES (1, 1, 3, 4.0, 0.0, 80, 65, 1, 1, 'admin@meattrack.com,manager@meattrack.com', 0, NULL, 5, 10);

INSERT INTO meat_types (name, default_expiry_days) VALUES
('Beef', 7),
('Chicken', 5),
('Pork', 7),
('Lamb', 7),
('Turkey', 5);

INSERT INTO storage_locations (name, temperature_range, capacity_kg) VALUES
('Cold Room 1', '0-4°C', 1000.00),
('Cold Room 2', '0-4°C', 1000.00),
('Freezer 1', '-18°C', 800.00),
('Freezer 2', '-18°C', 800.00);

INSERT INTO users (name, email, password, role, status, created_at) VALUES
('Admin User', 'admin@meattrack.com', '$2y$10$0G6xK1zq6mY7y3Z8z9A1qO2x3y4z5A6B7C8D9E0F1G2H3I4J5K6L', 'admin', 'active', '2025-04-23 16:31:47'),
('Fahim Mahtab', 'fahimmahtab40@gmail.com', '$2y$10$p3SdGga/SocgHIvTMEI6V.4h1kAjxq/It4BvIjX7HPakE7igUkHRO', 'manager', 'active', '2025-04-23 16:43:55'),
('Abdullah', 'abdullah55@gmail.com', '$2y$10$4froNn.qYksKwvdMk3S6xOuY6Cll749UfHbnppNgdYhv3KqCQp0E2', 'manager', 'active', '2025-04-23 17:00:13'),
('Sadaf', 'sadaf55@gmail.com', '$2y$10$4JRJywg/EME41hP6K2BH9Orjb7u2B4ll6sPbDn/nIFDlrTLbSs6sq', 'viewer', 'active', '2025-04-23 17:02:16');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;