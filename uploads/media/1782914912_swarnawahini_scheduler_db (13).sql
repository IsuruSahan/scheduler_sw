-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2026 at 11:42 AM
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
(51, 44, 151, 0, 108),
(52, 45, 40, 0, 1),
(53, 46, 40, 0, 7);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_daily_capacity`
--

CREATE TABLE `inventory_daily_capacity` (
  `id` int(11) NOT NULL,
  `rate_card_id` int(11) NOT NULL,
  `capacity_date` date NOT NULL,
  `capacity_qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_daily_capacity`
--

INSERT INTO `inventory_daily_capacity` (`id`, `rate_card_id`, `capacity_date`, `capacity_qty`) VALUES
(16, 45, '0000-00-00', 7),
(17, 46, '0000-00-00', 6),
(18, 47, '0000-00-00', 8),
(23, 44, '0000-00-00', 6);

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

--
-- Dumping data for table `media_attachments`
--

INSERT INTO `media_attachments` (`id`, `schedule_item_id`, `file_path`, `file_reference`, `uploaded_at`) VALUES
(102, 143, '../uploads/1782276644_create.php', 'wetewew', '2026-06-24 04:50:44'),
(106, 150, '../uploads/1782282348_create.php', 'tyt', '2026-06-24 06:25:48'),
(107, 152, '../uploads/1782282441_export_report.php', 'wef', '2026-06-24 06:27:21'),
(108, 152, '../uploads/1782282441_update_item.php', 'ergg', '2026-06-24 06:27:21'),
(109, 153, '../uploads/1782282687_process_schedule.php', 'fhnghfghjfjd', '2026-06-24 06:31:27'),
(110, 157, '../uploads/1782359660_Refund____1___1_.pdf', 'l;kok;o', '2026-06-25 03:54:20'),
(111, 159, '../uploads/1782374051_7798F433-8E1F-4F5D-987A-00D61CF78055.jpeg', 'uyytjt', '2026-06-25 07:54:11');

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
(11, 'sehedule1', 'shedule1', 'docs/1782897983_DOC-20251130-WA0003..pdf', 'doc', '2026-07-01 09:26:23', NULL, 'agency1', 'client 1', '2026-07-01', '2026-07-28', 'additional note', 0, NULL);

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
(46, NULL, 2, 4, 5000.00, 7, 6),
(47, NULL, 1, 1, 500.00, 2, 1),
(48, NULL, 1, 4, 2000.00, 9, 1);

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
(115, 3, 2, 'Laptop', 'ewrwrr', '2026-06-23', '2026-06-25', 30000.00, 5, 'Active', 'News Team', 11000.00, 0, 0, 0.00),
(116, 1, 3, 'Marie busicuit', '', '2026-06-23', '2026-06-26', 30800.00, 5, 'Stopped', 'Content Editor Team, News Team', 15400.00, 2, 4, 15400.00),
(118, 3, 1, 'White chocolate', 'White', '2026-06-23', '2026-06-26', 40000.00, 5, 'Stopped', 'Content Editor Team, News Team', 9000.00, 1, 4, 31000.00),
(119, 3, 2, 'Mouse', 'a33213', '2026-06-23', '2026-06-30', 40000.00, 5, 'Stopped', 'News Team', 5000.00, 1, 8, 35000.00),
(120, 3, 1, 'fffd', 'rt433', '2026-06-23', '2026-06-30', 353654.00, 5, 'Stopped', 'Content Editor Team, News Team', 44206.75, 1, 8, 309447.25),
(121, 3, 2, 'ccc', 'ccccccccc', '2026-06-25', '2026-07-04', 84684.00, 5, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(122, 4, 4, 'bbbbbb', 'bbbb', '2026-06-23', '2026-06-30', 432234.00, 5, 'Stopped', 'Content Editor Team, News Team', 54029.25, 1, 8, 378204.75),
(123, 1, 3, 'Samaposha', 'SW234', '2026-06-23', '2026-06-30', 43000.00, 5, 'Active', 'Content Editor Team', 16000.00, 2, 5, 24000.00),
(124, 1, 3, 'rrrrr', 'rrrrr', '2026-06-23', '2026-06-27', 2777.78, 5, 'Stopped', 'Content Editor Team', 1111.11, 2, 5, 1666.67),
(125, 3, 1, 'gffg', 'hfdh', '2026-06-23', '2026-06-27', 50000.00, 5, 'Stopped', 'Content Editor Team', 10000.00, 1, 5, 40000.00),
(126, 3, 1, 'uiyu7i', '', '2026-06-23', '2026-06-30', 56865.00, 5, 'Stopped', 'Content Editor Team', 14216.25, 2, 8, 42648.75),
(127, 3, 2, 'fsd', 'sdfsdf', '2026-06-24', '2026-06-30', 935934.50, 5, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(131, 3, 2, 'Laptop', 'SE235', '2026-06-24', '2026-07-17', 50000.00, 5, 'Active', 'Content Editor Team, News Team', 0.00, 0, 0, 0.00),
(132, 1, 3, 'fssfs', 'hdgfgdg', '2026-06-24', '2026-06-26', 2343243.00, 5, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(136, 3, 1, 'Whi', '', '2026-06-24', '2026-06-30', 10000.00, 5, 'Active', 'Content Editor Team, News Team', 0.00, 0, 0, 0.00),
(137, 1, 3, 'tdbgfb', 'gdfzfgd', '2026-06-24', '2026-06-30', 435345.00, 5, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(138, 4, 4, 'sdsd', 'hdfgdg', '2026-06-24', '2026-06-29', 33000.00, 5, 'Active', 'Content Editor Team', 13500.00, 0, 0, 0.00),
(139, 3, 2, 'yuukyu', 'jytyrt', '2026-06-24', '2026-06-30', 654646.00, 5, 'Stopped', 'Content Editor Team, News Team', 187041.71, 2, 7, 467604.29),
(140, 3, 2, 'iphone', 'hthtth', '2026-06-24', '2026-06-30', 112000.00, 2, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(141, 3, 2, 'Bottle', 'asd', '2026-06-25', '2026-06-30', 50000.00, 2, 'Stopped', 'Content Editor Team', 8333.33, 1, 6, 41666.67),
(142, 1, 3, 'Revello', 'sfsdf', '2026-06-25', '2026-06-30', 50000.00, 2, 'Active', 'Content Editor Team', 10000.00, 0, 0, 0.00),
(143, 3, 1, 'asad', 'dsad', '2026-06-25', '2026-06-29', 324535.00, 5, 'Pending Approval (Cost Review)', 'Content Editor Team', 70000.00, 0, 0, 0.00),
(144, 3, 1, '6uttut', 'ggkkhgj', '2026-06-25', '2026-07-03', 14000.00, 8, 'Stopped', 'Content Editor Team', 1555.56, 1, 9, 12444.44),
(154, 3, 2, 'dffg', 'hdfxdfs', '2026-06-30', '2026-06-30', 20000.00, 8, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(155, 3, 1, 'sdadsgdgs', 'fedhfd', '2026-06-30', '2026-06-30', 50000.00, 8, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(156, 3, 1, 'sdadsgdgs', 'fedhfd', '2026-07-01', '2026-07-01', 50000.00, 8, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(157, 1, 3, 'sdadsgdgs', 'fedhfd', '2026-07-04', '2026-07-04', 50000.00, 8, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(158, 1, 3, 'bnvc', 'gfdhdf', '2026-07-02', '2026-07-02', 3000.00, 8, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(159, 1, 3, 'ttt', 'tttttt', '2026-07-14', '2026-07-15', 50000.00, 8, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(160, 4, 4, 'yy', 'yyyyyyy', '2026-07-20', '2026-07-22', 40000.00, 8, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(161, 1, 3, 'cvcccccc', 'ccc', '2026-07-09', '2026-07-09', 4444.00, 8, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(163, 4, 4, 'lll', 'llllllll', '2026-06-30', '2026-07-01', 7865.00, 8, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(164, 4, 4, 'sacc', 'ngfh', '2026-08-04', '2026-08-05', 5555555.00, 8, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00),
(165, 1, 3, 'nnnnnnn', 'nh', '2026-07-01', '2026-07-01', 4000.00, 8, 'Stopped', 'Content Editor Team', 4000.00, 1, 1, 0.00),
(166, 4, 4, 'gbgcb', '3434', '2026-07-09', '2026-07-10', 23434.00, 8, 'Active', 'Content Editor Team', 0.00, 0, 0, 0.00);

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

--
-- Dumping data for table `schedule_approval_requests`
--

INSERT INTO `schedule_approval_requests` (`id`, `schedule_id`, `request_type`, `new_end_date`, `additional_budget`, `additional_qty`, `status`, `requested_at`) VALUES
(1, 123, 'extension', '2026-06-30', 3000.00, 1, 'Approved', '2026-06-23 09:28:36'),
(2, 124, 'extension', '2026-06-23', 200.00, NULL, 'Pending', '2026-06-23 10:12:00'),
(3, 124, 'extension', '2026-06-30', 60000.00, NULL, 'Pending', '2026-06-23 10:24:47'),
(4, 125, 'extension', '2026-07-01', 20.00, NULL, 'Pending', '2026-06-23 10:41:53'),
(5, 124, 'extension', '2026-06-27', 300.00, NULL, 'Pending', '2026-06-24 03:32:58'),
(6, 127, 'extension', '2026-06-30', 500.00, NULL, 'Approved', '2026-06-24 03:54:24'),
(7, 127, 'extension', '2026-06-30', 500.00, NULL, 'Pending', '2026-06-24 04:01:44'),
(8, 140, 'extension', '2026-06-26', 1000.00, NULL, 'Approved', '2026-06-24 08:07:00'),
(9, 116, 'extension', '2026-06-26', 100.00, NULL, 'Approved', '2026-06-24 08:11:29'),
(10, 116, 'extension', '2026-06-26', 500.00, NULL, 'Approved', '2026-06-24 08:15:51'),
(11, 116, 'extension', '2026-06-26', 300.00, NULL, 'Approved', '2026-06-24 08:20:12'),
(12, 140, 'extension', '2026-06-26', 100.00, NULL, 'Approved', '2026-06-24 08:23:12'),
(13, 140, 'extension', '2026-06-26', 300.00, NULL, 'Approved', '2026-06-24 08:49:51'),
(14, 140, 'extension', '2026-06-26', 300.00, NULL, 'Approved', '2026-06-24 08:56:08'),
(15, 140, 'extension', '2026-06-26', 200.00, NULL, 'Approved', '2026-06-24 09:12:31'),
(16, 140, 'extension', '2026-06-26', 78.00, NULL, 'Rejected', '2026-06-24 09:17:16'),
(17, 140, 'extension', '2026-06-27', 800.00, NULL, 'Approved', '2026-06-24 09:30:26'),
(18, 140, 'extension', '2026-06-27', 200.00, NULL, 'Approved', '2026-06-24 09:41:47'),
(19, 140, 'extension', '2026-06-27', 2000.00, NULL, 'Approved', '2026-06-24 09:50:42'),
(20, 140, 'extension', '2026-06-27', 2500.00, NULL, 'Approved', '2026-06-25 03:19:43'),
(21, 140, 'extension', '2026-06-27', 7000.00, NULL, 'Approved', '2026-06-25 03:21:25'),
(22, 140, 'extension', '2026-06-27', 15000.00, NULL, 'Approved', '2026-06-25 03:24:17'),
(23, 140, 'extension', '2026-06-27', 30000.00, NULL, 'Approved', '2026-06-25 03:36:58'),
(24, 140, 'extension', '2026-06-27', 57000.00, NULL, 'Approved', '2026-06-25 03:40:21'),
(25, 138, 'extension', '2026-06-29', 3000.00, NULL, 'Approved', '2026-06-25 06:28:21'),
(26, 144, 'extension', '2026-06-30', 5000.00, NULL, 'Approved', '2026-06-25 07:56:16'),
(27, 144, 'extension', '2026-06-30', 500.00, NULL, 'Approved', '2026-06-25 08:02:15'),
(28, 144, 'extension', '2026-06-30', 100.00, NULL, 'Approved', '2026-06-25 08:21:38'),
(29, 144, 'extension', '2026-06-30', 100.00, NULL, 'Approved', '2026-06-25 08:25:28'),
(30, 144, 'extension', '2026-06-30', 500.00, NULL, 'Approved', '2026-06-25 09:02:26');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_audit_log`
--

