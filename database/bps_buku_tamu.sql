-- =============================================================================
-- SISTEM INFORMASI BUKU TAMU & ANTRIAN TERPADU (SIRITA)
-- BADAN PUSAT STATISTIK KABUPATEN BANGKA
-- =============================================================================
-- File        : bps_buku_tamu.sql (Master Database Dump Terpadu)
-- Format      : MySQL / MariaDB (Universal: MySQL 5.7+, 8.x, MariaDB 10.x+)
-- Database    : bps_buku_tamu
-- Akun Default: Username: admin01 | Password: admin
-- Keterangan  : Berisi struktur lengkap 6 tabel utama, relasi foreign key,
--               index performa, serta data master aktif.
-- =============================================================================

-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: bps_buku_tamu
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `bps_buku_tamu`
--

/*!40000 DROP DATABASE IF EXISTS `bps_buku_tamu`*/;

CREATE DATABASE IF NOT EXISTS `bps_buku_tamu` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `bps_buku_tamu`;

--
-- Table structure for table `antrian`
--

DROP TABLE IF EXISTS `antrian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `antrian` (
  `id_antrian` int NOT NULL AUTO_INCREMENT,
  `id_pengunjung` int NOT NULL,
  `id_pelayanan` int NOT NULL,
  `nomor_antrian` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Menunggu','Dipanggil','Selesai') COLLATE utf8mb4_general_ci DEFAULT 'Menunggu',
  `waktu_ambil` datetime DEFAULT CURRENT_TIMESTAMP,
  `waktu_panggil` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  PRIMARY KEY (`id_antrian`),
  KEY `fk_antrian_pengunjung` (`id_pengunjung`),
  KEY `fk_antrian_pelayanan` (`id_pelayanan`),
  KEY `idx_antrian_status` (`status`),
  KEY `idx_antrian_nomor` (`nomor_antrian`),
  CONSTRAINT `fk_antrian_pelayanan` FOREIGN KEY (`id_pelayanan`) REFERENCES `pelayanan` (`id_pelayanan`),
  CONSTRAINT `fk_antrian_pengunjung` FOREIGN KEY (`id_pengunjung`) REFERENCES `pengunjung` (`id_pengunjung`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `antrian`
--

LOCK TABLES `antrian` WRITE;
/*!40000 ALTER TABLE `antrian` DISABLE KEYS */;
INSERT INTO `antrian` VALUES (27,28,5,'C001','Selesai','2026-10-27 15:41:23','2026-10-28 09:29:18','2026-10-28 09:29:38'),(28,29,7,'E001','Selesai','2026-10-27 15:53:52','2026-10-28 09:28:22','2026-10-28 09:29:16'),(29,30,7,'E002','Selesai','2026-10-27 15:53:54','2026-10-28 09:27:13','2026-10-28 09:29:07'),(30,31,8,'F001','Selesai','2026-10-27 15:55:23','2026-10-28 09:27:43','2026-10-28 09:29:03'),(31,32,5,'C002','Selesai','2026-10-27 15:57:41','2026-10-28 09:28:02','2026-10-28 09:28:21'),(32,33,1,'A001','Menunggu','2026-11-05 09:57:33',NULL,NULL),(33,34,5,'C001','Menunggu','2026-11-05 10:50:08',NULL,NULL),(37,38,2,'B001','Menunggu','2026-11-09 10:10:20',NULL,NULL);
/*!40000 ALTER TABLE `antrian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pelayanan`
--

DROP TABLE IF EXISTS `pelayanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pelayanan` (
  `id_pelayanan` int NOT NULL AUTO_INCREMENT,
  `kode` char(1) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_pelayanan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_pelayanan`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pelayanan`
--

LOCK TABLES `pelayanan` WRITE;
/*!40000 ALTER TABLE `pelayanan` DISABLE KEYS */;
INSERT INTO `pelayanan` VALUES (1,'A','Kunjungan'),(2,'B','PPID(Pejabat Pengelola Informasi dan Dokumentasi)'),(5,'C','PST Perpustakaan'),(6,'D','PST Rekomendasi'),(7,'E','PST Konsultasi Statistik'),(8,'F','Pengaduan'),(9,'G','test');
/*!40000 ALTER TABLE `pelayanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengumuman_display`
--

DROP TABLE IF EXISTS `pengumuman_display`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengumuman_display` (
  `id_pengumuman` int NOT NULL AUTO_INCREMENT,
  `isi_teks` text COLLATE utf8mb4_general_ci NOT NULL,
  `status_aktif` tinyint(1) DEFAULT '1' COMMENT '1: Aktif, 0: Nonaktif',
  `urutan` int DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pengumuman`),
  UNIQUE KEY `unique_urutan` (`urutan`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengumuman_display`
--

LOCK TABLES `pengumuman_display` WRITE;
/*!40000 ALTER TABLE `pengumuman_display` DISABLE KEYS */;
INSERT INTO `pengumuman_display` VALUES (2,'Jam Layanan PST: Senin s.d. Kamis (08.00 - 15.30 WIB) dan Jumat (08.00 - 15.00 WIB)',1,2,'2026-10-26 13:15:07'),(3,'Seluruh pelayanan publik, konsultasi statistik, dan rekomendasi kegiatan statistik di BPS Kabupaten Bangka tidak dipungut biaya (GRATIS)',1,1,'2026-10-26 13:15:07'),(4,'Silakan menunggu dengan nyaman hingga nomor antrean Anda dipanggil oleh petugas loket',1,3,'2026-10-26 13:15:07'),(5,'Selamat Datang di BPS Kabupaten Bangka • Pelayanan Statistik Terpadu (PST) siap melayani kebutuhan data Anda dengan Cepat, Akurat, dan Berintegritas',1,4,'2026-10-26 15:23:49');
/*!40000 ALTER TABLE `pengumuman_display` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengunjung`
--

DROP TABLE IF EXISTS `pengunjung`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengunjung` (
  `id_pengunjung` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `instansi` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_hp` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pekerjaan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `usia` int DEFAULT NULL,
  `pendidikan_terakhir` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nomor_parkir` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_pelayanan` int NOT NULL,
  `tujuan` text COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  PRIMARY KEY (`id_pengunjung`),
  KEY `idx_pengunjung_tanggal` (`tanggal`),
  KEY `idx_pengunjung_pelayanan` (`id_pelayanan`),
  CONSTRAINT `fk_pengunjung_pelayanan` FOREIGN KEY (`id_pelayanan`) REFERENCES `pelayanan` (`id_pelayanan`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengunjung`
--

LOCK TABLES `pengunjung` WRITE;
/*!40000 ALTER TABLE `pengunjung` DISABLE KEYS */;
INSERT INTO `pengunjung` VALUES (28,'Tamam Raffli','Laki-laki','masyarakat','08362739472','tamamraffli@gmail.com','asn',2,'S2','',5,'2','2026-10-27','08:41:23'),(29,'Tamam Raffli','Laki-laki','pt kepala ikan','08362739472','tamamraffli@gmail.com','juru juri',22,'S2','',7,'=','2026-10-27','08:53:52'),(30,'Tamam Raffli','Laki-laki','pt kepala ikan','08362739472','tamamraffli@gmail.com','juru juri',22,'S2','',7,'=','2026-10-27','08:53:54'),(31,'Tamam Raffli','Laki-laki','kominfo','083212132123','tamamraffli@gmail.com','dda',2,'Diploma 1-3','-',8,'2','2026-10-27','08:55:23'),(32,'Tamam Raffli','Laki-laki','pt kepala ikan','081283920328','tamamraffli@gmail.com','asn',21,'S2','',5,'2','2026-10-27','08:57:41'),(33,'raffli','Laki-laki','masyarakat','08292283981091','raffli@gmail.com','mahasiswa',14,'D4/S1','',1,'-','2026-11-05','02:57:33'),(34,'Tamam Raffli','Laki-laki','pt kepala ikan','083169607233','tamamraffli@gmail.com','juru juri',12,'S3','-',5,'-','2026-11-05','03:50:08'),(38,'-','Laki-laki','ada','083212132123','tamamraffli@gmail.com','juru juri',21,'S2','',2,'-','2026-11-09','03:10:20');
/*!40000 ALTER TABLE `pengunjung` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survei_kepuasan`
--

DROP TABLE IF EXISTS `survei_kepuasan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survei_kepuasan` (
  `id_survei` int NOT NULL AUTO_INCREMENT,
  `id_antrian` int NOT NULL,
  `id_pengunjung` int NOT NULL,
  `rating_pelayanan` tinyint(1) NOT NULL COMMENT '1: Sangat Tidak Puas, 2: Tidak Puas, 3: Cukup, 4: Puas, 5: Sangat Puas',
  `keramahan_petugas` tinyint(1) DEFAULT '5',
  `kecepatan_layanan` tinyint(1) DEFAULT '5',
  `kemudahan_prosedur` tinyint(1) DEFAULT '5',
  `saran` text COLLATE utf8mb4_general_ci,
  `waktu_survei` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_survei`),
  UNIQUE KEY `uniq_antrian_survei` (`id_antrian`),
  KEY `fk_survei_pengunjung` (`id_pengunjung`),
  CONSTRAINT `fk_survei_antrian` FOREIGN KEY (`id_antrian`) REFERENCES `antrian` (`id_antrian`) ON DELETE CASCADE,
  CONSTRAINT `fk_survei_pengunjung` FOREIGN KEY (`id_pengunjung`) REFERENCES `pengunjung` (`id_pengunjung`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survei_kepuasan`
--

LOCK TABLES `survei_kepuasan` WRITE;
/*!40000 ALTER TABLE `survei_kepuasan` DISABLE KEYS */;
/*!40000 ALTER TABLE `survei_kepuasan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ADMIN','admin01','$2y$10$9lZ.h.ZLWzMasyDPhTelJuXw3JtkC703h2WboJpqVK/iXsvXIjhdG',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'bps_buku_tamu'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 11:02:33
