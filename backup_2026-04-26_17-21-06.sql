-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: inventory-chrisbale
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
-- Table structure for table `adjust_stok`
--

DROP TABLE IF EXISTS `adjust_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjust_stok` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_adjust` varchar(255) NOT NULL,
  `tanggal` datetime NOT NULL,
  `keterangan` text DEFAULT NULL,
  `status` varchar(225) NOT NULL DEFAULT 'approve',
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `adjust_stok_kode_adjust_unique` (`kode_adjust`),
  KEY `adjust_stok_created_by_foreign` (`created_by`),
  CONSTRAINT `adjust_stok_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjust_stok`
--

LOCK TABLES `adjust_stok` WRITE;
/*!40000 ALTER TABLE `adjust_stok` DISABLE KEYS */;
INSERT INTO `adjust_stok` VALUES (14,'AS-20260419-276','2026-04-19 00:00:00',NULL,'approve',6,'2026-04-19 08:08:22','2026-04-19 08:08:22'),(15,'AS-20260419-626','2026-04-19 00:00:00',NULL,'approve',3,'2026-04-19 10:05:13','2026-04-19 10:05:13'),(16,'AS-20260419-760','2026-04-19 00:00:00',NULL,'approve',3,'2026-04-19 10:05:41','2026-04-19 10:05:41'),(17,'AS-20260420-236','2026-04-20 00:00:00',NULL,'approve',3,'2026-04-20 08:25:22','2026-04-20 08:25:22'),(18,'AS-20260425-649','2026-04-25 00:00:00',NULL,'approve',6,'2026-04-24 22:31:01','2026-04-24 22:31:01'),(21,'AS-20260425-347','2026-04-25 00:00:00',NULL,'approve',6,'2026-04-24 22:31:10','2026-04-24 22:31:10'),(22,'AS-20260425-552','2026-04-25 00:00:00',NULL,'approve',6,'2026-04-24 22:57:19','2026-04-24 22:57:19'),(23,'AS-20260425-123','2026-04-25 00:00:00',NULL,'pending',6,'2026-04-24 22:57:49','2026-04-24 22:57:49'),(24,'AS-20260425-622','2026-04-25 00:00:00',NULL,'pending',6,'2026-04-24 23:07:19','2026-04-24 23:07:19'),(25,'AS-20260425-951','2026-04-25 00:00:00',NULL,'approve',6,'2026-04-25 00:34:33','2026-04-25 00:34:33'),(27,'AS-20260425-922','2026-04-25 00:00:00',NULL,'approve',6,'2026-04-25 02:00:42','2026-04-25 02:00:42'),(30,'AS-20260425-854','2026-04-25 00:00:00',NULL,'approve',6,'2026-04-25 02:14:41','2026-04-25 04:31:01');
/*!40000 ALTER TABLE `adjust_stok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjust_stok_detail`
--

DROP TABLE IF EXISTS `adjust_stok_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adjust_stok_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `adjust_stok_id` bigint(20) unsigned NOT NULL,
  `barang_id` bigint(20) unsigned NOT NULL,
  `qty_sistem` int(11) NOT NULL,
  `qty_fisik` int(11) NOT NULL,
  `selisih` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `adjust_stok_detail_adjust_stok_id_foreign` (`adjust_stok_id`),
  KEY `adjust_stok_detail_barang_id_foreign` (`barang_id`),
  CONSTRAINT `adjust_stok_detail_adjust_stok_id_foreign` FOREIGN KEY (`adjust_stok_id`) REFERENCES `adjust_stok` (`id`),
  CONSTRAINT `adjust_stok_detail_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjust_stok_detail`
--

