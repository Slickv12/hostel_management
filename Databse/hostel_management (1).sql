-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 13, 2026 at 05:06 PM
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
-- Database: `hostel_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `actor_user_id` int(11) NOT NULL,
  `action_type` enum('student_approval','room_assignment','fee_update','leave_approved','leave_rejected','student_deleted') NOT NULL,
  `target_user_id` int(11) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `fee_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount_due` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','paid') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`fee_id`, `user_id`, `amount_due`, `due_date`, `status`) VALUES
(3, 1, 5000.00, '2025-05-01', 'pending'),
(4, 3, 0.00, '2025-05-01', 'paid'),
(5, 16, 1000.00, '2025-06-28', 'pending'),
(6, 17, 0.00, '2026-03-07', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`request_id`, `user_id`, `reason`, `start_date`, `end_date`, `status`, `approved_by`, `approved_at`) VALUES
(1, 1, 'family event', '2025-03-23', '2025-03-27', 'approved', NULL, NULL),
(2, 1, 'family event', '2025-03-23', '2025-03-27', 'approved', NULL, NULL),
(3, 1, 'family event', '2025-03-24', '2025-03-26', 'rejected', NULL, NULL),
(4, 1, 'family event', '2025-03-26', '2025-03-28', 'rejected', NULL, NULL),
(5, 1, 'family event', '2025-03-26', '2025-03-28', 'pending', NULL, NULL),
(6, 1, 'family event', '2025-03-26', '2025-03-28', 'pending', NULL, NULL),
(7, 1, 'family leave', '2025-03-27', '2025-03-31', 'pending', NULL, NULL),
(8, 1, 'family event', '2025-03-28', '2025-03-31', 'pending', NULL, NULL),
(9, 1, 'family event', '2025-03-29', '2025-03-31', 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `notice_id` int(11) NOT NULL,
  `rector_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`notice_id`, `rector_id`, `message`, `created_at`) VALUES
(1, 6, 'Hostel mess will be closed tomorrow', '2025-03-23 08:27:15');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_number`, `capacity`) VALUES
(1, '101', 3),
(2, '102', 2),
(3, '103', 3),
(4, '104', 2),
(5, '105', 3);

-- --------------------------------------------------------

--
-- Table structure for table `room_allocation`
--

CREATE TABLE `room_allocation` (
  `allocation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_allocation`
--

INSERT INTO `room_allocation` (`allocation_id`, `user_id`, `room_id`) VALUES
(1, 1, 1),
(2, 3, 1),
(3, 4, 3),
(4, 5, 2),
(5, 12, 3),
(13, 13, 1),
(14, 14, 3),
(15, 15, 2),
(16, 16, 4);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('student','rector') NOT NULL,
  `status` enum('pending','active') NOT NULL DEFAULT 'pending',
  `phone` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `course_details` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `user_type`, `status`, `phone`, `address`, `course_details`) VALUES
(1, 'Ritesh shende', 'RS123@mail.com', '$2y$10$8a/bR7zX7NNqGzoB/vGnI.B5R53lEOCaTOmPnpWjBwBUixqWbvDYu', 'student', 'pending', '1234567890', 'phaltan', NULL),
(3, 'aniket nanaware', 'AN123@mail.com', '$2y$10$ejm4sqC0o0Po6xd9skrWy.9Na31A1FDhszay88fjtptO/ZNBkazOG', 'student', 'pending', '1267536834', 'baramati', NULL),
(4, 'harsh shinde', 'HS123@mail.com', '$2y$10$gATlSaethr3veabE3K1lYea.4EDeXoJ6CH/1me9RGuVno.PArlyfK', 'student', 'pending', '5671234789', 'satara', NULL),
(5, 'sanket shinde', 'SS123@mail.com', '$2y$10$UjauxAMNjgX7uwyLAIs4QuQ88BjWhRNKHF6Tk..ppww8b/83WBm4S', 'student', 'pending', '1276459872', 'baramati', NULL),
(6, 'admin', 'ADMIN123@mail.com', '$2y$10$bc4TwXJO.CLnkhs/rikdwe5gLrqydfYBNXUQrbChiz9gtfX69tgU.', 'rector', 'active', '8972555776', 'baramati', NULL),
(11, 'karan power', 'KP123@mail.com', '$2y$10$Yt0qYA2/Ir6vgRkgEet7T.cKhGYWknhbgqfZynd69CExQ7wEQkJti', 'student', 'pending', '6734927343', 'baramati', NULL),
(12, 'student', 'S123@mail.com', '$2y$10$tK9AVDUewZoG3rHw2KXFi.dm6sH5UpyluLw.E0rb/9sxWzXjhogUi', 'student', 'pending', '2423434545', 'baramati', NULL),
(13, 'student1', 'S1123@mail.com', '$2y$10$0a7iNthpCCenzV.vsf3zpunT8pjyjsMjSEGZavb7nCHxnv7ftDANe', 'student', 'pending', '1234567890', 'baramati', NULL),
(14, 'student2', 'S2123@mail.com', '$2y$10$q76PjTiXHHhZbnAzqkRa/OaEVJpbRK3yk7kZwJXRx92ig8H.AHREW', 'student', 'pending', '5671234789', 'baramati', NULL),
(15, 'vaibhav shinde', 'VS123@mail.com', '$2y$10$TJsITYAAlfT1NQNd6x1dveylLgMThJo.A3tuHc4zXi6Do7VEMXeI6', 'student', 'pending', '4565132598', 'phaltan', NULL),
(16, 'vijay bhope', 'VB123@mail.com', '$2y$10$RcZ9yzDkbSWNv0w1BJ6u2OT9k7ZeAEF9OXSMIAJQWu6fL0e0aS.oG', 'student', 'pending', '8972555776', 'satara', NULL),
(17, 'user', 'u123@gmail.com', '$2y$10$SukUnfY6C68h04OrNy.Bmu7pyiyYx4pRGB2VmyNRRG1ohswCZuXSa', 'student', 'pending', '1234567890', 'baramati', NULL),
(18, 'Main Admin', 'mainadmin@hostel.com', '$2y$10$QOtNBdtzrG89zc5I6IvzVeyPMww25dEGPJYleto0gDsqMcuPsXvX.', 'rector', 'active', '9999999999', 'Hostel Office', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_activity_actor` (`actor_user_id`),
  ADD KEY `idx_activity_target` (`target_user_id`),
  ADD KEY `idx_activity_action` (`action_type`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`fee_id`),
  ADD UNIQUE KEY `uq_fees_user` (`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_leave_approved_by` (`approved_by`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`notice_id`),
  ADD KEY `idx_notices_rector_id` (`rector_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `room_number` (`room_number`);

--
-- Indexes for table `room_allocation`
--
ALTER TABLE `room_allocation`
  ADD PRIMARY KEY (`allocation_id`),
  ADD UNIQUE KEY `uq_room_allocation_user` (`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `notice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `room_allocation`
--
ALTER TABLE `room_allocation`
  MODIFY `allocation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_activity_target` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `fees`
--
ALTER TABLE `fees`
  ADD CONSTRAINT `fees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `fk_leave_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notices`
--
ALTER TABLE `notices`
  ADD CONSTRAINT `fk_notices_rector` FOREIGN KEY (`rector_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `room_allocation`
--
ALTER TABLE `room_allocation`
  ADD CONSTRAINT `room_allocation_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_allocation_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
