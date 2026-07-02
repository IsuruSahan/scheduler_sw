-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2026 at 11:08 AM
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
(6, 'End role');

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
(3, 'Ad world');

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
(3, 1, 'Munchie');

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
(3, 'P1', 'Program', NULL, NULL),
(4, 'News 1', 'News', NULL, NULL),
(5, 'T2', 'Teledrama', NULL, NULL),
(6, 'Natath ayek sura mathin', 'Teledrama', NULL, NULL),
(7, 'Hapan padura', 'Program', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `episodes`
--

CREATE TABLE `episodes` (
  `id` int(11) NOT NULL,
  `content_item_id` int(11) DEFAULT NULL,
  `episode_number` int(11) DEFAULT NULL,
  `episode_title` varchar(255) DEFAULT NULL,
  `upload_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `episodes`
--

INSERT INTO `episodes` (`id`, `content_item_id`, `episode_number`, `episode_title`, `upload_date`) VALUES
(1, 3, 2, 'title 1', '2026-06-11'),
(2, 4, 0, 'N/A', '2026-06-25'),
(4, 7, 2, 'Titile test', '2026-06-19'),
(5, 6, 5, 'ada kotasa', '2026-06-26');

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
  `max_quantity` int(11) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  `content_item_id` int(11) DEFAULT NULL,
  `media_format` enum('Full Video','Clip') DEFAULT 'Full Video'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rate_cards`
--

INSERT INTO `rate_cards` (`id`, `category`, `platform_id`, `placement_id`, `max_quantity`, `rate`, `content_item_id`, `media_format`) VALUES
(1, NULL, 1, 1, 2, 2000.00, 3, 'Full Video'),
(2, NULL, 1, 1, 4, 1000.00, 4, 'Clip'),
(3, NULL, 1, 1, 5, 2000.00, 1, 'Clip'),
(4, NULL, 1, 1, 4, 2000.00, 1, 'Clip'),
(5, NULL, 2, 4, 1, 3000.00, 5, 'Clip'),
(6, NULL, 3, 6, 4, 4000.00, 7, 'Clip');

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
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `agency_id`, `client_id`, `schedule_name`, `reference_no`, `start_date`, `end_date`, `budget_allocated`, `created_by`, `status`) VALUES
(1, 1, 3, 'test', NULL, '2026-06-09', '2026-06-20', 50000.00, 5, 'Pending'),
(2, 3, 1, 'testtt', NULL, '2026-06-09', '2026-06-20', 60000.00, 5, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_items`
--

CREATE TABLE `schedule_items` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `episode_id` int(11) NOT NULL,
  `content_item_id` int(11) NOT NULL,
  `platform_id` int(11) NOT NULL,
  `placement_id` int(11) NOT NULL,
  `media_file` varchar(255) DEFAULT NULL,
  `cost` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(5, 'Rumesh', 'rumesh@swarnawahini.lk', '$2y$10$e.r1WcPsHNYXeL4QD9vdxe5N/j0wCMMbl8hoLAuJAu/RLogbACGtq', 2);

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
-- Indexes for table `episodes`
--
ALTER TABLE `episodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_item_id` (`content_item_id`);

--
-- Indexes for table `media_attachments`
--
ALTER TABLE `media_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_item_id` (`schedule_item_id`);

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
  ADD KEY `content_item_id` (`content_item_id`);

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
-- Indexes for table `schedule_items`
--
ALTER TABLE `schedule_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `content_item_id` (`content_item_id`),
  ADD KEY `platform_id` (`platform_id`),
  ADD KEY `placement_id` (`placement_id`),
  ADD KEY `episode_id` (`episode_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `agencies`
--
ALTER TABLE `agencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `content_items`
--
ALTER TABLE `content_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `episodes`
--
ALTER TABLE `episodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `media_attachments`
--
ALTER TABLE `media_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `platforms`
--
ALTER TABLE `platforms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rate_cards`
--
ALTER TABLE `rate_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `schedule_items`
--
ALTER TABLE `schedule_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teledrama_series`
--
ALTER TABLE `teledrama_series`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Constraints for table `episodes`
--
ALTER TABLE `episodes`
  ADD CONSTRAINT `episodes_ibfk_1` FOREIGN KEY (`content_item_id`) REFERENCES `content_items` (`id`);

--
-- Constraints for table `media_attachments`
--
ALTER TABLE `media_attachments`
  ADD CONSTRAINT `media_attachments_ibfk_1` FOREIGN KEY (`schedule_item_id`) REFERENCES `schedule_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rate_cards`
--
ALTER TABLE `rate_cards`
  ADD CONSTRAINT `rate_cards_ibfk_1` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`),
  ADD CONSTRAINT `rate_cards_ibfk_2` FOREIGN KEY (`placement_id`) REFERENCES `ad_placements` (`id`),
  ADD CONSTRAINT `rate_cards_ibfk_3` FOREIGN KEY (`content_item_id`) REFERENCES `content_items` (`id`);

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`);

--
-- Constraints for table `schedule_items`
--
ALTER TABLE `schedule_items`
  ADD CONSTRAINT `schedule_items_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_items_ibfk_2` FOREIGN KEY (`content_item_id`) REFERENCES `content_items` (`id`),
  ADD CONSTRAINT `schedule_items_ibfk_3` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`),
  ADD CONSTRAINT `schedule_items_ibfk_4` FOREIGN KEY (`placement_id`) REFERENCES `ad_placements` (`id`),
  ADD CONSTRAINT `schedule_items_ibfk_5` FOREIGN KEY (`episode_id`) REFERENCES `episodes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
