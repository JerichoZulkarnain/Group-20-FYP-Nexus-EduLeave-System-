-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 01:16 AM
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
-- Database: `nexus_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_lecturers`
--

CREATE TABLE `student_lecturers` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_lecturers`
--

INSERT INTO `student_lecturers` (`id`, `student_id`, `lecturer_id`, `assigned_at`) VALUES
(8, 24, 23, '2026-06-09 04:11:03'),
(9, 26, 30, '2026-06-11 18:04:18'),
(10, 26, 23, '2026-06-11 18:04:18'),
(11, 26, 33, '2026-06-11 18:04:18'),
(12, 24, 30, '2026-06-11 18:04:24'),
(13, 24, 33, '2026-06-11 18:04:24'),
(14, 27, 33, '2026-06-11 18:04:38'),
(15, 27, 32, '2026-06-11 18:04:38'),
(16, 27, 31, '2026-06-11 18:04:38'),
(17, 28, 23, '2026-06-11 18:04:49'),
(18, 28, 32, '2026-06-11 18:04:49'),
(19, 28, 31, '2026-06-11 18:04:49'),
(20, 29, 30, '2026-06-11 18:05:01'),
(21, 29, 33, '2026-06-11 18:05:01'),
(23, 26, 31, '2026-06-11 18:05:30'),
(24, 24, 34, '2026-06-11 18:05:34'),
(25, 27, 23, '2026-06-11 18:05:40'),
(26, 28, 34, '2026-06-11 18:05:51'),
(27, 29, 32, '2026-06-11 18:06:01'),
(28, 29, 34, '2026-06-11 18:06:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `student_lecturers`
--
ALTER TABLE `student_lecturers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`student_id`,`lecturer_id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `student_lecturers`
--
ALTER TABLE `student_lecturers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
