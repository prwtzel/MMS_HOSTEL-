-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2026 at 05:18 AM
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
-- Database: `mms_hostel`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password_hash`, `created_at`, `role`, `is_active`) VALUES
(1, 'MMS Administrator', 'admin@mmshostel.local', '$2y$12$k1g0qKKYbqGnVDd7jKyo1u1Np0ZiPtWbHec4gkdhqQNl6UV50LrX.', '2026-10-22 12:42:30', 'admin', 1),
(2, 'fritchel', 'fritchel7flores@gmail.com', '$2y$10$SHM68h8hwc.78ASlNNA9suTUg5ZKz2m.QjOeJHlwtnb/Xh.QEpRbu', '2026-10-22 13:42:59', 'staff', 1);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_code` varchar(30) NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `guest_name` varchar(160) NOT NULL,
  `guest_email` varchar(190) NOT NULL,
  `guest_phone` varchar(50) DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests` tinyint(3) UNSIGNED NOT NULL,
  `nights` smallint(5) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('pay_at_hotel','online_demo') NOT NULL DEFAULT 'pay_at_hotel',
  `payment_status` enum('pay_at_hotel','pending_demo','demo_completed') NOT NULL DEFAULT 'pay_at_hotel',
  `status` enum('confirmed','checked_in','completed','cancelled') NOT NULL DEFAULT 'confirmed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_reference` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_code`, `room_id`, `guest_name`, `guest_email`, `guest_phone`, `check_in`, `check_out`, `guests`, `nights`, `total_amount`, `payment_method`, `payment_status`, `status`, `created_at`, `payment_reference`) VALUES
(1, 'MMS-F8AA8347', 5, 'flores', 'fritchel7flores@gmail.com', '09244358938', '2026-10-23', '2026-10-24', 1, 1, 2100.00, 'online_demo', 'demo_completed', 'confirmed', '2026-10-22 13:46:13', NULL),
(2, 'MMS-D51D09A3', 3, 'plores', 'fritchel7flores@gmail.com', '09244358938', '2026-10-22', '2026-10-23', 2, 1, 2600.00, 'pay_at_hotel', 'pay_at_hotel', 'checked_in', '2026-10-22 13:53:59', NULL),
(3, 'MMS-1B195B99', 4, 'flores,fritchel', 'fritchel7flores@gmail.com', '09244358938', '2026-10-22', '2026-10-23', 1, 1, 850.00, '', '', 'confirmed', '2026-10-22 14:21:03', NULL),
(4, 'MMS-9C786DD2', 2, 'bantaya', 'bantayajohnlester2@gmail.com', '', '2026-10-22', '2026-10-23', 2, 1, 3200.00, '', '', 'checked_in', '2026-10-22 14:26:22', NULL),
(5, 'MMS-4BB55C89', 2, 'flores', 'fritchel7flores@gmail.com', '09244358938', '2026-10-24', '2026-10-25', 1, 1, 3200.00, '', '', 'checked_in', '2026-10-22 15:50:44', NULL),
(6, 'MMS-2524627E', 4, 'flores', 'fritchel7flores@gmail.com', '09244358938', '2026-10-25', '2026-10-26', 1, 1, 850.00, '', '', '', '2026-10-22 16:04:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_records`
--

CREATE TABLE `payment_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('cash','gcash_manual','bank_transfer','other') NOT NULL DEFAULT 'cash',
  `reference_code` varchar(120) DEFAULT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `notes` varchar(500) DEFAULT NULL,
  `recorded_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_records`
--

INSERT INTO `payment_records` (`id`, `booking_id`, `amount`, `method`, `reference_code`, `status`, `notes`, `recorded_by`, `verified_by`, `created_at`, `verified_at`) VALUES
(1, 2, 2600.00, 'cash', NULL, 'pending', 'tyututu', 2, NULL, '2026-10-22 13:55:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `room_type` enum('private','shared') NOT NULL DEFAULT 'private',
  `price_per_night` decimal(10,2) NOT NULL,
  `capacity` tinyint(3) UNSIGNED NOT NULL DEFAULT 2,
  `bed_info` varchar(160) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `amenities` varchar(500) NOT NULL DEFAULT '',
  `image_url` varchar(500) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `room_type`, `price_per_night`, `capacity`, `bed_info`, `description`, `amenities`, `image_url`, `is_active`, `featured`, `created_at`) VALUES
(1, 'The Signature Suite', 'private', 4200.00, 2, '1 king bed', 'A serene private suite with warm textures, considered details, and room to slow down.', 'King bed, Air conditioning, Private bathroom, Wi-Fi, Smart TV', 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=1200&q=85', 1, 1, '2026-10-22 12:42:30'),
(2, 'Garden View Deluxe', 'private', 3200.00, 2, '1 queen bed', 'A bright and inviting room with a calm palette and a restful garden-inspired feel.', 'Queen bed, Air conditioning, Private bathroom, Wi-Fi', 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=1200&q=85', 1, 1, '2026-10-22 12:42:30'),
(3, 'The Comfort Twin', 'private', 2600.00, 2, '2 single beds', 'A flexible, comfortable room for friends, colleagues, or travel companions.', 'Twin beds, Air conditioning, Private bathroom, Wi-Fi', 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=85', 1, 1, '2026-10-22 12:42:30'),
(4, 'Social Stay Dorm', 'shared', 850.00, 4, '4 single bunks', 'A clean, sociable shared room with practical comforts for budget-conscious explorers.', 'Bunk beds, Air conditioning, Shared bathroom, Wi-Fi, Personal locker', 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=1200&q=85', 1, 0, '2026-10-22 12:42:30'),
(5, 'Quiet Corner Room', 'private', 2100.00, 1, '1 single bed', 'A compact private room designed for solo stays and peaceful nights.', 'Single bed, Air conditioning, Private bathroom, Wi-Fi', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=85', 1, 0, '2026-10-22 12:42:30'),
(6, 'Standard Room', 'private', 1500.00, 1, '', 'A comfortable and affordable private room for a relaxing stay.', 'Wi-Fi, Air conditioning, Private room', 'https://placehold.co/800x600?text=Standard+Room', 1, 0, '2026-10-22 15:01:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `idx_booking_dates` (`room_id`,`check_in`,`check_out`,`status`),
  ADD KEY `idx_booking_email` (`guest_email`);

--
-- Indexes for table `payment_records`
--
ALTER TABLE `payment_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_status` (`status`),
  ADD KEY `idx_payment_booking` (`booking_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment_records`
--
ALTER TABLE `payment_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Constraints for table `payment_records`
--
ALTER TABLE `payment_records`
  ADD CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