CREATE TABLE `schedule_audit_log` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `change_type` varchar(50) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_audit_log`
--

INSERT INTO `schedule_audit_log` (`id`, `schedule_id`, `changed_by`, `change_type`, `old_value`, `new_value`, `changed_at`) VALUES
(1, 124, NULL, 'Budget Reduction', '2000.00', '5000', '2026-06-23 10:23:40'),
(2, 124, NULL, 'Date Adjustment', '2026-06-30', '2026-07-01', '2026-06-23 10:33:35'),
(3, 125, NULL, 'Budget Reduction', '50000.00', '40000', '2026-06-23 10:40:51'),
(4, 125, NULL, 'Budget Reduction', '40000.00', '30000', '2026-06-23 10:41:01'),
(5, NULL, NULL, 'Budget Reduction', '', '', '2026-06-23 10:41:21'),
(6, NULL, NULL, 'Budget Reduction', '', '', '2026-06-23 10:41:24'),
(7, NULL, NULL, 'Budget Reduction', '', '', '2026-06-23 10:41:30'),
(8, NULL, NULL, 'Budget Reduction', '', '', '2026-06-23 10:41:41'),
(9, NULL, NULL, 'Budget Reduction', '', '', '2026-06-23 10:42:06'),
(10, NULL, NULL, 'Budget Reduction', '', '', '2026-06-23 10:42:06'),
(11, NULL, NULL, 'Budget Reduction', '', '', '2026-06-23 10:42:06'),
(12, NULL, NULL, 'Budget Reduction', '', '', '2026-06-23 10:42:06'),
(13, NULL, NULL, 'Budget Reduction', '', '', '2026-06-23 10:42:07'),
(14, 125, NULL, 'Date/Budget Adjustment', 'End: 2026-06-25 | Budget: 30000.00', 'End: 2026-06-30 | Budget: 80000', '2026-06-23 10:45:29'),
(15, 125, NULL, 'Date/Budget Adjustment', 'End: 2026-06-30 | Budget: 80000.00', 'End: 2026-06-27 | Budget: 50000', '2026-06-23 10:47:39'),
(16, 124, NULL, 'Date/Budget Adjustment', 'End: 2026-07-01 | Budget: 5000.00', 'End: 2026-06-26 | Budget: 2222.2222222222', '2026-06-24 03:26:02'),
(17, 124, NULL, 'Date/Budget Adjustment', 'End: 2026-06-26 | Budget: 2222.22', 'End: 2026-06-25 | Budget: 1666.665', '2026-06-24 03:26:20'),
(18, 124, NULL, 'Date/Budget Adjustment', 'End: 2026-06-25 | Budget: 1666.67', 'End: 2026-06-27 | Budget: 2777.7833333333', '2026-06-24 03:31:10'),
(19, 127, NULL, 'Date/Budget Adjustment', 'End: 2026-06-27 | Budget: 534534.00', 'End: 2026-06-30 | Budget: 935434.5', '2026-06-24 03:51:23'),
(20, 121, NULL, 'Date/Budget Adjustment', 'End: 2026-06-29 | Budget: 42342.00', 'End: 2026-07-04 | Budget: 84684', '2026-06-24 04:27:38'),
(21, 131, NULL, 'Date/Budget Adjustment', 'End: 2026-06-30 | Budget: 50000.00', 'End: 2026-07-04 | Budget: 78571.428571429', '2026-06-24 04:36:26'),
(22, 131, NULL, 'Budget Reduction', '78571.43', '50000', '2026-06-24 04:37:01'),
(23, 131, NULL, 'Date Adjustment', '2026-07-04', '2026-07-08', '2026-06-24 04:41:48'),
(24, 131, NULL, 'Date Adjustment', '2026-07-08', '2026-07-17', '2026-06-24 04:45:10'),
(25, 116, NULL, 'Budget Reduction', '40100.00', '30000', '2026-06-24 08:12:52'),
(26, 138, NULL, 'Budget Reduction', '343534.00', '30000', '2026-06-24 08:40:35'),
(27, 140, NULL, 'Date Adjustment', '2026-06-26', '2026-06-27', '2026-06-24 09:18:48'),
(28, 140, NULL, 'Budget Reduction', '5000.00', '500', '2026-06-24 10:58:56'),
(29, 140, NULL, 'Budget Approval', '25000', '55000', '2026-06-25 03:37:43'),
(30, 140, NULL, 'Budget Approval', '55000', '112000', '2026-06-25 03:40:39'),
(31, 140, NULL, 'Date Adjustment', '2026-06-27', '2026-06-30', '2026-06-25 03:52:02'),
(32, 138, NULL, 'Budget Approval', '30000', '33000', '2026-06-25 06:28:28'),
(33, 144, NULL, 'Budget Approval', '645645', '650645', '2026-06-25 07:56:30'),
(34, 144, NULL, 'Budget Approval', '650645', '651145', '2026-06-25 08:19:26'),
(35, 144, NULL, 'Budget Approval', '651145', '651245', '2026-06-25 08:21:44'),
(36, 144, NULL, 'Budget Approval', '651245', '651345', '2026-06-25 08:25:34'),
(37, 144, NULL, 'Budget Reduction', '651345.00', '50000', '2026-06-25 08:25:46'),
(38, 144, NULL, 'Budget Reduction', '50000.00', '4000000', '2026-06-25 08:28:31'),
(39, 144, NULL, 'Budget Reduction', '4000000.00', '500000000', '2026-06-25 08:29:42'),
(40, 144, NULL, 'Budget Reduction', '500000000.00', '20000', '2026-06-25 08:30:17'),
(41, 144, NULL, 'Budget Reduction', '20000.00', '30000', '2026-06-25 08:33:37'),
(42, 144, NULL, 'Budget Reduction', '30000.00', '20000', '2026-06-25 08:43:48'),
(43, 144, NULL, 'Budget Reduction', '20000.00', '30000', '2026-06-25 08:44:12'),
(44, 144, NULL, 'Budget Reduction', '30000.00', '20000', '2026-06-25 08:54:28'),
(45, 144, NULL, 'Budget Reduction', '20000.00', '17000', '2026-06-25 08:59:37'),
(46, 144, NULL, 'Budget Reduction', '17000.00', '15000', '2026-06-25 09:01:03'),
(47, 144, 6, 'Budget Approval', '15000', '15500', '2026-06-25 09:02:32'),
(48, 144, 8, 'Budget Reduction', '15500.00', '16000', '2026-06-25 09:13:11'),
(49, 144, 8, 'Budget Reduction', '16000.00', '14000', '2026-06-25 09:27:09'),
(50, 144, 8, 'Date Adjustment', '2026-06-30', '2026-07-03', '2026-06-25 09:32:35'),
(51, 148, 8, 'Date Adjustment', '2026-06-30', '2026-07-04', '2026-06-25 10:31:30'),
(52, 148, 7, 'Budget Approval', '10000', '12000', '2026-06-25 10:33:29'),
(53, 148, 8, 'Budget Reduction', '12000.00', '6000', '2026-06-25 10:35:23');

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
(7, 118, '2026-06-23', 9000.00, '2026-06-23 05:04:35'),
(8, 138, '2026-06-24', 6000.00, '2026-06-25 04:31:00'),
(9, 138, '2026-06-25', 7500.00, '2026-06-25 04:31:00'),
(10, 144, '2026-06-25', 2000.00, '2026-06-25 09:33:42'),
(11, 144, '2026-06-25', 2000.00, '2026-06-25 09:35:52'),
(12, 143, '2026-06-25', 70000.00, '2026-06-25 10:02:15'),
(13, 142, '2026-06-25', 10000.00, '2026-06-25 10:03:23');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_inventory_allocations`
--

