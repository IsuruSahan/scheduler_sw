-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2026 at 11:25 AM
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
-- Database: `swarnawahini_scheduler_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ad_placements`
--

CREATE TABLE `ad_placements` (
  `id` int(11) NOT NULL,
  `placement_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ad_placements`
--

INSERT INTO `ad_placements` (`id`, `placement_name`) VALUES
(1, 'Mid role'),
(4, 'Crowlers'),
(6, 'End role'),
(7, 'Lcrolers');

-- --------------------------------------------------------

--
-- Table structure for table `agencies`
--

CREATE TABLE `agencies` (
  `id` int(11) NOT NULL,
  `agency_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agencies`
--

INSERT INTO `agencies` (`id`, `agency_name`) VALUES
(1, 'Ad craft'),
(2, 'Ad agency'),
(3, 'Ad world'),
(4, 'Ads wave');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `agency_id`, `client_name`) VALUES
(1, 3, 'Kandos'),
(2, 3, 'Asus'),
(3, 1, 'Munchie'),
(4, 4, 'CBL');

-- --------------------------------------------------------

--
-- Table structure for table `content_items`
--

CREATE TABLE `content_items` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `type` enum('Teledrama','Program','News') DEFAULT NULL,
  `series_id` int(11) DEFAULT NULL,
  `episode_number` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content_items`
--

INSERT INTO `content_items` (`id`, `name`, `type`, `series_id`, `episode_number`) VALUES
(1, 'Jahutaa', 'Teledrama', NULL, NULL),
(2, 'Sinto', 'Program', NULL, NULL),
(4, 'News 1', 'News', NULL, NULL),
(6, 'Natath ayek sura mathin', 'Teledrama', NULL, NULL),
(7, 'Hapan padura', 'Program', NULL, NULL),
(8, 'Bolt Anayak', 'Teledrama', NULL, NULL),
(9, 'Rata watee', 'Program', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `rate_card_id` int(11) NOT NULL,
  `total_capacity` int(11) DEFAULT 0,
  `reserved_qty` int(11) NOT NULL DEFAULT 0,
  `used_qty` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `rate_card_id`, `total_capacity`, `reserved_qty`, `used_qty`) VALUES
(51, 44, 5, 0, 4),
(52, 45, 10, 0, 2),
(53, 46, 15, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `media_attachments`
--

CREATE TABLE `media_attachments` (
  `id` int(11) NOT NULL,
  `schedule_item_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_reference` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_formats`
--

CREATE TABLE `media_formats` (
  `id` int(11) NOT NULL,
  `format_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media_formats`
--

INSERT INTO `media_formats` (`id`, `format_name`) VALUES
(1, 'Full Video'),
(2, 'Clip'),
(5, 'Test'),
(6, 'Recap');

-- --------------------------------------------------------

--
-- Table structure for table `platforms`
--

CREATE TABLE `platforms` (
  `id` int(11) NOT NULL,
  `platform_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platforms`
--

INSERT INTO `platforms` (`id`, `platform_name`) VALUES
(1, 'Youtube'),
(2, 'TikTok'),
(3, 'Facebook');

-- --------------------------------------------------------

--
-- Table structure for table `rate_cards`
--

CREATE TABLE `rate_cards` (
  `id` int(11) NOT NULL,
  `category` enum('Drama','Program','News') DEFAULT NULL,
  `platform_id` int(11) DEFAULT NULL,
  `placement_id` int(11) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  `content_item_id` int(11) DEFAULT NULL,
  `media_format_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rate_cards`
--

INSERT INTO `rate_cards` (`id`, `category`, `platform_id`, `placement_id`, `rate`, `content_item_id`, `media_format_id`) VALUES
(44, NULL, 1, 4, 1000.00, 8, 2),
(45, NULL, 1, 7, 2000.00, 1, 6),
(46, NULL, 2, 4, 5000.00, 7, 6);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Scheduler'),
(3, 'Marketing Officer'),
(4, 'Editor');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `schedule_name` varchar(150) NOT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `budget_allocated` decimal(15,2) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `status` enum('Active','Stopped','Pending Approval','Pending Approval (Cost Review)') DEFAULT 'Active',
  `assigned_team` varchar(50) DEFAULT 'Content Editor Team',
  `final_cost` decimal(10,2) DEFAULT 0.00,
  `days_run` int(11) DEFAULT 0,
  `total_days` int(11) DEFAULT 0,
  `remaining_budget` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `agency_id`, `client_id`, `schedule_name`, `reference_no`, `start_date`, `end_date`, `budget_allocated`, `created_by`, `status`, `assigned_team`, `final_cost`, `days_run`, `total_days`, `remaining_budget`) VALUES
(114, 3, 1, 'test', '', '2026-06-23', '2026-06-27', 5000.00, 5, 'Stopped', 'Content Editor Team', 1000.00, 1, 5, 4000.00),
(115, 3, 2, 'Laptop', 'ewrwrr', '2026-06-23', '2026-06-25', 30000.00, 5, 'Pending Approval (Cost Review)', 'News Team', 11000.00, 0, 0, 0.00),
(116, 1, 3, 'Marie busicuit', '', '2026-06-23', '2026-06-26', 40000.00, 5, 'Pending Approval (Cost Review)', 'Content Editor Team, News Team', 12000.00, 0, 0, 0.00),
(118, 3, 1, 'White chocolate', 'White', '2026-06-23', '2026-06-26', 40000.00, 5, 'Stopped', 'Content Editor Team, News Team', 9000.00, 1, 4, 31000.00),
(119, 3, 2, 'Mouse', 'a33213', '2026-06-23', '2026-06-30', 40000.00, 5, 'Stopped', 'News Team', 5000.00, 1, 8, 35000.00),
(120, 3, 1, 'fffd', 'rt433', '2026-06-23', '2026-06-30', 353654.00, 5, 'Stopped', 'Content Editor Team, News Team', 44206.75, 1, 8, 309447.25),
(121, 3, 2, 'ccc', 'ccccccccc', '2026-06-25', '2026-06-29', 42342.00, 5, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(122, 4, 4, 'bbbbbb', 'bbbb', '2026-06-23', '2026-06-30', 432234.00, 5, 'Stopped', 'Content Editor Team, News Team', 54029.25, 1, 8, 378204.75),
(123, 1, 3, 'Samaposha', 'SW234', '2026-06-23', '2026-06-27', 40000.00, 5, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `schedule_approval_requests`
--

CREATE TABLE `schedule_approval_requests` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `request_type` varchar(50) DEFAULT 'extension',
  `new_end_date` date DEFAULT NULL,
  `additional_budget` decimal(10,2) DEFAULT NULL,
  `additional_qty` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_daily_costs`
--

CREATE TABLE `schedule_daily_costs` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `cost_date` date NOT NULL,
  `manual_cost` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_daily_costs`
--

INSERT INTO `schedule_daily_costs` (`id`, `schedule_id`, `cost_date`, `manual_cost`, `created_at`) VALUES
(4, 115, '2026-06-23', 11000.00, '2026-06-23 04:55:37'),
(5, 116, '2026-06-23', 12000.00, '2026-06-23 04:59:56'),
(7, 118, '2026-06-23', 9000.00, '2026-06-23 05:04:35');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_items`
--

CREATE TABLE `schedule_items` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `content_item_id` int(11) NOT NULL,
  `platform_id` int(11) NOT NULL,
  `placement_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `media_file` varchar(255) DEFAULT NULL,
  `cost` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_items`
--

INSERT INTO `schedule_items` (`id`, `schedule_id`, `content_item_id`, `platform_id`, `placement_id`, `quantity`, `media_file`, `cost`) VALUES
(120, 114, 8, 1, 4, 3, NULL, 3000.00),
(121, 115, 1, 1, 7, 1, NULL, 2000.00),
(122, 115, 8, 1, 4, 3, NULL, 3000.00),
(123, 116, 1, 1, 7, 1, NULL, 2000.00),
(125, 118, 1, 1, 7, 3, NULL, 6000.00),
(126, 119, 7, 2, 4, 2, NULL, 10000.00),
(127, 119, 8, 1, 4, 1, NULL, 1000.00),
(128, 120, 8, 1, 4, 1, NULL, 1000.00),
(129, 121, 7, 2, 4, 1, NULL, 5000.00),
(130, 122, 7, 2, 4, 1, NULL, 5000.00),
(131, 123, 8, 1, 4, 1, NULL, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `teledrama_series`
--

CREATE TABLE `teledrama_series` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role_id`) VALUES
(2, 'Admin User', 'admin@swarnawahini.lk', '$2y$10$SdQFQYEifXcNIdHH3CB4f.0ic9TgGwrRRhm9M6JiWYzZMOGmJ9qTC', 1),
(5, 'Rumesh', 'rumesh@swarnawahini.lk', '$2y$10$e.r1WcPsHNYXeL4QD9vdxe5N/j0wCMMbl8hoLAuJAu/RLogbACGtq', 2),
(6, 'Nimal silva', 'nimal@swarnawahini.lk', '$2y$10$sow4G2FvvZyDic5594BzsuWu3iQkpdC.KbtXJJsBUdvig5JkcU/g.', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ad_placements`
--
ALTER TABLE `ad_placements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `agencies`
--
ALTER TABLE `agencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Indexes for table `content_items`
--
ALTER TABLE `content_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `series_id` (`series_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rate_card_id` (`rate_card_id`);

--
-- Indexes for table `media_attachments`
--
ALTER TABLE `media_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_item_id` (`schedule_item_id`);

--
-- Indexes for table `media_formats`
--
ALTER TABLE `media_formats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `platforms`
--
ALTER TABLE `platforms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rate_cards`
--
ALTER TABLE `rate_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `platform_id` (`platform_id`),
  ADD KEY `placement_id` (`placement_id`),
  ADD KEY `content_item_id` (`content_item_id`),
  ADD KEY `fk_media_format` (`media_format_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Indexes for table `schedule_approval_requests`
--
ALTER TABLE `schedule_approval_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `schedule_daily_costs`
--
ALTER TABLE `schedule_daily_costs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `schedule_items`
--
ALTER TABLE `schedule_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `content_item_id` (`content_item_id`),
  ADD KEY `platform_id` (`platform_id`),
  ADD KEY `placement_id` (`placement_id`);

--
-- Indexes for table `teledrama_series`
--
ALTER TABLE `teledrama_series`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ad_placements`
--
ALTER TABLE `ad_placements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `agencies`
--
ALTER TABLE `agencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `content_items`
--
ALTER TABLE `content_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `media_attachments`
--
ALTER TABLE `media_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `media_formats`
--
ALTER TABLE `media_formats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `platforms`
--
ALTER TABLE `platforms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rate_cards`
--
ALTER TABLE `rate_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `schedule_approval_requests`
--
ALTER TABLE `schedule_approval_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule_daily_costs`
--
ALTER TABLE `schedule_daily_costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `schedule_items`
--
ALTER TABLE `schedule_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `teledrama_series`
--
ALTER TABLE `teledrama_series`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_items`
--
ALTER TABLE `content_items`
  ADD CONSTRAINT `content_items_ibfk_1` FOREIGN KEY (`series_id`) REFERENCES `teledrama_series` (`id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`rate_card_id`) REFERENCES `rate_cards` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_attachments`
--
ALTER TABLE `media_attachments`
  ADD CONSTRAINT `media_attachments_ibfk_1` FOREIGN KEY (`schedule_item_id`) REFERENCES `schedule_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rate_cards`
--
ALTER TABLE `rate_cards`
  ADD CONSTRAINT `fk_media_format` FOREIGN KEY (`media_format_id`) REFERENCES `media_formats` (`id`),
  ADD CONSTRAINT `rate_cards_ibfk_1` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`),
  ADD CONSTRAINT `rate_cards_ibfk_2` FOREIGN KEY (`placement_id`) REFERENCES `ad_placements` (`id`),
  ADD CONSTRAINT `rate_cards_ibfk_3` FOREIGN KEY (`content_item_id`) REFERENCES `content_items` (`id`);

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`);

--
-- Constraints for table `schedule_approval_requests`
--
ALTER TABLE `schedule_approval_requests`
  ADD CONSTRAINT `schedule_approval_requests_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_daily_costs`
--
ALTER TABLE `schedule_daily_costs`
  ADD CONSTRAINT `schedule_daily_costs_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_items`
--
ALTER TABLE `schedule_items`
  ADD CONSTRAINT `schedule_items_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_items_ibfk_2` FOREIGN KEY (`content_item_id`) REFERENCES `content_items` (`id`),
  ADD CONSTRAINT `schedule_items_ibfk_3` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`),
  ADD CONSTRAINT `schedule_items_ibfk_4` FOREIGN KEY (`placement_id`) REFERENCES `ad_placements` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
