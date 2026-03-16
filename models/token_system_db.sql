-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2026 at 03:23 PM
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
-- Database: `astana_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `clinic_settings`
--

CREATE TABLE `clinic_settings` (
  `id` int(11) NOT NULL,
  `clinic_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `helpline` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `opening_time` time NOT NULL,
  `closing_time` time NOT NULL,
  `total_daily_tokens` int(11) DEFAULT 50,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clinic_settings`
--

INSERT INTO `clinic_settings` (`id`, `clinic_name`, `address`, `phone_number`, `helpline`, `email`, `opening_time`, `closing_time`, `total_daily_tokens`, `created_at`, `updated_at`) VALUES
(1, 'Astana Aliya Tariqiya Usmania', 'shabeer qureshi gali near bismillah masjid baraboard, Pak-colorny, Karachi', '03163621849', '03163621849', 'info@astanaAliyaTariqiya.com', '19:00:00', '22:00:00', 50, '2025-11-29 12:33:02', '2025-11-29 12:33:02');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `name`, `specialization`, `email`, `phone`, `is_active`, `is_available`, `created_at`) VALUES
(1, 'Sufi Muheet Tariqi', NULL, NULL, NULL, 1, 1, '2025-11-29 13:51:56');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_schedules`
--

CREATE TABLE `doctor_schedules` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` tinyint(1) NOT NULL COMMENT '1=Sunday, 2=Monday, ..., 7=Saturday',
  `is_available` tinyint(1) DEFAULT 1,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_schedules`
--

INSERT INTO `doctor_schedules` (`id`, `doctor_id`, `day_of_week`, `is_available`, `start_time`, `end_time`, `created_at`) VALUES
(1, 1, 2, 1, NULL, NULL, '2025-12-21 10:07:15'),
(2, 1, 3, 1, NULL, NULL, '2025-12-21 10:07:15'),
(3, 1, 4, 1, NULL, NULL, '2025-12-21 10:07:15'),
(4, 1, 5, 1, NULL, NULL, '2025-12-21 10:07:15'),
(5, 1, 6, 1, NULL, NULL, '2025-12-21 10:07:15'),
(6, 1, 7, 1, NULL, NULL, '2025-12-21 10:07:15'),
(7, 1, 1, 1, NULL, NULL, '2025-12-21 10:07:15');

-- --------------------------------------------------------

--
-- Table structure for table `special_closures`
--

