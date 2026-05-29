-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: ictts
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `ictts`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `ictts` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `ictts`;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `actor_name` varchar(160) DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `entity_type` varchar(80) DEFAULT NULL,
  `entity_id` varchar(80) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(60) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `activity_action_idx` (`action`),
  KEY `activity_created_idx` (`created_at`),
  KEY `activity_logs_user_fk` (`user_id`),
  CONSTRAINT `activity_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_logs`
--

DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned DEFAULT NULL,
  `recipient_email` varchar(190) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` mediumtext NOT NULL,
  `status` enum('queued','sent','failed','logged') NOT NULL DEFAULT 'logged',
  `error_message` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `email_logs_ticket_fk` (`ticket_id`),
  CONSTRAINT `email_logs_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_logs`
--

LOCK TABLES `email_logs` WRITE;
/*!40000 ALTER TABLE `email_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `message` varchar(500) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `notifications_user_read_idx` (`user_id`,`read_at`),
  KEY `notifications_created_idx` (`created_at`),
  CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offices`
--

DROP TABLE IF EXISTS `offices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `region_id` int(10) unsigned NOT NULL,
  `name` varchar(190) NOT NULL,
  `office_type` enum('Regional Office','Branch Office','Central Office','District Office','Other') NOT NULL DEFAULT 'Branch Office',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `office_unique` (`region_id`,`name`),
  CONSTRAINT `offices_region_fk` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offices`
--

LOCK TABLES `offices` WRITE;
/*!40000 ALTER TABLE `offices` DISABLE KEYS */;
INSERT INTO `offices` VALUES (1,1,'Maguindanao','Branch Office','active','2026-05-28 15:19:34'),(2,1,'Basilan','Branch Office','active','2026-05-28 15:19:34'),(3,1,'ARMM Regional Office','Regional Office','active','2026-05-28 15:19:34'),(4,1,'Lanao del Sur','Branch Office','active','2026-05-28 15:19:34'),(5,2,'Surigao del Sur','Branch Office','active','2026-05-28 15:19:34'),(6,2,'Agusan del Sur','Branch Office','active','2026-05-28 15:19:34'),(7,2,'NFA CARAGA Regional Office','Regional Office','active','2026-05-28 15:19:34'),(8,3,'Administrative and General Services Department','Central Office','active','2026-05-28 15:19:34'),(9,3,'Finance Department','Central Office','active','2026-05-28 15:19:34'),(10,3,'Operations and Coordination Department','Central Office','active','2026-05-28 15:19:34'),(11,3,'Legal Affairs Department','Central Office','active','2026-05-28 15:19:34'),(12,3,'Corporate Planning and Management Services Deparment','Central Office','active','2026-05-28 15:19:34'),(13,3,'Public Affairs Division','Central Office','active','2026-05-28 15:19:34'),(14,4,'East District','District Office','active','2026-05-28 15:19:34'),(15,4,'Central District','District Office','active','2026-05-28 15:19:34'),(16,4,'NCR Regional Office','Regional Office','active','2026-05-28 15:19:34'),(17,5,'Eastern Pangasinan','Branch Office','active','2026-05-28 15:19:34'),(18,5,'Region I Office','Regional Office','active','2026-05-28 15:19:34'),(19,5,'La Union','Branch Office','active','2026-05-28 15:19:34'),(20,5,'Ilocos Norte','Branch Office','active','2026-05-28 15:19:34'),(21,6,'Cagayan','Branch Office','active','2026-05-28 15:19:34'),(22,6,'Nueva Vizcaya','Branch Office','active','2026-05-28 15:19:34'),(23,6,'Region II Office','Regional Office','active','2026-05-28 15:19:34'),(24,6,'Isabela','Branch Office','active','2026-05-28 15:19:34'),(25,7,'Region III Office','Regional Office','active','2026-05-28 15:19:34'),(26,7,'Bulacan','Branch Office','active','2026-05-28 15:19:34'),(27,7,'Tarlac','Branch Office','active','2026-05-28 15:19:34'),(28,7,'Pampanga','Branch Office','active','2026-05-28 15:19:34'),(29,7,'Nueva Ecija','Branch Office','active','2026-05-28 15:19:34'),(30,8,'Occidental Mindoro','Branch Office','active','2026-05-28 15:19:34'),(31,8,'Palawan','Branch Office','active','2026-05-28 15:19:34'),(32,8,'Oriental Mindoro','Branch Office','active','2026-05-28 15:19:34'),(33,8,'Region IV Office','Regional Office','active','2026-05-28 15:19:34'),(34,8,'Batangas','Branch Office','active','2026-05-28 15:19:34'),(35,8,'Quezon','Branch Office','active','2026-05-28 15:19:34'),(36,8,'Laguna','Branch Office','active','2026-05-28 15:19:34'),(37,9,'Zamboanga Del Sur','Branch Office','active','2026-05-28 15:19:34'),(38,9,'Region IX Office','Regional Office','active','2026-05-28 15:19:34'),(39,9,'Zamboanga','Branch Office','active','2026-05-28 15:19:34'),(40,10,'Region V Office','Regional Office','active','2026-05-28 15:19:34'),(41,10,'Camarines Sur','Branch Office','active','2026-05-28 15:19:34'),(42,10,'Sorsogon','Branch Office','active','2026-05-28 15:19:34'),(43,10,'Albay','Branch Office','active','2026-05-28 15:19:34'),(44,11,'Region VI Office','Regional Office','active','2026-05-28 15:19:34'),(45,11,'Iloilo','Branch Office','active','2026-05-28 15:19:34'),(46,11,'Capiz','Branch Office','active','2026-05-28 15:19:34'),(47,11,'Negros Occidental','Branch Office','active','2026-05-28 15:19:34'),(48,12,'Region VII Office','Regional Office','active','2026-05-28 15:19:34'),(49,12,'Cebu','Branch Office','active','2026-05-28 15:19:34'),(50,12,'Negros Oriental','Branch Office','active','2026-05-28 15:19:34'),(51,12,'Bohol','Branch Office','active','2026-05-28 15:19:34'),(52,13,'Leyte','Branch Office','active','2026-05-28 15:19:34'),(53,13,'Region VIII Office','Regional Office','active','2026-05-28 15:19:34'),(54,13,'Northern Samar','Branch Office','active','2026-05-28 15:19:34'),(55,14,'Misamis Oriental','Branch Office','active','2026-05-28 15:19:34'),(56,14,'Bukidnon','Branch Office','active','2026-05-28 15:19:34'),(57,14,'Region X Office','Regional Office','active','2026-05-28 15:19:34'),(58,14,'Lanao del Norte','Branch Office','active','2026-05-28 15:19:34'),(59,15,'Davao Oriental','Branch Office','active','2026-05-28 15:19:34'),(60,15,'Davao del Sur','Branch Office','active','2026-05-28 15:19:34'),(61,15,'Region XI Office','Regional Office','active','2026-05-28 15:19:34'),(62,15,'Davao del Norte','Branch Office','active','2026-05-28 15:19:34'),(63,16,'Sultan Kudarat','Branch Office','active','2026-05-28 15:19:34'),(64,16,'North Cotabato','Branch Office','active','2026-05-28 15:19:34'),(65,16,'Region XII Office','Regional Office','active','2026-05-28 15:19:34'),(66,16,'South Cotabato','Branch Office','active','2026-05-28 15:19:34');
/*!40000 ALTER TABLE `offices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `regions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(160) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regions`
--

LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
INSERT INTO `regions` VALUES (1,'ARMM','NFA ARMM Regional Office','active','2026-05-28 15:19:34'),(2,'CARAGA','NFA CARAGA Regional Office','active','2026-05-28 15:19:34'),(3,'CO','NFA Central Office','active','2026-05-28 15:19:34'),(4,'NCR','NFA NCR Regional Office','active','2026-05-28 15:19:34'),(5,'R1','NFA Region I Office','active','2026-05-28 15:19:34'),(6,'R2','NFA Region II Office','active','2026-05-28 15:19:34'),(7,'R3','NFA Region III Office','active','2026-05-28 15:19:34'),(8,'R4','NFA Region IV Office','active','2026-05-28 15:19:34'),(9,'R9','NFA Region IX Office','active','2026-05-28 15:19:34'),(10,'R5','NFA Region V Office','active','2026-05-28 15:19:34'),(11,'R6','NFA Region VI Office','active','2026-05-28 15:19:34'),(12,'R7','NFA Region VII Office','active','2026-05-28 15:19:34'),(13,'R8','NFA Region VIII Office','active','2026-05-28 15:19:34'),(14,'R10','NFA Region X Office','active','2026-05-28 15:19:34'),(15,'R11','NFA Region XI Office','active','2026-05-28 15:19:34'),(16,'R12','NFA Region XII Office','active','2026-05-28 15:19:34');
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requester_confirmation_tokens`
--

DROP TABLE IF EXISTS `requester_confirmation_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requester_confirmation_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `confirmation_tokens_ticket_fk` (`ticket_id`),
  CONSTRAINT `confirmation_tokens_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requester_confirmation_tokens`
--

