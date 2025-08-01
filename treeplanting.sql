-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: treeplanting
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
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `division`
--

DROP TABLE IF EXISTS `division`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `division` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `LGA_name` varchar(255) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `division`
--

LOCK TABLES `division` WRITE;
/*!40000 ALTER TABLE `division` DISABLE KEYS */;
INSERT INTO `division` VALUES (1,'Agaie',NULL,NULL,NULL,NULL),(2,'Agwara',NULL,NULL,NULL,NULL),(3,'Bida',NULL,NULL,NULL,NULL),(4,'Borgu',NULL,NULL,NULL,NULL),(5,'Bosso',NULL,NULL,NULL,NULL),(6,'Chanchaga',NULL,NULL,NULL,NULL),(7,'Edati',NULL,NULL,NULL,NULL),(8,'Gbako',NULL,NULL,NULL,NULL),(9,'Gurara',NULL,NULL,NULL,NULL),(10,'Katcha',NULL,NULL,NULL,NULL),(11,'Kontagora',NULL,NULL,NULL,NULL),(12,'Lapai',NULL,NULL,NULL,NULL),(13,'Lavun',NULL,NULL,NULL,NULL),(14,'Magama',NULL,NULL,NULL,NULL),(15,'Mariga',NULL,NULL,NULL,NULL),(16,'Mashegu',NULL,NULL,NULL,NULL),(17,'Mokwa',NULL,NULL,NULL,NULL),(18,'Munya',NULL,NULL,NULL,NULL),(19,'Paikoro',NULL,NULL,NULL,NULL),(20,'Rafi',NULL,NULL,NULL,NULL),(21,'Rijau',NULL,NULL,NULL,NULL),(22,'Shiroro',NULL,NULL,NULL,NULL),(23,'Suleja',NULL,NULL,NULL,NULL),(24,'Tafa',NULL,NULL,NULL,NULL),(25,'Wushishi',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `division` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inspections`
--

DROP TABLE IF EXISTS `inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inspections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inspection_date` date NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `comment` text DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inspections_user_id_foreign` (`user_id`),
  CONSTRAINT `inspections_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inspections`
--

LOCK TABLES `inspections` WRITE;
/*!40000 ALTER TABLE `inspections` DISABLE KEYS */;
/*!40000 ALTER TABLE `inspections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_07_06_211913_add_fields_to_users_table',1),(5,'2025_07_06_211914_create_inspections_table',1),(6,'2025_07_06_211914_create_pictures_table',1),(7,'2025_07_06_211915_create_division_table',1),(8,'2025_07_06_211916_create_planting_location_status_table',1),(9,'2025_07_06_211916_create_planting_locations_table',1),(10,'2025_07_06_211917_create_tree_planting_status_table',1),(11,'2025_07_06_211917_create_tree_types_table',1),(12,'2025_07_06_211918_create_tree_plantings_table',1),(13,'2025_07_07_053245_add_pictureid_foreign_to_users',1),(14,'2025_07_07_082922_create_roles_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pictures`
--

DROP TABLE IF EXISTS `pictures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pictures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `original_path` varchar(255) NOT NULL,
  `thumbnail_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pictures_user_id_foreign` (`user_id`),
  CONSTRAINT `pictures_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pictures`
--

LOCK TABLES `pictures` WRITE;
/*!40000 ALTER TABLE `pictures` DISABLE KEYS */;
/*!40000 ALTER TABLE `pictures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `planting_location_status`
--

DROP TABLE IF EXISTS `planting_location_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `planting_location_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `planting_location_status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planting_location_status`
--

LOCK TABLES `planting_location_status` WRITE;
/*!40000 ALTER TABLE `planting_location_status` DISABLE KEYS */;
INSERT INTO `planting_location_status` VALUES (1,'Planned',NULL,NULL),(2,'Active',NULL,NULL);
/*!40000 ALTER TABLE `planting_location_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `planting_locations`
--

DROP TABLE IF EXISTS `planting_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `planting_locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(255) NOT NULL,
  `division_id` bigint(20) unsigned NOT NULL,
  `comment` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `status` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `planting_locations_division_id_foreign` (`division_id`),
  KEY `planting_locations_user_id_foreign` (`user_id`),
  KEY `planting_locations_status_foreign` (`status`),
  CONSTRAINT `planting_locations_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `division` (`id`) ON DELETE CASCADE,
  CONSTRAINT `planting_locations_status_foreign` FOREIGN KEY (`status`) REFERENCES `planting_location_status` (`id`),
  CONSTRAINT `planting_locations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=301 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planting_locations`
--

LOCK TABLES `planting_locations` WRITE;
/*!40000 ALTER TABLE `planting_locations` DISABLE KEYS */;
INSERT INTO `planting_locations` VALUES (1,'South Tobystad',3,NULL,10.7258460,7.5427990,10,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(2,'Sydneyborough',1,NULL,10.0743260,7.4551990,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(3,'Loraton',25,NULL,9.3048910,7.7237770,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(4,'Favianborough',15,NULL,9.6564030,6.9837070,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(5,'Welchville',23,'Id eveniet beatae mollitia quia nisi.',9.1199670,6.0222300,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(6,'Port Emanuelstad',17,'Laboriosam aperiam assumenda similique molestiae.',9.9817390,7.5503080,19,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(7,'Port Maybellborough',11,'Id repudiandae magni eos.',10.6980450,6.5333930,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(8,'Freddyfurt',9,'Esse id saepe sit eos quos.',10.5273510,6.2101430,19,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(9,'North Ellen',5,'Repellendus quia in corporis qui.',10.4000840,7.5489780,18,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(10,'Lake Lydaburgh',23,NULL,10.0490630,6.5285860,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(11,'Volkmanburgh',7,NULL,10.4012190,7.4317170,1,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(12,'East Bethel',19,'Laboriosam sit non et enim.',10.0568450,7.0565950,19,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(13,'New Josephineberg',1,'Occaecati nam quisquam dolorem natus quia repudiandae.',10.6324700,6.0137970,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(14,'Bobbieborough',24,'Sit et eaque exercitationem.',9.9829830,7.6397350,10,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(15,'New Rowena',13,'Praesentium labore nobis exercitationem modi porro sit.',9.1182950,7.8639310,8,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(16,'New Gayle',16,'Ex et quia molestias blanditiis.',9.8924840,6.8580890,8,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(17,'Alejandrinburgh',4,NULL,9.3223000,7.4132370,16,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(18,'East Bettyton',13,NULL,9.8234010,6.2344000,6,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(19,'Littleview',11,NULL,9.4588850,7.7139870,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(20,'New Keshaunport',25,'Veritatis voluptatem asperiores voluptas ut quo.',10.0564510,7.1156970,1,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(21,'South Kitty',22,NULL,9.1782280,7.1851280,1,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(22,'Greenholtmouth',20,'Accusantium rerum similique ipsam sit consequuntur esse quis.',10.6287350,6.4098110,18,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(23,'Suzanneton',11,NULL,10.4480040,7.3513910,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(24,'West Gay',25,NULL,9.4458210,6.6542170,6,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(25,'Dillonmouth',7,NULL,10.3481610,6.1734680,13,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(26,'New Janicktown',8,NULL,9.0719810,7.0307910,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(27,'South Rodolfotown',8,NULL,10.7141690,6.7281040,8,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(28,'Hesselland',24,NULL,10.3198790,6.6441790,14,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(29,'South Sydneystad',15,NULL,9.7297820,7.1594380,10,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(30,'Funkmouth',22,NULL,9.5162440,6.8557350,10,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(31,'Gustavechester',22,'Illum recusandae doloremque non dolor ea et repudiandae.',9.9138050,7.2552730,2,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(32,'Martinemouth',23,'Corporis temporibus sed enim harum perspiciatis vel repellendus veritatis.',10.3171970,6.7694540,19,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(33,'East Abigayle',21,NULL,10.9771870,6.7326800,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(34,'Pacochaport',22,'Sit incidunt accusamus nesciunt eaque ad placeat vero velit.',10.7714380,6.7462760,6,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(35,'East Annieside',16,'Exercitationem repellat et eum maxime.',10.9124000,7.8052170,4,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(36,'North Giovanni',2,NULL,9.1453010,7.3004200,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(37,'Kochborough',1,'Illum dolorem qui omnis ut culpa ab odio minus.',10.7531070,6.1917140,15,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(38,'West Ora',10,NULL,10.5774770,7.9757190,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(39,'Bradyton',13,'Tempore ullam sunt sapiente ullam at a.',10.6472070,7.0561750,14,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(40,'Krajcikhaven',22,NULL,9.4592200,6.6090030,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(41,'East Patrick',19,NULL,9.0705310,7.5602300,20,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(42,'Gianniborough',19,NULL,9.2687040,7.0068980,8,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(43,'Kobybury',21,'Et voluptatem omnis vel perferendis.',9.3969070,7.5366060,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(44,'Lake Garret',14,'Id vel perspiciatis quo nemo.',10.8607780,6.4211270,15,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(45,'Shanialand',25,NULL,9.6870170,7.9631700,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(46,'Lake Mireya',20,NULL,9.0459090,6.9847350,20,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(47,'South Annie',18,'Quas rerum et voluptatem inventore nesciunt quisquam qui quidem.',9.6188570,6.2071490,2,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(48,'North Sophia',11,NULL,9.2809610,7.9688590,7,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(49,'Koelpinport',12,NULL,9.0753930,6.2264280,15,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(50,'North Opheliaside',14,'Perspiciatis alias culpa quaerat commodi.',9.1436530,7.5617290,7,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(51,'Port Kelvinburgh',25,'Beatae nesciunt ea ipsum odio et.',9.6120410,7.9238920,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(52,'Lake Carmel',15,NULL,10.8070320,6.5998330,20,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(53,'North Marcelohaven',19,NULL,9.8541390,6.0344420,6,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(54,'Larueview',20,NULL,9.6087770,6.1755780,8,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(55,'East Jessica',6,NULL,9.9769350,7.5888840,17,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(56,'Port Delbert',7,NULL,9.1044130,6.2791730,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(57,'East Princessville',3,'Quia eum est aut dolor.',10.5171160,7.1027770,13,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(58,'Millermouth',2,'Rem neque at nostrum doloremque velit doloremque eius.',10.5546590,6.3154580,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(59,'Port Jaquelineview',18,'Commodi voluptatem eum esse eius quo quo.',10.8962210,6.8419080,2,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(60,'Grantburgh',8,'Odio sed pariatur ducimus laudantium accusantium sit sint.',10.7571230,7.4173390,15,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(61,'Shanahanfurt',2,'Dolorem deleniti doloremque rerum labore ut quo totam.',10.4238570,7.4913410,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(62,'North Alanton',9,NULL,9.5867080,6.5902460,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(63,'Muellermouth',22,NULL,10.3679310,7.4128460,14,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(64,'Krisburgh',12,NULL,10.3044210,6.4196690,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(65,'Beerton',11,'Illum rerum tenetur est quo ratione ab consequuntur.',9.5976620,6.1635820,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(66,'North Dashawnland',7,'Perspiciatis autem dolorem eum quia quae voluptatem nesciunt culpa.',9.4840050,7.2242890,16,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(67,'South Jeremie',13,NULL,10.7217210,6.7153060,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(68,'New Lon',1,'Ratione vitae accusantium aliquam et eos molestiae nobis.',9.7590680,7.3090140,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(69,'Margaretmouth',14,NULL,10.1445790,6.2268340,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(70,'Runolfssonville',24,'Deleniti corporis aut fugit mollitia fuga laboriosam consectetur.',9.6304630,7.8105800,4,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(71,'South Alphonso',25,NULL,10.8422890,7.7918450,17,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(72,'North Asiafort',20,'Aut officiis aperiam velit minima minima eos voluptas illum.',9.1781660,7.4365140,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(73,'East Laverna',16,'Accusamus quis rerum neque eum.',9.9996530,6.6673700,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(74,'Lake Edd',15,'Aut architecto officiis et totam id ut.',9.0509110,6.5917110,1,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(75,'Matildeport',22,'Omnis labore reprehenderit quidem.',10.1420450,7.2538310,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(76,'West Nealstad',23,NULL,9.6658530,6.9360730,4,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(77,'New Simburgh',3,'Omnis laboriosam aperiam rerum.',10.5780260,6.3841210,1,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(78,'New Venaburgh',12,NULL,10.9000490,6.7285530,8,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(79,'East Dahlia',17,'Sed rem nobis temporibus ut pariatur.',9.8526680,6.7231140,19,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(80,'Port Michaleton',11,'Temporibus rerum omnis ab nostrum inventore voluptas.',9.1377210,7.5586070,16,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(81,'Romaguerabury',11,'Ut quia nemo nostrum omnis.',10.1509170,6.8691270,16,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(82,'Hellerborough',14,'Natus explicabo maiores soluta ut.',9.8858800,6.5205800,10,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(83,'East Emmanuelle',15,NULL,9.9508210,7.7070640,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(84,'Spencermouth',9,'Repellat id ducimus expedita et quae dolor officia.',10.3030460,7.9986410,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(85,'Dejaton',2,NULL,9.8786990,7.2384210,15,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(86,'Quitzonbury',20,NULL,9.9375470,6.3374740,16,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(87,'Hesselview',12,NULL,10.6648150,7.8091120,6,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(88,'Lake Maximilliastad',3,'Iste non aspernatur sapiente totam dignissimos aut aliquam nihil.',10.8326090,7.9274040,4,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(89,'Port Genesis',22,'Ullam deserunt hic minus unde.',10.0428270,7.0443220,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(90,'Genesistown',10,NULL,10.2726570,7.7094100,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(91,'Budland',19,'Placeat et quisquam saepe ut quis deleniti minima cum.',9.7200140,6.0284740,13,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(92,'Lynchport',3,'Voluptatem odit et et aliquid dolores quia ad nostrum.',10.1273160,6.1115960,2,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(93,'North Lemuel',22,NULL,9.3368690,7.2309330,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(94,'West Emiltown',22,'Nulla accusamus et repudiandae quia eaque asperiores natus dolor.',9.7089470,7.9471190,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(95,'East Frederique',14,'Perferendis sunt ut eum qui.',10.5627620,7.0859820,15,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(96,'Ariannaton',21,'Corrupti voluptatem eveniet eos non inventore.',9.6763350,6.8947030,20,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(97,'East Anastasiaview',18,NULL,10.3562540,7.6228920,15,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(98,'Lake Andrewtown',17,NULL,9.9459510,7.3246690,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(99,'Guyville',11,NULL,9.3186350,7.3908360,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(100,'South Rosamond',5,NULL,9.7773740,6.0577320,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(101,'Lebsackstad',16,NULL,10.7933720,6.7500250,7,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(102,'Carolynestad',9,NULL,9.9306950,7.7187490,17,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(103,'Hayesberg',16,NULL,9.7339320,6.6896810,6,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(104,'North Jermaineton',18,NULL,10.8468450,7.8679070,6,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(105,'Emmerichport',23,'Aut dicta qui voluptatibus ratione qui illo fuga.',9.6981780,7.7040580,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(106,'Goodwinstad',22,'Inventore occaecati veniam corrupti dignissimos.',10.7800800,6.5594220,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(107,'Hazelbury',16,'Esse tenetur quam quia.',10.4357770,6.0197750,13,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(108,'Moenborough',5,'Odio voluptatem eius eos.',9.1431820,6.7789440,14,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(109,'Port Taureanmouth',13,'Natus sit porro non iusto magnam repudiandae cumque.',10.1723060,7.9798810,16,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(110,'D\'Amorechester',20,NULL,10.5201840,6.0186600,18,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(111,'West Guido',3,'Non eius alias veniam quos modi.',10.1195570,7.4967350,14,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(112,'New Vidalborough',24,NULL,9.9386930,6.8265300,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(113,'North Angelineside',6,NULL,10.6809740,6.5303300,8,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(114,'New Taya',7,'Minus autem similique assumenda temporibus quis et non.',10.8857280,7.5873260,14,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(115,'East Yessenia',12,NULL,9.6836430,7.6782280,8,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(116,'Baumbachside',22,'A itaque placeat et consectetur dignissimos aliquid fuga.',10.1716020,6.4916000,19,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(117,'Geraldtown',8,NULL,10.1876470,7.1980470,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(118,'Quitzonside',24,'Earum excepturi at ipsam nesciunt quaerat sit incidunt quo.',9.3803540,6.9589210,1,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(119,'Granttown',15,'Architecto nemo voluptatem nihil rerum sint accusamus.',9.2557360,7.6107360,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(120,'New Kelsiburgh',19,NULL,10.2952500,6.4891640,13,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(121,'Annemouth',7,'Non rerum voluptates dolore minus officiis sequi.',10.4517590,7.0152810,4,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(122,'North Maidafurt',25,NULL,10.0294090,6.1591290,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(123,'Port Wilsonside',7,'Quam quis alias in aut ratione amet placeat provident.',9.7477140,6.8725240,18,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(124,'Ceciliabury',9,NULL,10.0780940,7.9987850,7,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(125,'Wittingside',10,NULL,10.9415580,6.2163310,20,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(126,'Hermannchester',6,NULL,10.1976440,7.3347650,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(127,'Fadelport',23,'Fuga distinctio aliquid incidunt eos eos at.',9.7538170,6.9588710,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(128,'North Randymouth',22,'Esse animi velit sapiente.',10.8095760,7.5756000,8,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(129,'South Josemouth',10,'Nihil ipsum voluptatem quo dolore quia.',9.2450960,7.7262640,6,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(130,'Salvatoreshire',5,NULL,9.5620930,6.4623400,10,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(131,'Yvonnehaven',10,NULL,10.1391290,6.6406130,20,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(132,'South Rebeca',16,'Qui inventore doloremque consequuntur veniam asperiores.',9.0435950,6.9953250,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(133,'Dickenschester',19,'Qui qui possimus omnis omnis.',10.6237340,7.0548920,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(134,'Port Enid',25,'Corrupti quis ut quibusdam est dicta illum perferendis.',10.0021890,6.6715260,7,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(135,'South Gabeburgh',17,'Eum incidunt ab dignissimos iure beatae harum esse.',10.6674220,7.9162500,20,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(136,'West Rozella',20,'Expedita quaerat minima beatae qui molestiae voluptates.',9.2669430,6.9413710,10,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(137,'Betteland',17,'Voluptatibus enim in tempora excepturi et architecto dignissimos.',9.0539780,6.6176250,18,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(138,'Alizamouth',10,'Id odit non voluptate quasi cum harum nostrum.',9.4209210,6.8688620,18,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(139,'Powlowskifurt',25,'Debitis eius et a id rem accusamus.',9.3136410,6.3798350,6,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(140,'Lake Adolfomouth',1,NULL,10.4280290,7.1254320,7,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(141,'Hilmaside',8,'Aspernatur ut neque error rerum sequi voluptatem.',9.6472900,7.5279430,10,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(142,'Lake Simone',2,'Quae porro nihil expedita deserunt amet dolorem harum.',9.4467960,6.4191840,7,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(143,'Greenfelderfort',4,NULL,9.7069630,7.7671400,18,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(144,'West Anabelleborough',13,NULL,10.2330040,7.6900010,18,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(145,'West Jewelstad',6,NULL,9.0569340,6.8331280,4,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(146,'West Elzaville',5,'Suscipit odit quas quae labore.',9.2712810,7.4716770,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(147,'Corkeryton',16,NULL,9.1467640,7.9201410,6,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(148,'Penelopebury',9,'Ab libero non voluptatem inventore.',10.2446100,6.7747300,14,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(149,'Ociechester',5,'Vero repellat provident voluptas sed.',10.4303940,7.9284850,4,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(150,'Wilberchester',8,'Ipsum quo excepturi consequatur ea enim.',10.3682180,7.0882230,14,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(151,'Creolafurt',10,NULL,9.1640620,7.0802580,20,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(152,'East Valerie',22,NULL,10.0304170,7.8602170,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(153,'Creolaland',8,'Assumenda illum illo accusantium inventore quos.',9.6984910,6.8475180,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(154,'Raufort',24,NULL,9.9674990,7.8206390,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(155,'Adelaland',25,NULL,9.3592030,7.8790850,4,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(156,'Lake Brianne',11,NULL,9.9870670,6.4177190,2,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(157,'Halvorsonside',4,NULL,10.4244040,6.2719920,10,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(158,'West Lempiside',17,NULL,9.8508460,7.7140170,15,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(159,'Delfinamouth',20,'Quo et illo commodi quidem eius voluptatem.',10.9352630,6.3038270,13,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(160,'Rhiannonfort',15,'Consequatur labore ea asperiores debitis fugiat dignissimos cupiditate voluptatem.',10.1426480,6.0964210,7,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(161,'Stanmouth',16,'Est voluptatem similique doloribus nam tempora unde provident.',9.5506770,6.0807680,20,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(162,'Roobborough',14,NULL,10.4282500,6.1933940,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(163,'North Melliemouth',5,'Saepe qui ipsam deleniti reiciendis qui ut.',10.7109400,7.2630840,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(164,'Lake Keagan',24,'Iure blanditiis reiciendis adipisci molestiae vitae.',9.0296020,7.4824530,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(165,'Walkerburgh',1,'Est et ratione et officiis nulla nemo.',10.8070960,6.3844720,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(166,'Considineberg',15,'Possimus minima aut aut non cumque nesciunt dolore.',9.9736070,7.6490800,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(167,'Sanfordstad',23,'Soluta unde voluptate accusamus nostrum quis.',9.5449200,7.6602050,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(168,'Port Harrisonton',11,NULL,9.5266570,7.4192430,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(169,'East Kay',3,NULL,10.9280800,7.8942640,8,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(170,'New Harmon',17,NULL,9.4232980,7.5323830,1,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(171,'Lednerfort',3,NULL,10.1014600,7.9488270,13,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(172,'New Hectorfurt',9,NULL,10.8327390,7.5863960,4,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(173,'New Otis',1,'Mollitia et quos perspiciatis aperiam sunt occaecati corrupti voluptate.',10.4916420,7.7098570,1,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(174,'Port Mylene',6,NULL,9.6136630,6.4146590,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(175,'East Miguelchester',3,NULL,9.4871560,7.1622210,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(176,'Murazikport',6,'Autem magnam totam totam possimus dolorem.',9.4440220,6.3958860,17,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(177,'Port Kylaview',18,NULL,9.4212370,7.6520950,16,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(178,'Rodgerton',7,'Voluptatum sed sit eaque omnis.',9.8440310,7.2329110,7,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(179,'South Melyssa',2,'Vero nihil aut est.',10.5731430,7.1407030,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(180,'East Aldaberg',7,'Nulla suscipit corrupti blanditiis qui.',10.5079820,7.3186070,16,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(181,'Hauckburgh',21,NULL,10.6400260,7.9069370,6,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(182,'Arlietown',20,NULL,9.9444340,6.9658540,15,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(183,'Jeannehaven',20,'Quia sit voluptatibus iure et sit.',10.5643210,6.7907580,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(184,'Lake Mac',15,NULL,10.2329490,7.0232480,18,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(185,'South Cade',22,'Velit rerum maiores officiis rerum.',9.0110250,6.0311760,2,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(186,'Clintview',6,NULL,10.1980550,7.4851070,15,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(187,'New Cecileborough',13,NULL,9.9492630,7.8901730,4,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(188,'Grantchester',15,NULL,10.0038580,7.2164640,19,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(189,'Wisokyhaven',8,'Pariatur ut voluptatem voluptatem.',10.0132930,6.1474650,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(190,'Carmellahaven',3,'Quia nostrum dolorum nemo ullam perspiciatis cum.',9.1280460,7.4094730,20,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(191,'New Adellbury',4,NULL,9.2537750,6.1636380,17,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(192,'New Antonettefurt',13,NULL,9.3900120,6.0864580,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(193,'Windlerberg',8,NULL,10.8230540,7.9152730,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(194,'Lake Christinetown',11,'Quo doloremque voluptatem aspernatur est consequatur natus quos dolore.',9.9782910,7.3787470,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(195,'North Berneicechester',9,'Ea perspiciatis sit qui a.',9.8380820,6.3455700,10,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(196,'North Generaltown',22,'Eaque et ipsum dignissimos odio doloribus impedit ut illo.',9.4492880,6.0352510,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(197,'Wymanborough',17,NULL,10.6192690,7.5983060,17,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(198,'West Beatrice',16,'Magni temporibus quo aliquam.',9.1781280,6.0742900,13,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(199,'North Nikita',23,NULL,9.4861290,7.2797640,16,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(200,'Junechester',19,NULL,10.7547630,6.1188560,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(201,'East Nathanchester',16,NULL,9.4725780,6.2961280,4,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(202,'West Francesport',10,'Est sint vero doloremque error quo eum maxime consequatur.',10.8428290,6.9583220,13,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(203,'Stantonstad',18,NULL,10.5478220,6.6713460,5,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(204,'Leraland',22,NULL,10.3080240,6.6676040,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(205,'East Fernland',14,NULL,10.1702220,6.3783100,2,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(206,'Daughertyshire',7,NULL,9.0441510,6.5429530,13,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(207,'Lefflerstad',12,'Quos expedita minus rerum.',10.4867480,6.4951450,20,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(208,'West Krystal',24,NULL,9.7200900,6.7347240,20,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(209,'Amayahaven',17,NULL,9.1551270,6.2462000,13,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(210,'Kassulkeville',12,'Enim sunt sed assumenda voluptates in.',9.5867570,7.9328350,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(211,'Lutherport',10,NULL,9.4126300,7.0552740,1,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(212,'Sarinatown',12,'Rem nostrum vero expedita ipsum.',10.1654820,7.1567640,4,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(213,'South Kamille',16,NULL,10.1863060,6.5855190,17,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(214,'East Cheyanne',22,'Distinctio ullam ut repellendus dicta id.',9.0936590,7.5624380,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(215,'Legrosland',23,'Voluptatem numquam officia provident commodi veniam sit.',10.1203800,7.0425530,19,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(216,'Aricchester',19,'Beatae inventore commodi perferendis et et culpa.',10.0167950,6.3185690,14,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(217,'Wolffland',25,'Aspernatur aliquid fuga eum tenetur est consequatur aut.',9.9617570,7.8494050,16,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(218,'Lake Dinobury',11,'Quos animi pariatur commodi quia voluptas ad.',10.8290420,7.0238200,16,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(219,'Watersfurt',21,NULL,9.7235390,6.3382730,16,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(220,'West Leoniefurt',17,NULL,10.5077720,7.6051150,15,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(221,'Lehnerton',5,'In aut et omnis labore corporis.',10.1498230,7.5997020,2,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(222,'Gerlachchester',20,NULL,9.1588110,7.1785090,14,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(223,'Annaberg',11,'Et itaque suscipit libero aperiam eligendi delectus officiis.',10.0737480,6.4008580,18,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(224,'Yostfurt',23,NULL,10.3075160,6.1821470,2,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(225,'East Keanu',17,NULL,9.7722000,7.9667060,10,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(226,'Christyshire',5,NULL,10.9400580,6.3055160,12,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(227,'Port Janicetown',13,NULL,9.1910340,7.5337350,3,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(228,'South Hesterburgh',3,'Eaque molestias quis nihil occaecati doloribus.',10.1585310,7.9997960,18,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(229,'East Evieside',25,NULL,10.7403310,6.3998620,19,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(230,'West Cordieshire',21,NULL,9.4284090,6.8133340,10,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(231,'Lake Malcolmhaven',19,'Blanditiis amet sequi aspernatur.',9.1413930,6.9409740,14,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(232,'Tysonmouth',3,'Et iste ea provident consequatur.',9.0905250,7.1612710,11,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(233,'West Donavon',5,'Hic maxime sint sit.',10.0387030,7.9760540,9,1,'2025-07-13 05:57:30','2025-07-13 05:57:30'),(234,'Bufordburgh',24,NULL,9.2352240,7.7085440,12,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(235,'South Arnold',10,NULL,10.7168480,7.4205070,7,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(236,'North Ebbaland',2,NULL,10.2568110,7.3827310,8,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(237,'South Shirleyfort',1,'In molestiae facilis non quidem enim et suscipit ut.',9.4242690,6.9635750,11,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(238,'Tierratown',17,NULL,10.2811230,6.3458410,9,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(239,'Bonnieview',15,NULL,9.9488800,7.9517510,5,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(240,'Domenickstad',18,'Quae voluptatem enim quia.',10.3822400,6.5999790,2,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(241,'Lake Domingo',22,'Esse necessitatibus sed accusamus labore delectus.',10.3635530,6.7982060,19,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(242,'Port Freemanport',18,'Officia nemo ratione quos quod ipsum eligendi tenetur hic.',9.3089810,7.1856010,5,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(243,'Goodwinberg',19,NULL,10.9519470,7.5168280,3,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(244,'South Savannamouth',11,'Ut omnis blanditiis recusandae et quod.',9.7097740,7.3789320,18,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(245,'Lake Andy',1,'Dolor mollitia iusto quia voluptas occaecati.',10.1658070,6.8682840,15,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(246,'Port Osvaldoville',13,'A ut consequuntur eos quis officia praesentium quia.',9.0564500,6.7276140,17,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(247,'Lake Ceceliamouth',21,NULL,10.1838630,7.8094250,4,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(248,'Kilbackshire',2,'Inventore exercitationem dolores a enim incidunt perspiciatis.',10.4434690,7.1163970,19,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(249,'Klockoville',25,NULL,9.3683560,6.7556980,16,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(250,'New Arnoldo',10,'Esse aliquid inventore aut ea.',10.1662670,7.7335650,12,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(251,'Lehnerstad',7,'Rerum quia et modi.',10.8064280,6.4027850,7,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(252,'Emorystad',23,'Voluptas sunt accusamus nulla.',9.4444600,7.6878510,9,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(253,'Port Luehaven',10,NULL,10.1001510,7.4438770,11,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(254,'Wendellchester',1,'Est fugit aperiam est asperiores.',10.6648080,7.9774250,11,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(255,'Wizaberg',11,'Et cupiditate officia nulla esse.',9.3453120,6.4132190,9,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(256,'Avisfurt',3,'Quo doloremque consequatur reprehenderit eum voluptatem quo dolor.',10.4676470,6.3211110,4,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(257,'Lake Vernermouth',17,NULL,10.0989140,7.1310010,4,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(258,'New Petebury',25,'Consectetur occaecati quis tempora soluta quasi voluptatum hic.',10.5390530,6.6524290,3,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(259,'Port Mallie',4,'Est quam est vitae.',9.7666000,7.4281890,10,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(260,'Daynaside',1,NULL,10.6681580,7.7351220,13,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(261,'Crooksfurt',18,'Quis libero unde suscipit nostrum iusto quidem dolorum.',10.8025180,7.6311890,17,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(262,'East Mozelle',3,NULL,9.2376110,7.2743710,7,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(263,'Lilyanchester',16,'Vel autem vitae ea recusandae corporis.',9.1347110,7.0658620,12,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(264,'Lake Lonzofurt',14,NULL,10.2276840,6.5566890,1,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(265,'Lake Domenick',21,NULL,10.4936840,6.8499360,16,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(266,'East Ervinmouth',23,'Labore mollitia labore eligendi illum necessitatibus nostrum voluptates.',10.4264310,7.9624060,19,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(267,'Garfieldside',24,'Veniam sapiente similique quis omnis vel culpa perspiciatis.',10.4687790,7.8895940,10,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(268,'North Breanaborough',2,'Eum atque voluptatum unde atque.',9.1079700,7.1496610,1,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(269,'Lake Kaitlin',20,'Ipsam ut repudiandae saepe ad ex.',9.0127030,6.4903150,16,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(270,'Port Judemouth',16,'Illo odit quaerat mollitia ipsa.',9.6970130,7.6971370,17,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(271,'West Emmanuelle',15,NULL,9.7783700,6.4518670,6,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(272,'Lake Sethshire',24,NULL,9.0267430,6.8618000,6,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(273,'South Kalliehaven',18,'Quam maiores quis quod ratione voluptatem.',9.2520820,6.0771810,19,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(274,'Lake Vern',1,NULL,9.6496120,6.7434470,6,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(275,'Claudeberg',15,NULL,10.1084990,7.9746270,13,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(276,'Lake Darrontown',5,NULL,10.8861210,7.9134830,9,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(277,'East Kellenburgh',2,NULL,10.1856740,7.3330380,9,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(278,'East Devenchester',25,'Tempora dolor iste consequuntur neque qui.',9.8968860,6.0141670,13,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(279,'Homenickburgh',1,NULL,10.5500450,7.8206320,6,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(280,'Port Kendra',21,'Voluptas blanditiis veritatis excepturi cum.',10.3364650,7.0434010,5,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(281,'Hintzmouth',9,'Sunt a vero animi aperiam quia quibusdam delectus.',9.1805500,7.0746570,15,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(282,'North Daisha',5,'Ducimus aliquam expedita impedit qui illo.',10.1964140,6.6401940,16,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(283,'West Isacside',8,NULL,9.8040080,7.0987310,8,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(284,'Larkinhaven',1,'Aut explicabo ab dolore blanditiis.',10.6933950,6.4588740,7,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(285,'East Reneburgh',21,'Qui est atque dolor numquam dolor.',10.5006290,7.6287460,16,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(286,'West Deronview',17,NULL,9.3138940,6.2433960,7,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(287,'Leuschkeland',9,NULL,10.2685280,7.4889050,16,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(288,'Smithview',20,'Saepe ducimus architecto eligendi mollitia expedita.',10.2732730,6.8210380,12,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(289,'Elianchester',6,NULL,9.8510700,6.2477280,13,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(290,'South Lillianberg',15,'Odio placeat quasi quia harum tempora est.',9.3947080,7.5074730,12,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(291,'Baileyview',25,NULL,9.3053120,6.4404310,17,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(292,'Hettiefurt',6,NULL,9.1038970,6.7380360,14,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(293,'North Patsybury',11,'Qui eos similique officiis fugiat.',9.4212930,6.5376190,16,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(294,'North Eduardoport',9,'Esse ipsum voluptatum adipisci quis.',9.0901360,7.0256900,10,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(295,'Gersonview',18,NULL,9.2153850,6.8968140,18,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(296,'Port Einoview',3,NULL,10.7634500,7.0299820,8,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(297,'Loweview',3,NULL,9.8179630,7.6001110,2,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(298,'West Oswaldburgh',2,NULL,9.2435050,6.0888670,8,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(299,'North Domenico',15,'Reiciendis vitae voluptate natus repellat aut quidem.',10.2364030,6.0703380,9,1,'2025-07-13 05:57:31','2025-07-13 05:57:31'),(300,'Hartmannburgh',9,NULL,10.1337500,6.8020450,18,1,'2025-07-13 05:57:31','2025-07-13 05:57:31');
/*!40000 ALTER TABLE `planting_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin','Full access. EverythingÔÇöcreate, edit, delete any record',NULL,NULL),(2,'Inspector','Create/edit plantings and inspections, but not delete others',NULL,NULL),(3,'Verifier','Limited access, Mark plantings as verified',NULL,NULL),(4,'Viewer','View only',NULL,NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('sb59urcCpMA4D5EzFoqdpgMbKW6VIpO3WoW6auv9',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWElMZGtiS2NrMkxwSUQzTDVnUm42dWliQVpaNTBUSE1xOFVrSml3TyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1752393527);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tree_planting_status`
--

DROP TABLE IF EXISTS `tree_planting_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tree_planting_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tree_planting_status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tree_planting_status`
--

LOCK TABLES `tree_planting_status` WRITE;
/*!40000 ALTER TABLE `tree_planting_status` DISABLE KEYS */;
INSERT INTO `tree_planting_status` VALUES (1,'Planted',NULL,NULL),(2,'Verified',NULL,NULL);
/*!40000 ALTER TABLE `tree_planting_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tree_plantings`
--

DROP TABLE IF EXISTS `tree_plantings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tree_plantings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `planting_date` date NOT NULL,
  `number_of_trees` int(11) NOT NULL,
  `tree_type_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `planting_location_id` bigint(20) unsigned NOT NULL,
  `status` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tree_plantings_tree_type_id_foreign` (`tree_type_id`),
  KEY `tree_plantings_user_id_foreign` (`user_id`),
  KEY `tree_plantings_planting_location_id_foreign` (`planting_location_id`),
  KEY `tree_plantings_status_foreign` (`status`),
  CONSTRAINT `tree_plantings_planting_location_id_foreign` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tree_plantings_status_foreign` FOREIGN KEY (`status`) REFERENCES `tree_planting_status` (`id`),
  CONSTRAINT `tree_plantings_tree_type_id_foreign` FOREIGN KEY (`tree_type_id`) REFERENCES `tree_types` (`id`),
  CONSTRAINT `tree_plantings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tree_plantings`
--

LOCK TABLES `tree_plantings` WRITE;
/*!40000 ALTER TABLE `tree_plantings` DISABLE KEYS */;
/*!40000 ALTER TABLE `tree_plantings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tree_types`
--

DROP TABLE IF EXISTS `tree_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tree_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `latin_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tree_types`
--

LOCK TABLES `tree_types` WRITE;
/*!40000 ALTER TABLE `tree_types` DISABLE KEYS */;
INSERT INTO `tree_types` VALUES (1,'Flame Tree',NULL,'Delonix regia',NULL,NULL),(2,'Mahogany',NULL,'Swietenia macrophylla',NULL,NULL),(3,'Black Plum',NULL,'Syzygium cumini',NULL,NULL),(4,'Ficus',NULL,'Ficus spp.',NULL,NULL),(5,'Satelite',NULL,'Terminalia mantaly',NULL,NULL),(6,'Eucalyptus',NULL,'Eucalyptus camaldulensis',NULL,NULL),(7,'Almond',NULL,'Prunus amygdalus',NULL,NULL),(8,'Albizia',NULL,'Albizia lebbeck',NULL,NULL),(9,'Teak',NULL,'Tectona grandis',NULL,NULL),(10,'Bamboo',NULL,'Bambusa vulgaris',NULL,NULL),(11,'Baobab',NULL,'Adansonia digitata',NULL,NULL),(12,'Cashew',NULL,'Anacardium occidentale',NULL,NULL),(13,'Guava',NULL,'Psidium guajava',NULL,NULL),(14,'Moringa',NULL,'Moringa oleifera',NULL,NULL),(15,'Neem',NULL,'Azadirachta indica',NULL,NULL),(16,'Leucaena',NULL,'Leucaena leucocephala',NULL,NULL),(17,'Orange',NULL,'Citrus sinensis',NULL,NULL),(18,'Mango',NULL,'Mangifera indica',NULL,NULL),(19,'Shea Butter',NULL,'Vitellaria paradoxa',NULL,NULL);
/*!40000 ALTER TABLE `tree_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `role_id` int(10) unsigned NOT NULL DEFAULT 1,
  `gender` varchar(255) DEFAULT NULL,
  `picture_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_picture_id_foreign` (`picture_id`),
  CONSTRAINT `users_picture_id_foreign` FOREIGN KEY (`picture_id`) REFERENCES `pictures` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Dr. Art Hagenes','billie.hodkiewicz@example.net','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','DpU3ZLi4rl','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(2,'Lazaro Gulgowski','domenica.parisian@example.com','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','sCp669Z2oE','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(3,'Jalyn Ankunding','zakary82@example.net','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','gu5nyo6U3a','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(4,'Prof. Clement Schimmel','nyasia.hickle@example.org','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','R6FyHMaJGV','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(5,'Vern Veum','considine.hugh@example.com','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','iTGahTfSku','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(6,'Jerod Kreiger','haag.shea@example.com','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','fJGU2dmptW','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(7,'Nya Bahringer','newton03@example.net','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','Y6rvGGNqMa','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(8,'Zander Corkery II','gerhold.reginald@example.com','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','Xyy8uuGsjJ','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(9,'Robbie Marvin DVM','xvon@example.net','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','snvObvQLIs','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(10,'Gabe Hammes','josefina32@example.net','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','UOItWHdukb','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(11,'Bernardo Emmerich MD','ltorp@example.com','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','9MnSguPGBE','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(12,'Dr. Felipa Conroy','yesenia.lueilwitz@example.com','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','cQAZn6jlZh','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(13,'Erich Ledner','bhomenick@example.com','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','XH1nvggenf','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(14,'Mrs. Makayla Wintheiser','ykertzmann@example.net','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','Bj3Jfj9cqZ','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(15,'Dandre Grant','tara.mosciski@example.com','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','owjSXe2aKv','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(16,'Mr. Merle Shanahan II','franz23@example.net','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','3WxQNDBw4L','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(17,'Miss Vivien Gibson I','hgraham@example.com','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','rwl7z9FDVz','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(18,'Heidi Flatley','cletus.buckridge@example.net','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','W0UJWXrNR7','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(19,'Mr. Bertha Deckow Jr.','queenie74@example.org','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','2TXNSORrXY','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL),(20,'Prof. Giuseppe Dicki Sr.','fahey.lavonne@example.net','2025-07-13 05:57:29','$2y$12$fuqfrUWHba4Ep90sPxsgcuoUmLPm7XXAERRjIbreIIct7zuuwLf.6','kIq9jiiQ3A','2025-07-13 05:57:29','2025-07-13 05:57:29',NULL,1,NULL,NULL);
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

-- Dump completed on 2025-07-13 10:26:00
