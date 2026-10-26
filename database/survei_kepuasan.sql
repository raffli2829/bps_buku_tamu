-- ==========================================================
-- Tabel Survei Kepuasan Masyarakat (SKM / IKM)
-- Sistem Informasi Registrasi Tamu dan Antrian (SIRITA)
-- BPS Kabupaten Bangka
-- ==========================================================

CREATE TABLE IF NOT EXISTS `survei_kepuasan` (
  `id_survei` int(11) NOT NULL AUTO_INCREMENT,
  `id_antrian` int(11) NOT NULL,
  `id_pengunjung` int(11) NOT NULL,
  `rating_pelayanan` tinyint(1) NOT NULL COMMENT '1: Sangat Tidak Puas, 2: Tidak Puas, 3: Cukup, 4: Puas, 5: Sangat Puas',
  `keramahan_petugas` tinyint(1) DEFAULT 5,
  `kecepatan_layanan` tinyint(1) DEFAULT 5,
  `kemudahan_prosedur` tinyint(1) DEFAULT 5,
  `saran` text DEFAULT NULL,
  `waktu_survei` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_survei`),
  UNIQUE KEY `uniq_antrian_survei` (`id_antrian`),
  KEY `fk_survei_pengunjung` (`id_pengunjung`),
  CONSTRAINT `fk_survei_antrian` FOREIGN KEY (`id_antrian`) REFERENCES `antrian` (`id_antrian`) ON DELETE CASCADE,
  CONSTRAINT `fk_survei_pengunjung` FOREIGN KEY (`id_pengunjung`) REFERENCES `pengunjung` (`id_pengunjung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
