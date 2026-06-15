-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 01:15 AM
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
-- Table structure for table `lecturer_signatures`
--

CREATE TABLE `lecturer_signatures` (
  `id` int(11) NOT NULL,
  `leave_id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Signed') DEFAULT 'Pending',
  `signed_at` datetime DEFAULT NULL,
  `signature_image` varchar(500) DEFAULT NULL,
  `approval_reason` text DEFAULT NULL,
  `signature_data` text DEFAULT NULL,
  `signed_form` varchar(255) DEFAULT NULL,
  `approved_days` varchar(50) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `chairman_signature` varchar(255) DEFAULT NULL,
  `chairman_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturer_signatures`
--

INSERT INTO `lecturer_signatures` (`id`, `leave_id`, `lecturer_id`, `subject`, `section`, `status`, `signed_at`, `signature_image`, `approval_reason`, `signature_data`, `signed_form`, `approved_days`, `approval_date`, `chairman_signature`, `chairman_date`) VALUES
(135, 82, 30, 'DSPD2733', NULL, 'Approved', '2026-06-11 22:57:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(136, 82, 23, 'DSPD1233', NULL, 'Approved', '2026-06-11 22:56:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(137, 83, 30, 'DSPD1234', NULL, 'Approved', '2026-06-11 23:00:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(138, 83, 30, 'DSPD1235', NULL, 'Approved', '2026-06-11 23:00:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(139, 83, 23, 'DSPD1326', NULL, 'Approved', '2026-06-11 22:59:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(140, 83, 23, 'DSPD1237', NULL, 'Approved', '2026-06-11 22:59:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(141, 83, 33, 'DSPD1236', NULL, 'Approved', '2026-06-11 23:01:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(142, 83, 33, 'DSPD7123', NULL, 'Approved', '2026-06-11 23:01:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(143, 83, 34, 'DSPD1223', NULL, 'Approved', '2026-06-11 23:02:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(144, 84, 30, 'DSPD2733', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(145, 84, 23, 'DSPD1233', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(146, 85, 30, 'DSPD2733', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(147, 85, 23, 'DSPD1233', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(148, 86, 30, 'w2', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(149, 86, 23, 'w3', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(150, 87, 30, '122', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(151, 87, 30, '121', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(152, 87, 23, '123', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(153, 87, 23, '124', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(154, 87, 33, '125', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(155, 87, 33, '126', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(156, 87, 34, '127', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lecturer_signatures`
--
ALTER TABLE `lecturer_signatures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_id` (`leave_id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lecturer_signatures`
--
ALTER TABLE `lecturer_signatures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lecturer_signatures`
--
ALTER TABLE `lecturer_signatures`
  ADD CONSTRAINT `lecturer_signatures_ibfk_1` FOREIGN KEY (`leave_id`) REFERENCES `leave_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lecturer_signatures_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
