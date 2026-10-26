<?php
include 'config/koneksi.php';

$pelayanan = mysqli_query($conn, "SELECT * FROM pelayanan ORDER BY kode ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<title>SIRITA - Registrasi Pengunjung</title>

<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="dist/css/adminlte.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

* {
    font-family: 'Poppins', sans-serif;
    box-sizing: border-box;
}

html, body {
    width: 100%;
    min-height: 100%;
    overflow-x: hidden;
}

body {
    margin: 0;
    min-height: 100vh;
    min-height: 100dvh;
    background:
        radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 35%),
        radial-gradient(circle at bottom left, rgba(255,255,255,.12), transparent 30%),
        linear-gradient(135deg, #0d6efd 0%, #16a34a 100%);
    position: relative;
    padding: 24px 0 40px;
}

body::before {
    content: "";
    position: fixed;
    inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.12) 1px, transparent 1px);
    background-size: 25px 25px;
    opacity: .4;
    pointer-events: none;
    z-index: 0;
}

.main-wrapper {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 16px;
}

/* Tombol Kembali Responsif - Tidak Bertumpuk dengan Logo */
.btn-back-custom {
    display: inline-flex;
    align-items: center;
    background-color: #ffffff;
    color: #0d6efd;
    padding: 9px 18px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    transition: all 0.2s ease;
    margin-bottom: 20px;
}

.btn-back-custom:hover {
    background-color: #f8f9fa;
    color: #0a58ca;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.18);
}

.left-panel {
    color: white;
    padding: 10px 24px 24px;
}

.logo-main {
    width: 95px;
    height: auto;
    filter: drop-shadow(0 4px 10px rgba(0,0,0,0.18));
}

.brand-title {
    font-size: 40px;
    font-weight: 700;
    letter-spacing: 1px;
    margin-bottom: 4px;
    text-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.brand-subtitle {
    font-size: 15px;
    font-weight: 300;
    line-height: 1.4;
    opacity: 0.95;
    margin-bottom: 6px;
}

.brand-agency {
    font-size: 17px;
    font-weight: 600;
    letter-spacing: 0.5px;
    opacity: 0.95;
}

/* Box Informasi Petunjuk - Bebas dari Flex Row AdminLTE */
.guide-card {
    display: block !important;
    margin-top: 24px;
    background: rgba(255, 255, 255, 0.16);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    padding: 20px;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.28);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.guide-card-header {
    display: flex;
    align-items: center;
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

.guide-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 10px;
    font-size: 13.5px;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.95);
}

.guide-item:last-child {
    margin-bottom: 0;
}

.guide-item i {
    font-size: 15px;
    margin-top: 3px;
    margin-right: 10px;
    flex-shrink: 0;
}

/* Card Registrasi Form */
.card-regis {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    background: #ffffff;
}

.card-regis .card-header {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
    color: white;
    padding: 20px 24px;
    border-bottom: none;
}

.card-regis .card-header h3 {
    font-size: 20px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-regis .card-header p {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.85);
    margin: 4px 0 0 0;
}

.card-regis .card-body {
    padding: 26px 24px;
}

/* Input group terpadu & modern */
.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 6px;
    display: block;
}

.input-box-group {
    display: flex;
    align-items: center;
    border: 1px solid #ced4da;
    border-radius: 10px;
    background: #ffffff;
    transition: all 0.2s ease;
    overflow: hidden;
}

.input-box-group:focus-within {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.18);
}

.input-box-icon {
    width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 14px;
    background: #f8f9fa;
    border-right: 1px solid #e9ecef;
    align-self: stretch;
    flex-shrink: 0;
}

.input-box-field {
    flex: 1;
    min-width: 0;
    height: 46px;
    border: none;
    outline: none;
    padding: 10px 14px;
    font-size: 14px;
    color: #212529;
    background: transparent;
}

.input-box-field:focus {
    box-shadow: none !important;
    border: none !important;
}

.input-box-textarea {
    align-items: flex-start;
}

textarea.input-box-field {
    height: 100px;
    resize: vertical;
}

