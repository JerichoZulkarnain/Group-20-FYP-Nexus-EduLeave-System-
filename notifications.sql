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
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(2, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: AZRIL BIN ZAFRI', 'account', 0, '2026-06-08 22:11:03'),
(3, 22, 'Report Generated', 'Absence Summary Report generated for 05 May 2026 to 09 Jun 2026', 'report', 0, '2026-06-09 08:53:21'),
(4, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: AZRIL BIN ZAFRI', 'account', 0, '2026-06-09 09:11:47'),
(5, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: AZRIL BIN ZAFRI', 'account', 0, '2026-06-09 09:12:32'),
(6, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: AZRIL BIN ZAFRI', 'account', 0, '2026-06-09 16:43:50'),
(7, 22, 'Report Generated', 'Absence Summary Report generated for 01 May 2026 to 09 Jun 2026', 'report', 0, '2026-06-09 16:45:52'),
(8, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: AZRIL BIN ZAFRI', 'account', 0, '2026-06-09 16:57:09'),
(9, 22, 'Report Generated', 'Absence Summary Report generated for 08 May 2026 to 08 Jun 2026', 'report', 0, '2026-06-09 16:59:44'),
(10, 22, 'Report Generated', 'Absence Summary Report generated for 08 Jun 2026 to 09 Jun 2026', 'report', 0, '2026-06-09 16:59:59'),
(11, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: AHMAD AKIM BIN SUFI', 'account', 0, '2026-06-11 12:04:18'),
(12, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: AZRIL BIN ZAFRI', 'account', 0, '2026-06-11 12:04:24'),
(13, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: ISKANDAR DANIAL ', 'account', 0, '2026-06-11 12:04:38'),
(14, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: NUR SURAYA BINTI HANAFI', 'account', 0, '2026-06-11 12:04:49'),
(15, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: NURUL ANIS BINTI ABU BAKAR', 'account', 0, '2026-06-11 12:05:01'),
(16, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: AHMAD AKIM BIN SUFI', 'account', 0, '2026-06-11 12:05:30'),
(17, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: AZRIL BIN ZAFRI', 'account', 0, '2026-06-11 12:05:34'),
(18, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: ISKANDAR DANIAL ', 'account', 0, '2026-06-11 12:05:40'),
(19, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: NUR SURAYA BINTI HANAFI', 'account', 0, '2026-06-11 12:05:51'),
(20, 22, 'Lecturer Assignment', 'Updated lecturer assignments for student: NURUL ANIS BINTI ABU BAKAR', 'account', 0, '2026-06-11 12:06:01'),
(21, 22, 'Report Generated', 'Absence Summary Report generated for 07 Jun 2026 to 11 Jun 2026', 'report', 0, '2026-06-11 20:04:07'),
(22, 24, 'Leave Update', 'Your Emergency leave application has been approved by ALI BIN ABU. Waiting for remaining lecturers.', 'leave', 1, '2026-06-11 20:59:23'),
(23, 24, 'Leave Approved', 'Your Emergency leave application has been fully approved by all lecturers.', 'leave', 1, '2026-06-11 20:59:41'),
(24, 24, 'Leave Update', 'Your Medical leave application has been approved by ALI BIN ABU. Waiting for remaining lecturers.', 'leave', 1, '2026-06-11 21:43:01'),
(25, 24, 'Leave Approved', 'Your Medical leave application has been fully approved by all lecturers.', 'leave', 1, '2026-06-11 21:43:42'),
(26, 24, 'Leave Update', 'Your Long Vacation leave application has been approved by ALI BIN ABU. Waiting for remaining lecturers.', 'leave', 1, '2026-06-11 21:46:38'),
(27, 24, 'Leave Update', 'Your Long Vacation leave application has been approved by ALI BIN ABU for all their subjects. Waiting for remaining lecturers.', 'leave', 1, '2026-06-11 22:05:52'),
(28, 24, 'Leave Approved', 'Your Long Vacation leave application has been fully approved by all lecturers.', 'leave', 1, '2026-06-11 22:07:30'),
(29, 30, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Emergency leave application for subject: 244444', 'leave', 0, '2026-06-12 04:33:13'),
(30, 23, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Emergency leave application for subject: e3r3r3', 'leave', 0, '2026-06-12 04:33:13'),
(31, 30, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Emergency leave application for subject: 123', 'leave', 0, '2026-06-12 04:36:48'),
(32, 23, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Emergency leave application for subject: 124', 'leave', 0, '2026-06-12 04:36:48'),
(33, 30, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 123', 'leave', 0, '2026-06-12 04:41:09'),
(34, 30, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 124', 'leave', 0, '2026-06-12 04:41:09'),
(35, 23, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 125', 'leave', 0, '2026-06-12 04:41:09'),
(36, 23, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 127', 'leave', 0, '2026-06-12 04:41:09'),
(37, 33, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 126', 'leave', 0, '2026-06-12 04:41:09'),
(38, 33, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 128', 'leave', 0, '2026-06-12 04:41:09'),
(39, 34, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 129', 'leave', 0, '2026-06-12 04:41:09'),
(40, 30, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Medical leave application for subject: DSPD2733', 'leave', 0, '2026-06-12 04:56:11'),
(41, 23, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Medical leave application for subject: DSPD1233', 'leave', 0, '2026-06-12 04:56:11'),
(42, 24, 'Leave Update', 'Your Medical leave application has been approved by ALI BIN ABU for all their subjects.', 'leave', 0, '2026-06-12 04:56:35'),
(43, 24, 'Leave Approved', 'Your Medical leave application has been fully approved.', 'leave', 0, '2026-06-12 04:57:22'),
(44, 30, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: DSPD1234', 'leave', 0, '2026-06-12 04:59:09'),
(45, 30, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: DSPD1235', 'leave', 0, '2026-06-12 04:59:09'),
(46, 23, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: DSPD1326', 'leave', 0, '2026-06-12 04:59:09'),
(47, 23, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: DSPD1237', 'leave', 0, '2026-06-12 04:59:09'),
(48, 33, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: DSPD1236', 'leave', 0, '2026-06-12 04:59:09'),
(49, 33, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: DSPD7123', 'leave', 0, '2026-06-12 04:59:09'),
(50, 34, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: DSPD1223', 'leave', 0, '2026-06-12 04:59:09'),
(51, 24, 'Leave Update', 'Your Long Vacation leave application has been approved by ALI BIN ABU for all their subjects.', 'leave', 0, '2026-06-12 04:59:39'),
(52, 24, 'Leave Update', 'Your Long Vacation leave application has been approved by AHMAD YUSRI BIN JUNAIDI for all their subjects.', 'leave', 0, '2026-06-12 05:00:39'),
(53, 24, 'Leave Update', 'Your Long Vacation leave application has been approved by HAFIZ BIN MOHD JAMALUDIN for all their subjects.', 'leave', 0, '2026-06-12 05:01:14'),
(54, 24, 'Leave Approved', 'Your Long Vacation leave application has been fully approved.', 'leave', 0, '2026-06-12 05:02:19'),
(55, 30, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Emergency leave application for subject: DSPD2733', 'leave', 0, '2026-06-12 05:40:54'),
(56, 23, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Emergency leave application for subject: DSPD1233', 'leave', 0, '2026-06-12 05:40:54'),
(57, 30, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Medical leave application for subject: DSPD2733', 'leave', 0, '2026-06-12 05:49:45'),
(58, 23, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Medical leave application for subject: DSPD1233', 'leave', 0, '2026-06-12 05:49:45'),
(59, 30, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Medical leave application for subject: w2', 'leave', 0, '2026-06-12 05:59:11'),
(60, 23, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Medical leave application for subject: w3', 'leave', 0, '2026-06-12 05:59:11'),
(61, 30, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 122', 'leave', 0, '2026-06-12 06:03:23'),
(62, 30, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 121', 'leave', 0, '2026-06-12 06:03:23'),
(63, 23, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 123', 'leave', 0, '2026-06-12 06:03:23'),
(64, 23, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 124', 'leave', 0, '2026-06-12 06:03:23'),
(65, 33, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 125', 'leave', 0, '2026-06-12 06:03:23'),
(66, 33, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 126', 'leave', 0, '2026-06-12 06:03:23'),
(67, 34, 'New Leave Application', 'AZRIL BIN ZAFRI has submitted a Long Vacation leave application for subject: 127', 'leave', 0, '2026-06-12 06:03:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
