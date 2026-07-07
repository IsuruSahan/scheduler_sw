-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 06, 2026 at 09:19 AM
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
-- Table structure for table `media_library`
--

CREATE TABLE `media_library` (
  `id` int(11) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `schedule_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` enum('media','doc') DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `agency_name` varchar(255) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `note` text DEFAULT NULL,
  `is_acknowledged` tinyint(1) DEFAULT 0,
  `acknowledged_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media_library`
--

INSERT INTO `media_library` (`id`, `reference_no`, `schedule_name`, `file_path`, `file_type`, `uploaded_at`, `description`, `agency_name`, `client_name`, `start_date`, `end_date`, `note`, `is_acknowledged`, `acknowledged_at`) VALUES
(1, 'were', 'test', 'media/1782884716_7798F433-8E1F-4F5D-987A-00D61CF78055.jpeg', 'media', '2026-07-01 05:45:16', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(2, 'dfgf', 'test', 'media/1782884716_Bar sign.jpeg', 'media', '2026-07-01 05:45:16', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(3, 'test_1234', 'test', 'docs/1782884716_101085113445200_20260623042610364.pdf', 'doc', '2026-07-01 05:45:16', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(4, 'ADSD', 'maliban', 'media/1782885805_1770882375_maxresdefault (1).jpg', 'media', '2026-07-01 06:03:25', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(5, 'GFWEF', 'maliban', 'media/1782885805_481125176_960935996218703_7108050420934181707_n.jpg', 'media', '2026-07-01 06:03:25', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(6, 'mLI123', 'maliban', 'docs/1782885805_101085113445200_20260623042610364.pdf', 'doc', '2026-07-01 06:03:25', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(7, 'fdsff', 'shesdulee', 'media/1782897065_1770882375_maxresdefault (1).jpg', 'media', '2026-07-01 09:11:05', '', 'test', 'test client', '2026-07-01', '2026-07-30', 'fsfsfsf', 1, '2026-07-01 09:11:05'),
(8, '324we', 'shesdulee', 'docs/1782897065_tin.pdf', 'doc', '2026-07-01 09:11:05', NULL, 'test', 'test client', '2026-07-01', '2026-07-30', 'fsfsfsf', 0, NULL),
(9, 'ref1', 'shedule1', 'media/1782897983_7798F433-8E1F-4F5D-987A-00D61CF78055.jpeg', 'media', '2026-07-01 09:26:23', 'banner', 'agency1', 'client 1', '2026-07-01', '2026-07-28', 'additional note', 1, '2026-07-01 09:26:23'),
(10, 'ref2', 'shedule1', 'media/1782897983_Capture2.PNG', 'media', '2026-07-01 09:26:23', 'gsdfsdf', 'agency1', 'client 1', '2026-07-01', '2026-07-28', 'additional note', 1, '2026-07-01 09:26:23'),
(11, 'sehedule1', 'shedule1', 'docs/1782897983_DOC-20251130-WA0003..pdf', 'doc', '2026-07-01 09:26:23', NULL, 'agency1', 'client 1', '2026-07-01', '2026-07-28', 'additional note', 0, NULL),
(16, NULL, 'sh8', 'media/1782915374_inventory_daily_capacity (1).sql', 'media', '2026-07-01 14:16:14', 'hhgkhgk', 'ag8', 'cl8', '2026-07-28', '2026-07-31', 'sdfffffffffffff', 1, '2026-07-01 14:16:14'),
(17, NULL, 'sh8', 'docs/1782915374_Schedule_Report_rt433.xls', 'doc', '2026-07-01 14:16:14', NULL, 'ag8', 'cl8', '2026-07-28', '2026-07-31', 'sdfffffffffffff', 0, NULL),
(18, NULL, 'rrrrr', 'media/1782980632_dashboard.php', 'media', '2026-07-02 08:23:52', 'rrrrrrr', 'Ad craft', 'Munchie', '2026-07-02', '2026-07-11', 'rrrrrrrrr', 1, '2026-07-02 08:23:52'),
(19, NULL, 'ggggggg', 'media/1782981270_swarnawahini_scheduler_db (9).sql', 'media', '2026-07-02 08:34:30', 'ggggggggg', 'Ad world', 'Asus', '2026-07-02', '2026-07-04', 'ggggggggg', 1, '2026-07-02 08:34:30'),
(20, NULL, 'ggggggg', 'media/1782981270_swarnawahini_scheduler_db (1).sql', 'media', '2026-07-02 08:34:30', 'ggg', 'Ad world', 'Asus', '2026-07-02', '2026-07-04', 'ggggggggg', 1, '2026-07-02 08:34:30'),
(21, NULL, 'ggggggg', 'docs/1782981271_Schedule_Report_hdfgdg.xls', 'doc', '2026-07-02 08:34:31', NULL, 'Ad world', 'Asus', '2026-07-02', '2026-07-04', 'ggggggggg', 0, NULL),
(22, NULL, 'ggggggg', 'docs/1782981271_Schedule_Report_ (2).xls', 'doc', '2026-07-02 08:34:31', NULL, 'Ad world', 'Asus', '2026-07-02', '2026-07-04', 'ggggggggg', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `media_library`
--
ALTER TABLE `media_library`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reference_no` (`reference_no`),
  ADD KEY `fk_media_agency` (`agency_name`),
  ADD KEY `fk_media_client` (`client_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `media_library`
--
ALTER TABLE `media_library`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
