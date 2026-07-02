-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 30, 2026 at 05:37 AM
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
(1, 44, '2026-06-27', 5),
(3, 44, '2026-06-26', 5),
(4, 45, '2026-06-29', 4),
(5, 46, '2026-07-16', 5),
(6, 44, '2026-07-24', 5),
(9, 47, '2026-06-27', 5),
(10, 44, '2026-06-30', 10),
(12, 45, '2026-06-30', 12),
(13, 44, '2026-07-01', 5),
(14, 45, '2026-07-01', 4),
(15, 45, '2026-07-17', 22);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory_daily_capacity`
--
ALTER TABLE `inventory_daily_capacity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date_item` (`rate_card_id`,`capacity_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory_daily_capacity`
--
ALTER TABLE `inventory_daily_capacity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
