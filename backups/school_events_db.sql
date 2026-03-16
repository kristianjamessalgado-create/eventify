-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2026 at 11:49 AM
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
-- Database: `school_events_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `actor_id` int(11) DEFAULT NULL,
  `actor_role` varchar(50) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `actor_id`, `actor_role`, `action`, `target_type`, `target_id`, `details`, `created_at`) VALUES
(1, 27, 'multimedia', 'login_success', 'user', 27, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 06:56:30'),
(2, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 06:56:47'),
(3, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 07:16:18'),
(4, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-13 07:20:09'),
(5, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 00:28:18'),
(6, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 00:45:17'),
(7, 28, 'admin', 'login_success', 'user', 28, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 01:13:21'),
(8, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 01:27:33'),
(9, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 14:52:49'),
(10, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 14:56:17'),
(11, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 14:57:03'),
(12, 14, 'student', 'login_success', 'user', 14, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 14:57:51'),
(13, 17, 'organizer', 'login_success', 'user', 17, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 15:18:26'),
(14, 17, 'organizer', 'login_success', 'user', 17, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 15:33:33'),
(15, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 15:36:11'),
(16, 13, 'student', 'login_success', 'user', 13, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 15:36:39'),
(17, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 15:37:05'),
(18, 28, 'admin', 'login_success', 'user', 28, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 15:37:33'),
(19, 28, 'admin', 'event_approved', 'event', 9, 'Approved event ID 9', '2026-03-14 15:37:43'),
(20, 13, 'student', 'login_success', 'user', 13, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 15:37:58'),
(21, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 15:46:35'),
(22, 17, 'organizer', 'login_success', 'user', 17, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 15:49:37'),
(23, 17, 'organizer', 'login_success', 'user', 17, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 16:01:13'),
(24, 17, 'organizer', 'login_success', 'user', 17, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-14 16:14:08'),
(25, 13, 'student', 'login_success', 'user', 13, 'Successful login from IP 192.168.1.11 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-03-14 16:21:15'),
(26, 13, 'student', 'login_success', 'user', 13, 'Successful login from IP 192.168.1.11 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-03-14 16:23:09'),
(27, 13, 'student', 'login_success', 'user', 13, 'Successful login from IP 192.168.1.11 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-03-14 16:27:44'),
(28, 17, 'organizer', 'login_success', 'user', 17, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 01:21:01'),
(29, 13, 'student', 'login_success', 'user', 13, 'Successful login from IP 192.168.1.11 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-03-15 01:28:03'),
(30, 14, 'student', 'login_success', 'user', 14, 'Successful login from IP 192.168.1.11 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-03-15 01:31:46'),
(31, 14, 'student', 'login_success', 'user', 14, 'Successful login from IP 192.168.1.11 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-03-15 02:16:52'),
(32, 17, 'organizer', 'login_success', 'user', 17, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 10:29:03'),
(33, 17, 'organizer', 'login_success', 'user', 17, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 10:31:49'),
(34, 13, 'student', 'login_success', 'user', 13, 'Successful login from IP 192.168.1.13 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-03-15 10:35:08'),
(35, 13, 'student', 'login_success', 'user', 13, 'Successful login from IP 192.168.1.13 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-03-15 10:54:27'),
(36, 13, 'student', 'login_success', 'user', 13, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 11:02:17'),
(37, 17, 'organizer', 'login_success', 'user', 17, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 11:35:38'),
(38, 17, 'organizer', 'login_success', 'user', 17, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 11:55:42'),
(39, 13, 'student', 'login_success', 'user', 13, 'Successful login from IP 192.168.1.13 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-03-15 11:56:29'),
(40, 3, 'super_admin', 'login_success', 'user', 3, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 12:35:18'),
(41, 13, 'student', 'login_success', 'user', 13, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 13:48:19'),
(42, 13, 'student', 'login_success', 'user', 13, 'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-03-15 13:49:15');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `attended_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `organizer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','active','rejected','closed') DEFAULT 'pending',
  `department` enum('ALL','BSIT','BSHM','CONAHS','Senior High','High school department','College of Communication, Information and Technology','College of Accountancy and Business','School of Law and Political Science','College of Education','College of Nursing and Allied health sciences','College of Hospitality Management') NOT NULL DEFAULT 'ALL',
  `checkin_token` varchar(64) DEFAULT NULL,
  `reject_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `date`, `start_time`, `end_time`, `location`, `organizer_id`, `created_at`, `status`, `department`, `checkin_token`, `reject_reason`) VALUES
