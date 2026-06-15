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
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration` varchar(50) NOT NULL,
  `reason` text NOT NULL,
  `student_section` varchar(10) DEFAULT NULL,
  `mc_file` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `form_token` varchar(100) DEFAULT NULL,
  `form_generated_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `evidence_file` varchar(255) DEFAULT NULL,
  `selected_lecturers` text DEFAULT NULL,
  `student_statement` text DEFAULT NULL,
  `student_signature` varchar(255) DEFAULT NULL,
  `course` varchar(255) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `subject_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_applications`
--

INSERT INTO `leave_applications` (`id`, `student_id`, `leave_type`, `start_date`, `end_date`, `duration`, `reason`, `student_section`, `mc_file`, `status`, `form_token`, `form_generated_at`, `rejection_reason`, `created_at`, `evidence_file`, `selected_lecturers`, `student_statement`, `student_signature`, `course`, `phone_number`, `subject_name`) VALUES
(82, 24, 'Medical', '2026-06-15', '2026-06-15', '1 Days', 'Lung Infection', '40', NULL, 'Approved', NULL, NULL, NULL, '2026-06-11 20:56:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(83, 24, 'Long Vacation', '2026-06-19', '2026-06-26', '8 Days', 'Family Trip to Korea', '40', NULL, 'Approved', '6501d8e931655d3a99c3c07aaf6b3ea4', NULL, NULL, '2026-06-11 20:59:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(84, 24, 'Emergency', '2026-06-15', '2026-06-15', '1 Days', 'mak masuk wad', '40', NULL, 'Cancelled', NULL, NULL, NULL, '2026-06-11 21:40:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(85, 24, 'Medical', '2026-06-15', '2026-06-15', '1 Days', 'mak masuk wad', '40', NULL, 'Cancelled', NULL, NULL, NULL, '2026-06-11 21:49:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(86, 24, 'Medical', '2026-06-15', '2026-06-15', '1 Days', 'wssw', '34', NULL, 'Pending_Approvals', NULL, NULL, NULL, '2026-06-11 21:59:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(87, 24, 'Long Vacation', '2026-07-10', '2026-07-17', '8 Days', 'FAMILY TRIP TO TURKEY', '40', NULL, 'Pending_Approvals', 'daa84ea278377915afca61085c41a20f', NULL, NULL, '2026-06-11 22:03:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD CONSTRAINT `leave_applications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