LOCK TABLES `requester_confirmation_tokens` WRITE;
/*!40000 ALTER TABLE `requester_confirmation_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `requester_confirmation_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_categories`
--

DROP TABLE IF EXISTS `service_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(160) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_categories`
--

LOCK TABLES `service_categories` WRITE;
/*!40000 ALTER TABLE `service_categories` DISABLE KEYS */;
INSERT INTO `service_categories` VALUES (1,'Hardware and Network Infrastructure','active','2026-05-28 15:19:34'),(2,'Systems and Application','active','2026-05-28 15:19:34');
/*!40000 ALTER TABLE `service_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_items`
--

DROP TABLE IF EXISTS `service_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_category_id` int(10) unsigned NOT NULL,
  `name` varchar(160) NOT NULL,
  `default_priority` enum('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_item_unique` (`service_category_id`,`name`),
  CONSTRAINT `service_items_category_fk` FOREIGN KEY (`service_category_id`) REFERENCES `service_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_items`
--

LOCK TABLES `service_items` WRITE;
/*!40000 ALTER TABLE `service_items` DISABLE KEYS */;
INSERT INTO `service_items` VALUES (1,1,'Certifications','Low','active','2026-05-28 15:19:34'),(2,1,'ICT Resource User Access','Medium','active','2026-05-28 15:19:34'),(3,1,'Technical Support','Medium','active','2026-05-28 15:19:34'),(4,1,'Connectivity Problem','High','active','2026-05-28 15:19:34'),(5,1,'Replacement of Parts/Equipment','Medium','active','2026-05-28 15:19:34'),(6,2,'E-IFOMIS','High','active','2026-05-28 15:19:34'),(7,2,'Cash Monitoring','High','active','2026-05-28 15:19:34'),(8,2,'HURIS','High','active','2026-05-28 15:19:34'),(9,2,'Payroll','High','active','2026-05-28 15:19:34'),(10,2,'Website Posting','Medium','active','2026-05-28 15:19:34'),(11,2,'GovMail Support','Medium','active','2026-05-28 15:19:34'),(12,2,'Bid Posting','Medium','active','2026-05-28 15:19:34');
/*!40000 ALTER TABLE `service_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(120) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'ict_notification_email','ict@nfa.gov.ph','2026-05-28 15:19:34',NULL),(2,'system_public_url','https://ebps.nfa.gov.ph/ICTTS/public','2026-05-28 15:19:34',NULL);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_assignees`
--

DROP TABLE IF EXISTS `ticket_assignees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_assignees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `assignment_role` enum('primary','supporting') NOT NULL DEFAULT 'supporting',
  `assigned_by` int(10) unsigned NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `removed_at` datetime DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_assignees_ticket_idx` (`ticket_id`,`removed_at`),
  KEY `ticket_assignees_user_idx` (`user_id`,`removed_at`),
  KEY `ticket_assignees_by_fk` (`assigned_by`),
  CONSTRAINT `ticket_assignees_by_fk` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  CONSTRAINT `ticket_assignees_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_assignees_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_assignees`
--

LOCK TABLES `ticket_assignees` WRITE;
/*!40000 ALTER TABLE `ticket_assignees` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_assignees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_assignments`
--

DROP TABLE IF EXISTS `ticket_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `previous_assignee` int(10) unsigned DEFAULT NULL,
  `assigned_to` int(10) unsigned NOT NULL,
  `assigned_by` int(10) unsigned NOT NULL,
  `assignment_role` enum('primary','supporting') NOT NULL DEFAULT 'primary',
  `action` enum('assign','reassign','add_support','remove_support') NOT NULL DEFAULT 'assign',
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `notes` varchar(500) DEFAULT NULL,
  `reason` varchar(700) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_assignments_ticket_fk` (`ticket_id`),
  KEY `ticket_assignments_previous_fk` (`previous_assignee`),
  KEY `ticket_assignments_to_fk` (`assigned_to`),
  KEY `ticket_assignments_by_fk` (`assigned_by`),
  CONSTRAINT `ticket_assignments_by_fk` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  CONSTRAINT `ticket_assignments_previous_fk` FOREIGN KEY (`previous_assignee`) REFERENCES `users` (`id`),
  CONSTRAINT `ticket_assignments_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_assignments_to_fk` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_assignments`
--

LOCK TABLES `ticket_assignments` WRITE;
/*!40000 ALTER TABLE `ticket_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_attachments`
--

DROP TABLE IF EXISTS `ticket_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `uploaded_by` int(10) unsigned DEFAULT NULL,
  `uploaded_by_name` varchar(160) DEFAULT NULL,
  `source` enum('requester','technical','manager','admin') NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(120) NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_attachments_ticket_idx` (`ticket_id`),
  KEY `ticket_attachments_user_fk` (`uploaded_by`),
  CONSTRAINT `ticket_attachments_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_attachments_user_fk` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_attachments`
--

LOCK TABLES `ticket_attachments` WRITE;
/*!40000 ALTER TABLE `ticket_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_endorsements`
--

DROP TABLE IF EXISTS `ticket_endorsements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_endorsements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `from_group` varchar(160) DEFAULT NULL,
  `to_group` varchar(160) NOT NULL,
  `old_service_category_id` int(10) unsigned DEFAULT NULL,
  `new_service_category_id` int(10) unsigned DEFAULT NULL,
  `old_service_item_id` int(10) unsigned DEFAULT NULL,
  `new_service_item_id` int(10) unsigned DEFAULT NULL,
  `endorsed_by` int(10) unsigned NOT NULL,
  `reason` varchar(700) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_endorsements_ticket_idx` (`ticket_id`),
  KEY `ticket_endorsements_old_category_fk` (`old_service_category_id`),
  KEY `ticket_endorsements_new_category_fk` (`new_service_category_id`),
  KEY `ticket_endorsements_old_item_fk` (`old_service_item_id`),
  KEY `ticket_endorsements_new_item_fk` (`new_service_item_id`),
  KEY `ticket_endorsements_by_fk` (`endorsed_by`),
  CONSTRAINT `ticket_endorsements_by_fk` FOREIGN KEY (`endorsed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `ticket_endorsements_new_category_fk` FOREIGN KEY (`new_service_category_id`) REFERENCES `service_categories` (`id`),
  CONSTRAINT `ticket_endorsements_new_item_fk` FOREIGN KEY (`new_service_item_id`) REFERENCES `service_items` (`id`),
  CONSTRAINT `ticket_endorsements_old_category_fk` FOREIGN KEY (`old_service_category_id`) REFERENCES `service_categories` (`id`),
  CONSTRAINT `ticket_endorsements_old_item_fk` FOREIGN KEY (`old_service_item_id`) REFERENCES `service_items` (`id`),
  CONSTRAINT `ticket_endorsements_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_endorsements`
--

LOCK TABLES `ticket_endorsements` WRITE;
/*!40000 ALTER TABLE `ticket_endorsements` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_endorsements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_escalations`
--

DROP TABLE IF EXISTS `ticket_escalations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_escalations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `escalation_type` enum('response_overdue','resolution_overdue','manual') NOT NULL,
  `escalated_to_role` varchar(80) NOT NULL,
  `escalated_to_user` int(10) unsigned DEFAULT NULL,
  `escalated_by` int(10) unsigned DEFAULT NULL,
  `reason` varchar(700) NOT NULL,
  `notice_key` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_escalations_notice_unique` (`ticket_id`,`notice_key`),
  KEY `ticket_escalations_ticket_idx` (`ticket_id`),
  KEY `ticket_escalations_to_user_fk` (`escalated_to_user`),
  KEY `ticket_escalations_by_fk` (`escalated_by`),
  CONSTRAINT `ticket_escalations_by_fk` FOREIGN KEY (`escalated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `ticket_escalations_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_escalations_to_user_fk` FOREIGN KEY (`escalated_to_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_escalations`
--

LOCK TABLES `ticket_escalations` WRITE;
/*!40000 ALTER TABLE `ticket_escalations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_escalations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_feedback`
--

DROP TABLE IF EXISTS `ticket_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_feedback` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `rating` tinyint(3) unsigned DEFAULT NULL,
  `resolved_yes_no` enum('yes','no') NOT NULL,
  `feedback_comments` text DEFAULT NULL,
  `submitted_by_name` varchar(160) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_feedback_ticket_idx` (`ticket_id`),
  CONSTRAINT `ticket_feedback_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_feedback`
--

LOCK TABLES `ticket_feedback` WRITE;
/*!40000 ALTER TABLE `ticket_feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_reopen_logs`
--

DROP TABLE IF EXISTS `ticket_reopen_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_reopen_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `old_status` varchar(80) NOT NULL,
  `new_status` varchar(80) NOT NULL,
  `reopened_by` int(10) unsigned DEFAULT NULL,
  `reopened_by_name` varchar(160) DEFAULT NULL,
  `reason` varchar(700) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_reopen_logs_ticket_idx` (`ticket_id`),
  KEY `ticket_reopen_logs_user_fk` (`reopened_by`),
  CONSTRAINT `ticket_reopen_logs_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_reopen_logs_user_fk` FOREIGN KEY (`reopened_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_reopen_logs`
--

LOCK TABLES `ticket_reopen_logs` WRITE;
/*!40000 ALTER TABLE `ticket_reopen_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_reopen_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_status_logs`
--

DROP TABLE IF EXISTS `ticket_status_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_status_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `old_status` varchar(80) DEFAULT NULL,
  `new_status` varchar(80) NOT NULL,
  `changed_by` int(10) unsigned DEFAULT NULL,
  `changed_by_name` varchar(160) DEFAULT NULL,
  `remarks` varchar(700) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_status_logs_ticket_fk` (`ticket_id`),
  KEY `ticket_status_logs_user_fk` (`changed_by`),
  CONSTRAINT `ticket_status_logs_ticket_fk` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_status_logs_user_fk` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_status_logs`
--

LOCK TABLES `ticket_status_logs` WRITE;
/*!40000 ALTER TABLE `ticket_status_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_status_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_no` varchar(40) NOT NULL,
  `requested_at` datetime NOT NULL,
  `requester_name` varchar(160) NOT NULL,
  `requester_position` varchar(160) DEFAULT NULL,
  `requester_email` varchar(190) NOT NULL,
  `requester_contact` varchar(50) NOT NULL,
  `region_id` int(10) unsigned NOT NULL,
  `office_id` int(10) unsigned NOT NULL,
  `requested_for` datetime NOT NULL,
  `service_category_id` int(10) unsigned NOT NULL,
  `service_item_id` int(10) unsigned NOT NULL,
  `responsible_group` varchar(160) DEFAULT NULL,
  `description` text NOT NULL,
  `priority` enum('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  `status` enum('Submitted','Assigned','In Progress','Pending','Completed','Confirmed Completed','Returned for Further Action','Cancelled') NOT NULL DEFAULT 'Submitted',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `assigned_by` int(10) unsigned DEFAULT NULL,
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
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_no` (`ticket_no`),
  KEY `tickets_status_idx` (`status`),
  KEY `tickets_priority_idx` (`priority`),
  KEY `tickets_sla_status_idx` (`sla_status`),
  KEY `tickets_resolution_due_idx` (`resolution_due_at`),
  KEY `tickets_requested_at_idx` (`requested_at`),
  KEY `tickets_assigned_to_idx` (`assigned_to`),
  KEY `tickets_region_fk` (`region_id`),
  KEY `tickets_office_fk` (`office_id`),
  KEY `tickets_category_fk` (`service_category_id`),
  KEY `tickets_item_fk` (`service_item_id`),
  KEY `tickets_assigned_by_fk` (`assigned_by`),
  CONSTRAINT `tickets_assigned_by_fk` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  CONSTRAINT `tickets_assigned_to_fk` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  CONSTRAINT `tickets_category_fk` FOREIGN KEY (`service_category_id`) REFERENCES `service_categories` (`id`),
  CONSTRAINT `tickets_item_fk` FOREIGN KEY (`service_item_id`) REFERENCES `service_items` (`id`),
  CONSTRAINT `tickets_office_fk` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`),
  CONSTRAINT `tickets_region_fk` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_number` varchar(50) NOT NULL,
  `name` varchar(160) NOT NULL,
  `position` varchar(160) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('technical','unit_head','division_chief','admin') NOT NULL DEFAULT 'technical',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_number` (`id_number`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'TECH-001','Temporary Technical User','ICT Support Staff','tech@nfa.gov.ph','$2y$10$atcIkMqtOWtfbckbbDIJG.HQGLdvD0R.jhjDEIj1pNxN/CSl79oQG','technical','active',NULL,'2026-05-28 15:19:34',NULL),(2,'HEAD-001','Temporary Unit Head','Unit Head','unithead@nfa.gov.ph','$2y$10$atcIkMqtOWtfbckbbDIJG.HQGLdvD0R.jhjDEIj1pNxN/CSl79oQG','unit_head','active',NULL,'2026-05-28 15:19:34',NULL),(3,'CHIEF-001','Temporary Division Chief','Division Chief','chief@nfa.gov.ph','$2y$10$atcIkMqtOWtfbckbbDIJG.HQGLdvD0R.jhjDEIj1pNxN/CSl79oQG','division_chief','active',NULL,'2026-05-28 15:19:34',NULL),(4,'ADMIN-001','Temporary Administrator','System Administrator','admin@nfa.gov.ph','$2y$10$atcIkMqtOWtfbckbbDIJG.HQGLdvD0R.jhjDEIj1pNxN/CSl79oQG','admin','active',NULL,'2026-05-28 15:19:34',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-28 15:19:43
