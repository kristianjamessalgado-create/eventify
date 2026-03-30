-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: school_events_db
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
-- Table structure for table `account_email_otps`
--

DROP TABLE IF EXISTS `account_email_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_email_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purpose` enum('register','reactivate') NOT NULL,
  `email` varchar(120) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `payload_json` text DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `attempt_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_purpose` (`email`,`purpose`),
  KEY `idx_user_purpose` (`user_id`,`purpose`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_email_otps`
--

LOCK TABLES `account_email_otps` WRITE;
/*!40000 ALTER TABLE `account_email_otps` DISABLE KEYS */;
INSERT INTO `account_email_otps` VALUES (1,'register','bojiking31@gmail.com',NULL,'$2y$10$2JcibEoXg6V3w/X/0pefuOpBUQ0rE1Urbn.5DGO5sU.kcS5JCQrWy','{\"name\":\"boji king\",\"password_hash\":\"$2y$10$Fl\\/R2PzHuN6yI0PWNeXOpevCkAXwFwlRRVemHI\\/gBDuERnlSe.4s.\",\"role\":\"student\",\"department\":\"High school department\",\"user_code\":\"STU-440\"}','2026-03-27 10:58:53','2026-03-27 17:48:57',0,'2026-03-27 09:48:53'),(2,'register','bojiking31@gmail.com',NULL,'$2y$10$o/hZUKMDm36WGXJJzhT4qek1s6oM1VOBYEb28eTN4qjRGphrDFnH2','{\"name\":\"boji king\",\"password_hash\":\"$2y$10$ry5IRHNBTm4CCwXQPJUgW.OGeLshuflC\\/eyq3BM3H3Mh5ZhyO4nOC\",\"role\":\"student\",\"department\":\"High school department\",\"user_code\":\"STU-992\"}','2026-03-27 10:58:57','2026-03-27 17:49:01',0,'2026-03-27 09:48:57'),(3,'register','bojiking31@gmail.com',NULL,'$2y$10$qFvmXmklQJwsD6wspjSYYOCUDekpYUqR4cu7J0TyrFu70LvbHW0Yy','{\"name\":\"boji king\",\"password_hash\":\"$2y$10$YCSM2en7zcx350zWqNcSv.71yLWJo8sbAU9LguzoqFHs4rU5CTcRa\",\"role\":\"student\",\"department\":\"High school department\",\"user_code\":\"STU-677\"}','2026-03-27 10:59:01','2026-03-27 17:49:51',0,'2026-03-27 09:49:01'),(4,'register','bojiking31@gmail.com',NULL,'$2y$10$4EGZtcNMt1lJt34beuolTOHY0NGGrAxLEeC/qdVEJZu8EZ66KQh0K','{\"name\":\"boji king\",\"password_hash\":\"$2y$10$zpyG\\/vrXbYu2skJLsqUGheFA7D0IZzX7Sew15j2GJUMbxpn\\/u9Nsm\",\"role\":\"student\",\"department\":\"High school department\",\"user_code\":\"STU-601\"}','2026-03-27 10:59:51','2026-03-27 17:49:53',0,'2026-03-27 09:49:51'),(5,'register','bojiking31@gmail.com',NULL,'$2y$10$Ck8/nZ/OKhhB8afcWC79R.MEvPYr5azhipWR9uaK7fdkpXTF7oKw2','{\"name\":\"boji king\",\"password_hash\":\"$2y$10$q7sUyScjdW2ax9DHIRqNB.8c5Vxy1tg1sLq1xVPb5wV15VVOez.Fe\",\"role\":\"student\",\"department\":\"High school department\",\"user_code\":\"STU-162\"}','2026-03-27 10:59:54','2026-03-27 17:50:29',0,'2026-03-27 09:49:54'),(6,'reactivate','bojiking31@gmail.com',29,'$2y$10$D4qZVD8AKQFFzLfQYV0RUeCk8DQmjgHNOGC2wZxoZ8Y0EpgQT59Ei',NULL,'2026-03-27 11:05:13','2026-03-27 19:26:40',0,'2026-03-27 09:55:13'),(7,'register','deanechristiancamat121212@gmail.com',NULL,'$2y$10$xTo.WnNeZdcVlU0hoSqf.uph6sPwcqjgaFANcewyVPcpou10mw3Ju','{\"name\":\"deane gwapo\",\"password_hash\":\"$2y$10$ip\\/XDQfYJ86kHjgQWLBkjuhnVwNpvNjoOrdECsQHhMdqS3mdRH5di\",\"role\":\"student\",\"department\":\"College of Communication, Information and Technology\",\"user_code\":\"STU-799\"}','2026-03-27 11:30:55','2026-03-27 18:21:46',0,'2026-03-27 10:20:55'),(8,'reactivate','bojiking31@gmail.com',29,'$2y$10$S3GwrBsF5DcyToZ4IDzX8OUicnw4mjLhudkad/biif.lLt28stdLa',NULL,'2026-03-27 12:36:40','2026-03-27 22:03:57',0,'2026-03-27 11:26:40'),(9,'reactivate','bojiking31@gmail.com',29,'$2y$10$5oI3qusTrkA72uA2y.bYOeBUtxQmqI6qmXFoyxt.uniXuOw1DeAaG',NULL,'2026-03-27 15:13:57','2026-03-27 22:14:07',0,'2026-03-27 14:03:57'),(10,'reactivate','bojiking31@gmail.com',29,'$2y$10$8g0HjNmB85Vs/J3Ox2.ln.ep6t1.hwnTtTydqUQPGTWA5E18kRlyq',NULL,'2026-03-27 15:24:07','2026-03-27 22:15:27',0,'2026-03-27 14:14:07'),(11,'reactivate','bojiking31@gmail.com',29,'$2y$10$RmAJ5MGSD02RKyZIk5/lj.uYjzqx4kHL2YUqIoruLWLsYh7iWnZ/i',NULL,'2026-03-27 15:27:21','2026-03-27 22:17:53',0,'2026-03-27 14:17:21'),(12,'reactivate','bojiking31@gmail.com',29,'$2y$10$q87GKh.yQ6kTzhj5ut9o3.3L4B2RrGH.oeYFPGrHfGqzYy2AAU5pu',NULL,'2026-03-27 15:41:52','2026-03-27 22:32:21',0,'2026-03-27 14:31:52'),(13,'reactivate','bojiking31@gmail.com',29,'$2y$10$QSHb1fDA1qYPVzgBDcpSmOt6MdUFwrMWL2gcXINtrtiiOJ8Ih2pMO',NULL,'2026-03-27 15:50:52','2026-03-27 22:41:18',0,'2026-03-27 14:40:52'),(14,'register','blademale31@gmail.com',NULL,'$2y$10$IoW7BWCaGe1p3jzTNkrX9e3UsJxSbZyN3Z8OQZ1IAUhNawTPWRmbK','{\"name\":\"blademale\",\"password_hash\":\"$2y$10$YQxrnNdwXQhRnQpu4vUpPeO6n2QtreRy6BS4msx4ZwdvE6xVjGLMW\",\"role\":\"student\",\"department\":\"High school department\",\"user_code\":\"STU-129\"}','2026-03-30 17:10:24','2026-03-30 23:02:16',0,'2026-03-30 15:00:24'),(15,'reactivate','bojiking31@gmail.com',29,'$2y$10$6MWLhyOSVoNL8x5rMHuFBOtaUdQqTU1zr.4qdFjAw.SXnW3oi8tDi',NULL,'2026-03-30 17:29:54','2026-03-30 23:20:17',0,'2026-03-30 15:19:54');
/*!40000 ALTER TABLE `account_email_otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actor_id` int(11) DEFAULT NULL,
  `actor_role` varchar(50) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `actor_id` (`actor_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,27,'multimedia','login_success','user',27,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-13 06:56:30'),(2,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-13 06:56:47'),(3,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-13 07:16:18'),(4,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-13 07:20:09'),(5,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 00:28:18'),(6,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 00:45:17'),(7,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 01:13:21'),(8,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 01:27:33'),(9,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 14:52:49'),(10,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 14:56:17'),(11,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 14:57:03'),(12,14,'student','login_success','user',14,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 14:57:51'),(13,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 15:18:26'),(14,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 15:33:33'),(15,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 15:36:11'),(16,13,'student','login_success','user',13,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 15:36:39'),(17,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 15:37:05'),(18,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 15:37:33'),(19,28,'admin','event_approved','event',9,'Approved event ID 9','2026-03-14 15:37:43'),(20,13,'student','login_success','user',13,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 15:37:58'),(21,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 15:46:35'),(22,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 15:49:37'),(23,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 16:01:13'),(24,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-14 16:14:08'),(25,13,'student','login_success','user',13,'Successful login from IP 192.168.1.11 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1','2026-03-14 16:21:15'),(26,13,'student','login_success','user',13,'Successful login from IP 192.168.1.11 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1','2026-03-14 16:23:09'),(27,13,'student','login_success','user',13,'Successful login from IP 192.168.1.11 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1','2026-03-14 16:27:44'),(28,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-15 01:21:01'),(29,13,'student','login_success','user',13,'Successful login from IP 192.168.1.11 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1','2026-03-15 01:28:03'),(30,14,'student','login_success','user',14,'Successful login from IP 192.168.1.11 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1','2026-03-15 01:31:46'),(31,14,'student','login_success','user',14,'Successful login from IP 192.168.1.11 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1','2026-03-15 02:16:52'),(32,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-15 10:29:03'),(33,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-15 10:31:49'),(34,13,'student','login_success','user',13,'Successful login from IP 192.168.1.13 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1','2026-03-15 10:35:08'),(35,13,'student','login_success','user',13,'Successful login from IP 192.168.1.13 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1','2026-03-15 10:54:27'),(36,13,'student','login_success','user',13,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-15 11:02:17'),(37,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-15 11:35:38'),(38,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-15 11:55:42'),(39,13,'student','login_success','user',13,'Successful login from IP 192.168.1.13 | UA: Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1','2026-03-15 11:56:29'),(40,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-15 12:35:18'),(41,13,'student','login_success','user',13,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-15 13:48:19'),(42,13,'student','login_success','user',13,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-15 13:49:15'),(43,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 14:57:08'),(44,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 15:18:53'),(45,17,'organizer','event_submitted_pending','event',10,'Submitted for admin approval: call lablab','2026-03-26 15:27:41'),(46,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 15:28:13'),(47,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:29:13'),(48,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:29:39'),(49,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:32:02'),(50,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:32:58'),(51,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:33:07'),(52,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 15:33:47'),(53,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:33:50'),(54,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:34:01'),(55,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 15:35:02'),(56,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:35:17'),(57,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:35:33'),(58,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 15:35:53'),(59,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:35:56'),(60,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:36:54'),(61,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:37:14'),(62,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:38:29'),(63,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:44:25'),(64,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via phone','2026-03-26 15:53:52'),(65,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 15:57:39'),(66,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 15:58:39'),(67,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via email','2026-03-26 15:58:47'),(68,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 16:17:26'),(69,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 16:17:54'),(70,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via email','2026-03-26 16:26:54'),(71,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via email','2026-03-26 16:31:34'),(72,28,'admin','event_approval_otp_sent','event',10,'Sent OTP via email','2026-03-26 16:32:53'),(73,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 16:33:29'),(74,17,'organizer','event_approved_via_otp','event',10,'Organizer verified OTP and event became active','2026-03-26 16:33:41'),(75,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 16:35:20'),(76,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-26 16:38:25'),(77,14,'student','login_success','user',14,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 09:38:37'),(78,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 09:39:23'),(79,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 09:40:25'),(80,3,'super_admin','user_reactivated','user',20,'Reactivated user ID 20','2026-03-27 09:40:30'),(81,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 09:40:59'),(82,3,'super_admin','user_reactivated','user',20,'Reactivated user ID 20','2026-03-27 09:41:04'),(83,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 09:49:13'),(84,29,'student','register_email_verified','user',29,'Completed registration via OTP email verification','2026-03-27 09:50:29'),(85,29,'student','login_success','user',29,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 09:50:39'),(86,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 09:54:51'),(87,3,'super_admin','user_deactivated','user',29,'Deactivated user ID 29','2026-03-27 09:55:07'),(88,3,'super_admin','user_reactivated','user',29,'Sent reactivation OTP to user ID 29','2026-03-27 09:55:16'),(89,30,'student','register_email_verified','user',30,'Completed registration via OTP email verification','2026-03-27 10:21:46'),(90,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 10:22:20'),(91,3,'super_admin','user_activated','user',30,'Activated pending user ID 30','2026-03-27 10:25:49'),(92,30,'student','login_success','user',30,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 10:26:07'),(93,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 11:25:35'),(94,3,'super_admin','user_activated','user',29,'Activated pending user ID 29','2026-03-27 11:25:39'),(95,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 11:26:36'),(96,3,'super_admin','user_reactivated','user',29,'Sent reactivation OTP to user ID 29','2026-03-27 11:26:43'),(97,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 13:39:34'),(98,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 14:02:57'),(99,3,'super_admin','user_reactivated','user',29,'Sent reactivation OTP to user ID 29','2026-03-27 14:04:01'),(100,3,'super_admin','user_reactivated','user',29,'Sent reactivation OTP to user ID 29','2026-03-27 14:14:10'),(101,29,'user','reactivation_otp_verified_pending_activation','user',29,'User completed reactivation OTP verification and is pending super admin activation','2026-03-27 14:15:27'),(102,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 14:17:18'),(103,3,'super_admin','user_reactivated','user',29,'Sent reactivation OTP to user ID 29','2026-03-27 14:17:24'),(104,29,'user','reactivation_otp_verified_pending_activation','user',29,'User completed reactivation OTP verification and is pending super admin activation','2026-03-27 14:17:53'),(105,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 14:31:38'),(106,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 14:31:48'),(107,3,'super_admin','user_reactivated','user',29,'Sent reactivation OTP to user ID 29','2026-03-27 14:31:55'),(108,29,'user','reactivation_otp_verified_pending_activation','user',29,'User completed reactivation OTP verification and is pending super admin activation','2026-03-27 14:32:21'),(109,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 14:40:49'),(110,3,'super_admin','user_reactivated','user',29,'Sent reactivation OTP to user ID 29','2026-03-27 14:40:56'),(111,29,'student','account_reactivated_by_otp','user',29,'User completed reactivation OTP verification and was auto-logged in','2026-03-27 14:41:18'),(112,27,'multimedia','login_success','user',27,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-27 14:50:34'),(113,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 14:47:37'),(114,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 14:52:02'),(115,31,'student','register_email_verified','user',31,'Completed registration via OTP email verification','2026-03-30 15:02:16'),(116,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:04:02'),(117,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:06:23'),(118,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:07:18'),(119,3,'super_admin','user_activated','user',31,'Activated pending user ID 31','2026-03-30 15:07:21'),(120,31,'student','login_success','user',31,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:07:58'),(121,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:08:24'),(122,17,'organizer','event_submitted_pending','event',11,'Submitted for admin approval: unli siomeow kaon','2026-03-30 15:14:14'),(123,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:14:31'),(124,31,'student','login_success','user',31,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:15:15'),(125,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:15:39'),(126,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:18:01'),(127,3,'super_admin','user_role_changed','user',29,'Changed user ID 29 role from student to admin','2026-03-30 15:18:22'),(128,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:18:44'),(129,3,'super_admin','user_role_changed','user',29,'Changed user ID 29 role from admin to organizer','2026-03-30 15:18:48'),(130,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:19:50'),(131,3,'super_admin','user_reactivated','user',29,'Sent reactivation OTP to user ID 29','2026-03-30 15:19:57'),(132,29,'organizer','account_reactivated_by_otp','user',29,'User completed reactivation OTP verification and was auto-logged in','2026-03-30 15:20:17'),(133,29,'organizer','login_success','user',29,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:22:55'),(134,29,'organizer','event_submitted_pending','event',12,'Submitted for admin approval: cozy florist','2026-03-30 15:23:50'),(135,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:24:15'),(136,3,'super_admin','event_approved','event',12,'Approved event ID 12','2026-03-30 15:24:24'),(137,3,'super_admin','event_approved','event',12,'Approved event ID 12','2026-03-30 15:24:28'),(138,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:24:40'),(139,28,'admin','event_rejected','event',11,'Rejected event ID 11','2026-03-30 15:24:54'),(140,14,'student','login_success','user',14,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:25:16'),(141,27,'multimedia','login_success','user',27,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:30:19'),(142,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:34:27'),(143,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:35:18'),(144,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 15:37:02'),(145,17,'organizer','login_success','user',17,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 16:01:30'),(146,3,'super_admin','login_success','user',3,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 16:08:59'),(147,28,'admin','login_success','user',28,'Successful login from IP ::1 | UA: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-03-30 16:09:29');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `attended_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_approval_otps`
