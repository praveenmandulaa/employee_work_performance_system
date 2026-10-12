-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2026 at 07:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `employee_work_performance`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `details` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 1, 'login', 'User signed in', '2026-10-12 17:17:59'),
(2, 1, 'logout', 'User signed out', '2026-10-12 17:18:08'),
(3, 2, 'login', 'User signed in', '2026-10-12 17:18:37'),
(4, 2, 'logout', 'User signed out', '2026-10-12 17:19:08'),
(5, 3, 'login', 'User signed in', '2026-10-12 17:19:22'),
(6, 3, 'task_status_changed', 'Task #1 changed to completed', '2026-10-12 17:19:43'),
(7, 3, 'daily_update_created', 'Submitted daily update for 2026-10-12', '2026-10-12 17:20:11'),
(8, 3, 'logout', 'User signed out', '2026-10-12 17:21:09'),
(9, 2, 'login', 'User signed in', '2026-10-12 17:21:27'),
(10, 2, 'logout', 'User signed out', '2026-10-12 17:23:19'),
(11, 1, 'login', 'User signed in', '2026-10-12 17:23:47'),
(12, 1, 'user_created', 'Created employee account for praveenmandula6@gmail.com', '2026-10-12 17:25:08'),
(13, 1, 'user_created', 'Created manager account for uday@gmail.com', '2026-10-12 17:25:34'),
(14, 1, 'logout', 'User signed out', '2026-10-12 17:26:34'),
(15, 6, 'login', 'User signed in', '2026-10-12 17:26:48'),
(16, 6, 'logout', 'User signed out', '2026-10-12 17:26:56'),
(17, 5, 'login', 'User signed in', '2026-10-12 17:27:06'),
(18, 5, 'logout', 'User signed out', '2026-10-12 17:27:16'),
(19, 1, 'login', 'User signed in', '2026-10-12 17:27:28'),
(20, 1, 'logout', 'User signed out', '2026-10-12 17:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `daily_updates`
--

CREATE TABLE `daily_updates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `work_date` date NOT NULL,
  `completed_work` text DEFAULT NULL,
  `todo_work` text DEFAULT NULL,
  `pending_work` text DEFAULT NULL,
  `blockers` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_updates`
--

INSERT INTO `daily_updates` (`id`, `user_id`, `work_date`, `completed_work`, `todo_work`, `pending_work`, `blockers`, `created_at`) VALUES
(1, 3, '2026-10-12', 'Reviewed dashboard layout and tested task status changes.', 'Validate report filters.', 'CSV export needs final verification.', 'Waiting for confirmation of report columns.', '2026-10-12 17:05:10'),
(2, 4, '2026-10-11', 'Investigated overdue filter behaviour.', 'Add test cases for completed tasks.', 'One edge case remains to verify.', '', '2026-10-12 17:05:10'),
(3, 3, '2026-10-12', 'test', 'test1', 'test2', 'test3', '2026-10-12 17:20:11');

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `reviewer_id` bigint(20) UNSIGNED NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `quality_score` tinyint(3) UNSIGNED NOT NULL,
  `communication_score` tinyint(3) UNSIGNED NOT NULL,
  `ownership_score` tinyint(3) UNSIGNED NOT NULL,
  `timeliness_score` tinyint(3) UNSIGNED NOT NULL,
  `overall_score` decimal(3,2) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `evaluations`
--

INSERT INTO `evaluations` (`id`, `employee_id`, `reviewer_id`, `period_start`, `period_end`, `quality_score`, `communication_score`, `ownership_score`, `timeliness_score`, `overall_score`, `comments`, `created_at`) VALUES
(1, 3, 2, '2026-09-12', '2026-10-12', 4, 4, 5, 4, 4.25, 'Good ownership and communication. Continue improving test coverage and documentation.', '2026-10-12 17:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED NOT NULL,
  `assigned_by` bigint(20) UNSIGNED NOT NULL,
  `due_date` date NOT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('todo','in_progress','blocked','completed') NOT NULL DEFAULT 'todo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `assigned_by`, `due_date`, `priority`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Review employee dashboard', 'Validate summary cards and identify usability improvements.', 3, 2, '2026-10-14', 'high', 'completed', '2026-10-12 17:05:10', '2026-10-12 17:19:43'),
(2, 'Prepare weekly work summary', 'Collect completed work, pending items, and risks for the weekly review.', 3, 2, '2026-10-16', 'medium', 'todo', '2026-10-12 17:05:10', '2026-10-12 17:05:10'),
(3, 'Fix overdue-task filter', 'Verify edge cases for completed and overdue tasks.', 4, 2, '2026-10-11', 'high', 'in_progress', '2026-10-12 17:05:10', '2026-10-12 17:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','employee') NOT NULL DEFAULT 'employee',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'WorkPulse Admin', 'admin@workpulse.local', '$2y$12$A1eOwWtTLpl0PU9mV1pquOXusZ2uN5gLLmiKY58tCH7ExdZSY52We', 'admin', 1, '2026-10-12 17:05:10', '2026-10-12 17:17:38'),
(2, 'Demo Manager', 'manager@workpulse.local', '$2y$12$A1eOwWtTLpl0PU9mV1pquOXusZ2uN5gLLmiKY58tCH7ExdZSY52We', 'manager', 1, '2026-10-12 17:05:10', '2026-10-12 17:17:38'),
(3, 'Demo Employee', 'employee@workpulse.local', '$2y$12$A1eOwWtTLpl0PU9mV1pquOXusZ2uN5gLLmiKY58tCH7ExdZSY52We', 'employee', 1, '2026-10-12 17:05:10', '2026-10-12 17:17:38'),
(4, 'Praveen', 'praveen@workpulse.local', '$2y$12$A1eOwWtTLpl0PU9mV1pquOXusZ2uN5gLLmiKY58tCH7ExdZSY52We', 'employee', 1, '2026-10-12 17:05:10', '2026-10-12 17:22:34'),
(5, 'praveenkumar mandula', 'praveenmandula6@gmail.com', '$2y$10$pwKPIrJUO.pcMTWaH8deuuOqo4I0iDt4YSuQGMouYSoZQpRqUJ7iC', 'manager', 1, '2026-10-12 17:25:08', '2026-10-12 17:25:57'),
(6, 'Uday', 'uday@gmail.com', '$2y$10$Q4T7QxSpU1DqwsTJh1jWnOqCm6HGhAkgNoXZh/AIOVrRe6c9sU9bK', 'employee', 1, '2026-10-12 17:25:34', '2026-10-12 17:26:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_user_time` (`user_id`,`created_at`);

--
-- Indexes for table `daily_updates`
--
ALTER TABLE `daily_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_updates_user_date` (`user_id`,`work_date`),
  ADD KEY `idx_updates_work_date` (`work_date`);

--
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_evaluations_employee_period` (`employee_id`,`period_start`,`period_end`),
  ADD KEY `fk_evaluations_reviewer` (`reviewer_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tasks_assigned_status` (`assigned_to`,`status`),
  ADD KEY `idx_tasks_due_status` (`due_date`,`status`),
  ADD KEY `fk_tasks_assigner` (`assigned_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role_active` (`role`,`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `daily_updates`
--
ALTER TABLE `daily_updates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `daily_updates`
--
ALTER TABLE `daily_updates`
  ADD CONSTRAINT `fk_updates_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `fk_evaluations_employee` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_evaluations_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_assignee` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tasks_assigner` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