CREATE TABLE `schedule_inventory_allocations` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `rate_card_id` int(11) NOT NULL,
  `allocation_date` date NOT NULL,
  `quantity_allocated` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `cost` decimal(15,2) DEFAULT NULL,
  `rate_card_id` int(11) DEFAULT NULL,
  `scheduled_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_items`
--

INSERT INTO `schedule_items` (`id`, `schedule_id`, `content_item_id`, `platform_id`, `placement_id`, `quantity`, `media_file`, `cost`, `rate_card_id`, `scheduled_date`) VALUES
(120, 114, 8, 1, 4, 3, NULL, 3000.00, NULL, NULL),
(121, 115, 1, 1, 7, 1, NULL, 2000.00, NULL, NULL),
(122, 115, 8, 1, 4, 3, NULL, 3000.00, NULL, NULL),
(123, 116, 1, 1, 7, 1, NULL, 2000.00, NULL, NULL),
(125, 118, 1, 1, 7, 3, NULL, 6000.00, NULL, NULL),
(126, 119, 7, 2, 4, 2, NULL, 10000.00, NULL, NULL),
(127, 119, 8, 1, 4, 1, NULL, 1000.00, NULL, NULL),
(128, 120, 8, 1, 4, 1, NULL, 1000.00, NULL, NULL),
(129, 121, 7, 2, 4, 1, NULL, 5000.00, NULL, NULL),
(130, 122, 7, 2, 4, 1, NULL, 5000.00, NULL, NULL),
(131, 123, 8, 1, 4, 1, NULL, 1000.00, NULL, NULL),
(132, 124, 8, 1, 4, 1, NULL, 1000.00, NULL, NULL),
(133, 124, 7, 2, 4, 1, NULL, 5000.00, NULL, NULL),
(134, 126, 8, 1, 4, 3, NULL, 3000.00, NULL, NULL),
(141, 131, 8, 1, 4, 2, NULL, 2000.00, NULL, NULL),
(142, 131, 7, 2, 4, 5, NULL, 25000.00, NULL, NULL),
(143, 132, 8, 1, 4, 1, NULL, 1000.00, NULL, NULL),
(150, 136, 8, 1, 4, 1, NULL, 1000.00, NULL, NULL),
(151, 136, 7, 2, 4, 1, NULL, 5000.00, NULL, NULL),
(152, 138, 8, 1, 4, 33, NULL, 33000.00, NULL, NULL),
(153, 139, 8, 1, 4, 1, NULL, 1000.00, NULL, NULL),
(154, 140, 8, 1, 4, 56, NULL, 56000.00, NULL, NULL),
(155, 141, 8, 1, 4, 5, NULL, 5000.00, NULL, NULL),
(156, 141, 7, 2, 4, 1, NULL, 5000.00, NULL, NULL),
(157, 142, 8, 1, 4, 5, NULL, 5000.00, NULL, NULL),
(158, 143, 8, 1, 4, 1, NULL, 1000.00, NULL, NULL),
(159, 144, 8, 1, 4, 10, NULL, 10000.00, NULL, NULL),
(163, 154, 1, 1, 7, 1, NULL, 2000.00, 45, '2026-06-30'),
(164, 155, 1, 1, 7, 2, NULL, 4000.00, 45, '2026-06-30'),
(165, 156, 1, 1, 7, 1, NULL, 2000.00, 45, '2026-07-01'),
(166, 157, 7, 2, 4, 2, NULL, 10000.00, 46, '2026-07-04'),
(167, 158, 8, 1, 4, 2, NULL, 2000.00, 44, '2026-07-02'),
(168, 159, 2, 1, 1, 2, NULL, 1000.00, 47, '2026-07-15'),
(169, 160, 8, 1, 4, 2, NULL, 2000.00, 44, '2026-07-21'),
(170, 160, 7, 2, 4, 2, NULL, 10000.00, 46, '2026-07-21'),
(171, 161, 8, 1, 4, 1, NULL, 1000.00, 44, '2026-07-09'),
(172, 161, 2, 1, 1, 2, NULL, 1000.00, 47, '2026-07-09'),
(173, 163, 1, 1, 7, 6, NULL, 12000.00, 45, '2026-07-01'),
(174, 163, 8, 1, 4, 6, NULL, 6000.00, 44, '2026-07-01'),
(175, 164, 8, 1, 4, 4, NULL, 4000.00, 44, '2026-08-04'),
(176, 165, 8, 1, 4, 0, NULL, 0.00, 44, '2026-07-01'),
(177, 166, 8, 1, 4, 3, NULL, 3000.00, 44, '2026-07-09');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_item_media`
--

CREATE TABLE `schedule_item_media` (
  `schedule_item_id` int(11) DEFAULT NULL,
  `media_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_item_media`
--

INSERT INTO `schedule_item_media` (`schedule_item_id`, `media_id`) VALUES
(176, 4),
(176, 2),
(177, 2),
(177, 5);

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
(6, 'Nimal silva', 'nimal@swarnawahini.lk', '$2y$10$sow4G2FvvZyDic5594BzsuWu3iQkpdC.KbtXJJsBUdvig5JkcU/g.', 3),
(7, 'kamal', 'kamal@swarnawahini.lk', '$2y$10$4bhYuYAmBIy4lYLPIUQVIO/9Jk6vTkQ7KJ.UpSRVb2ceao/3XlJNK', 3),
(8, 'amali', 'amali@swarnawahini.lk', '$2y$10$djG0lql3N0nuXpEHMF15Q.3C0Xd2tct25Ve5IBGXoJ4cQXL6afH3m', 2);

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
-- Indexes for table `inventory_daily_capacity`
--
ALTER TABLE `inventory_daily_capacity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date_item` (`rate_card_id`,`capacity_date`);

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
-- Indexes for table `media_library`
--
ALTER TABLE `media_library`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reference_no` (`reference_no`),
  ADD KEY `fk_media_agency` (`agency_name`),
  ADD KEY `fk_media_client` (`client_name`);

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
-- Indexes for table `schedule_audit_log`
--
ALTER TABLE `schedule_audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedule_daily_costs`
--
ALTER TABLE `schedule_daily_costs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `schedule_inventory_allocations`
--
ALTER TABLE `schedule_inventory_allocations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_allocation` (`schedule_id`,`rate_card_id`,`allocation_date`),
  ADD KEY `rate_card_id` (`rate_card_id`);

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
-- Indexes for table `schedule_item_media`
--
ALTER TABLE `schedule_item_media`
  ADD KEY `schedule_item_id` (`schedule_item_id`),
  ADD KEY `media_id` (`media_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `inventory_daily_capacity`
