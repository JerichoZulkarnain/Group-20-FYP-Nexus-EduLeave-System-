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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `nexus_id` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `lecturer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `nexus_id`, `email`, `username`, `password`, `role`, `status`, `lecturer_id`, `created_at`) VALUES
(22, 'AINA ATIQAH HUDA BINTI AHMAD YUSRI', 'ADM1234', 'ainaatiqahhuda@gmail.com', 'ainaatiqah', '$2y$10$yeBS5efvXxoiE17FOQa4SO6dIM1Dy3Lhme0nSD3QROsHSeeHR.EoK', 'admin', 'active', NULL, '2026-06-07 13:31:33'),
(23, 'ALI BIN ABU', 'LEC1234', 'meeyoora7@gmail.com', 'drali1993', '$2y$10$RmKEo9pxYRmBLTuGgg2M6O.3gUtBvURNwjEYxQFy/vRDJ6Pnhz4rO', 'lecturer', 'active', NULL, '2026-06-07 14:24:15'),
(24, 'AZRIL BIN ZAFRI', 'STU1234', 'azrilzafri@gmail.com', 'azrilzafri11', '$2y$10$3wZnyyEk1kDfQuimAfNVXeOcUIZXN1EAQvJq/bPfQ1c1tMtUp4dWS', 'student', 'active', 23, '2026-06-07 14:26:45'),
(25, 'ALIN THURAYA BINTI ABD RAHMAN ', 'ADM1233', 'alin@gmail.com', 'alinthuraya', '$2y$10$ZQ02bK86BvidNum.azvLFeNGFomScGiRxx3JziVeyR3XJNbzo4bSi', 'admin', 'active', NULL, '2026-06-11 09:48:20'),
(26, 'AHMAD AKIM BIN SUFI', 'STU1235', 'ahmadakim@gmail.com', 'ahmadakim', '$2y$10$y8tDC6xc4bBQsVV3UekX5eI0jqWOQPpmlSR2QB.iKH8Svwgk9czfe', 'student', 'active', NULL, '2026-06-11 09:50:16'),
(27, 'ISKANDAR DANIAL ', 'STU1236', 'iskandardanial@gmail.com', 'iskandardanial', '$2y$10$7YrHcNfICkTRHc8XIVepw.E71TrT6yCK0Lwg5wfTF6HZpXiIrT9rS', 'student', 'inactive', NULL, '2026-06-11 09:51:20'),
(28, 'NUR SURAYA BINTI HANAFI', 'STU1237', 'ainaatiqahhudaahmadyusri@gmail.com', 'nursuraya', '$2y$10$eLZJDMYVragGtu5N5fiN9.jTySVMO6wiUYVOHdxF5zIQ1erm1G8OC', 'student', 'active', NULL, '2026-06-11 09:52:00'),
(29, 'NURUL ANIS BINTI ABU BAKAR', 'STU1238', 'nurulanis06@gmail.com', 'nurulanis', '$2y$10$.h/tNkgHLbWRlI.12OU3Peb4TKN6RMWxZMSIZ5QWL0HcgPuJ2J/eO', 'student', 'active', NULL, '2026-06-11 09:53:21'),
(30, 'AHMAD YUSRI BIN JUNAIDI', 'LEC1233', 'ahmadyusri@gmail.com', 'ahmadyusri69', '$2y$10$x4mAStX5ioxhvbe.5bfSqenePHxqJlzCNpWk58qr/IaDjjn9IYaMG', 'lecturer', 'active', NULL, '2026-06-11 09:54:54'),
(31, 'SAIDATUL SHILA BINTI MOHD ARIP', 'LEC1232', 'saidatulshila@gmail.com', 'saidashie', '$2y$10$WzJ/Lck6EAtMK2mbRanNKOjiQahGAEX6Pg7SZQNyF.gLgLVoAtVa.', 'lecturer', 'active', NULL, '2026-06-11 09:58:09'),
(32, 'JAMILAH BINTI MUHAMMAD', 'LEC1231', 'jamilahmuhammad@gmail.com', 'jamilahmuhammad', '$2y$10$r/f6f9GaP2dvB/PKq2PUnO6gBs.TXIUOytZlBUBXweFoRlBdGZufW', 'lecturer', 'active', NULL, '2026-06-11 09:58:48'),
(33, 'HAFIZ BIN MOHD JAMALUDIN', 'LEC1230', 'hafizmohdjamaludin@gmail.com', 'hafizjamal', '$2y$10$4NMNRIPZcNt3EF9CxY9q1ODFbBXRbeRWDl7OhOuSfLCway9f6LIwK', 'lecturer', 'active', NULL, '2026-06-11 10:00:18'),
(34, 'SYAFIQ MUHAMMAD BIN OSMAN', 'LEC1239', 'syafiqmuhammad@gmail.com', 'syafiqosman', '$2y$10$sv/UQxZEV7aocasZvvs/xuSbJHiBSGFp/M1e1UaHXarxN1Kvhg.Yq', 'lecturer', 'active', NULL, '2026-06-11 10:01:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nexus_id` (`nexus_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
