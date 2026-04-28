-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 02:48 AM
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
-- Database: `asc_website_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `user_login` varchar(60) NOT NULL,
  `action` enum('CRE','MOD','DEL') NOT NULL,
  `table_name` enum('wp_users','courses','schedule','events') NOT NULL,
  `table_key` varchar(20) NOT NULL,
  `old_data` longtext DEFAULT NULL,
  `new_data` longtext DEFAULT NULL,
  `time_stamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`log_id`, `user_login`, `action`, `table_name`, `table_key`, `old_data`, `new_data`, `time_stamp`) VALUES
(58, 'WB55131, Justin Collier', 'MOD', 'events', '2', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-21\",NULL,\"30\"', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-21\",NULL,\"30\"', '2026-04-23 05:32:30'),
(59, 'WB55131, Justin Collier', 'MOD', 'events', '2', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-21\",NULL,\"30\"', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-21\",NULL,\"45\"', '2026-04-23 05:41:55'),
(60, 'WB55131, Justin Collier', 'MOD', 'events', '2', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-21\",NULL,\"45\"', '\"Shakib Thomas\",\"called_out\",\"2026-04-23\",\"2026-04-23\",NULL', '2026-04-23 05:42:28'),
(61, 'WB55131, Justin Collier', 'CRE', 'events', '19', NULL, '\"Avi Hill\",\"leaving_early\",\"2026-04-23\",NULL,\"15\"', '2026-04-23 16:12:25'),
(62, 'WB55131, Justin Collier', 'CRE', 'schedule', '570', NULL, '\"Theo Ramirez\",\"BIOL 101\",\"MON\",\"01:00:00\",\"13:00:00\"', '2026-04-24 02:07:12'),
(63, 'WB55131, Justin Collier', 'DEL', 'schedule', '570', '\"Theo Ramirez\",\"BIOL 101\",\"MON\",\"01:00:00\",\"13:00:00\"', NULL, '2026-04-24 02:07:19'),
(64, 'WB55131, Justin Collier', 'DEL', 'events', '10', '\"Molemo Anderson\",\"at_capacity\",\"2026-04-22\",NULL,NULL', NULL, '2026-04-24 02:21:39'),
(65, 'WB55131, Justin Collier', 'DEL', 'events', '14', '\"Moyo Torres\",\"leaving_early\",\"2026-04-22\",NULL,\"15\"', NULL, '2026-04-24 02:21:45'),
(66, 'WB55131, Justin Collier', 'DEL', 'events', '7', '\"Shakib Thomas\",\"late\",\"2026-04-21\",NULL,NULL', NULL, '2026-04-24 02:22:14'),
(67, 'WB55131, Justin Collier', 'DEL', 'schedule', '567', '\"Sina Rodriguez\",\"CHEM 101\",\"MON\",\"06:00:00\",\"19:15:00\"', NULL, '2026-04-24 02:22:27'),
(68, 'WB55131, Justin Collier', 'DEL', 'wp_users', '3095', '\"FZ99192\",\"sgreen@umbc.edu\",\"Susanna\",\"Green\",\"tutor\"', NULL, '2026-04-24 02:22:41'),
(69, 'WB55131, Justin Collier', 'MOD', 'events', '2', '\"Shakib Thomas\",\"called_out\",\"2026-04-23\",\"2026-04-23\",NULL', '\"Shakib Thomas\",\"called_out\",\"2026-04-23\",\"2026-04-23\",NULL', '2026-04-24 03:12:09'),
(70, 'WB55131, Justin Collier', 'MOD', 'events', '2', '\"Shakib Thomas\",\"called_out\",\"2026-04-23\",\"2026-04-23\",NULL', '\"Shakib Thomas\",\"called_out\",\"2026-04-23\",\"2026-04-24\",NULL', '2026-04-24 03:12:51'),
(71, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3097', '\"TO79352\",\"tharris@umbc.edu\",\"Tristan\",\"Harris\",\"tutor,asc_staff\"', '\"TO79352\",\"tharris@umbc.edu\",\"Tristan\",\"Harris\",\"tutor,asc_staff\"', '2026-04-24 03:13:27'),
(72, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3097', '\"TO79352\",\"tharris@umbc.edu\",\"Tristan\",\"Harris\",\"tutor,asc_staff\"', '\"TO79352\",\"tharris@umbc.edu\",\"Tristan\",\"Harris\",\"tutor\"', '2026-04-24 03:13:33'),
(73, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3098', '\"RA99166\",\"ztaylor@umbc.edu\",\"Zach\",\"Taylor\",\"tutor\"', '\"RA99166\",\"ztaylor@umbc.edu\",\"Zach\",\"Taylor\",\"tutor\"', '2026-04-24 03:14:32'),
(74, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor,asc_staff\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor\"', '2026-04-24 03:20:02'),
(75, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor,asc_staff\"', '2026-04-24 03:24:54'),
(76, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor,asc_staff\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_staff\"', '2026-04-24 03:25:27'),
(77, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_staff\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor,asc_staff\"', '2026-04-24 03:25:33'),
(78, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor,asc_staff\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_admin\"', '2026-04-24 03:28:46'),
(79, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_admin\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor\"', '2026-04-24 03:30:13'),
(80, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_admin\"', '2026-04-24 03:30:17'),
(81, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_admin\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor\"', '2026-04-24 03:30:37'),
(82, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_admin\"', '2026-04-24 03:30:42'),
(83, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_admin\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor\"', '2026-04-24 03:31:05'),
(84, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_admin\"', '2026-04-24 03:31:08'),
(85, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_admin\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor\"', '2026-04-24 03:32:41'),
(86, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_admin\"', '2026-04-24 03:32:51'),
(87, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_admin\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_staff\"', '2026-04-24 03:33:04'),
(88, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_staff\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor\"', '2026-04-24 03:33:15'),
(89, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3104', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"tutor\"', '\"TC60432\",\"tramirez@umbc.edu\",\"Theo\",\"Ramirez\",\"asc_admin\"', '2026-04-24 03:33:20'),
(90, 'WB55131, Justin Collier', 'CRE', 'events', '20', NULL, '\"Zach Taylor\",\"leaving_early\",\"2026-04-24\",NULL,NULL', '2026-04-24 04:11:45'),
(91, 'WB55131, Justin Collier', 'CRE', 'events', '21', NULL, '\"Zach Taylor\",\"called_out\",\"2026-04-23\",\"2026-04-25\",NULL', '2026-04-24 04:15:16'),
(92, 'WB55131, Justin Collier', 'CRE', 'events', '22', NULL, '\"Tristan Harris\",\"leaving_early\",\"2026-04-24\",NULL,NULL', '2026-04-24 04:15:29'),
(93, 'WB55131, Justin Collier', 'CRE', 'events', '23', NULL, '\"Shrikant Nguyen\",\"leaving_early\",\"2026-04-24\",NULL,NULL', '2026-04-24 04:18:31'),
(94, 'WB55131, Justin Collier', 'CRE', 'events', '24', NULL, '\"Aren Garcia\",\"leaving_early\",\"2026-04-24\",NULL,\"10\"', '2026-04-24 04:24:13'),
(95, 'WB55131, Justin Collier', 'CRE', 'events', '9', NULL, '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"19:29:00\"', '2026-04-24 23:29:44'),
(96, 'WB55131, Justin Collier', 'DEL', 'events', '9', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"00:00:19\"', NULL, '2026-04-24 23:32:22'),
(97, 'WB55131, Justin Collier', 'CRE', 'events', '10', NULL, '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"19:32:00\"', '2026-04-24 23:32:31'),
(98, 'WB55131, Justin Collier', 'DEL', 'events', '10', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"00:00:19\"', NULL, '2026-04-24 23:36:19'),
(99, 'WB55131, Justin Collier', 'CRE', 'events', '11', NULL, '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"19:37:00\"', '2026-04-24 23:37:34'),
(100, 'WB55131, Justin Collier', 'DEL', 'events', '11', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"00:00:19\"', NULL, '2026-04-24 23:38:10'),
(101, 'WB55131, Justin Collier', 'CRE', 'events', '12', NULL, '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"19:40:00\"', '2026-04-24 23:40:19'),
(102, 'WB55131, Justin Collier', 'DEL', 'events', '12', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"19:40:00\"', NULL, '2026-04-24 23:40:32'),
(103, 'WB55131, Justin Collier', 'MOD', 'events', '2', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-24\",NULL,\"13:30:00\"', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-24\",NULL,\"13:30:00:00\"', '2026-04-24 23:41:07'),
(104, 'WB55131, Justin Collier', 'MOD', 'events', '2', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-24\",NULL,\"00:00:13\"', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-24\",NULL,\"19:41:00\"', '2026-04-24 23:41:25'),
(105, 'WB55131, Justin Collier', 'MOD', 'events', '2', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-24\",NULL,\"00:00:19\"', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-24\",NULL,\"19:41:00:00\"', '2026-04-24 23:41:44'),
(106, 'WB55131, Justin Collier', 'MOD', 'events', '2', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-24\",NULL,\"00:00:19\"', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-24\",NULL,\"00:00:00:00\"', '2026-04-24 23:42:15'),
(107, 'WB55131, Justin Collier', 'MOD', 'events', '2', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-24\",NULL,\"00:00:00\"', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-24\",NULL,\"03:00:00\"', '2026-04-24 23:42:32'),
(108, 'WB55131, Justin Collier', 'DEL', 'events', '2', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-24\",NULL,\"00:00:03\"', NULL, '2026-04-24 23:49:00'),
(109, 'WB55131, Justin Collier', 'CRE', 'schedule', '567', NULL, '\"Zoya Campbell\",\"BIOL 101\",\"FRI\",\"17:00:00\",\"21:00:00\"', '2026-04-24 23:49:29'),
(110, 'WB55131, Justin Collier', 'CRE', 'schedule', '568', NULL, '\"Zach Taylor\",\"BIOL 101\",\"FRI\",\"18:00:00\",\"20:30:00\"', '2026-04-24 23:49:50'),
(111, 'WB55131, Justin Collier', 'CRE', 'events', '13', NULL, '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"20:00:00\"', '2026-04-24 23:50:11'),
(112, 'WB55131, Justin Collier', 'CRE', 'events', '14', NULL, '\"Zach Taylor\",\"leaving_early\",\"2026-04-24\",NULL,\"19:30:00\"', '2026-04-24 23:50:24'),
(113, 'WB55131, Justin Collier', 'MOD', 'events', '13', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"20:00:00\"', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"20:00:00\"', '2026-04-24 23:58:10'),
(114, 'WB55131, Justin Collier', 'MOD', 'events', '13', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"20:00:00\"', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"20:00:00\"', '2026-04-24 23:59:10'),
(115, 'WB55131, Justin Collier', 'MOD', 'events', '13', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"20:00:00\"', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"20:04:00\"', '2026-04-24 23:59:19'),
(116, 'WB55131, Justin Collier', 'CRE', 'events', '15', NULL, '\"O\'Tega Adams\",\"leaving_early\",\"2026-04-24\",NULL,\"19:59:00\"', '2026-04-24 23:59:34'),
(117, 'WB55131, Justin Collier', 'CRE', 'events', '16', NULL, '\"Kodi Hernandez\",\"leaving_early\",\"2026-04-24\",NULL,\"22:15:00\"', '2026-04-25 00:18:44'),
(118, 'WB55131, Justin Collier', 'MOD', 'events', '13', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"20:04:00\"', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"20:04:00\"', '2026-04-25 00:23:08'),
(119, 'WB55131, Justin Collier', 'MOD', 'events', '13', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"20:04:00\"', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"20:04:00\"', '2026-04-25 00:23:12'),
(120, 'WB55131, Justin Collier', 'MOD', 'schedule', '568', '\"Zach Taylor\",\"BIOL 101\",\"FRI\",\"18:00:00\",\"20:30:00\"', '\"Zach Taylor\",\"BIOL 101\",\"FRI\",\"18:00:00\",\"20:30:00\"', '2026-04-25 00:23:18'),
(121, 'WB55131, Justin Collier', 'MOD', 'schedule', '568', '\"Zach Taylor\",\"BIOL 101\",\"FRI\",\"18:00:00\",\"20:30:00\"', '\"Zach Taylor\",\"BIOL 101\",\"FRI\",\"18:00:00\",\"20:30:00\"', '2026-04-25 00:23:22'),
(122, 'WB55131, Justin Collier', 'MOD', 'schedule', '568', '\"Zach Taylor\",\"BIOL 101\",\"FRI\",\"18:00:00\",\"20:30:00\"', '\"Zach Taylor\",\"BIOL 101\",\"FRI\",\"06:00:00\",\"10:15:00\"', '2026-04-25 00:52:43'),
(123, 'WB55131, Justin Collier', 'DEL', 'schedule', '568', '\"Zach Taylor\",\"BIOL 101\",\"FRI\",\"06:00:00\",\"10:15:00\"', NULL, '2026-04-25 01:05:52'),
(124, 'WB55131, Justin Collier', 'MOD', 'events', '3', '\"Zoya Campbell\",\"at_capacity\",\"2026-04-24\",NULL,NULL', '\"Zoya Campbell\",\"at_capacity\",\"2026-04-24\",NULL,NULL', '2026-04-25 01:53:15'),
(125, 'WB55131, Justin Collier', 'MOD', 'events', '14', '\"Zach Taylor\",\"leaving_early\",\"2026-04-24\",NULL,\"19:30:00\"', '\"Zach Taylor\",\"leaving_early\",\"2026-04-24\",NULL,\"19:30:00\"', '2026-04-25 01:53:44'),
(126, 'WB55131, Justin Collier', 'MOD', 'events', '13', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"20:04:00\"', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"19:00:00\"', '2026-04-25 01:53:58'),
(127, 'WB55131, Justin Collier', 'MOD', 'events', '13', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"19:00:00\"', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"19:15:00\"', '2026-04-25 01:54:21'),
(128, 'WB55131, Justin Collier', 'DEL', 'events', '13', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"19:15:00\"', NULL, '2026-04-25 02:35:13'),
(129, 'WB55131, Justin Collier', 'CRE', 'events', '17', NULL, '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"12:00:00\"', '2026-04-25 02:35:40'),
(130, 'WB55131, Justin Collier', 'DEL', 'events', '17', '\"Zoya Campbell\",\"leaving_early\",\"2026-04-24\",NULL,\"12:00:00\"', NULL, '2026-04-25 02:35:43'),
(131, 'WB55131, Justin Collier', 'CRE', 'schedule', '569', NULL, '\"Zoya Campbell\",\"BIOL 101\",\"MON\",\"12:00:00\",\"12:15:00\"', '2026-04-25 02:38:08'),
(132, 'WB55131, Justin Collier', 'CRE', 'events', '9', NULL, '\"Chelsea Moore\",\"at_capacity\",\"2026-04-25\",NULL,NULL', '2026-04-25 22:03:42'),
(133, 'WB55131, Justin Collier', 'CRE', 'events', '10', NULL, '\"Chelsea Moore\",\"at_capacity\",\"2026-04-25\",NULL,NULL', '2026-04-25 22:04:44'),
(134, 'WB55131, Justin Collier', 'CRE', 'schedule', '5111', NULL, '\"Chelsea Moore\",\"BIOL 101\",\"MON\",\"12:00:00\",\"12:45:00\"', '2026-04-26 16:29:05'),
(135, 'WB55131, Justin Collier', 'DEL', 'schedule', '5111', '\"Chelsea Moore\",\"BIOL 101\",\"MON\",\"12:00:00\",\"12:45:00\"', NULL, '2026-04-26 16:29:31'),
(136, 'WB55131, Justin Collier', 'CRE', 'events', '11', NULL, '\"Chelsea Moore\",\"at_capacity\",\"2026-04-26\",NULL,NULL', '2026-04-26 17:15:52'),
(137, 'WB55131, Justin Collier', 'MOD', 'events', '11', '\"Chelsea Moore\",\"at_capacity\",\"2026-04-26\",NULL,NULL', '\"Chelsea Moore\",\"leaving_early\",\"2026-04-26\",NULL,\"12:00:00\"', '2026-04-26 17:16:00'),
(138, 'WB55131, Justin Collier', 'MOD', 'schedule', '5110', '\"Dani Martinez\",\"STAT 355\",\"WED\",\"14:00:00\",\"16:00:00\"', '\"Dani Martinez\",\"SPAN 202\",\"WED\",\"14:00:00\",\"16:00:00\"', '2026-04-26 17:16:12'),
(139, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3890', '\"ZA30926\",\"cmoore@umbc.edu\",\"Chelsea\",\"Moore\",\"tutor\"', '\"ZA30926\",\"cmoore@umbc.edu\",\"Chelsea\",\"Moore\",\"tutor,asc_staff\"', '2026-04-26 17:16:29'),
(140, 'WB55131, Justin Collier', 'DEL', 'wp_users', '3890', '\"ZA30926\",\"cmoore@umbc.edu\",\"Chelsea\",\"Moore\",\"tutor,asc_staff\"', NULL, '2026-04-26 17:16:33'),
(141, 'WB55131, Justin Collier', 'DEL', 'wp_users', '3889', '\"XS66155\",\"staylor@umbc.edu\",\"Shayan\",\"Taylor\",\"Tutor\"', NULL, '2026-04-26 18:03:17'),
(142, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3888', '\"XR80644\",\"sthomas@umbc.edu\",\"Shakib\",\"Thomas\",\"Tutor\"', '\"XR80644\",\"sthomas@umbc.edu\",\"Shakib\",\"Thomas\",\"Tutor, Asc Staff\"', '2026-04-26 18:03:21'),
(143, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3888', '\"XR80644\",\"sthomas@umbc.edu\",\"Shakib\",\"Thomas\",\"Tutor | Asc Staff\"', '\"XR80644\",\"sthomas@umbc.edu\",\"Shakib\",\"Thomas\",\"Tutor\"', '2026-04-26 18:04:49'),
(144, 'WB55131, Justin Collier', 'DEL', 'wp_users', '3860', '\"GY54118\",\"dmartinez@umbc.edu\",\"Dani\",\"Martinez\",\"Tutor\"', NULL, '2026-04-26 18:55:54'),
(145, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3888', '\"XR80644\",\"sthomas@umbc.edu\",\"Shakib\",\"Thomas\",\"Tutor\"', '\"XR80644\",\"sthomas@umbc.edu\",\"Shakib\",\"Thomas\",\"Tutor | Asc Staff\"', '2026-04-26 19:07:06'),
(146, 'WB55131, Justin Collier', 'CRE', 'events', '12', NULL, '\"Shakib Thomas\",\"at_capacity\",\"2026-04-26\",NULL,NULL', '2026-04-26 19:19:29'),
(147, 'WB55131, Justin Collier', 'MOD', 'events', '12', '\"Shakib Thomas\",\"at_capacity\",\"2026-04-26\",NULL,NULL', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-26\",NULL,\"12:00:00\"', '2026-04-26 19:19:36'),
(148, 'WB55131, Justin Collier', 'DEL', 'events', '12', '\"Shakib Thomas\",\"leaving_early\",\"2026-04-26\",NULL,\"12:00:00\"', NULL, '2026-04-26 19:19:38'),
(149, 'WB55131, Justin Collier', 'MOD', 'schedule', '5109', '\"Akanksha Baker\",\"STAT 355\",\"WED\",\"13:00:00\",\"14:00:00\"', '\"Akanksha Baker\",\"STAT 355\",\"WED\",\"13:00:00\",\"14:00:00\"', '2026-04-26 19:19:43'),
(150, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3888', '\"XR80644\",\"sthomas@umbc.edu\",\"Shakib\",\"Thomas\",\"Tutor | ASC Staff\"', '\"XR80644\",\"sthomas@umbc.edu\",\"Shakib\",\"Thomas\",\"Tutor\"', '2026-04-26 19:20:15'),
(151, 'WB55131, Justin Collier', 'DEL', 'wp_users', '3888', '\"XR80644\",\"sthomas@umbc.edu\",\"Shakib\",\"Thomas\",\"Tutor\"', NULL, '2026-04-26 19:20:18'),
(152, 'WB55131, Justin Collier', 'CRE', 'events', '13', NULL, '\"Akanksha Baker\",\"leaving_early\",\"2026-04-26\",NULL,NULL', '2026-04-26 19:41:11'),
(153, 'WB55131, Justin Collier', 'CRE', 'events', '14', NULL, '\"Akanksha Baker\",\"at_capacity\",\"2026-04-26\",NULL,NULL', '2026-04-26 20:29:38'),
(154, 'WB55131, Justin Collier', 'CRE', 'schedule', '5640', NULL, '\"Akanksha Baker\",\"BIOL 101\",\"MON\",\"12:00:00\",\"13:00:00\"', '2026-04-26 20:31:23'),
(155, 'WB55131, Justin Collier', 'MOD', 'schedule', '5640', '\"Akanksha Baker\",\"BIOL 101\",\"MON\",\"12:00:00\",\"13:00:00\"', '\"Akanksha Baker\",\"BIOL 101\",\"MON\",\"12:00:00\",\"13:15:00\"', '2026-04-26 20:32:00'),
(156, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3928', '\"XR21395\",\"abaker@umbc.edu\",\"Akanksha\",\"Baker\",\"Tutor\"', '\"XR21395\",\"abaker@umbc.edu\",\"Akanksha\",\"Baker\",\"Tutor | ASC Staff\"', '2026-04-26 20:33:28'),
(157, 'WB55131, Justin Collier', 'CRE', 'events', '15', NULL, '\"Ian Williams\",\"late\",\"2026-04-26\",NULL,NULL', '2026-04-26 20:34:09'),
(158, 'WB55131, Justin Collier', 'CRE', 'events', '16', NULL, '\"Max M. Green\",\"called_out\",\"2026-04-19\",\"2026-04-28\",NULL', '2026-04-26 20:34:26'),
(159, 'WB55131, Justin Collier', 'CRE', 'events', '17', NULL, '\"Akanksha Baker\",\"called_out\",\"2026-04-07\",\"2026-04-07\",NULL', '2026-04-26 21:28:32'),
(160, 'WB55131, Justin Collier', 'DEL', 'events', '13', '\"Akanksha Baker\",\"leaving_early\",\"2026-04-26\",NULL,NULL', NULL, '2026-04-26 21:34:48'),
(161, 'WB55131, Justin Collier', 'DEL', 'events', '17', '\"Akanksha Baker\",\"called_out\",\"2026-04-07\",\"2026-04-07\",NULL', NULL, '2026-04-26 23:41:23'),
(162, 'WB55131, Justin Collier', 'DEL', 'schedule', '5640', '\"Akanksha Baker\",\"BIOL 101\",\"MON\",\"12:00:00\",\"13:15:00\"', NULL, '2026-04-26 23:41:37'),
(163, 'WB55131, Justin Collier', 'DEL', 'schedule', '5634', '\"Moyo Torres\",\"STAT 121\",\"WED\",\"17:00:00\",\"18:00:00\"', NULL, '2026-04-26 23:44:16'),
(164, 'WB55131, Justin Collier', 'DEL', 'wp_users', '3923', '\"UW81426\",\"arivera@umbc.edu\",\"Aya\",\"Rivera\",\"Tutor\"', NULL, '2026-04-26 23:44:28'),
(165, 'WB55131, Justin Collier', 'DEL', '', '10000', '\"BIOL 101\",\"Concepts of Biology\"', NULL, '2026-04-27 22:18:21'),
(166, 'WB55131, Justin Collier', 'DEL', '', '10001', '\"BIOL 140\",\"Foundations of Biology: Ecology and Evolution\"', NULL, '2026-04-27 22:20:00'),
(167, 'WB55131, Justin Collier', 'DEL', '', '10002', '\"BIOL 141\",\"Foundations of Biology: Cells, Energy and Organisms\"', NULL, '2026-04-27 22:24:13'),
(168, 'WB55131, Justin Collier', 'DEL', 'schedule', '5139', '\"Moyo Torres\",\"BIOL 251\",\"WED\",\"17:00:00\",\"18:00:00\"', NULL, '2026-04-27 22:40:28'),
(169, 'WB55131, Justin Collier', 'DEL', 'schedule', '5138', '\"Moyo Torres\",\"BIOL 251\",\"TUE\",\"17:00:00\",\"18:00:00\"', NULL, '2026-04-27 22:44:25'),
(170, 'WB55131, Justin Collier', 'DEL', '', '10003', '\"BIOL 251\",\"Human Anatomy and Physiology I\"', NULL, '2026-04-27 22:45:24'),
(171, 'WB55131, Justin Collier', 'DEL', '', '10004', '\"BIOL 273\",\"Microbiology for Allied Health\"', NULL, '2026-04-27 22:49:48'),
(172, 'WB55131, Justin Collier', 'DEL', '', '10005', '\"BIOL 302\",\"Molecular and General Genetics\"', NULL, '2026-04-27 22:53:15'),
(173, 'WB55131, Justin Collier', 'DEL', 'courses', '10006', '\"BIOL 303\",\"Cell Biology\"', NULL, '2026-04-27 22:56:40'),
(174, 'WB55131, Justin Collier', 'DEL', 'courses', '10007', '\"BIOL 430\",\"Biological Chemistry\"', NULL, '2026-04-27 23:06:52'),
(175, 'WB55131, Justin Collier', 'DEL', 'courses', '10008', '\"CHEM 100\",\"The Chemical World\"', NULL, '2026-04-28 00:20:58'),
(176, 'WB55131, Justin Collier', 'DEL', 'schedule', '5658', '\"Molemo Anderson\",\"CHEM 101\",\"FRI\",\"13:00:00\",\"14:00:00\"', NULL, '2026-04-28 00:26:51'),
(177, 'WB55131, Justin Collier', 'DEL', 'schedule', '6120', '\"Akanksha Baker\",\"STAT 355\",\"WED\",\"13:00:00\",\"14:00:00\"', NULL, '2026-04-28 00:29:55'),
(178, 'WB55131, Justin Collier', 'DEL', 'schedule', '6119', '\"Akanksha Baker\",\"STAT 355\",\"TUE\",\"15:00:00\",\"16:30:00\"', NULL, '2026-04-28 00:30:03'),
(179, 'WB55131, Justin Collier', 'DEL', 'schedule', '6118', '\"Akanksha Baker\",\"STAT 355\",\"MON\",\"11:00:00\",\"12:00:00\"', NULL, '2026-04-28 00:30:09'),
(180, 'WB55131, Justin Collier', 'DEL', 'courses', '10009', '\"CHEM 101\",\"Principles of Chemistry I\"', NULL, '2026-04-28 00:30:18'),
(181, 'WB55131, Justin Collier', 'MOD', 'wp_users', '3965', '\"XR21395\",\"abaker@umbc.edu\",\"Akanksha\",\"Baker\",\"Tutor | ASC Staff\"', '\"XR21395\",\"abaker@umbc.edu\",\"Akanksha\",\"Baker\",\"Tutor\"', '2026-04-28 00:31:31'),
(182, 'WB55131, Justin Collier', 'DEL', 'schedule', '6117', '\"Karen Moore\",\"STAT 350\",\"TUE\",\"16:00:00\",\"17:00:00\"', NULL, '2026-04-28 00:31:56'),
(183, 'WB55131, Justin Collier', 'DEL', 'schedule', '5672', '\"Molemo Anderson\",\"CHEM 102\",\"FRI\",\"13:00:00\",\"14:00:00\"', NULL, '2026-04-28 00:32:09'),
(184, 'WB55131, Justin Collier', 'DEL', 'schedule', '6116', '\"Avi Hill\",\"STAT 121\",\"THU\",\"13:00:00\",\"16:00:00\"', NULL, '2026-04-28 00:38:56'),
(185, 'WB55131, Justin Collier', 'DEL', 'courses', '10060', '\"STAT 121\",\"Introduction to Statistics for the Social Sciences\"', NULL, '2026-04-28 00:39:03'),
(186, 'WB55131, Justin Collier', 'CRE', 'schedule', '6121', NULL, '\"Ian Williams\",\"STAT 121\",\"MON\",\"12:00:00\",\"12:15:00\"', '2026-04-28 00:39:27'),
(187, 'WB55131, Justin Collier', 'DEL', 'courses', '10063', '\"STAT 355\",\"Introduction to Probability and Statistics for Scientists and Engineers\"', NULL, '2026-04-28 00:40:00'),
(188, 'WB55131, Justin Collier', 'DEL', 'wp_users', '3964', '\"XO80284\",\"iwilliams@umbc.edu\",\"Ian\",\"Williams\",\"Tutor\"', NULL, '2026-04-28 00:40:50'),
(189, 'WB55131, Justin Collier', 'DEL', 'courses', '10060', '\"STAT 121\",\"Introduction to Statistics for the Social Sciences\"', NULL, '2026-04-28 00:41:17'),
(190, 'WB55131, Justin Collier', 'DEL', 'courses', '10061', '\"STAT 350\",\"Statistics with Applications in the Biological Sciences\"', NULL, '2026-04-28 00:41:19'),
(191, 'WB55131, Justin Collier', 'DEL', 'courses', '10062', '\"STAT 351\",\"Applied Statistics for Business and Economics\"', NULL, '2026-04-28 00:41:22'),
(192, 'WB55131, Justin Collier', 'CRE', 'schedule', '6122', NULL, '\"Akanksha Baker\",\"STAT 350\",\"MON\",\"12:00:00\",\"12:45:00\"', '2026-04-28 00:41:47'),
(193, 'WB55131, Justin Collier', 'CRE', 'schedule', '6123', NULL, '\"Max M. Green\",\"STAT 350\",\"MON\",\"12:15:00\",\"12:45:00\"', '2026-04-28 00:42:25'),
(194, 'WB55131, Justin Collier', 'CRE', 'schedule', '6124', NULL, '\"Akanksha Baker\",\"STAT 121\",\"MON\",\"12:00:00\",\"12:45:00\"', '2026-04-28 00:46:12'),
(195, 'WB55131, Justin Collier', 'DEL', 'courses', '10060', '\"STAT 121\",\"Introduction to Statistics for the Social Sciences\"', NULL, '2026-04-28 00:46:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`log_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