--
ALTER TABLE `inventory_daily_capacity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `media_attachments`
--
ALTER TABLE `media_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `media_formats`
--
ALTER TABLE `media_formats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `media_library`
--
ALTER TABLE `media_library`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `platforms`
--
ALTER TABLE `platforms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rate_cards`
--
ALTER TABLE `rate_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT for table `schedule_approval_requests`
--
ALTER TABLE `schedule_approval_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `schedule_audit_log`
--
ALTER TABLE `schedule_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `schedule_daily_costs`
--
ALTER TABLE `schedule_daily_costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `schedule_inventory_allocations`
--
ALTER TABLE `schedule_inventory_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule_items`
--
ALTER TABLE `schedule_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `teledrama_series`
--
ALTER TABLE `teledrama_series`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- Constraints for table `schedule_inventory_allocations`
--
ALTER TABLE `schedule_inventory_allocations`
  ADD CONSTRAINT `schedule_inventory_allocations_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_inventory_allocations_ibfk_2` FOREIGN KEY (`rate_card_id`) REFERENCES `inventory` (`rate_card_id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_items`
--
ALTER TABLE `schedule_items`
  ADD CONSTRAINT `schedule_items_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_items_ibfk_2` FOREIGN KEY (`content_item_id`) REFERENCES `content_items` (`id`),
  ADD CONSTRAINT `schedule_items_ibfk_3` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`),
  ADD CONSTRAINT `schedule_items_ibfk_4` FOREIGN KEY (`placement_id`) REFERENCES `ad_placements` (`id`);

--
-- Constraints for table `schedule_item_media`
--
ALTER TABLE `schedule_item_media`
  ADD CONSTRAINT `schedule_item_media_ibfk_1` FOREIGN KEY (`schedule_item_id`) REFERENCES `schedule_items` (`id`),
  ADD CONSTRAINT `schedule_item_media_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `media_library` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