(1, 'Sample Orientation', 'Orientation for new students', '2025-12-20', NULL, NULL, 'Main Auditorium', 17, '2025-12-18 06:30:41', 'active', 'ALL', 'aad31a873785a99f2d9af0a6166783ad', NULL),
(2, 'sample', 'sir', '2026-02-28', NULL, NULL, 'wlc', 17, '2026-01-27 03:54:13', 'active', 'ALL', NULL, NULL),
(3, 'sample2', 'hehe', '2026-01-30', NULL, NULL, 'western leyte college', 17, '2026-01-27 15:46:49', 'active', 'BSHM', NULL, NULL),
(4, 'For All', 'For all sample', '2026-02-04', NULL, NULL, 'wlc', 17, '2026-01-28 13:30:13', 'active', 'ALL', NULL, NULL),
(6, 'intrams badminton', 'intrams badminton- conahs vs cicte 9:00 am- 9:00 pm', '2026-03-14', NULL, NULL, 'Western Leyte College, Ormoc City', 17, '2026-03-09 12:03:37', 'active', 'ALL', NULL, NULL),
(7, 'intrams basket', 'basker', '2026-03-14', NULL, NULL, 'Western Leyte College, Ormoc City', 17, '2026-03-13 04:18:19', '', 'ALL', '4bf23f1b7a9438bdd999125822140d48', NULL),
(8, 'intrams swimming', 'swimming', '2026-03-14', NULL, NULL, 'Western Leyte College, Ormoc City', 17, '2026-03-13 04:19:32', 'active', 'ALL', NULL, NULL),
(9, 'dan vs adrianne sumbagay', 'sumbagay', '2026-03-16', '13:00:00', '14:34:00', 'western leyte college', 17, '2026-03-14 15:35:35', 'active', 'ALL', '3e755df4c20ca0035def27f82e30f910', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event_photos`
--

CREATE TABLE `event_photos` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_photos`
--

INSERT INTO `event_photos` (`id`, `event_id`, `uploaded_by`, `file_path`, `caption`, `created_at`) VALUES
(1, 2, 27, 'uploads/events/2/20260205_075003_69843d9ba58ee_finale.png', NULL, '2026-02-05 06:50:03'),
(3, 4, 27, 'uploads/events/20260205_175211_6984cabb37ce8_RobloxScreenShot20250601_192737133.png', NULL, '2026-02-05 16:52:11'),
(4, 2, 27, 'uploads/events/20260309_080343_69ae70cfe770f_Screenshot_2024-09-03_002302.png', NULL, '2026-03-09 07:03:43'),
(5, 4, 27, 'uploads/events/all/20260309_095140_69ae8a1c7eeff_Screenshot_2024-09-17_181103.png', NULL, '2026-03-09 08:51:40'),
(6, 6, 27, 'uploads/events/all/20260309_130945_69aeb889afa26_Screenshot_2024-09-03_002302.png', NULL, '2026-03-09 12:09:45');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registration_date` datetime DEFAULT current_timestamp(),
  `qr_code` varchar(255) DEFAULT NULL,
  `status` enum('absent','present') DEFAULT 'absent',
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `user_id`, `event_id`, `registration_date`, `qr_code`, `status`, `time_in`, `time_out`) VALUES
(1, 13, 9, '2026-03-15 00:27:48', NULL, 'present', '2026-03-15 18:35:11', NULL),
(3, 14, 9, '2026-03-15 09:31:47', NULL, 'present', '2026-03-15 09:31:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','organizer','student','multimedia') NOT NULL,
  `department` enum('BSIT','BSHM','CONAHS','Senior High','High school department','College of Communication, Information and Technology','College of Accountancy and Business','School of Law and Political Science','College of Education','College of Nursing and Allied health sciences','College of Hospitality Management') DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `failed_attempts` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `name`, `email`, `password`, `role`, `department`, `profile_picture`, `status`, `created_at`, `failed_attempts`) VALUES
(1, 'SA-001', 'Kristian', 'kristian@school.com', '$2y$10$PASTE_HASH_HERE', 'super_admin', NULL, NULL, 'active', '2025-12-15 17:38:46', 3),
(3, 'SA-001', 'Kristian Salgado', 'kristian1@school.com', '$2y$10$IYqWZeXPvmCz3SvEmeSd6Op5eV2J3PRXeWaC5tPU3Guu7xZBs9TRK', 'super_admin', NULL, NULL, 'active', '2025-12-15 18:09:54', 0),
(4, 'STU-781', 'sample', 'sample@gmail.com', 'af2bdbe1aa9b6ec1e2ade1d694f41fc71a831d0268e9891562113d8a62add1bf', 'student', NULL, NULL, 'active', '2025-12-15 18:32:46', 0),
(5, 'STU-776', 'suai', 'suai@gmail.com', 'dea259230178e8b71e2ee186546e9cd6c56b7922b4519e6835d6ebc507f0c64e', 'student', NULL, NULL, 'active', '2025-12-15 18:55:30', 1),
(6, 'STU-842', 'suai1', 'suai1@gmail.com', '$2y$10$wj9ZwjEM02hYCLS7tXe6e.Kn7k03k6mJWRSFHSxrXk3AXX7990DiS', 'student', NULL, NULL, 'active', '2025-12-15 18:57:12', 2),
(7, 'STU-738', 'suai2', 'suai2@gmail.com', 'ba1889bd80d5dffe82089bb71ca4683831c4c872ef817b1306421c421130eee2', 'student', NULL, NULL, 'active', '2025-12-15 18:57:32', 0),
(8, 'STU-351', 'suai3', 'suai3@gmail.com', '4e78ba3f87382d0ce4a41471737af8f0b2310c6fc7295b4a40ab00508f2f9620', 'student', NULL, NULL, 'active', '2025-12-15 18:59:36', 2),
(9, 'STU-253', 'suai4', 'suai4@gmail.com', '51ee5f59976acb3565d0609c70cb939ded012c4267c3d0fce29a5af2562129d4', 'student', NULL, NULL, 'active', '2025-12-15 19:00:19', 0),
(10, 'STU-781', 'suai5', 'suai5@gmail.com', 'd65c023117c75d15c1f117f53ac85232fdb728513310411aceae11449f350d43', 'student', NULL, NULL, 'active', '2025-12-15 19:01:36', 0),
(11, 'STU-979', 'another sample', 'anothersample@gmail.com', 'a08f8859605f0b362db593d2a8e756f0f65a334800b575329cf7d5af6d424f21', 'student', NULL, NULL, 'active', '2025-12-16 07:11:30', 2),
(12, 'STU-512', 'sample1', 'sample1@gmail.com', 'e85130791f31db1699f61a5e7ae7b5e85e70399414f38476091896214771cd17', 'student', NULL, NULL, 'active', '2025-12-16 07:21:16', 2),
(13, 'STU-913', 'Kristian James Salgado', 'sample4@gmail.com', '$2y$10$GOSpGfllB2kBaCJLmvORrOR9ScVRgG5eOrwDxA9G8E.zhwlUVl9xq', 'student', NULL, 'uploads/profile_pictures/profile_13_1773572295_69b690c737f8d.jpeg', 'active', '2025-12-16 08:41:26', 0),
(14, 'STU-226', 'Kristian James Salgado', 'sample5@gmail.com', '$2y$10$bYL4yAXTtDweHG0hnXFb8.lZmw9l8l6kQ9/5PsrCjwCh3F9WJhzY6', 'student', 'BSIT', 'uploads/profile_pictures/profile_14_1770312568_6984d378b4f87.png', 'active', '2025-12-17 04:05:13', 0),
(15, 'ORG-318', 'organizer', 'organizer@gmail.com', '154a0a277d0a9e90475532eeb50bb087f6dcf19172db5fc8091221091c772ac5', 'organizer', NULL, NULL, 'active', '2025-12-18 04:23:41', 0),
(16, 'ORG-206', 'organizer1', 'organizer1@gmail.com', '154a0a277d0a9e90475532eeb50bb087f6dcf19172db5fc8091221091c772ac5', 'organizer', NULL, NULL, 'active', '2025-12-18 06:16:33', 3),
(17, 'ORG-880', 'organizer2', 'organizer2@gmail.com', '$2y$10$ztiS2BWvxE0qzbBgOASB5u7aMcmVvhm8ChxKmz08ya5T85XsDSPRm', 'organizer', NULL, 'uploads/profile_pictures/profile_17_1771385893_69953425b8545.png', 'active', '2025-12-18 06:25:39', 0),
(18, 'STU-238', 'sample6', 'sample6@gmail.com', 'bcd8eb16b2ae1c881de513d28a3f49426afaa1ab34a3e834df5fbf7bdcbe9770', 'student', NULL, NULL, 'active', '2025-12-18 07:41:13', 1),
(19, 'STU-558', 'sample7', 'sample7@gmail.com', '24714505b9df6e69f9367f12217d590d4f15b4367d1697b6f833d1e07b291d2a', 'student', NULL, NULL, 'active', '2025-12-19 05:57:19', 0),
(20, 'ORG-923', 'samplereg', 'Samplereg@gmail.com', '76cd579e5eea4f719469276719558fa1b46c0196a613cb8aa5bfcdd9a43628f8', 'organizer', NULL, NULL, 'active', '2025-12-21 06:09:01', 0),
(21, 'ORG-994', 'samplereg2', 'Samplereg2@gmail.com', '64be8f3069aeb2299bc66aa17b7c0e47530d5dc0475ef0606ea6ffbaad04f819', 'organizer', NULL, NULL, 'active', '2025-12-21 06:44:58', 0),
(22, 'ORG-964', 'samplereg3', 'Samplereg3@gmail.com', '8d9353a8accc17cba0ee8dab3b744552799c6f37226a5b9e48a37cb82945de8f', 'organizer', NULL, NULL, 'active', '2025-12-21 06:50:28', 1),
(23, 'STU-827', 'sammilby', 'sammilby@gmail.com', '5e6eb2532b6f1eb86b9bfd41c5c1e9ca14d444eb013c223c0958b9dde57fd54f', 'student', NULL, NULL, 'active', '2025-12-22 03:55:21', 0),
(24, 'STU-510', 'deanecamat', 'deanecamat@gmail.com', 'faae366a9b3bc5e637a5f10b53a826ce31546dfb03a1cc3f7849001906d13dff', 'student', NULL, NULL, 'active', '2025-12-23 12:42:31', 0),
(25, 'STU-939', 'jabes', 'jabes@gmail.com', '781e60f7f136510363f9a9522ebeb73f647d94111c6e2d27fd669e0770a26aef', 'student', NULL, NULL, 'active', '2025-12-23 15:13:46', 2),
(26, 'MUL-692', 'multimedia', 'multimedia@gmail.com', '$2y$10$8uY9QHmNVKHaGzqCpc3FVOFli2Ad4.ftlDkKq4.GzWEtGVpP73o2m', 'multimedia', '', 'uploads/profile_pictures/profile_26_1771386378_6995360ae6219.png', 'active', '2026-02-05 06:36:11', 0),
(27, 'MUL-721', 'multimedia1', 'multimedia1@gmail.com', '$2y$10$iFLWXYM3owfuH7JrE1Qvuu10xeErUur7GmU.eEa95YnC3JQ7BiygS', 'multimedia', '', NULL, 'active', '2026-02-05 06:49:05', 0),
(28, 'ADM-214', 'admin1', 'admin1@gmail.com', '$2y$10$GhltrcajVDqPr9dvTpRWhuxsXhOXupLct6Pe9tZp.HY3OcgJrDtLa', 'admin', '', NULL, 'active', '2026-03-13 04:26:02', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actor_id` (`actor_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `checkin_token` (`checkin_token`),
  ADD KEY `organizer_id` (`organizer_id`);

--
-- Indexes for table `event_photos`
--
ALTER TABLE `event_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `read_at` (`read_at`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration` (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `event_photos`
--
ALTER TABLE `event_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `event_photos`
--
ALTER TABLE `event_photos`
  ADD CONSTRAINT `event_photos_event_fk` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_photos_user_fk` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