--

DROP TABLE IF EXISTS `event_approval_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_approval_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `delivery_method` enum('email','phone') NOT NULL,
  `delivery_target` varchar(120) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_org_id` (`organizer_id`),
  KEY `idx_expires` (`expires_at`),
  KEY `event_approval_otps_verified_by_fk` (`verified_by`),
  KEY `event_approval_otps_created_by_fk` (`created_by`),
  CONSTRAINT `event_approval_otps_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_approval_otps_event_fk` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_approval_otps_organizer_fk` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_approval_otps_verified_by_fk` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_approval_otps`
--

LOCK TABLES `event_approval_otps` WRITE;
/*!40000 ALTER TABLE `event_approval_otps` DISABLE KEYS */;
INSERT INTO `event_approval_otps` VALUES (1,10,17,'phone','09085210452','$2y$10$UnmMeUCmMGH7mrbsog8NSekd3ZrSMHg4pn6RTFFwhi2UC8Q6C26HC','2026-03-26 16:39:13','2026-03-26 23:29:38',NULL,28,'2026-03-26 15:29:13'),(2,10,17,'phone','09085210452','$2y$10$5aqygCSqsC0DiKbLLv3vw.0aC8KCT/.AK9Z0FEFV7XPkEosadZTs6','2026-03-26 16:39:38','2026-03-26 23:32:02',NULL,28,'2026-03-26 15:29:39'),(3,10,17,'phone','09085210452','$2y$10$G1cAcn.v8.G3/AutDlwF9OjRBg23GsZFxi/eqoxMax29E1qPbb8MO','2026-03-26 16:42:02','2026-03-26 23:32:58',NULL,28,'2026-03-26 15:32:02'),(4,10,17,'phone','09085210452','$2y$10$4GnjaghMuh3yraujaTcjW.yYWWwWBsuF.td5ESOpAsvaiYJRTPfJ6','2026-03-26 16:42:58','2026-03-26 23:33:07',NULL,28,'2026-03-26 15:32:58'),(5,10,17,'phone','09085210452','$2y$10$cJDIOhndapq6oVKXif7hCOKn2JetJZYIvKhoKirW5H9eb1D1UvaUm','2026-03-26 16:43:07','2026-03-26 23:33:50',NULL,28,'2026-03-26 15:33:07'),(6,10,17,'phone','09085210452','$2y$10$4xWdWrc65ewKtaJPFzoazOAaEvJsqARRRt9QSzs7VQnnIAE81qeTy','2026-03-26 16:43:50','2026-03-26 23:34:01',NULL,28,'2026-03-26 15:33:50'),(7,10,17,'phone','09085210452','$2y$10$OHy3Rz1oInDzBRSwhI01HulBd4l1Uz9D2KhBThiEFbjuqh1a9SBM6','2026-03-26 16:44:01','2026-03-26 23:35:17',NULL,28,'2026-03-26 15:34:01'),(8,10,17,'phone','09085210452','$2y$10$Y/BqGOyZbz7X8BL5LUha5OCWKJ5oOGWLRTL2MZNwhO21lGZ9q/9ia','2026-03-26 16:45:17','2026-03-26 23:35:33',NULL,28,'2026-03-26 15:35:17'),(9,10,17,'phone','09085210452','$2y$10$3bPF8t6STlgy7Wh6cdQYWeoTJzsq4qsACrtXerx1JauL9LvNyPbKe','2026-03-26 16:45:33','2026-03-26 23:35:55',NULL,28,'2026-03-26 15:35:33'),(10,10,17,'phone','09085210452','$2y$10$ANvS/huT5AfUyh/QiNIbFuUwlNcgnmcSLSDM9.NdopzpfK.UsPTJq','2026-03-26 16:45:55','2026-03-26 23:36:54',NULL,28,'2026-03-26 15:35:56'),(11,10,17,'phone','09085210452','$2y$10$B.8JR/s5IN9r6rbh2vPx9.uyovWxhYKAzNA.RxpIKx4FXlDttyv1O','2026-03-26 16:46:54','2026-03-26 23:37:14',NULL,28,'2026-03-26 15:36:54'),(12,10,17,'phone','09085210452','$2y$10$AWRqR366yLEw7wNAi54bMOBpKrQz43vJ59sFirzPlC9DeNDRH1X7C','2026-03-26 16:47:14','2026-03-26 23:38:29',NULL,28,'2026-03-26 15:37:14'),(13,10,17,'phone','09085210452','$2y$10$J/wKZjY2zqeH3rLSHZVgG.BsIAKgxdTaD5USaamu54m1G7JTI03mO','2026-03-26 16:48:29','2026-03-26 23:44:24',NULL,28,'2026-03-26 15:38:29'),(14,10,17,'phone','09085210452','$2y$10$9BgGv2t1tN6cIAEO23ptFueGys/WtLPuNu1s1ZakKdvRCarb/RJ9G','2026-03-26 16:54:24','2026-03-26 23:53:51',NULL,28,'2026-03-26 15:44:24'),(15,10,17,'phone','09085210452','$2y$10$Wl/lhg11Wcveuu/X1j4ohe6dPIC0kNRgfoKgc5QZlhXAlMj8V9Etu','2026-03-26 17:03:51','2026-03-26 23:58:45',NULL,28,'2026-03-26 15:53:51'),(16,10,17,'email','kristianjamessalgado@gmail.com','$2y$10$fgWaH1VFgYokccEupdDRn.9YqJRmrKUdNd554HZ0IZdD1iYzqAfvu','2026-03-26 17:08:45','2026-03-27 00:26:52',NULL,28,'2026-03-26 15:58:45'),(17,10,17,'email','bojiking31@gmail.com','$2y$10$BzEPctd63Vw/rQBGjJ1Px.QZnus5quR7L7s8ThtR1IhFo1fQ5eKcy','2026-03-26 17:36:52','2026-03-27 00:31:32',NULL,28,'2026-03-26 16:26:52'),(18,10,17,'email','bojiking31@gmail.com','$2y$10$3Eh0Sp2BkJl/oSWHXSJGTurguElCucCTtTJC3/2pUfDiBzl6hKvyC','2026-03-26 17:41:32','2026-03-27 00:32:49',NULL,28,'2026-03-26 16:31:32'),(19,10,17,'email','bojiking31@gmail.com','$2y$10$oFm/zsduFT/v3Gw6Y/GQLO7FH5Zhf5GcxCHqrZo1.WEm16kGo6aL.','2026-03-26 17:42:49','2026-03-27 00:33:41',NULL,28,'2026-03-26 16:32:49');
/*!40000 ALTER TABLE `event_approval_otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_photos`
--

DROP TABLE IF EXISTS `event_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `event_photos_event_fk` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_photos_user_fk` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_photos`
--