.btn-daftar {
    background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);
    color: white;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 0.5px;
    border-radius: 12px;
    height: 52px;
    border: none;
    box-shadow: 0 6px 18px rgba(253, 126, 20, 0.35);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-daftar:hover {
    background: linear-gradient(135deg, #198754 0%, #157347 100%);
    color: white;
    box-shadow: 0 8px 22px rgba(25, 135, 84, 0.4);
    transform: translateY(-2px);
}

/* Responsif Mobile */
@media (max-width: 991px) {
    body {
        padding: 14px 0 30px;
    }

    .main-wrapper {
        padding: 0 12px;
    }

    .left-panel {
        text-align: center;
        padding: 6px 8px 20px;
    }

    .btn-back-custom {
        margin-bottom: 14px;
    }

    .logo-main {
        width: 70px;
        margin-bottom: 10px;
    }

    .brand-title {
        font-size: 30px;
    }

    .brand-subtitle {
        font-size: 13.5px;
    }

    .brand-agency {
        font-size: 15px;
    }

    .guide-card {
        margin-top: 16px;
        text-align: left;
        padding: 16px;
    }

    .card-regis .card-header {
        padding: 16px 18px;
    }

    .card-regis .card-header h3 {
        font-size: 18px;
    }

    .card-regis .card-body {
        padding: 20px 16px;
    }
}

</style>

</head>

<body>

<div class="main-wrapper">

    <!-- Tombol Kembali yang aman dan responsif -->
    <a href="login.php" class="btn-back-custom">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Login
    </a>

    <div class="row align-items-start">

        <!-- Panel Kiri: Informasi Brand & Petunjuk -->
        <div class="col-lg-5 left-panel">

            <div class="text-center">
                <img src="assets/img/logo_bps.png" alt="Logo BPS" class="logo-main mb-3">
                <h1 class="brand-title">SIRITA</h1>
                <h4 class="brand-subtitle">
                    Sistem Informasi Registrasi Tamu dan Antrian
                </h4>
                <h5 class="brand-agency">
                    BPS Kabupaten Bangka
                </h5>
            </div>

            <!-- Petunjuk Informasi: Menggunakan guide-card agar tidak bentrok dengan AdminLTE -->
            <div class="guide-card">
                <div class="guide-card-header">
                    <i class="fas fa-info-circle mr-2 text-warning"></i> Informasi Penting
                </div>
                <div class="guide-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <span>Silakan isi data diri Anda dengan benar dan lengkap untuk mendapatkan nomor antrean pelayanan resmi.</span>
                </div>
                <div class="guide-item">
                    <i class="fas fa-parking text-info"></i>
                    <span>Nomor parkir diisi sesuai dengan nomor karcis yang telah diberikan oleh petugas keamanan BPS.</span>
                </div>
            </div>

        </div>

        <!-- Panel Kanan: Form Registrasi -->
        <div class="col-lg-7">

            <div class="card card-regis">

                <div class="card-header">
                    <h3>
                        <i class="fas fa-user-edit"></i>
                        Registrasi Pengunjung
                    </h3>
                    <p>Lengkapi formulir di bawah ini untuk mengambil nomor antrean</p>
                </div>

                <div class="card-body">

                    <form action="proses_registrasi.php" method="POST">

                        <div class="row">

                            <!-- Tanggal Kunjungan -->
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Tanggal Kunjungan</label>
                                <div class="input-box-group">
                                    <div class="input-box-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <input
                                        type="text"
                                        class="input-box-field"
                                        value="<?= date('d-m-Y'); ?>"
                                        readonly>
                                </div>
                            </div>

                            <!-- Nama Lengkap -->
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-box-group">
                                    <div class="input-box-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <input
                                        type="text"
                                        name="nama"
                                        class="input-box-field"
                                        placeholder="Nama lengkap sesuai KTP"
                                        required>
                                </div>
                            </div>

                            <!-- Jenis Kelamin -->
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                <div class="input-box-group">
                                    <div class="input-box-icon">
                                        <i class="fas fa-venus-mars"></i>
                                    </div>
                                    <select name="jenis_kelamin" class="input-box-field" required>
                                        <option value="">-- Pilih Jenis Kelamin --</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Nomor HP -->
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                                <div class="input-box-group">
                                    <div class="input-box-icon">
                                        <i class="fas fa-phone-alt"></i>
                                    </div>
                                    <input
                                        type="text"
                                        name="no_hp"
                                        class="input-box-field"
                                        maxlength="15"
                                        inputmode="numeric"
                                        pattern="[0-9]{10,15}"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                        placeholder="Contoh: 081234567890"
                                        required>
                                </div>
                            </div>

                            <!-- Email Aktif -->
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Email Aktif <span class="text-danger">*</span></label>
                                <div class="input-box-group">
                                    <div class="input-box-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <input
                                        type="email"
                                        name="email"
                                        class="input-box-field"
                                        placeholder="nama@email.com"
                                        required>
                                </div>
                            </div>

                            <!-- Pekerjaan -->
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Pekerjaan <span class="text-danger">*</span></label>
                                <div class="input-box-group">
                                    <div class="input-box-icon">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <input
                                        type="text"
                                        name="pekerjaan"
                                        class="input-box-field"
                                        placeholder="PNS, Swasta, Mahasiswa, dll"
                                        required>
                                </div>
                            </div>

                            <!-- Usia -->
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Usia (Tahun) <span class="text-danger">*</span></label>
                                <div class="input-box-group">
                                    <div class="input-box-icon">
                                        <i class="fas fa-birthday-cake"></i>
                                    </div>
                                    <input
                                        type="number"
                                        name="usia"
                                        min="1"
                                        max="120"
                                        class="input-box-field"
                                        placeholder="Contoh: 28"
                                        required>
                                </div>
                            </div>

                            <!-- Pendidikan Terakhir -->
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Pendidikan Terakhir <span class="text-danger">*</span></label>
                                <div class="input-box-group">
                                    <div class="input-box-icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <select name="pendidikan_terakhir" class="input-box-field" required>
                                        <option value="">-- Pilih Pendidikan --</option>
                                        <option value="SMP sederajat">SMP sederajat</option>
                                        <option value="SMA sederajat">SMA sederajat</option>
                                        <option value="Diploma 1-3">Diploma 1-3</option>
                                        <option value="D4/S1">D4/S1</option>
                                        <option value="S2">S2</option>
                                        <option value="S3">S3</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Asal Instansi -->
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Asal Instansi / Lembaga <span class="text-danger">*</span></label>
                                <div class="input-box-group">
                                    <div class="input-box-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <input
                                        type="text"
                                        name="instansi"
                                        class="input-box-field"
                                        placeholder="Nama instansi/organisasi/umum"
                                        required>
                                </div>
                            </div>

                            <!-- Nomor Parkir -->
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label">Nomor Parkir (Opsional)</label>
                                <div class="input-box-group">
                                    <div class="input-box-icon">
                                        <i class="fas fa-parking"></i>
                                    </div>
                                    <input
                                        type="text"
                                        name="nomor_parkir"
                                        class="input-box-field"
                                        placeholder="Nomor karcis dari satpam">
                                </div>
                            </div>

                            <!-- Pelayanan -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Pilihan Pelayanan <span class="text-danger">*</span></label>
                                <div class="input-box-group">
                                    <div class="input-box-icon">
                                        <i class="fas fa-concierge-bell"></i>
                                    </div>
                                    <select name="id_pelayanan" class="input-box-field" required>
                                        <option value="">-- Pilih Pelayanan yang Dituju --</option>
                                        <?php while($p = mysqli_fetch_assoc($pelayanan)){ ?>
                                        <option value="<?= $p['id_pelayanan']; ?>">
                                            <?= htmlspecialchars($p['kode']); ?> - <?= htmlspecialchars($p['nama_pelayanan']); ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Tujuan -->
                            <div class="col-12 mb-4">
                                <label class="form-label">Keperluan / Tujuan Kunjungan <span class="text-danger">*</span></label>
                                <div class="input-box-group input-box-textarea">
                                    <div class="input-box-icon pt-3">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <textarea
                                        name="tujuan"
                                        class="input-box-field"
                                        rows="3"
                                        placeholder="Jelaskan secara singkat keperluan kunjungan Anda..."
                                        required></textarea>
                                </div>
                            </div>

                            <!-- Tombol Submit -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-daftar w-100">
                                    <i class="fas fa-ticket-alt"></i>
                                    DAFTAR & AMBIL NOMOR ANTRIAN
                                </button>
                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>