LOCK TABLES `adjust_stok_detail` WRITE;
/*!40000 ALTER TABLE `adjust_stok_detail` DISABLE KEYS */;
INSERT INTO `adjust_stok_detail` VALUES (16,14,5,6,8,2,'2026-04-19 08:08:22','2026-04-19 08:08:22'),(17,15,5,8,6,-2,'2026-04-19 10:05:13','2026-04-19 10:05:13'),(18,16,5,6,4,-2,'2026-04-19 10:05:41','2026-04-19 10:05:41'),(19,17,2,0,6,6,'2026-04-20 08:25:22','2026-04-20 08:25:22'),(20,18,8,545,2,-543,'2026-04-24 22:31:01','2026-04-24 22:31:01'),(21,21,8,2,0,0,'2026-04-24 22:31:10','2026-04-24 22:31:10'),(22,22,8,2,4,2,'2026-04-24 22:57:19','2026-04-24 22:57:19'),(23,23,8,4,6,2,'2026-04-24 22:57:49','2026-04-24 22:57:49'),(24,24,8,6,2,-4,'2026-04-24 23:07:19','2026-04-24 23:07:19'),(25,25,8,6,3,-3,'2026-04-25 00:34:33','2026-04-25 00:34:33'),(26,27,8,3,6,3,'2026-04-25 02:00:43','2026-04-25 02:00:43'),(27,30,8,6,9,3,'2026-04-25 02:14:41','2026-04-25 04:31:01');
/*!40000 ALTER TABLE `adjust_stok_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barang`
--

DROP TABLE IF EXISTS `barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `barang` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(255) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `satuan_id` bigint(20) unsigned NOT NULL,
  `harga_1` decimal(12,2) DEFAULT NULL,
  `harga_2` decimal(12,2) DEFAULT NULL,
  `stok_minimum` int(11) NOT NULL DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barang_sku_unique` (`sku`),
  KEY `barang_satuan_id_foreign` (`satuan_id`),
  CONSTRAINT `barang_satuan_id_foreign` FOREIGN KEY (`satuan_id`) REFERENCES `satuan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barang`
--

LOCK TABLES `barang` WRITE;
/*!40000 ALTER TABLE `barang` DISABLE KEYS */;
INSERT INTO `barang` VALUES (2,'SKU-BRG43','SEPATU',1,2000.00,4000.00,1,'tesss 222','2026-04-17 02:47:45','2026-04-20 00:16:17',NULL),(5,'jultn41','SENDAL',1,9000.00,10000.00,1,'TES','2026-04-17 10:07:26','2026-04-26 00:46:35',NULL),(6,'SKU-99999','JAKET',1,35000.00,50000.00,1,'CX','2026-04-17 10:07:59','2026-04-19 05:09:09',NULL),(8,'hfghfghfg','mouse',1,44444.00,5555.00,1,NULL,'2026-04-22 09:39:18','2026-04-22 09:39:18',NULL),(109,'jovcrm40','Christian Bale JOVANKA Sandal Wanita Kekinian Sendal Jepit Cewek Flat Casual Terbaru',1,4000.00,8000.00,1,NULL,'2026-04-26 00:08:26','2026-04-26 00:45:39',NULL),(112,'SKU001','Contoh Barang 1',1,15000.00,25000.00,1,'Keterangan opsional','2026-04-26 08:47:14','2026-04-26 08:47:14',NULL),(114,'SKU002','Contoh Barang 2',1,20000.00,35000.00,1,NULL,'2026-04-26 09:05:25','2026-04-26 09:05:25',NULL);
/*!40000 ALTER TABLE `barang` ENABLE KEYS */;
UNLOCK TABLES;

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
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
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
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
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
-- Table structure for table `dropshipper`
--

DROP TABLE IF EXISTS `dropshipper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dropshipper` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dropshipper`
--

LOCK TABLES `dropshipper` WRITE;
/*!40000 ALTER TABLE `dropshipper` DISABLE KEYS */;
INSERT INTO `dropshipper` VALUES (1,'CHRISBALE','99999999','CIAPUS','tes','2026-04-16 09:28:40','2026-04-16 09:32:22',NULL),(3,'JJ','41242','Fdsfds',NULL,'2026-04-19 07:03:23','2026-04-19 07:03:23',NULL),(4,'TCBG','321312','fdsfds',NULL,'2026-04-19 07:03:36','2026-04-19 07:03:36',NULL),(5,'tes','999','indo',NULL,'2026-04-20 18:57:43','2026-04-20 18:57:43',NULL);
/*!40000 ALTER TABLE `dropshipper` ENABLE KEYS */;
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
-- Table structure for table `hak_akses`
--

DROP TABLE IF EXISTS `hak_akses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hak_akses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_permission` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hak_akses`
--

