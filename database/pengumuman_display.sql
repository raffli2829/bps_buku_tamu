-- ==========================================================
-- Tabel Pengumuman Running Text Display TV
-- Sistem Informasi Registrasi Tamu dan Antrian (SIRITA)
-- BPS Kabupaten Bangka
-- ==========================================================

CREATE TABLE IF NOT EXISTS `pengumuman_display` (
  `id_pengumuman` int(11) NOT NULL AUTO_INCREMENT,
  `isi_teks` text NOT NULL,
  `status_aktif` tinyint(1) DEFAULT 1 COMMENT '1: Aktif, 0: Nonaktif',
  `urutan` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pengumuman`),
  UNIQUE KEY `unique_urutan` (`urutan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data Awal Pengumuman Resmi BPS Kabupaten Bangka
INSERT INTO `pengumuman_display` (`isi_teks`, `status_aktif`, `urutan`) VALUES
('Selamat Datang di BPS Kabupaten Bangka • Pelayanan Statistik Terpadu (PST) siap melayani kebutuhan data Anda dengan Cepat, Akurat, dan Berintegritas', 1, 1),
('Jam Layanan PST: Senin s.d. Kamis (08.00 - 15.30 WIB) dan Jumat (08.00 - 15.00 WIB)', 1, 2),
('Seluruh pelayanan publik, konsultasi statistik, dan rekomendasi kegiatan statistik di BPS Kabupaten Bangka tidak dipungut biaya (GRATIS)', 1, 3),
('Silakan menunggu dengan nyaman hingga nomor antrean Anda dipanggil oleh petugas loket', 1, 4);