CREATE TABLE `special_closures` (
  `id` int(11) NOT NULL,
  `closure_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `affects_token_type` int(11) DEFAULT NULL COMMENT 'NULL affects all types',
  `affects_category` int(11) DEFAULT NULL COMMENT 'NULL affects all categories',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `special_closures`
--

INSERT INTO `special_closures` (`id`, `closure_date`, `reason`, `affects_token_type`, `affects_category`, `created_at`) VALUES
(1, '2024-12-25', 'Christmas Holiday', NULL, NULL, '2025-12-21 10:07:15');

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

CREATE TABLE `tokens` (
  `id` int(11) NOT NULL,
  `token_number` int(11) NOT NULL,
  `token_type_id` int(11) NOT NULL,
  `token_category_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `patient_phone` varchar(20) DEFAULT NULL,
  `patient_email` varchar(255) DEFAULT NULL,
  `token_date` date NOT NULL,
  `token_time` time NOT NULL,
  `token_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `meeting_link` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tokens`
--

INSERT INTO `tokens` (`id`, `token_number`, `token_type_id`, `token_category_id`, `doctor_id`, `patient_name`, `patient_phone`, `patient_email`, `token_date`, `token_time`, `token_price`, `status`, `meeting_link`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 'muhammad bilal', '03132004039', 'huzaifa2582003@gmail.com', '2026-03-15', '00:20:08', 200.00, 'pending', NULL, NULL, '2026-03-15 12:51:00', '2026-03-15 12:51:00'),
(2, 2, 1, 1, 1, 'muhammad bilal', '03132004039', 'huzaifa2582003@gmail.com', '2026-03-15', '00:20:08', 200.00, 'pending', NULL, NULL, '2026-03-15 12:56:43', '2026-03-15 12:56:43'),
(3, 3, 1, 1, 1, 'muhammad bilal', '03132004039', 'huzaifa2582003@gmail.com', '2026-03-15', '00:20:08', 200.00, 'pending', NULL, NULL, '2026-03-15 12:57:13', '2026-03-15 12:57:13'),
(4, 4, 1, 1, 1, 'muhammad bilal', '03132004039', 'huzaifa2582003@gmail.com', '2026-03-15', '00:20:08', 200.00, 'pending', NULL, NULL, '2026-03-15 13:12:24', '2026-03-15 13:12:24'),
(5, 1, 1, 2, 1, 'muhammad bilal', '03132004039', 'huzaifa2582003@gmail.com', '2026-03-15', '00:20:08', 500.00, 'pending', NULL, NULL, '2026-03-15 13:16:04', '2026-03-15 13:16:04'),
(6, 2, 1, 2, 1, 'muhammad bilal', '03132004039', 'huzaifa2582003@gmail.com', '2026-03-15', '00:20:08', 500.00, 'pending', NULL, NULL, '2026-03-15 13:19:57', '2026-03-15 13:19:57'),
(7, 5, 1, 1, 1, 'muhammad bilal', '03132004039', 'huzaifa2582003@gmail.com', '2026-03-15', '00:20:08', 200.00, 'pending', NULL, NULL, '2026-03-15 13:37:02', '2026-03-15 13:37:02'),
(8, 6, 1, 1, 1, 'bilal', '03132004039', 'huzaifa2582003@gmail.com', '2026-03-15', '00:20:08', 200.00, 'pending', NULL, NULL, '2026-03-15 13:38:26', '2026-03-15 13:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `token_categories`
--

CREATE TABLE `token_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `token_type_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `token_categories`
--

INSERT INTO `token_categories` (`id`, `category_name`, `token_type_id`, `description`, `base_price`, `is_active`, `created_at`) VALUES
(1, 'normal', 1, 'Regular appointment', 200.00, 1, '2025-11-29 12:33:02'),
(2, 'urgent', 1, 'Urgent care appointment', 500.00, 1, '2025-11-29 12:33:02'),
(3, 'normal-online', 2, NULL, 500.00, 1, '2025-11-29 14:20:27'),
(4, 'urgent-online', 2, NULL, 1000.00, 1, '2025-11-29 14:20:43');

-- --------------------------------------------------------

--
-- Table structure for table `token_counter`
--

CREATE TABLE `token_counter` (
  `id` int(11) NOT NULL,
  `count_date` date NOT NULL,
  `token_type_id` int(11) NOT NULL,
  `token_category_id` int(11) NOT NULL,
  `token_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `token_limits`
--

CREATE TABLE `token_limits` (
  `id` int(11) NOT NULL,
  `token_type_id` int(11) DEFAULT NULL,
  `token_category_id` int(11) DEFAULT NULL,
  `daily_limit` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `token_limits`
--

INSERT INTO `token_limits` (`id`, `token_type_id`, `token_category_id`, `daily_limit`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 7, 1, '2025-11-29 12:33:02', '2026-03-15 13:37:25'),
(2, 1, 2, 5, 1, '2025-11-29 12:33:02', '2025-11-29 12:33:02'),
(3, 2, 3, 8, 1, '2025-11-29 12:33:02', '2025-12-05 20:20:59'),
(4, 2, 4, 2, 1, '2025-11-29 12:33:02', '2025-12-05 20:21:06');

-- --------------------------------------------------------

--
-- Table structure for table `token_types`
--

CREATE TABLE `token_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `token_types`
--

INSERT INTO `token_types` (`id`, `type_name`, `description`, `is_active`, `created_at`) VALUES
(1, 'physical', 'Patient visits clinic physically', 1, '2025-11-29 12:33:02'),
(2, 'online', 'Online consultation via video call', 1, '2025-11-29 12:33:02');

-- --------------------------------------------------------

--
-- Table structure for table `token_type_restrictions`
--

CREATE TABLE `token_type_restrictions` (
  `id` int(11) NOT NULL,
  `token_type_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` tinyint(1) DEFAULT NULL COMMENT 'NULL means all days',
  `is_allowed` tinyint(1) DEFAULT 1 COMMENT '0=not allowed, 1=allowed',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `token_type_restrictions`
--

INSERT INTO `token_type_restrictions` (`id`, `token_type_id`, `doctor_id`, `day_of_week`, `is_allowed`, `start_date`, `end_date`, `notes`, `created_at`) VALUES
(1, 2, 1, 1, 0, NULL, NULL, NULL, '2025-12-21 10:07:15'),
(2, 1, 1, 1, 1, NULL, NULL, NULL, '2025-12-21 10:07:15'),
(3, 2, 0, 3, 0, NULL, NULL, NULL, '2025-12-21 10:07:15'),
(4, 2, 0, 4, 0, NULL, NULL, NULL, '2025-12-21 10:07:15'),
(5, 2, 0, 5, 0, NULL, NULL, NULL, '2025-12-21 10:07:15'),
(6, 2, 0, 6, 0, NULL, NULL, NULL, '2025-12-21 10:07:15'),
(7, 2, 0, 7, 1, NULL, NULL, NULL, '2025-12-21 10:07:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clinic_settings`
--
ALTER TABLE `clinic_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_doctor_day` (`doctor_id`,`day_of_week`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `special_closures`
--
ALTER TABLE `special_closures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_closure_date` (`closure_date`,`affects_token_type`,`affects_category`);

--
-- Indexes for table `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token_type_id` (`token_type_id`),
  ADD KEY `token_category_id` (`token_category_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `idx_token_date` (`token_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `token_categories`
--
ALTER TABLE `token_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `token_counter`
--
ALTER TABLE `token_counter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date_type_category` (`count_date`,`token_type_id`,`token_category_id`),
  ADD KEY `token_type_id` (`token_type_id`),
  ADD KEY `token_category_id` (`token_category_id`);

--
-- Indexes for table `token_limits`
--
ALTER TABLE `token_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_type_category` (`token_type_id`,`token_category_id`),
  ADD KEY `token_category_id` (`token_category_id`);

--
-- Indexes for table `token_types`
--
ALTER TABLE `token_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `token_type_restrictions`
--
ALTER TABLE `token_type_restrictions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_type_day` (`token_type_id`,`day_of_week`),
  ADD KEY `token_type_id` (`token_type_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clinic_settings`
--
ALTER TABLE `clinic_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `special_closures`
--
ALTER TABLE `special_closures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tokens`
--
ALTER TABLE `tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `token_categories`
--
ALTER TABLE `token_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `token_counter`
--
ALTER TABLE `token_counter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `token_limits`
--
ALTER TABLE `token_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `token_types`
--
ALTER TABLE `token_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `token_type_restrictions`
--
ALTER TABLE `token_type_restrictions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  ADD CONSTRAINT `doctor_schedules_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tokens`
--
ALTER TABLE `tokens`
  ADD CONSTRAINT `tokens_ibfk_1` FOREIGN KEY (`token_type_id`) REFERENCES `token_types` (`id`),
  ADD CONSTRAINT `tokens_ibfk_2` FOREIGN KEY (`token_category_id`) REFERENCES `token_categories` (`id`),
  ADD CONSTRAINT `tokens_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);

--
-- Constraints for table `token_counter`
--
ALTER TABLE `token_counter`
  ADD CONSTRAINT `token_counter_ibfk_1` FOREIGN KEY (`token_type_id`) REFERENCES `token_types` (`id`),
  ADD CONSTRAINT `token_counter_ibfk_2` FOREIGN KEY (`token_category_id`) REFERENCES `token_categories` (`id`);

--
-- Constraints for table `token_limits`
--
ALTER TABLE `token_limits`
  ADD CONSTRAINT `token_limits_ibfk_1` FOREIGN KEY (`token_type_id`) REFERENCES `token_types` (`id`),
  ADD CONSTRAINT `token_limits_ibfk_2` FOREIGN KEY (`token_category_id`) REFERENCES `token_categories` (`id`);

--
-- Constraints for table `token_type_restrictions`
--
ALTER TABLE `token_type_restrictions`
  ADD CONSTRAINT `token_type_restrictions_ibfk_1` FOREIGN KEY (`token_type_id`) REFERENCES `token_types` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