LOCK TABLES `hak_akses` WRITE;
/*!40000 ALTER TABLE `hak_akses` DISABLE KEYS */;
INSERT INTO `hak_akses` VALUES (1,'lihat_dashboard','2026-04-20 17:54:44','2026-04-20 17:54:44'),(2,'lihat_supplier',NULL,NULL),(3,'tambah_supplier',NULL,NULL),(4,'edit_supplier',NULL,NULL),(5,'hapus_supplier',NULL,NULL),(6,'lihat_satuan',NULL,NULL),(7,'tambah_satuan',NULL,NULL),(8,'edit_satuan',NULL,NULL),(9,'hapus_satuan',NULL,NULL),(10,'lihat_barang',NULL,NULL),(11,'tambah_barang',NULL,NULL),(12,'edit_barang',NULL,NULL),(13,'hapus_barang',NULL,NULL),(14,'lihat_dropshipper',NULL,NULL),(15,'tambah_dropshipper',NULL,NULL),(16,'edit_dropshipper',NULL,NULL),(17,'hapus_dropshipper',NULL,NULL),(18,'lihat_pembelian',NULL,NULL),(19,'buat_pembelian',NULL,NULL),(20,'hapus_pembelian',NULL,NULL),(21,'lihat_penjualan',NULL,NULL),(22,'buat_penjualan',NULL,NULL),(23,'hapus_penjualan',NULL,NULL),(24,'lihat_manajemen_stok',NULL,NULL),(25,'tambah_manajemen_stok',NULL,NULL),(26,'edit_manajemen_stok',NULL,NULL),(27,'lihat_laporan_pembelian',NULL,NULL),(28,'export_laporan_pembelian',NULL,NULL),(29,'lihat_laporan_penjualan',NULL,NULL),(30,'export_laporan_penjualan',NULL,NULL),(31,'lihat_laporan_stok',NULL,NULL),(32,'export_laporan_stok',NULL,NULL),(33,'lihat_laporan_barang',NULL,NULL),(34,'export_laporan_barang',NULL,NULL),(35,'lihat_pengguna',NULL,NULL),(36,'tambah_pengguna',NULL,NULL),(37,'edit_pengguna',NULL,NULL),(38,'hapus_pengguna',NULL,NULL),(39,'lihat_hak_akses',NULL,NULL),(40,'tambah_hak_akses',NULL,NULL),(41,'edit_hak_akses',NULL,NULL),(42,'hapus_hak_akses',NULL,NULL),(43,'backup_database',NULL,NULL),(44,'lihat_pengguna','2026-04-21 01:45:08','2026-04-21 01:45:08'),(45,'tambah_pengguna','2026-04-21 01:45:08','2026-04-21 01:45:08'),(46,'edit_pengguna','2026-04-21 01:45:08','2026-04-21 01:45:08'),(47,'hapus_pengguna','2026-04-21 01:45:08','2026-04-21 01:45:08'),(48,'lihat_hak_akses','2026-04-21 01:45:08','2026-04-21 01:45:08'),(49,'tambah_hak_akses','2026-04-21 01:45:08','2026-04-21 01:45:08'),(50,'edit_hak_akses','2026-04-21 01:45:08','2026-04-21 01:45:08'),(51,'hapus_hak_akses','2026-04-21 01:45:08','2026-04-21 01:45:08'),(52,'lihat_role_hak_akses','2026-04-21 01:45:08','2026-04-21 01:45:08'),(53,'edit_role_hak_akses','2026-04-21 01:45:08','2026-04-21 01:45:08'),(54,'lihat_backup_database','2026-04-21 01:45:08','2026-04-21 01:45:08'),(55,'buat_backup_database','2026-04-21 01:45:08','2026-04-21 01:45:08'),(56,'lihat_role',NULL,NULL),(57,'tambah_role',NULL,NULL),(58,'edit_role',NULL,NULL),(59,'hapus_role',NULL,NULL),(60,'lihat_hitung_stok','2026-04-24 10:14:19','2026-04-24 10:14:19'),(61,'tambah_hitung_stok','2026-04-24 10:14:33','2026-04-24 10:16:01'),(62,'edit_hitung_stok','2026-04-24 10:16:42','2026-04-24 10:16:42'),(63,'hapus_hitung_stok','2026-04-24 10:16:53','2026-04-24 10:16:53'),(64,'hapus_manajemen_stok','2026-04-24 21:31:02','2026-04-24 21:31:02');
/*!40000 ALTER TABLE `hak_akses` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_04_13_092038_create_personal_access_tokens_table',1),(5,'2026_04_14_111445_create_role_table',1),(6,'2026_04_14_111449_create_pengguna_table',1),(7,'2026_04_14_111450_create_hak_akses_table',1),(8,'2026_04_14_111451_create_satuan_table',1),(9,'2026_04_14_111452_create_supplier_table',1),(10,'2026_04_14_111454_create_dropshipper_table',1),(11,'2026_04_14_111455_create_barang_table',1),(12,'2026_04_14_111456_create_stok_barang_table',1),(13,'2026_04_14_111457_create_stok_movement_table',1),(14,'2026_04_14_111458_create_pembelian_table',1),(15,'2026_04_14_111500_create_pembelian_detail_table',1),(16,'2026_04_14_111501_create_penjualan_table',1),(17,'2026_04_14_111502_create_penjualan_detail_table',1),(18,'2026_04_14_111503_create_adjust_stok_table',1),(19,'2026_04_14_111504_create_adjust_stok_detail_table',1),(20,'2026_04_14_111800_create_role_hak_akses_table',1),(21,'2026_04_19_122618_update_adjust_stok_table',2),(22,'2026_04_19_123101_update_adjust_stok_table2',3);
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
-- Table structure for table `pembelian`
--

DROP TABLE IF EXISTS `pembelian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pembelian` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_pembelian` varchar(255) NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `tanggal` datetime NOT NULL,
  `total_harga` decimal(14,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pembelian_kode_pembelian_unique` (`kode_pembelian`),
  KEY `pembelian_supplier_id_foreign` (`supplier_id`),
  KEY `pembelian_created_by_foreign` (`created_by`),
  CONSTRAINT `pembelian_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `pembelian_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembelian`
--

LOCK TABLES `pembelian` WRITE;
/*!40000 ALTER TABLE `pembelian` DISABLE KEYS */;
INSERT INTO `pembelian` VALUES (8,'PB-20260419-294',1,'2026-04-23 00:00:00',10000.00,NULL,3,'2026-04-19 07:39:43','2026-04-19 07:39:43');
/*!40000 ALTER TABLE `pembelian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembelian_detail`
--

DROP TABLE IF EXISTS `pembelian_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pembelian_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pembelian_id` bigint(20) unsigned NOT NULL,
  `barang_id` bigint(20) unsigned NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pembelian_detail_pembelian_id_foreign` (`pembelian_id`),
  KEY `pembelian_detail_barang_id_foreign` (`barang_id`),
  CONSTRAINT `pembelian_detail_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pembelian_detail_pembelian_id_foreign` FOREIGN KEY (`pembelian_id`) REFERENCES `pembelian` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembelian_detail`
--

LOCK TABLES `pembelian_detail` WRITE;
/*!40000 ALTER TABLE `pembelian_detail` DISABLE KEYS */;
INSERT INTO `pembelian_detail` VALUES (41,8,2,5,2000.00,10000.00,'2026-04-19 07:39:43','2026-04-19 07:39:43');
/*!40000 ALTER TABLE `pembelian_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengguna`
--

DROP TABLE IF EXISTS `pengguna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengguna` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengguna_email_unique` (`email`),
  KEY `pengguna_role_id_foreign` (`role_id`),
  CONSTRAINT `pengguna_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengguna`
--

LOCK TABLES `pengguna` WRITE;
/*!40000 ALTER TABLE `pengguna` DISABLE KEYS */;
INSERT INTO `pengguna` VALUES (3,'Super Admin','superadmin@gmail.com','$2y$12$CTM7VTqVU.dIxeoeh.1DbuWaiCNQFdCLHmbIFclB6xMJnMYG/o55C',1,NULL,'2026-04-24 21:57:07'),(5,'Admin','admin@gmail.com','$2y$12$SlP8z57.iHQd0P4u.ZaENuz4NFNqaZNplJEmZMSUm0d3B5IR0lyWK',2,'2026-04-24 21:56:29','2026-04-24 21:56:29'),(6,'karyawan siti','karyawan@gmail.com','$2y$12$NFoli/tV.5KJT5E2RkiW6.MQGHflhuIpLv845RWxb5w3/RDE1l.1C',2,'2026-04-24 21:56:57','2026-04-24 21:56:57');
/*!40000 ALTER TABLE `pengguna` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan`
--

DROP TABLE IF EXISTS `penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penjualan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_penjualan` varchar(255) NOT NULL,
  `nomor_resi` varchar(255) DEFAULT NULL,
  `nomor_pesanan` varchar(255) DEFAULT NULL,
  `nomor_transaksi` varchar(255) DEFAULT NULL,
  `dropshipper_id` bigint(20) unsigned DEFAULT NULL,
  `tanggal` datetime NOT NULL,
  `total_harga` decimal(14,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `scan_out` varchar(255) NOT NULL DEFAULT 'pending',
  `is_draft` varchar(255) NOT NULL DEFAULT 'no',
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `penjualan_kode_penjualan_unique` (`kode_penjualan`),
  KEY `penjualan_dropshipper_id_foreign` (`dropshipper_id`),
  KEY `penjualan_created_by_foreign` (`created_by`),
  CONSTRAINT `penjualan_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `penjualan_dropshipper_id_foreign` FOREIGN KEY (`dropshipper_id`) REFERENCES `dropshipper` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan`
--

LOCK TABLES `penjualan` WRITE;
/*!40000 ALTER TABLE `penjualan` DISABLE KEYS */;
INSERT INTO `penjualan` VALUES (12,'PJ-20260419-166','','','',4,'2026-04-23 00:00:00',10000.00,NULL,'done','no',3,'2026-04-19 07:45:58','2026-04-23 09:22:27'),(13,'PJ-20260419-285','XX','XX','XX',3,'2026-04-19 00:00:00',10000.00,NULL,'pending','no',3,'2026-04-19 07:47:23','2026-04-19 07:47:23'),(14,'PJ-20260419-324','','','',1,'2026-04-19 00:00:00',22000.00,NULL,'pending','no',3,'2026-04-19 07:48:57','2026-04-19 07:48:57'),(15,'PJ-20260420-160','','','',1,'2026-04-20 00:00:00',16000.00,NULL,'pending','no',3,'2026-04-20 00:19:20','2026-04-20 00:25:46'),(16,'PJ-20260420-134','XXCC','XXCC','1',1,'2026-04-20 00:00:00',54000.00,NULL,'pending','no',3,'2026-04-20 08:22:35','2026-04-20 08:25:35'),(17,'PJ-20260420-994','XXCC2','','1',3,'2026-04-20 00:00:00',50000.00,NULL,'pending','no',3,'2026-04-20 08:44:38','2026-04-20 08:46:40'),(18,'PJ-20260420-128','','','1',4,'2026-04-20 00:00:00',4000.00,NULL,'pending','no',3,'2026-04-20 09:32:17','2026-04-20 09:32:17'),(19,'PJ-20260422-172','fgfdgfd','gfdgfd','1',4,'2026-04-22 00:00:00',14000.00,NULL,'pending','no',3,'2026-04-22 09:01:54','2026-04-22 09:01:54'),(20,'PJ-20260422-970','','','1',4,'2026-04-22 00:00:00',10000.00,NULL,'pending','no',3,'2026-04-22 09:08:38','2026-04-22 09:08:38'),(21,'PJ-20260423-167','CCSSDF','FSEDS','1',1,'2026-04-23 00:00:00',50000.00,NULL,'failed','no',3,'2026-04-23 09:22:51','2026-04-23 09:23:10'),(22,'PJ-20260425-381','fgkfdgj','fgfdgfd','1',1,'2026-04-25 00:00:00',50000.00,NULL,'done','no',6,'2026-04-25 02:21:39','2026-04-25 02:21:56'),(23,'PJ-20260426-335','SPXID067214182654','260422F8P6PMQE','1',3,'2026-04-26 00:00:00',48000.00,NULL,'done','no',3,'2026-04-26 01:51:42','2026-04-26 10:11:40'),(25,'PJ-20260426-743','CCCC','CCCC','1',3,'2026-04-26 00:00:00',50000.00,NULL,'done','yes',3,'2026-04-26 07:04:17','2026-04-26 10:19:40'),(26,'PJ-20260426-408','','','1',3,'2026-04-26 00:00:00',900000.00,NULL,'pending','yes',3,'2026-04-26 07:15:16','2026-04-26 07:32:09'),(27,'PJ-20260426-477','','','1',4,'2026-04-26 00:00:00',50000.00,NULL,'pending','no',3,'2026-04-26 07:36:40','2026-04-26 07:38:07'),(28,'PJ-20260426-741','','','1',1,'2026-04-26 00:00:00',125000.00,NULL,'pending','no',3,'2026-04-26 08:37:48','2026-04-26 08:37:48');
/*!40000 ALTER TABLE `penjualan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan_detail`
--

DROP TABLE IF EXISTS `penjualan_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penjualan_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `penjualan_id` bigint(20) unsigned NOT NULL,
  `nomor_resi` varchar(255) DEFAULT NULL,
  `nomor_pesanan` varchar(255) DEFAULT NULL,
  `nomor_transaksi` varchar(255) DEFAULT NULL,
  `barang_id` bigint(20) unsigned NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `penjualan_detail_penjualan_id_foreign` (`penjualan_id`),
  KEY `penjualan_detail_barang_id_foreign` (`barang_id`),
  CONSTRAINT `penjualan_detail_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penjualan_detail_penjualan_id_foreign` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan_detail`
--

LOCK TABLES `penjualan_detail` WRITE;
/*!40000 ALTER TABLE `penjualan_detail` DISABLE KEYS */;
INSERT INTO `penjualan_detail` VALUES (37,12,'','','',2,5,2000.00,10000.00,'2026-04-19 07:45:58','2026-04-23 09:22:27'),(38,13,'XX','XX','XX',2,5,2000.00,10000.00,'2026-04-19 07:47:23','2026-04-19 07:47:23'),(39,14,'','','',2,11,2000.00,22000.00,'2026-04-19 07:48:57','2026-04-19 07:48:57'),(40,15,'','','',2,4,4000.00,16000.00,'2026-04-20 00:19:20','2026-04-20 00:25:46'),(42,16,'XXCC','XXCC','1',6,1,50000.00,50000.00,'2026-04-20 08:22:35','2026-04-20 08:25:35'),(43,16,'','','2',2,1,4000.00,4000.00,'2026-04-20 08:25:35','2026-04-20 08:25:35'),(44,17,'XXCC2','','1',6,1,50000.00,50000.00,'2026-04-20 08:44:38','2026-04-20 08:46:40'),(45,18,'','','1',2,1,4000.00,4000.00,'2026-04-20 09:32:17','2026-04-20 09:32:17'),(46,19,'fgfdgfd','gfdgfd','1',2,1,4000.00,4000.00,'2026-04-22 09:01:54','2026-04-22 09:01:54'),(47,19,'fgfdgfd','gfdgfd','2',5,1,10000.00,10000.00,'2026-04-22 09:01:54','2026-04-22 09:01:54'),(48,20,'','','1',5,1,10000.00,10000.00,'2026-04-22 09:08:38','2026-04-22 09:08:38'),(49,21,'CCSSDF','FSEDS','1',6,1,50000.00,50000.00,'2026-04-23 09:22:51','2026-04-23 09:23:10'),(50,22,'fgkfdgj','fgfdgfd','1',6,1,50000.00,50000.00,'2026-04-25 02:21:40','2026-04-25 02:21:56'),(51,23,'SPXID067214182654','260422F8P6PMQE','1',109,1,8000.00,8000.00,'2026-04-26 01:51:42','2026-04-26 01:51:42'),(52,23,'JX9133433589','583584783432058170','2',5,4,10000.00,40000.00,'2026-04-26 01:51:42','2026-04-26 01:51:42'),(54,25,'CCCC','CCCC','1',6,1,50000.00,50000.00,'2026-04-26 07:04:17','2026-04-26 07:04:17'),(55,26,'','','1',6,18,50000.00,900000.00,'2026-04-26 07:15:16','2026-04-26 07:32:09'),(56,27,'','','1',5,5,10000.00,50000.00,'2026-04-26 07:36:40','2026-04-26 07:38:07');
/*!40000 ALTER TABLE `penjualan_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'super_admin',NULL,NULL),(2,'admin',NULL,NULL),(3,'karyawan',NULL,NULL);
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_hak_akses`
--

DROP TABLE IF EXISTS `role_hak_akses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_hak_akses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `hak_akses_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_hak_akses_role_id_foreign` (`role_id`),
  KEY `role_hak_akses_hak_akses_id_foreign` (`hak_akses_id`),
  CONSTRAINT `role_hak_akses_hak_akses_id_foreign` FOREIGN KEY (`hak_akses_id`) REFERENCES `hak_akses` (`id`),
  CONSTRAINT `role_hak_akses_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1322 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_hak_akses`
--

LOCK TABLES `role_hak_akses` WRITE;
/*!40000 ALTER TABLE `role_hak_akses` DISABLE KEYS */;
INSERT INTO `role_hak_akses` VALUES (1077,1,1,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1078,1,2,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1079,1,6,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1080,1,10,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1081,1,14,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1082,1,18,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1083,1,21,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1084,1,24,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1085,1,27,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1086,1,29,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1087,1,31,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1088,1,33,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1089,1,35,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1090,1,39,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1091,1,44,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1092,1,48,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1093,1,52,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1094,1,54,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1095,1,56,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1096,1,60,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1097,1,3,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1098,1,7,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1099,1,11,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1100,1,15,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1101,1,25,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1102,1,36,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1103,1,40,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1104,1,45,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1105,1,49,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1106,1,57,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1107,1,61,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1108,1,4,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1109,1,8,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1110,1,12,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1111,1,16,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1112,1,26,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1113,1,37,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1114,1,41,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1115,1,46,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1116,1,50,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1117,1,53,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1118,1,58,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1119,1,62,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1120,1,5,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1121,1,9,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1122,1,13,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1123,1,17,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1124,1,20,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1125,1,23,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1126,1,38,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1127,1,42,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1128,1,47,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1129,1,51,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1130,1,59,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1131,1,63,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1132,1,19,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1133,1,22,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1134,1,55,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1135,1,28,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1136,1,30,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1137,1,32,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1138,1,34,'2026-04-24 10:17:01','2026-04-24 10:17:01'),(1139,2,1,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1140,2,2,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1141,2,6,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1142,2,10,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1143,2,14,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1144,2,18,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1145,2,21,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1146,2,24,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1147,2,27,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1148,2,29,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1149,2,31,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1150,2,33,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1151,2,35,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1152,2,39,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1153,2,44,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1154,2,48,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1155,2,52,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1156,2,54,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1157,2,56,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1158,2,60,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1159,2,3,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1160,2,7,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1161,2,11,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1162,2,15,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1163,2,25,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1164,2,36,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1165,2,40,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1166,2,45,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1167,2,49,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1168,2,57,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1169,2,61,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1170,2,4,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1171,2,8,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1172,2,12,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1173,2,16,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1174,2,26,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1175,2,37,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1176,2,41,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1177,2,46,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1178,2,50,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1179,2,53,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1180,2,58,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1181,2,62,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1182,2,5,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1183,2,9,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1184,2,13,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1185,2,17,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1186,2,20,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1187,2,23,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1188,2,38,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1189,2,42,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1190,2,47,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1191,2,51,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1192,2,59,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1193,2,63,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1194,2,19,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1195,2,22,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1196,2,55,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1197,2,28,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1198,2,30,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1199,2,32,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1200,2,34,'2026-04-24 10:18:52','2026-04-24 10:18:52'),(1263,3,1,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1264,3,2,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1265,3,6,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1266,3,10,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1267,3,14,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1268,3,18,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1269,3,21,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1270,3,27,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1271,3,29,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1272,3,31,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1273,3,33,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1274,3,35,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1275,3,39,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1276,3,44,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1277,3,48,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1278,3,52,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1279,3,54,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1280,3,56,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1281,3,60,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1282,3,3,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1283,3,7,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1284,3,11,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1285,3,15,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1286,3,36,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1287,3,40,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1288,3,45,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1289,3,49,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1290,3,57,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1291,3,61,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1292,3,4,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1293,3,8,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1294,3,12,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1295,3,16,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1296,3,37,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1297,3,41,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1298,3,46,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1299,3,50,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1300,3,53,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1301,3,58,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1302,3,62,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1303,3,5,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1304,3,9,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1305,3,13,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1306,3,17,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1307,3,20,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1308,3,23,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1309,3,38,'2026-04-24 21:30:06','2026-04-24 21:30:06'),(1310,3,42,'2026-04-24 21:30:07','2026-04-24 21:30:07'),(1311,3,47,'2026-04-24 21:30:07','2026-04-24 21:30:07'),(1312,3,51,'2026-04-24 21:30:07','2026-04-24 21:30:07'),(1313,3,59,'2026-04-24 21:30:07','2026-04-24 21:30:07'),(1314,3,63,'2026-04-24 21:30:07','2026-04-24 21:30:07'),(1315,3,19,'2026-04-24 21:30:07','2026-04-24 21:30:07'),(1316,3,22,'2026-04-24 21:30:07','2026-04-24 21:30:07'),(1317,3,55,'2026-04-24 21:30:07','2026-04-24 21:30:07'),(1318,3,28,'2026-04-24 21:30:07','2026-04-24 21:30:07'),(1319,3,30,'2026-04-24 21:30:07','2026-04-24 21:30:07'),(1320,3,32,'2026-04-24 21:30:07','2026-04-24 21:30:07'),(1321,3,34,'2026-04-24 21:30:07','2026-04-24 21:30:07');
/*!40000 ALTER TABLE `role_hak_akses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `satuan`
--

DROP TABLE IF EXISTS `satuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `satuan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_satuan` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satuan`
--

LOCK TABLES `satuan` WRITE;
/*!40000 ALTER TABLE `satuan` DISABLE KEYS */;
INSERT INTO `satuan` VALUES (1,'PCS','2026-04-15 10:51:05','2026-04-15 10:51:05'),(2,'BAL','2026-04-15 10:51:37','2026-04-15 10:51:37'),(3,'tes 00','2026-04-15 10:51:44','2026-04-15 10:52:52');
/*!40000 ALTER TABLE `satuan` ENABLE KEYS */;
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
INSERT INTO `sessions` VALUES ('kXoGJShB7wq0Dx3he5mkorcDcG3Cv6yB5TPkWH4L',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVkdJdGxaZGRLQTl6bHNBVHllRm5ycHpsWnFOcm5nNVpTejVYb1JLUCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC90cmFuc2Frc2kvcGVuanVhbGFuL2NyZWF0ZSI7czo1OiJyb3V0ZSI7czoxNjoicGVuanVhbGFuLmNyZWF0ZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTU6ImxvZ2luX3BlbmdndW5hXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mzt9',1777193520),('l4v0trVNmfF6mXdvZk9x12pp0QS6AmC8htzSBpdu',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUHFha1VpMWVyRFF4OXNTb3BwU0NHTzduMWp6QUNZWUdsQ2prTm51cyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC90cmFuc2Frc2kvcGVuanVhbGFuIjtzOjU6InJvdXRlIjtzOjE1OiJwZW5qdWFsYW4uaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjU1OiJsb2dpbl9wZW5nZ3VuYV81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==',1777223883),('mqvbVHflX8vEyj5geYlM0043Fdsm1sKQYsKwCaMR',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSHByT3FEWmNsNFp0NVkzbEFVZmJSbDFncVFHUzhRSExDRGc5dlBwOCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC90cmFuc2Frc2kvcGVuanVhbGFuL2NyZWF0ZSI7czo1OiJyb3V0ZSI7czoxNjoicGVuanVhbGFuLmNyZWF0ZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTU6ImxvZ2luX3BlbmdndW5hXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mzt9',1777204295);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stok_barang`
--

DROP TABLE IF EXISTS `stok_barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_barang` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `barang_id` bigint(20) unsigned NOT NULL,
  `jumlah_stok` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stok_barang_barang_id_foreign` (`barang_id`),
  CONSTRAINT `stok_barang_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_barang`
--

LOCK TABLES `stok_barang` WRITE;
/*!40000 ALTER TABLE `stok_barang` DISABLE KEYS */;
INSERT INTO `stok_barang` VALUES (2,2,3,'2026-04-17 02:47:45','2026-04-22 09:01:54'),(5,5,0,'2026-04-17 10:07:26','2026-04-26 07:38:07'),(6,6,6,'2026-04-17 10:07:59','2026-04-26 07:30:37'),(8,8,9,'2026-04-22 09:39:18','2026-04-25 04:31:01'),(9,109,79,'2026-04-26 00:08:26','2026-04-26 01:51:42'),(12,112,10,'2026-04-26 08:47:14','2026-04-26 08:47:14'),(14,114,5,'2026-04-26 09:05:25','2026-04-26 09:05:25');
/*!40000 ALTER TABLE `stok_barang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stok_movement`
--

DROP TABLE IF EXISTS `stok_movement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_movement` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `barang_id` bigint(20) unsigned NOT NULL,
  `jenis` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `stok_sebelum` int(11) NOT NULL,
  `stok_sesudah` int(11) NOT NULL,
  `referensi_tipe` varchar(255) DEFAULT NULL,
  `referensi_id` bigint(20) unsigned DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stok_movement_created_by_foreign` (`created_by`),
  KEY `stok_movement_barang_id_foreign` (`barang_id`),
  CONSTRAINT `stok_movement_barang_id_foreign` FOREIGN KEY (`barang_id`) REFERENCES `barang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stok_movement_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=230 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_movement`
--

LOCK TABLES `stok_movement` WRITE;
/*!40000 ALTER TABLE `stok_movement` DISABLE KEYS */;
INSERT INTO `stok_movement` VALUES (190,2,'masuk',5,20,25,'pembelian',8,'Pembelian PB-20260419-294',3,'2026-04-19 07:39:43','2026-04-19 07:39:43'),(191,2,'keluar',5,25,20,'penjualan',12,'Penjualan PJ-20260419-166',3,'2026-04-19 07:45:58','2026-04-19 07:45:58'),(192,2,'keluar',5,20,15,'penjualan',13,'Penjualan PJ-20260419-285',3,'2026-04-19 07:47:23','2026-04-19 07:47:23'),(193,2,'keluar',11,15,4,'penjualan',14,'Penjualan PJ-20260419-324',3,'2026-04-19 07:48:57','2026-04-19 07:48:57'),(194,5,'adjustment',2,6,8,'adjust_stok',14,'Adjustment stok',3,'2026-04-19 08:08:22','2026-04-19 08:08:22'),(195,5,'adjustment',-2,8,6,'adjust_stok',15,'Adjustment stok',3,'2026-04-19 10:05:13','2026-04-19 10:05:13'),(196,5,'adjustment',-2,6,4,'adjust_stok',16,'Adjustment stok',3,'2026-04-19 10:05:41','2026-04-19 10:05:41'),(197,2,'keluar',2,4,2,'penjualan',15,'Penjualan PJ-20260420-160',3,'2026-04-20 00:19:20','2026-04-20 00:19:20'),(198,2,'keluar',2,2,0,'penjualan_update',15,'Edit penjualan PJ-20260420-160',3,'2026-04-20 00:25:46','2026-04-20 00:25:46'),(199,5,'keluar',1,4,3,'penjualan',16,'Penjualan PJ-20260420-134',3,'2026-04-20 08:22:35','2026-04-20 08:22:35'),(200,6,'keluar',1,10,9,'penjualan',16,'Penjualan PJ-20260420-134',3,'2026-04-20 08:22:35','2026-04-20 08:22:35'),(201,2,'adjustment',6,0,6,'adjust_stok',17,'Adjustment stok',3,'2026-04-20 08:25:22','2026-04-20 08:25:22'),(202,2,'keluar',1,6,5,'penjualan_update',16,'Edit penjualan PJ-20260420-134',3,'2026-04-20 08:25:35','2026-04-20 08:25:35'),(203,5,'masuk',1,3,4,'penjualan_update_delete_item',16,'Hapus item saat edit PJ-20260420-134',3,'2026-04-20 08:25:35','2026-04-20 08:25:35'),(204,6,'keluar',1,9,8,'penjualan',17,'Penjualan PJ-20260420-994',3,'2026-04-20 08:44:38','2026-04-20 08:44:38'),(205,2,'keluar',1,5,4,'penjualan',18,'Penjualan PJ-20260420-128',3,'2026-04-20 09:32:17','2026-04-20 09:32:17'),(206,2,'keluar',1,4,3,'penjualan',19,'Penjualan PJ-20260422-172',3,'2026-04-22 09:01:54','2026-04-22 09:01:54'),(207,5,'keluar',1,4,3,'penjualan',19,'Penjualan PJ-20260422-172',3,'2026-04-22 09:01:54','2026-04-22 09:01:54'),(208,5,'keluar',1,3,2,'penjualan',20,'Penjualan PJ-20260422-970',3,'2026-04-22 09:08:38','2026-04-22 09:08:38'),(209,8,'masuk',545,0,545,'stok_awal',8,'Input stok awal saat membuat barang',3,'2026-04-22 09:39:18','2026-04-22 09:39:18'),(210,6,'keluar',1,8,7,'penjualan',21,'Penjualan PJ-20260423-167',3,'2026-04-23 09:22:51','2026-04-23 09:22:51'),(211,8,'adjustment',-543,545,2,'adjust_stok',18,'Adjustment stok',6,'2026-04-24 22:31:01','2026-04-24 22:31:01'),(212,8,'adjustment',0,2,2,'adjust_stok',21,'Adjustment stok',6,'2026-04-24 22:31:10','2026-04-24 22:31:10'),(213,8,'adjustment',2,2,4,'adjust_stok',22,'Adjustment stok',6,'2026-04-24 22:57:19','2026-04-24 22:57:19'),(214,8,'adjustment',2,4,6,'adjust_stok',23,'Adjustment stok',6,'2026-04-24 22:57:49','2026-04-24 22:57:49'),(215,8,'adjustment',-3,6,3,'adjust_stok',25,'Adjustment stok',6,'2026-04-25 00:34:33','2026-04-25 00:34:33'),(216,8,'adjustment',3,3,6,'adjust_stok',27,'Adjustment stok',6,'2026-04-25 02:00:49','2026-04-25 02:00:49'),(217,6,'keluar',1,7,6,'penjualan',22,'Penjualan PJ-20260425-381',6,'2026-04-25 02:21:40','2026-04-25 02:21:40'),(218,8,'adjustment',3,6,9,'adjust_stok',30,'Approve adjustment stok',6,'2026-04-25 04:31:01','2026-04-25 04:31:01'),(219,109,'masuk',80,0,80,'stok_awal',109,'Input stok awal saat membuat barang',3,'2026-04-26 00:08:27','2026-04-26 00:08:27'),(220,109,'keluar',1,80,79,'penjualan',23,'Penjualan PJ-20260426-335',3,'2026-04-26 01:51:42','2026-04-26 01:51:42'),(221,5,'keluar',4,9,5,'penjualan',23,'Penjualan PJ-20260426-335',3,'2026-04-26 01:51:42','2026-04-26 01:51:42'),(222,6,'keluar',4,6,2,'penjualan_draft_processed',26,'Draft diproses PJ-20260426-408',3,'2026-04-26 07:23:36','2026-04-26 07:23:36'),(223,6,'masuk',4,2,6,'penjualan_draft_revert',26,'Revert ke draft PJ-20260426-408',3,'2026-04-26 07:27:37','2026-04-26 07:27:37'),(224,6,'keluar',1,6,5,'penjualan_draft_processed',26,'Draft diproses PJ-20260426-408',3,'2026-04-26 07:30:29','2026-04-26 07:30:29'),(225,6,'masuk',1,5,6,'penjualan_draft_revert',26,'Revert ke draft PJ-20260426-408',3,'2026-04-26 07:30:37','2026-04-26 07:30:37'),(226,5,'keluar',5,5,0,'penjualan_draft_processed',27,'Draft diproses PJ-20260426-477',3,'2026-04-26 07:37:22','2026-04-26 07:37:22'),(227,5,'masuk',5,0,5,'penjualan_draft_revert',27,'Revert ke draft PJ-20260426-477',3,'2026-04-26 07:37:32','2026-04-26 07:37:32'),(228,5,'keluar',5,5,0,'penjualan_draft_processed',27,'Draft diproses PJ-20260426-477',3,'2026-04-26 07:38:07','2026-04-26 07:38:07');
/*!40000 ALTER TABLE `stok_movement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_supplier` varchar(255) NOT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier`
--

LOCK TABLES `supplier` WRITE;
/*!40000 ALTER TABLE `supplier` DISABLE KEYS */;
INSERT INTO `supplier` VALUES (1,'PABRIK','08999999999','JL CIAPUS BARU',NULL,NULL,'2026-04-16 03:33:35',NULL),(6,'PABRIK B',NULL,NULL,NULL,'2026-04-15 10:23:32','2026-04-15 10:23:32',NULL),(7,'PABRIK C',NULL,NULL,NULL,'2026-04-15 10:23:58','2026-04-15 10:23:58',NULL);
/*!40000 ALTER TABLE `supplier` ENABLE KEYS */;
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
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

-- Dump completed on 2026-04-27  0:21:07