LOCK TABLES `event_photos` WRITE;
/*!40000 ALTER TABLE `event_photos` DISABLE KEYS */;
INSERT INTO `event_photos` VALUES (1,2,27,'uploads/events/2/20260205_075003_69843d9ba58ee_finale.png',NULL,'2026-02-05 06:50:03'),(3,4,27,'uploads/events/20260205_175211_6984cabb37ce8_RobloxScreenShot20250601_192737133.png',NULL,'2026-02-05 16:52:11'),(4,2,27,'uploads/events/20260309_080343_69ae70cfe770f_Screenshot_2024-09-03_002302.png',NULL,'2026-03-09 07:03:43'),(5,4,27,'uploads/events/all/20260309_095140_69ae8a1c7eeff_Screenshot_2024-09-17_181103.png',NULL,'2026-03-09 08:51:40'),(6,6,27,'uploads/events/all/20260309_130945_69aeb889afa26_Screenshot_2024-09-03_002302.png',NULL,'2026-03-09 12:09:45');
/*!40000 ALTER TABLE `event_photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `reject_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `checkin_token` (`checkin_token`),
  KEY `organizer_id` (`organizer_id`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'Sample Orientation','Orientation for new students','2025-12-20',NULL,NULL,'Main Auditorium',17,'2025-12-18 06:30:41','closed','ALL','aad31a873785a99f2d9af0a6166783ad',NULL),(2,'sample','sir','2026-02-28',NULL,NULL,'wlc',17,'2026-01-27 03:54:13','closed','ALL',NULL,NULL),(3,'sample2','hehe','2026-01-30',NULL,NULL,'western leyte college',17,'2026-01-27 15:46:49','closed','BSHM',NULL,NULL),(4,'For All','For all sample','2026-02-04',NULL,NULL,'wlc',17,'2026-01-28 13:30:13','closed','ALL',NULL,NULL),(6,'intrams badminton','intrams badminton- conahs vs cicte 9:00 am- 9:00 pm','2026-03-14',NULL,NULL,'Western Leyte College, Ormoc City',17,'2026-03-09 12:03:37','closed','ALL',NULL,NULL),(7,'intrams basket','basker','2026-03-14',NULL,NULL,'Western Leyte College, Ormoc City',17,'2026-03-13 04:18:19','','ALL','4bf23f1b7a9438bdd999125822140d48',NULL),(8,'intrams swimming','swimming','2026-03-14',NULL,NULL,'Western Leyte College, Ormoc City',17,'2026-03-13 04:19:32','closed','ALL',NULL,NULL),(9,'dan vs adrianne sumbagay','sumbagay','2026-03-16','13:00:00','14:34:00','western leyte college',17,'2026-03-14 15:35:35','closed','ALL','3e755df4c20ca0035def27f82e30f910',NULL),(10,'call lablab','call lablab','2026-03-27','12:26:00',NULL,'Superdome, I. Larrazabal Boulevard, South, Ormoc City Proper, Ormoc, Leyte, Eastern Visayas, 6541, P',17,'2026-03-26 15:27:41','closed','ALL','507494106f5dbb3f99450ba794883503',NULL),(11,'unli siomeow kaon','unli','2026-03-31','12:13:00',NULL,'Superdome, I. Larrazabal Boulevard, South, Ormoc City Proper, Ormoc, Leyte, Eastern Visayas, 6541, P',17,'2026-03-30 15:14:14','rejected','ALL','ba34b6270ff9f63007f806a7a64a6b34',''),(12,'cozy florist','florist','2026-04-01','12:23:00',NULL,'Superdome, I. Larrazabal Boulevard, South, Ormoc City Proper, Ormoc, Leyte, Eastern Visayas, 6541, P',29,'2026-03-30 15:23:50','active','ALL','16f97daaa46dfae80c1cd82efc3bcc38',NULL);
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `event_id` (`event_id`),
  KEY `read_at` (`read_at`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'event_pending_review','New event pending approval','Organizer submitted \"call lablab\" for approval.',10,NULL,'2026-03-26 15:27:41'),(2,3,'event_pending_review','New event pending approval','Organizer submitted \"call lablab\" for approval.',10,NULL,'2026-03-26 15:27:41'),(3,28,'event_pending_review','New event pending approval','Organizer submitted \"call lablab\" for approval.',10,'2026-03-26 23:29:52','2026-03-26 15:27:41'),(24,1,'event_auto_approved','Event approved via organizer OTP','Organizer verified OTP. Event \"call lablab\" is now active.',10,NULL,'2026-03-26 16:33:41'),(25,3,'event_auto_approved','Event approved via organizer OTP','Organizer verified OTP. Event \"call lablab\" is now active.',10,NULL,'2026-03-26 16:33:41'),(26,28,'event_auto_approved','Event approved via organizer OTP','Organizer verified OTP. Event \"call lablab\" is now active.',10,NULL,'2026-03-26 16:33:41'),(27,1,'account_pending_approval','New account pending approval','Email-verified registration waiting approval: deane gwapo (student)',NULL,NULL,'2026-03-27 10:21:46'),(28,3,'account_pending_approval','New account pending approval','Email-verified registration waiting approval: deane gwapo (student)',NULL,NULL,'2026-03-27 10:21:46'),(30,1,'reactivation_ready','Reactivation OTP verified','User verified reactivation OTP and is ready for activation: bojiking31@gmail.com',NULL,NULL,'2026-03-27 14:15:27'),(31,3,'reactivation_ready','Reactivation OTP verified','User verified reactivation OTP and is ready for activation: bojiking31@gmail.com',NULL,NULL,'2026-03-27 14:15:27'),(32,1,'reactivation_ready','Reactivation OTP verified','User verified reactivation OTP and is ready for activation: bojiking31@gmail.com',NULL,NULL,'2026-03-27 14:17:53'),(33,3,'reactivation_ready','Reactivation OTP verified','User verified reactivation OTP and is ready for activation: bojiking31@gmail.com',NULL,NULL,'2026-03-27 14:17:53'),(34,1,'reactivation_ready','Reactivation OTP verified','User verified reactivation OTP and is ready for activation: bojiking31@gmail.com',NULL,NULL,'2026-03-27 14:32:21'),(35,3,'reactivation_ready','Reactivation OTP verified','User verified reactivation OTP and is ready for activation: bojiking31@gmail.com',NULL,NULL,'2026-03-27 14:32:21'),(36,1,'account_pending_approval','New account pending approval','Email-verified registration waiting approval: blademale (student)',NULL,NULL,'2026-03-30 15:02:16'),(37,3,'account_pending_approval','New account pending approval','Email-verified registration waiting approval: blademale (student)',NULL,NULL,'2026-03-30 15:02:16'),(38,1,'event_pending_review','New event pending approval','Organizer submitted \"unli siomeow kaon\" for approval.',11,NULL,'2026-03-30 15:14:14'),(39,3,'event_pending_review','New event pending approval','Organizer submitted \"unli siomeow kaon\" for approval.',11,NULL,'2026-03-30 15:14:14'),(40,28,'event_pending_review','New event pending approval','Organizer submitted \"unli siomeow kaon\" for approval.',11,NULL,'2026-03-30 15:14:14'),(41,1,'event_pending_review','New event pending approval','Organizer submitted \"cozy florist\" for approval.',12,NULL,'2026-03-30 15:23:50'),(42,3,'event_pending_review','New event pending approval','Organizer submitted \"cozy florist\" for approval.',12,NULL,'2026-03-30 15:23:50'),(43,28,'event_pending_review','New event pending approval','Organizer submitted \"cozy florist\" for approval.',12,NULL,'2026-03-30 15:23:50'),(44,29,'event_approved','Event approved','Your event \"cozy florist\" has been approved and is now visible to students.',12,NULL,'2026-03-30 15:24:24'),(45,29,'event_approved','Event approved','Your event \"cozy florist\" has been approved and is now visible to students.',12,NULL,'2026-03-30 15:24:28'),(46,17,'event_rejected','Event rejected','Your event \"unli siomeow kaon\" was rejected.',11,NULL,'2026-03-30 15:24:54');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registrations`
--

DROP TABLE IF EXISTS `registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registration_date` datetime DEFAULT current_timestamp(),
  `qr_code` varchar(255) DEFAULT NULL,
  `status` enum('absent','present') DEFAULT 'absent',
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_registration` (`user_id`,`event_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registrations`
--

LOCK TABLES `registrations` WRITE;
/*!40000 ALTER TABLE `registrations` DISABLE KEYS */;
INSERT INTO `registrations` VALUES (1,13,9,'2026-03-15 00:27:48',NULL,'present','2026-03-15 18:35:11',NULL),(3,14,9,'2026-03-15 09:31:47',NULL,'present','2026-03-15 09:31:47',NULL),(5,30,10,'2026-03-27 18:26:25',NULL,'absent',NULL,NULL);
/*!40000 ALTER TABLE `registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `organizer_contact_email` varchar(100) DEFAULT NULL,
  `organizer_phone` varchar(25) DEFAULT NULL,
  `organizer_contact_method` enum('email','phone') NOT NULL DEFAULT 'email',
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','organizer','student','multimedia') NOT NULL,
  `department` enum('BSIT','BSHM','CONAHS','Senior High','High school department','College of Communication, Information and Technology','College of Accountancy and Business','School of Law and Political Science','College of Education','College of Nursing and Allied health sciences','College of Hospitality Management') DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `failed_attempts` int(11) DEFAULT 0,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'SA-001','Kristian','kristian@school.com',NULL,NULL,'email','$2y$10$PASTE_HASH_HERE','super_admin',NULL,NULL,'active','2025-12-15 17:38:46',3,0),(3,'SA-001','Kristian Salgado','kristian1@school.com',NULL,NULL,'email','$2y$10$IYqWZeXPvmCz3SvEmeSd6Op5eV2J3PRXeWaC5tPU3Guu7xZBs9TRK','super_admin',NULL,NULL,'active','2025-12-15 18:09:54',0,0),(4,'STU-781','sample','sample@gmail.com',NULL,NULL,'email','af2bdbe1aa9b6ec1e2ade1d694f41fc71a831d0268e9891562113d8a62add1bf','student',NULL,NULL,'active','2025-12-15 18:32:46',0,0),(5,'STU-776','suai','suai@gmail.com',NULL,NULL,'email','dea259230178e8b71e2ee186546e9cd6c56b7922b4519e6835d6ebc507f0c64e','student',NULL,NULL,'active','2025-12-15 18:55:30',1,0),(6,'STU-842','suai1','suai1@gmail.com',NULL,NULL,'email','$2y$10$wj9ZwjEM02hYCLS7tXe6e.Kn7k03k6mJWRSFHSxrXk3AXX7990DiS','student',NULL,NULL,'active','2025-12-15 18:57:12',2,0),(7,'STU-738','suai2','suai2@gmail.com',NULL,NULL,'email','ba1889bd80d5dffe82089bb71ca4683831c4c872ef817b1306421c421130eee2','student',NULL,NULL,'active','2025-12-15 18:57:32',0,0),(8,'STU-351','suai3','suai3@gmail.com',NULL,NULL,'email','4e78ba3f87382d0ce4a41471737af8f0b2310c6fc7295b4a40ab00508f2f9620','student',NULL,NULL,'active','2025-12-15 18:59:36',2,0),(9,'STU-253','suai4','suai4@gmail.com',NULL,NULL,'email','51ee5f59976acb3565d0609c70cb939ded012c4267c3d0fce29a5af2562129d4','student',NULL,NULL,'active','2025-12-15 19:00:19',0,0),(10,'STU-781','suai5','suai5@gmail.com',NULL,NULL,'email','d65c023117c75d15c1f117f53ac85232fdb728513310411aceae11449f350d43','student',NULL,NULL,'active','2025-12-15 19:01:36',0,0),(11,'STU-979','another sample','anothersample@gmail.com',NULL,NULL,'email','a08f8859605f0b362db593d2a8e756f0f65a334800b575329cf7d5af6d424f21','student',NULL,NULL,'active','2025-12-16 07:11:30',2,0),(12,'STU-512','sample1','sample1@gmail.com',NULL,NULL,'email','e85130791f31db1699f61a5e7ae7b5e85e70399414f38476091896214771cd17','student',NULL,NULL,'active','2025-12-16 07:21:16',2,0),(13,'STU-913','Kristian James Salgado','sample4@gmail.com',NULL,NULL,'email','$2y$10$GOSpGfllB2kBaCJLmvORrOR9ScVRgG5eOrwDxA9G8E.zhwlUVl9xq','student',NULL,'uploads/profile_pictures/profile_13_1773572295_69b690c737f8d.jpeg','active','2025-12-16 08:41:26',0,0),(14,'STU-226','Kristian James Salgado','sample5@gmail.com',NULL,NULL,'email','$2y$10$bYL4yAXTtDweHG0hnXFb8.lZmw9l8l6kQ9/5PsrCjwCh3F9WJhzY6','student','BSIT','uploads/profile_pictures/profile_14_1770312568_6984d378b4f87.png','active','2025-12-17 04:05:13',0,0),(15,'ORG-318','organizer','organizer@gmail.com',NULL,NULL,'email','154a0a277d0a9e90475532eeb50bb087f6dcf19172db5fc8091221091c772ac5','organizer',NULL,NULL,'active','2025-12-18 04:23:41',0,0),(16,'ORG-206','organizer1','organizer1@gmail.com',NULL,NULL,'email','154a0a277d0a9e90475532eeb50bb087f6dcf19172db5fc8091221091c772ac5','organizer',NULL,NULL,'active','2025-12-18 06:16:33',3,0),(17,'ORG-880','organizer2','organizer2@gmail.com','bojiking31@gmail.com','09085210452','email','$2y$10$ztiS2BWvxE0qzbBgOASB5u7aMcmVvhm8ChxKmz08ya5T85XsDSPRm','organizer',NULL,'uploads/profile_pictures/profile_17_1771385893_69953425b8545.png','active','2025-12-18 06:25:39',0,0),(18,'STU-238','sample6','sample6@gmail.com',NULL,NULL,'email','bcd8eb16b2ae1c881de513d28a3f49426afaa1ab34a3e834df5fbf7bdcbe9770','student',NULL,NULL,'active','2025-12-18 07:41:13',1,0),(19,'STU-558','sample7','sample7@gmail.com',NULL,NULL,'email','24714505b9df6e69f9367f12217d590d4f15b4367d1697b6f833d1e07b291d2a','student',NULL,NULL,'active','2025-12-19 05:57:19',0,0),(20,'ORG-923','samplereg','Samplereg@gmail.com',NULL,NULL,'email','76cd579e5eea4f719469276719558fa1b46c0196a613cb8aa5bfcdd9a43628f8','organizer',NULL,NULL,'active','2025-12-21 06:09:01',6,0),(21,'ORG-994','samplereg2','Samplereg2@gmail.com',NULL,NULL,'email','64be8f3069aeb2299bc66aa17b7c0e47530d5dc0475ef0606ea6ffbaad04f819','organizer',NULL,NULL,'active','2025-12-21 06:44:58',0,0),(22,'ORG-964','samplereg3','Samplereg3@gmail.com',NULL,NULL,'email','8d9353a8accc17cba0ee8dab3b744552799c6f37226a5b9e48a37cb82945de8f','organizer',NULL,NULL,'active','2025-12-21 06:50:28',1,0),(23,'STU-827','sammilby','sammilby@gmail.com',NULL,NULL,'email','5e6eb2532b6f1eb86b9bfd41c5c1e9ca14d444eb013c223c0958b9dde57fd54f','student',NULL,NULL,'active','2025-12-22 03:55:21',0,0),(24,'STU-510','deanecamat','deanecamat@gmail.com',NULL,NULL,'email','faae366a9b3bc5e637a5f10b53a826ce31546dfb03a1cc3f7849001906d13dff','student',NULL,NULL,'active','2025-12-23 12:42:31',0,0),(25,'STU-939','jabes','jabes@gmail.com',NULL,NULL,'email','781e60f7f136510363f9a9522ebeb73f647d94111c6e2d27fd669e0770a26aef','student',NULL,NULL,'active','2025-12-23 15:13:46',2,0),(26,'MUL-692','multimedia','multimedia@gmail.com',NULL,NULL,'email','$2y$10$8uY9QHmNVKHaGzqCpc3FVOFli2Ad4.ftlDkKq4.GzWEtGVpP73o2m','multimedia','','uploads/profile_pictures/profile_26_1771386378_6995360ae6219.png','active','2026-02-05 06:36:11',0,0),(27,'MUL-721','multimedia1','multimedia1@gmail.com',NULL,NULL,'email','$2y$10$iFLWXYM3owfuH7JrE1Qvuu10xeErUur7GmU.eEa95YnC3JQ7BiygS','multimedia','',NULL,'active','2026-02-05 06:49:05',0,0),(28,'ADM-214','admin1','admin1@gmail.com',NULL,NULL,'email','$2y$10$GhltrcajVDqPr9dvTpRWhuxsXhOXupLct6Pe9tZp.HY3OcgJrDtLa','admin','',NULL,'active','2026-03-13 04:26:02',0,0),(29,'STU-162','boji king','bojiking31@gmail.com',NULL,NULL,'email','$2y$10$dKy5cG0sPDQguZjta5yaaOqeDT3rllW7ZEDTlna/YSk4XT4Cd2uIe','organizer','High school department',NULL,'active','2026-03-27 09:50:29',0,0),(30,'STU-799','deane gwapo','deanechristiancamat121212@gmail.com',NULL,NULL,'email','$2y$10$ip/XDQfYJ86kHjgQWLBkjuhnVwNpvNjoOrdECsQHhMdqS3mdRH5di','student','College of Communication, Information and Technology',NULL,'active','2026-03-27 10:21:46',0,0),(31,'STU-129','blademale','blademale31@gmail.com',NULL,NULL,'email','$2y$10$YQxrnNdwXQhRnQpu4vUpPeO6n2QtreRy6BS4msx4ZwdvE6xVjGLMW','student','High school department',NULL,'active','2026-03-30 15:02:16',0,0);
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

-- Dump completed on 2026-03-31  0:19:26
