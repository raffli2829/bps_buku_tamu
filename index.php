<?php
session_start();
include 'config/koneksi.php';

// Ambil semua data pelayanan urut berdasarkan kode
$query = mysqli_query($conn, "SELECT * FROM pelayanan ORDER BY kode ASC");

$layanan_pst = [];
$layanan_lain = [];

while ($row = mysqli_fetch_assoc($query)) {
    // Layanan yang mengandung kata 'PST' masuk ke grup PST, sisanya ke grup Lainnya
    if (stripos($row['nama_pelayanan'], 'PST') !== false) {
        $layanan_pst[] = $row;
    } else {
        $layanan_lain[] = $row;
    }
}

// Helper deskripsi & icon dinamis untuk tiap jenis layanan
function get_pelayanan_meta($nama) {
    $nama_lower = strtolower($nama);
    if (strpos($nama_lower, 'perpustakaan') !== false) {
        return [
            'icon'  => 'fas fa-book-reader',
            'desc'  => 'Layanan literatur, buku referensi, dan publikasi statistik BPS lengkap.',
            'color' => '#0284c7'
        ];
    } elseif (strpos($nama_lower, 'rekomendasi') !== false) {
        return [
            'icon'  => 'fas fa-file-signature',
            'desc'  => 'Pengajuan dan konsultasi rekomendasi kegiatan statistik sektoral (ROMANTIK).',
            'color' => '#2563eb'
        ];
    } elseif (strpos($nama_lower, 'konsultasi') !== false) {
        return [
            'icon'  => 'fas fa-chart-line',
            'desc'  => 'Konsultasi analisis data, metodologi sensus/survei, dan metadata statistik.',
            'color' => '#4f46e5'
        ];
    } elseif (strpos($nama_lower, 'kunjungan') !== false) {
        return [
            'icon'  => 'fas fa-building-user',
            'desc'  => 'Kunjungan kerja, koordinasi dinas, maupun audiensi umum dengan pegawai BPS.',
            'color' => '#059669'
        ];
    } elseif (strpos($nama_lower, 'ppid') !== false) {
        return [
            'icon'  => 'fas fa-info-circle',
            'desc'  => 'Layanan permohonan informasi publik dan dokumentasi resmi BPS Bangka.',
            'color' => '#0d9488'
        ];
    } elseif (strpos($nama_lower, 'pengaduan') !== false) {
        return [
            'icon'  => 'fas fa-bullhorn',
            'desc'  => 'Penyampaian aspirasi, saran, kritik, dan pengaduan layanan masyarakat.',
            'color' => '#ea580c'
        ];
    } else {
        return [
            'icon'  => 'fas fa-concierge-bell',
            'desc'  => 'Pelayanan umum dan registrasi tamu BPS Kabupaten Bangka.',
            'color' => '#64748b'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pilihan Pelayanan | SIRITA BPS Kabupaten Bangka</title>

    <!-- Bootstrap 4 & FontAwesome & Google Fonts Poppins -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-blue: #0d6efd;
            --primary-dark: #0a58ca;
            --accent-pst: #0284c7;
            --accent-other: #0d9488;
            --bg-body: #f1f5f9;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            min-height: 100%;
            background-color: var(--bg-body);
            color: var(--text-dark);
        }

        body {
            display: flex;
            flex-direction: column;
            margin: 0;
            overflow-x: hidden;
            position: relative;
        }

        /* Top Header Navigation Bar */
        .top-navbar {
            background: linear-gradient(135deg, #0d6efd 0%, #084298 100%);
            color: #ffffff;
            padding: 14px 20px;
            box-shadow: 0 4px 20px rgba(13, 110, 253, 0.25);
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .navbar-brand-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: #ffffff;
        }

        .navbar-brand-wrap:hover {
            color: #ffffff;
            text-decoration: none;
        }

        .logo-nav {
            width: 50px;
            height: auto;
            filter: drop-shadow(0 2px 6px rgba(0,0,0,0.25));
        }

        .nav-title {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1.1;
            margin: 0;
        }

        .nav-subtitle {
            font-size: 12px;
            font-weight: 300;
            opacity: 0.9;
            margin: 0;
            letter-spacing: 0.3px;
        }

        /* Tombol Navigasi Kanan */
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-nav-display {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-nav-display:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .btn-login-admin {
            background: #ffffff;
            color: #0d6efd;
            border: none;
            border-radius: 10px;
            padding: 8px 18px;
            font-size: 13.5px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-login-admin:hover {
            background: #f8fafc;
            color: #0a58ca;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.22);
        }

        /* Hero Banner */
        .hero-section {
            background:
                radial-gradient(circle at 10% 20%, rgba(13, 110, 253, 0.08) 0, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(13, 148, 136, 0.08) 0, transparent 40%),
                #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 36px 16px 28px;
            text-align: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #e0f2fe;
            color: #0284c7;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 14px;
            border: 1px solid #bae6fd;
        }

        .hero-title {
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .hero-subtitle {
            font-size: 16px;
            color: #475569;
            max-width: 720px;
            margin: 0 auto 12px;
            line-height: 1.6;
        }

        .live-clock-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 4px 14px;
            border-radius: 20px;
            font-weight: 500;
        }

        /* Main Service Container */
        .main-container {
            flex: 1;
            padding: 32px 16px 48px;
            max-width: 1240px;
            margin: 0 auto;
            width: 100%;
        }

        /* Section Category Headers */
        .category-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-radius: 16px 16px 0 0;
            color: #ffffff;
            margin-bottom: 0;
        }

        .cat-pst {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);
        }

        .cat-other {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            box-shadow: 0 4px 14px rgba(13, 148, 136, 0.25);
        }

        .cat-title-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cat-icon-circle {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #ffffff;
            backdrop-filter: blur(4px);
        }

        .cat-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .cat-desc {
            font-size: 12px;
            opacity: 0.9;
            margin: 0;
            font-weight: 400;
        }

        .cat-count-badge {
            background: rgba(255, 255, 255, 0.25);
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
            align-self: center;
        }

        /* Category Card Body */
        .category-body {
            background: #ffffff;
            border-radius: 0 0 16px 16px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-top: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* Interactive Service Card */
        .service-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 18px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            background: #ffffff;
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: transparent;
            transition: background 0.2s ease;
        }

        .service-card-pst:hover {
            border-color: #0284c7;
            background: #f0f9ff;
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(2, 132, 199, 0.14);
            text-decoration: none;
            color: #0369a1;
        }

        .service-card-pst:hover::before {
            background: #0284c7;
        }

        .service-card-other:hover {
            border-color: #0d9488;
            background: #f0fdfa;
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(13, 148, 136, 0.14);
            text-decoration: none;
            color: #0f766e;
        }

        .service-card-other:hover::before {
            background: #0d9488;
        }

        .service-card-content {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            min-width: 0;
        }

        .service-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .service-card:hover .service-icon-box {
            transform: scale(1.08);
        }

        .service-info {
            flex: 1;
            min-width: 0;
        }



        .service-name {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            line-height: 1.3;
        }

        .service-desc {
            font-size: 12.5px;
            color: #64748b;
            margin: 0;
            line-height: 1.45;
        }

        .service-btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #f8fafc;
            color: #94a3b8;
            border: 1px solid #e2e8f0;
            margin-left: 12px;
            flex-shrink: 0;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .service-card-pst:hover .service-btn-action {
            background: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
            transform: translateX(3px);
        }

        .service-card-other:hover .service-btn-action {
            background: #0d9488;
            color: #ffffff;
            border-color: #0d9488;
            transform: translateX(3px);
        }

        /* Empty state */
        .empty-service-box {
            text-align: center;
            padding: 32px 16px;
            color: #94a3b8;
        }

        /* Footer */
        .footer-public {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 24px 16px;
            text-align: center;
            color: #64748b;
            font-size: 13px;
        }

        .footer-public a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }

        /* Responsif Mobile Phone */
        @media (max-width: 768px) {
            .top-navbar {
                padding: 10px 14px;
            }

            .logo-nav {
                width: 40px;
            }

            .nav-title {
                font-size: 18px;
            }

            .nav-subtitle {
                font-size: 10.5px;
            }

            .btn-login-admin {
                padding: 7px 12px;
                font-size: 12px;
            }

            .btn-nav-display {
                display: none; /* Hemat ruang navbar di HP */
            }

            .hero-section {
                padding: 24px 14px 20px;
            }

            .hero-title {
                font-size: 22px;
            }

            .hero-subtitle {
                font-size: 14px;
            }

            .main-container {
                padding: 20px 12px 36px;
            }

            .category-header {
                padding: 14px 16px;
                gap: 12px;
            }

            .cat-title-wrap {
                gap: 10px;
                flex: 1;
                min-width: 0;
            }

            .cat-title {
                font-size: 15px;
            }

            .cat-desc {
                font-size: 11px;
                line-height: 1.35;
            }

            .cat-count-badge {
                font-size: 11px;
                padding: 4px 10px;
            }

            .category-body {
                padding: 14px 12px;
            }

            .service-card {
                padding: 14px 12px;
            }

            .service-icon-box {
                width: 42px;
                height: 42px;
                font-size: 17px;
            }

            .service-name {
                font-size: 14.5px;
            }

            .service-desc {
                font-size: 11.5px;
            }

            .service-btn-action {
                width: 32px;
                height: 32px;
                font-size: 12px;
                margin-left: 6px;
            }
        }
    </style>
</head>
<body>

    <!-- TOP NAVBAR -->
    <header class="top-navbar">
        <div class="container-fluid d-flex align-items-center justify-content-between p-0">
            <!-- Brand Logo -->
            <a href="index.php" class="navbar-brand-wrap">
                <img src="assets/img/logo_bps.png" alt="Logo BPS Bangka" class="logo-nav">
                <div>
                    <h1 class="nav-title">SIRITA</h1>
                    <p class="nav-subtitle">BPS Kabupaten Bangka</p>
                </div>
            </a>

            <!-- Tombol Navigasi Kanan -->
            <div class="nav-actions">
                <a href="login.php" class="btn-login-admin" title="Masuk ke Halaman Petugas / Admin">
                    <i class="fas fa-user-shield"></i>
                    <span>Login Admin</span>
                </a>
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="container">
            <h2 class="hero-title">Selamat Datang di BPS Kabupaten Bangka</h2>
            <p class="hero-subtitle">
                Silakan pilih jenis pelayanan yang Anda tuju di bawah ini untuk mengisi buku tamu dan mendapatkan nomor antrean resmi.
            </p>
            <div class="live-clock-pill">
                <i class="far fa-clock text-primary"></i>
                <span id="liveClockTeks"><?= date('d F Y'); ?></span>
            </div>
        </div>
    </section>

    <!-- MAIN SERVICE SELECTION CONTAINER -->
    <main class="main-container">
        <div class="row">

            <!-- ============================================================== -->
            <!-- KOLOM KIRI (UTAMA): SEMUA PELAYANAN STATISTIK TERPADU (PST) -->
            <!-- ============================================================== -->
            <div class="col-lg-6 mb-4">
                <div class="category-header cat-pst">
                    <div class="cat-title-wrap">
                        <div class="cat-icon-circle">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div>
                            <h3 class="cat-title">Pelayanan Statistik Terpadu (PST)</h3>
                            <p class="cat-desc">Layanan konsultasi data, rekomendasi, dan perpustakaan statistik</p>
                        </div>
                    </div>
                    <span class="cat-count-badge" title="Total Layanan PST">
                        <?= count($layanan_pst); ?> Layanan
                    </span>
                </div>

                <div class="category-body">
                    <?php if (!empty($layanan_pst)) { ?>
                        <?php foreach ($layanan_pst as $p) { 
                            $meta = get_pelayanan_meta($p['nama_pelayanan']);
                        ?>
                            <a href="registrasi.php?id_pelayanan=<?= (int)$p['id_pelayanan']; ?>" 
                               class="service-card service-card-pst" 
                               title="Pilih <?= htmlspecialchars($p['nama_pelayanan']); ?>">
                                <div class="service-card-content">
                                    <div class="service-info">
                                        <h4 class="service-name"><?= htmlspecialchars($p['nama_pelayanan']); ?></h4>
                                        <p class="service-desc"><?= $meta['desc']; ?></p>
                                    </div>
                                </div>
                                <div class="service-btn-action">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="empty-service-box">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p class="m-0">Belum ada data layanan PST aktif saat ini.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- KOLOM KANAN: LAYANAN LAINNYA / NON-PST (Kunjungan, PPID, dll) -->
            <!-- ============================================================== -->
            <div class="col-lg-6 mb-4">
                <div class="category-header cat-other">
                    <div class="cat-title-wrap">
                        <div class="cat-icon-circle">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <h3 class="cat-title">Layanan Lainnya / Non-PST</h3>
                            <p class="cat-desc">Kunjungan dinas, PPID, pengaduan, dan urusan administrasi lainnya</p>
                        </div>
                    </div>
                    <span class="cat-count-badge" title="Total Layanan Lainnya">
                        <?= count($layanan_lain); ?> Layanan
                    </span>
                </div>

                <div class="category-body">
                    <?php if (!empty($layanan_lain)) { ?>
                        <?php foreach ($layanan_lain as $p) { 
                            $meta = get_pelayanan_meta($p['nama_pelayanan']);
                        ?>
                            <a href="registrasi.php?id_pelayanan=<?= (int)$p['id_pelayanan']; ?>" 
                               class="service-card service-card-other" 
                               title="Pilih <?= htmlspecialchars($p['nama_pelayanan']); ?>">
                                <div class="service-card-content">
                                    <div class="service-info">
                                        <h4 class="service-name"><?= htmlspecialchars($p['nama_pelayanan']); ?></h4>
                                        <p class="service-desc"><?= $meta['desc']; ?></p>
                                    </div>
                                </div>
                                <div class="service-btn-action">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="empty-service-box">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p class="m-0">Belum ada data layanan non-PST aktif saat ini.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer-public">
        <div class="container">
            <p class="mb-1 font-weight-bold">
                SIRITA &bull; Sistem Informasi Registrasi Tamu dan Antrian
            </p>
            <p class="mb-0" style="font-size: 12px;">
                &copy; <?= date('Y'); ?> <strong>Badan Pusat Statistik Kabupaten Bangka</strong>. Seluruh Hak Cipta Dilindungi.
            </p>
        </div>
    </footer>

    <!-- LIVE CLOCK SCRIPT -->
    <script>
        function updateLiveClock() {
            const el = document.getElementById('liveClockTeks');
            if (!el) return;
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            const dayName = days[now.getDay()];
            const date = String(now.getDate()).padStart(2, '0');
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            el.innerHTML = `${dayName}, ${date} ${monthName} ${year} &bull; ${hours}:${minutes}:${seconds} WIB`;
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();
    </script>

    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>