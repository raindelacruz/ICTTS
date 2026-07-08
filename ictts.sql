-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2026 at 09:21 AM
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
-- Database: `ictts`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `actor_name` varchar(160) DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `entity_type` varchar(80) DEFAULT NULL,
  `entity_id` varchar(80) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(60) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED DEFAULT NULL,
  `recipient_email` varchar(190) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` mediumtext NOT NULL,
  `status` enum('queued','sent','failed','logged') NOT NULL DEFAULT 'logged',
  `error_message` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `message` varchar(500) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `id` int(10) UNSIGNED NOT NULL,
  `region_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(190) NOT NULL,
  `office_type` enum('Regional Office','Branch Office','Central Office','District Office','Other') NOT NULL DEFAULT 'Branch Office',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`id`, `region_id`, `name`, `office_type`, `status`, `created_at`) VALUES
(1, 1, 'Maguindanao', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(2, 1, 'Basilan', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(3, 1, 'ARMM Regional Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(4, 1, 'Lanao del Sur', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(5, 2, 'Surigao del Sur', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(6, 2, 'Agusan del Sur', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(7, 2, 'NFA CARAGA Regional Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(8, 3, 'Administrative and General Services Department', 'Central Office', 'active', '2026-05-28 15:19:34'),
(9, 3, 'Finance Department', 'Central Office', 'active', '2026-05-28 15:19:34'),
(10, 3, 'Operations and Coordination Department', 'Central Office', 'active', '2026-05-28 15:19:34'),
(11, 3, 'Legal Affairs Department', 'Central Office', 'active', '2026-05-28 15:19:34'),
(12, 3, 'Corporate Planning and Management Services Deparment', 'Central Office', 'active', '2026-05-28 15:19:34'),
(13, 3, 'Public Affairs Division', 'Central Office', 'active', '2026-05-28 15:19:34'),
(14, 4, 'East District', 'District Office', 'active', '2026-05-28 15:19:34'),
(15, 4, 'Central District', 'District Office', 'active', '2026-05-28 15:19:34'),
(16, 4, 'NCR Regional Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(17, 5, 'Eastern Pangasinan', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(18, 5, 'Region I Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(19, 5, 'La Union', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(20, 5, 'Ilocos Norte', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(21, 6, 'Cagayan', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(22, 6, 'Nueva Vizcaya', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(23, 6, 'Region II Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(24, 6, 'Isabela', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(25, 7, 'Region III Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(26, 7, 'Bulacan', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(27, 7, 'Tarlac', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(28, 7, 'Pampanga', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(29, 7, 'Nueva Ecija', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(30, 8, 'Occidental Mindoro', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(31, 8, 'Palawan', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(32, 8, 'Oriental Mindoro', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(33, 8, 'Region IV Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(34, 8, 'Batangas', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(35, 8, 'Quezon', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(36, 8, 'Laguna', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(37, 9, 'Zamboanga Del Sur', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(38, 9, 'Region IX Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(39, 9, 'Zamboanga', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(40, 10, 'Region V Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(41, 10, 'Camarines Sur', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(42, 10, 'Sorsogon', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(43, 10, 'Albay', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(44, 11, 'Region VI Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(45, 11, 'Iloilo', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(46, 11, 'Capiz', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(47, 11, 'Negros Occidental', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(48, 12, 'Region VII Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(49, 12, 'Cebu', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(50, 12, 'Negros Oriental', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(51, 12, 'Bohol', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(52, 13, 'Leyte', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(53, 13, 'Region VIII Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(54, 13, 'Northern Samar', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(55, 14, 'Misamis Oriental', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(56, 14, 'Bukidnon', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(57, 14, 'Region X Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(58, 14, 'Lanao del Norte', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(59, 15, 'Davao Oriental', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(60, 15, 'Davao del Sur', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(61, 15, 'Region XI Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(62, 15, 'Davao del Norte', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(63, 16, 'Sultan Kudarat', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(64, 16, 'North Cotabato', 'Branch Office', 'active', '2026-05-28 15:19:34'),
(65, 16, 'Region XII Office', 'Regional Office', 'active', '2026-05-28 15:19:34'),
(66, 16, 'South Cotabato', 'Branch Office', 'active', '2026-05-28 15:19:34');

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(160) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `code`, `name`, `status`, `created_at`) VALUES
(1, 'ARMM', 'NFA ARMM Regional Office', 'active', '2026-05-28 15:19:34'),
(2, 'CARAGA', 'NFA CARAGA Regional Office', 'active', '2026-05-28 15:19:34'),
(3, 'CO', 'NFA Central Office', 'active', '2026-05-28 15:19:34'),
(4, 'NCR', 'NFA NCR Regional Office', 'active', '2026-05-28 15:19:34'),
(5, 'R1', 'NFA Region I Office', 'active', '2026-05-28 15:19:34'),
(6, 'R2', 'NFA Region II Office', 'active', '2026-05-28 15:19:34'),
(7, 'R3', 'NFA Region III Office', 'active', '2026-05-28 15:19:34'),
(8, 'R4', 'NFA Region IV Office', 'active', '2026-05-28 15:19:34'),
(9, 'R9', 'NFA Region IX Office', 'active', '2026-05-28 15:19:34'),
(10, 'R5', 'NFA Region V Office', 'active', '2026-05-28 15:19:34'),
(11, 'R6', 'NFA Region VI Office', 'active', '2026-05-28 15:19:34'),
(12, 'R7', 'NFA Region VII Office', 'active', '2026-05-28 15:19:34'),
(13, 'R8', 'NFA Region VIII Office', 'active', '2026-05-28 15:19:34'),
(14, 'R10', 'NFA Region X Office', 'active', '2026-05-28 15:19:34'),
(15, 'R11', 'NFA Region XI Office', 'active', '2026-05-28 15:19:34'),
(16, 'R12', 'NFA Region XII Office', 'active', '2026-05-28 15:19:34');

-- --------------------------------------------------------

--
-- Table structure for table `requester_confirmation_tokens`
--

CREATE TABLE `requester_confirmation_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `status`, `created_at`) VALUES
(1, 'Hardware and Network Infrastructure', 'active', '2026-05-28 15:19:34'),
(2, 'Systems and Application', 'active', '2026-05-28 15:19:34');

-- --------------------------------------------------------

--
-- Table structure for table `service_items`
--

CREATE TABLE `service_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `service_category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `default_priority` enum('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_items`
--

INSERT INTO `service_items` (`id`, `service_category_id`, `name`, `default_priority`, `status`, `created_at`) VALUES
(1, 1, 'Certifications', 'Low', 'active', '2026-05-28 15:19:34'),
(2, 1, 'ICT Resource User Access', 'Medium', 'active', '2026-05-28 15:19:34'),
(3, 1, 'Technical Support', 'Medium', 'active', '2026-05-28 15:19:34'),
(4, 1, 'Connectivity Problem', 'High', 'active', '2026-05-28 15:19:34'),
(5, 1, 'Replacement of Parts/Equipment', 'Medium', 'active', '2026-05-28 15:19:34'),
(6, 2, 'E-IFOMIS', 'High', 'active', '2026-05-28 15:19:34'),
(7, 2, 'Cash Monitoring', 'High', 'active', '2026-05-28 15:19:34'),
(8, 2, 'HURIS', 'High', 'active', '2026-05-28 15:19:34'),
(9, 2, 'Payroll', 'High', 'active', '2026-05-28 15:19:34'),
(10, 2, 'Website Posting', 'Medium', 'active', '2026-05-28 15:19:34'),
(11, 2, 'GovMail Support', 'Medium', 'active', '2026-05-28 15:19:34'),
(12, 2, 'Bid Posting', 'Medium', 'active', '2026-05-28 15:19:34');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(120) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'ict_notification_email', 'ict@nfa.gov.ph', '2026-05-28 15:19:34', NULL),
(2, 'system_public_url', 'https://ebps.nfa.gov.ph/ICTTS/public', '2026-05-28 15:19:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_no` varchar(40) NOT NULL,
  `requested_at` datetime NOT NULL,
  `requester_name` varchar(160) NOT NULL,
  `requester_position` varchar(160) DEFAULT NULL,
  `requester_email` varchar(190) NOT NULL,
  `requester_contact` varchar(50) NOT NULL,
  `region_id` int(10) UNSIGNED NOT NULL,
  `office_id` int(10) UNSIGNED NOT NULL,
  `requested_for` datetime NOT NULL,
  `service_category_id` int(10) UNSIGNED NOT NULL,
  `service_item_id` int(10) UNSIGNED NOT NULL,
  `responsible_group` varchar(160) DEFAULT NULL,
  `description` text NOT NULL,
  `priority` enum('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  `status` enum('Submitted','Assigned','In Progress','Pending','Completed','Confirmed Completed','Returned for Further Action','Cancelled') NOT NULL DEFAULT 'Submitted',
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `assigned_by` int(10) UNSIGNED DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `response_due_at` datetime DEFAULT NULL,
  `resolution_due_at` datetime DEFAULT NULL,
  `first_responded_at` datetime DEFAULT NULL,
  `sla_status` enum('Within SLA','Response Overdue','Resolution Overdue','Met','Breached') NOT NULL DEFAULT 'Within SLA',
  `sla_breached_at` datetime DEFAULT NULL,
  `completed_by_tech_at` datetime DEFAULT NULL,
  `requester_confirmed_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_assignees`
--

CREATE TABLE `ticket_assignees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `assignment_role` enum('primary','supporting') NOT NULL DEFAULT 'supporting',
  `assigned_by` int(10) UNSIGNED NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `removed_at` datetime DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_assignments`
--

CREATE TABLE `ticket_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `previous_assignee` int(10) UNSIGNED DEFAULT NULL,
  `assigned_to` int(10) UNSIGNED NOT NULL,
  `assigned_by` int(10) UNSIGNED NOT NULL,
  `assignment_role` enum('primary','supporting') NOT NULL DEFAULT 'primary',
  `action` enum('assign','reassign','add_support','remove_support') NOT NULL DEFAULT 'assign',
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `notes` varchar(500) DEFAULT NULL,
  `reason` varchar(700) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_attachments`
--

CREATE TABLE `ticket_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `uploaded_by_name` varchar(160) DEFAULT NULL,
  `source` enum('requester','technical','manager','admin') NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(120) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_endorsements`
--

CREATE TABLE `ticket_endorsements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `from_group` varchar(160) DEFAULT NULL,
  `to_group` varchar(160) NOT NULL,
  `old_service_category_id` int(10) UNSIGNED DEFAULT NULL,
  `new_service_category_id` int(10) UNSIGNED DEFAULT NULL,
  `old_service_item_id` int(10) UNSIGNED DEFAULT NULL,
  `new_service_item_id` int(10) UNSIGNED DEFAULT NULL,
  `endorsed_by` int(10) UNSIGNED NOT NULL,
  `reason` varchar(700) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_escalations`
--

CREATE TABLE `ticket_escalations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `escalation_type` enum('response_overdue','resolution_overdue','manual') NOT NULL,
  `escalated_to_role` varchar(80) NOT NULL,
  `escalated_to_user` int(10) UNSIGNED DEFAULT NULL,
  `escalated_by` int(10) UNSIGNED DEFAULT NULL,
  `reason` varchar(700) NOT NULL,
  `notice_key` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_feedback`
--

CREATE TABLE `ticket_feedback` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `resolved_yes_no` enum('yes','no') NOT NULL,
  `feedback_comments` text DEFAULT NULL,
  `submitted_by_name` varchar(160) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_reopen_logs`
--

CREATE TABLE `ticket_reopen_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `old_status` varchar(80) NOT NULL,
  `new_status` varchar(80) NOT NULL,
  `reopened_by` int(10) UNSIGNED DEFAULT NULL,
  `reopened_by_name` varchar(160) DEFAULT NULL,
  `reason` varchar(700) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_status_logs`
--

CREATE TABLE `ticket_status_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `old_status` varchar(80) DEFAULT NULL,
  `new_status` varchar(80) NOT NULL,
  `changed_by` int(10) UNSIGNED DEFAULT NULL,
  `changed_by_name` varchar(160) DEFAULT NULL,
  `remarks` varchar(700) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `name` varchar(160) NOT NULL,
  `position` varchar(160) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('technical','unit_head','division_chief','admin') NOT NULL DEFAULT 'technical',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `id_number`, `name`, `position`, `email`, `password_hash`, `role`, `status`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'TECH-001', 'Temporary Technical User', 'ICT Support Staff', 'tech@nfa.gov.ph', '$2y$10$atcIkMqtOWtfbckbbDIJG.HQGLdvD0R.jhjDEIj1pNxN/CSl79oQG', 'technical', 'inactive', NULL, '2026-05-28 15:19:34', NULL),
(2, 'HEAD-001', 'Temporary Unit Head', 'Unit Head', 'unithead@nfa.gov.ph', '$2y$10$atcIkMqtOWtfbckbbDIJG.HQGLdvD0R.jhjDEIj1pNxN/CSl79oQG', 'unit_head', 'inactive', NULL, '2026-05-28 15:19:34', NULL),
(3, 'CHIEF-001', 'Temporary Division Chief', 'Division Chief', 'chief@nfa.gov.ph', '$2y$10$atcIkMqtOWtfbckbbDIJG.HQGLdvD0R.jhjDEIj1pNxN/CSl79oQG', 'division_chief', 'inactive', NULL, '2026-05-28 15:19:34', NULL),
(4, 'ADMIN-001', 'Temporary Administrator', 'System Administrator', 'admin@nfa.gov.ph', '$2y$10$atcIkMqtOWtfbckbbDIJG.HQGLdvD0R.jhjDEIj1pNxN/CSl79oQG', 'admin', 'inactive', NULL, '2026-05-28 15:19:34', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_action_idx` (`action`),
  ADD KEY `activity_created_idx` (`created_at`),
  ADD KEY `activity_logs_user_fk` (`user_id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_logs_ticket_fk` (`ticket_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_read_idx` (`user_id`,`read_at`),
  ADD KEY `notifications_created_idx` (`created_at`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `office_unique` (`region_id`,`name`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `requester_confirmation_tokens`
--
ALTER TABLE `requester_confirmation_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `confirmation_tokens_ticket_fk` (`ticket_id`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `service_items`
--
ALTER TABLE `service_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_item_unique` (`service_category_id`,`name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_no` (`ticket_no`),
  ADD KEY `tickets_status_idx` (`status`),
  ADD KEY `tickets_priority_idx` (`priority`),
  ADD KEY `tickets_sla_status_idx` (`sla_status`),
  ADD KEY `tickets_resolution_due_idx` (`resolution_due_at`),
  ADD KEY `tickets_requested_at_idx` (`requested_at`),
  ADD KEY `tickets_assigned_to_idx` (`assigned_to`),
  ADD KEY `tickets_region_fk` (`region_id`),
  ADD KEY `tickets_office_fk` (`office_id`),
  ADD KEY `tickets_category_fk` (`service_category_id`),
  ADD KEY `tickets_item_fk` (`service_item_id`),
  ADD KEY `tickets_assigned_by_fk` (`assigned_by`);

--
-- Indexes for table `ticket_assignees`
--
ALTER TABLE `ticket_assignees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_assignees_ticket_idx` (`ticket_id`,`removed_at`),
  ADD KEY `ticket_assignees_user_idx` (`user_id`,`removed_at`),
  ADD KEY `ticket_assignees_by_fk` (`assigned_by`);

--
-- Indexes for table `ticket_assignments`
--
ALTER TABLE `ticket_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_assignments_ticket_fk` (`ticket_id`),
  ADD KEY `ticket_assignments_previous_fk` (`previous_assignee`),
  ADD KEY `ticket_assignments_to_fk` (`assigned_to`),
  ADD KEY `ticket_assignments_by_fk` (`assigned_by`);

--
-- Indexes for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_attachments_ticket_idx` (`ticket_id`),
  ADD KEY `ticket_attachments_user_fk` (`uploaded_by`);

--
-- Indexes for table `ticket_endorsements`
--
ALTER TABLE `ticket_endorsements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_endorsements_ticket_idx` (`ticket_id`),
  ADD KEY `ticket_endorsements_old_category_fk` (`old_service_category_id`),
  ADD KEY `ticket_endorsements_new_category_fk` (`new_service_category_id`),
  ADD KEY `ticket_endorsements_old_item_fk` (`old_service_item_id`),
  ADD KEY `ticket_endorsements_new_item_fk` (`new_service_item_id`),
  ADD KEY `ticket_endorsements_by_fk` (`endorsed_by`);

--
-- Indexes for table `ticket_escalations`
--
ALTER TABLE `ticket_escalations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_escalations_notice_unique` (`ticket_id`,`notice_key`),
  ADD KEY `ticket_escalations_ticket_idx` (`ticket_id`),
  ADD KEY `ticket_escalations_to_user_fk` (`escalated_to_user`),
  ADD KEY `ticket_escalations_by_fk` (`escalated_by`);

--
-- Indexes for table `ticket_feedback`
--
ALTER TABLE `ticket_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_feedback_ticket_idx` (`ticket_id`);

--
-- Indexes for table `ticket_reopen_logs`
--
ALTER TABLE `ticket_reopen_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_reopen_logs_ticket_idx` (`ticket_id`),
  ADD KEY `ticket_reopen_logs_user_fk` (`reopened_by`);

--
-- Indexes for table `ticket_status_logs`
--
ALTER TABLE `ticket_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_status_logs_ticket_fk` (`ticket_id`),
  ADD KEY `ticket_status_logs_user_fk` (`changed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_number` (`id_number`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `requester_confirmation_tokens`
--
ALTER TABLE `requester_confirmation_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `service_items`
--
ALTER TABLE `service_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_assignees`
--
ALTER TABLE `ticket_assignees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_assignments`
--
ALTER TABLE `ticket_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_endorsements`
--
ALTER TABLE `ticket_endorsements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_escalations`
--
ALTER TABLE `ticket_escalations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_feedback`
--
ALTER TABLE `ticket_feedback`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_reopen_logs`
--
ALTER TABLE `ticket_reopen_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket_status_logs`
--
ALTER TABLE `ticket_status_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offices`
--
ALTER TABLE `offices`
  ADD CONSTRAINT `offices_region_fk` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`);

--
-- Constraints for table `requester_confirmation_tokens`
--
ALTER TABLE `requester_confirmation_tokens`
  ADD CONSTRAINT `confirmation_tokens_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_items`
--
ALTER TABLE `service_items`
  ADD CONSTRAINT `service_items_category_fk` FOREIGN KEY (`service_category_id`) REFERENCES `service_categories` (`id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_assigned_by_fk` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tickets_assigned_to_fk` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tickets_category_fk` FOREIGN KEY (`service_category_id`) REFERENCES `service_categories` (`id`),
  ADD CONSTRAINT `tickets_item_fk` FOREIGN KEY (`service_item_id`) REFERENCES `service_items` (`id`),
  ADD CONSTRAINT `tickets_office_fk` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`),
  ADD CONSTRAINT `tickets_region_fk` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`);

--
-- Constraints for table `ticket_assignees`
--
ALTER TABLE `ticket_assignees`
  ADD CONSTRAINT `ticket_assignees_by_fk` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ticket_assignees_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_assignees_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ticket_assignments`
--
ALTER TABLE `ticket_assignments`
  ADD CONSTRAINT `ticket_assignments_by_fk` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ticket_assignments_previous_fk` FOREIGN KEY (`previous_assignee`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ticket_assignments_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_assignments_to_fk` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`);

--
-- Constraints for table `ticket_attachments`
--
ALTER TABLE `ticket_attachments`
  ADD CONSTRAINT `ticket_attachments_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_attachments_user_fk` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `ticket_endorsements`
--
ALTER TABLE `ticket_endorsements`
  ADD CONSTRAINT `ticket_endorsements_by_fk` FOREIGN KEY (`endorsed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ticket_endorsements_new_category_fk` FOREIGN KEY (`new_service_category_id`) REFERENCES `service_categories` (`id`),
  ADD CONSTRAINT `ticket_endorsements_new_item_fk` FOREIGN KEY (`new_service_item_id`) REFERENCES `service_items` (`id`),
  ADD CONSTRAINT `ticket_endorsements_old_category_fk` FOREIGN KEY (`old_service_category_id`) REFERENCES `service_categories` (`id`),
  ADD CONSTRAINT `ticket_endorsements_old_item_fk` FOREIGN KEY (`old_service_item_id`) REFERENCES `service_items` (`id`),
  ADD CONSTRAINT `ticket_endorsements_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_escalations`
--
ALTER TABLE `ticket_escalations`
  ADD CONSTRAINT `ticket_escalations_by_fk` FOREIGN KEY (`escalated_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ticket_escalations_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_escalations_to_user_fk` FOREIGN KEY (`escalated_to_user`) REFERENCES `users` (`id`);

--
-- Constraints for table `ticket_feedback`
--
ALTER TABLE `ticket_feedback`
  ADD CONSTRAINT `ticket_feedback_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_reopen_logs`
--
ALTER TABLE `ticket_reopen_logs`
  ADD CONSTRAINT `ticket_reopen_logs_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_reopen_logs_user_fk` FOREIGN KEY (`reopened_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `ticket_status_logs`
--
ALTER TABLE `ticket_status_logs`
  ADD CONSTRAINT `ticket_status_logs_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_status_logs_user_fk` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
