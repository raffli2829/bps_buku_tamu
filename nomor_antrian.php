<?php
include 'config/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: registrasi.php");
    exit;
}

// Gunakan Prepared Statement untuk keamanan SQL Injection
$stmt = $conn->prepare("
    SELECT
        pengunjung.id_pengunjung,
        pengunjung.nama,
        pengunjung.nomor_parkir,
        pengunjung.no_hp,
        pelayanan.id_pelayanan,
        pelayanan.nama_pelayanan,
        antrian.id_antrian,
        antrian.nomor_antrian,
        antrian.status,
        antrian.waktu_ambil
    FROM pengunjung
    JOIN antrian ON pengunjung.id_pengunjung = antrian.id_pengunjung
    JOIN pelayanan ON pelayanan.id_pelayanan = antrian.id_pelayanan
    WHERE pengunjung.id_pengunjung = ?
    ORDER BY antrian.id_antrian DESC
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$stmt->close();

if (!$data) {
    die("Data antrian tidak ditemukan untuk ID = " . htmlspecialchars($id));
}

$id_antrian = (int)$data['id_antrian'];
$id_pelayanan = (int)$data['id_pelayanan'];

// Cek apakah sudah pernah isi survei
$stmt_survei = $conn->prepare("SELECT id_survei FROM survei_kepuasan WHERE id_antrian = ? LIMIT 1");
$stmt_survei->bind_param("i", $id_antrian);
$stmt_survei->execute();
$res_survei = $stmt_survei->get_result();
$sudah_survei = $res_survei->fetch_assoc() ? true : false;
$stmt_survei->close();

// Deteksi URL aktif untuk QR Code agar saat di-scan HP otomatis membuka tiket & survei
if (!empty($BASE_PUBLIC_URL)) {
    // 1. Prioritaskan URL Publik Online dari config/koneksi.php jika diisi
    $clean_base = rtrim($BASE_PUBLIC_URL, '/');
    $live_ticket_url = $clean_base . '/nomor_antrian.php?id=' . (int)$data['id_pengunjung'];
} else {
    // 2. Deteksi otomatis dari header HTTP / Domain / Tunnel Publik
    $protocol = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
        ? $_SERVER['HTTP_X_FORWARDED_PROTO'] . "://"
        : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://");

    $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $port = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;

    // Cek apakah host merupakan alamat lokal (localhost / loopback)
    $parsedHost = parse_url('http://' . $host, PHP_URL_HOST);
    if (in_array($parsedHost, ['localhost', '127.0.0.1', '::1'])) {
        $lan_ip = null;
        
        // Coba deteksi IP Wi-Fi/LAN via ipconfig (Windows)
        $ipconfig_output = @shell_exec('ipconfig');
        if ($ipconfig_output && preg_match_all('/(?:IPv4 Address|Alamat IPv4)[^\r\n:]*:\s*(192\.168\.\d+\.\d+|10\.\d+\.\d+\.\d+)/i', $ipconfig_output, $ip_matches)) {
            if (!empty($ip_matches[1][0])) {
                $lan_ip = $ip_matches[1][0];
            }
        }

        // Fallback via socket UDP jika ipconfig tidak tersedia
        if (empty($lan_ip) && function_exists('socket_create')) {
            $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            if ($sock) {
                if (@socket_connect($sock, "8.8.8.8", 53)) {
                    @socket_getsockname($sock, $lan_ip);
                }
                @socket_close($sock);
            }
        }

        // Fallback via hostname
        if (empty($lan_ip)) {
            $candidate_ip = @gethostbyname(gethostname());
            if ($candidate_ip && $candidate_ip !== '127.0.0.1' && filter_var($candidate_ip, FILTER_VALIDATE_IP)) {
                $lan_ip = $candidate_ip;
            }
        }

        if (!empty($lan_ip)) {
            $portSuffix = ($port != 80 && $port != 443 && !str_contains($host, ':')) ? (':' . $port) : '';
            $host = $lan_ip . $portSuffix;
        }
    }

    $script_dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $live_ticket_url = $protocol . $host . $script_dir . '/nomor_antrian.php?id=' . (int)$data['id_pengunjung'];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<title>Nomor Antrian: <?= htmlspecialchars($data['nomor_antrian']); ?> | SIRITA BPS</title>

<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>

* {
    font-family: 'Poppins', sans-serif;
    box-sizing: border-box;
}

body {
    background:
        radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 35%),
        radial-gradient(circle at bottom left, rgba(255,255,255,.12), transparent 30%),
        linear-gradient(135deg, #0d6efd 0%, #16a34a 100%);
    min-height: 100vh;
    min-height: 100dvh;
    margin: 0;
    padding: 20px 14px 40px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.ticket-wrapper {
    width: 100%;
    max-width: 500px;
    margin: auto;
}

.card-ticket {
    background: #ffffff;
    border: none;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
    transition: all 0.3s ease;
}

.card-header-ticket {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: #ffffff;
    text-align: center;
    padding: 24px 20px 20px;
    position: relative;
}

.logo-bps {
    width: 65px;
    height: auto;
    margin-bottom: 8px;
    filter: drop-shadow(0 3px 6px rgba(0,0,0,0.2));
}

.card-header-ticket h2 {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 2px;
    letter-spacing: 1px;
}

.card-header-ticket h5 {
    font-size: 13px;
    font-weight: 300;
    margin-bottom: 2px;
    opacity: 0.95;
}

.card-header-ticket h6 {
    font-size: 14px;
    font-weight: 600;
    margin: 0;
    opacity: 0.95;
}

/* Status Antrean Live */
.status-pill-container {
    margin: -14px 20px 0;
    position: relative;
    z-index: 3;
}

.status-pill {
    padding: 10px 16px;
    border-radius: 14px;
    text-align: center;
    font-weight: 700;
    font-size: 13.5px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.status-menunggu {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
}

.status-dipanggil {
    background: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
    animation: pulse-glow 1.5s infinite;
}

.status-selesai {
    background: #cfe2ff;
    color: #084298;
    border: 1px solid #b6d4fe;
}

@keyframes pulse-glow {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.4); }
    70% { transform: scale(1.02); box-shadow: 0 0 0 10px rgba(25, 135, 84, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
}

.card-body-ticket {
    padding: 24px 22px 20px;
    text-align: center;
}

.ticket-label {
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 4px;
}

.nomor-antrian-big {
    font-size: 78px;
    font-weight: 800;
    color: #0d6efd;
    line-height: 1;
    margin: 6px 0 12px;
    letter-spacing: 2px;
    text-shadow: 0 4px 10px rgba(13, 110, 253, 0.15);
}

.nama-layanan-badge {
    display: inline-block;
    background: #f1f5f9;
    color: #1e293b;
    padding: 8px 18px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
}

/* Live Queue Tracker Box */
.tracker-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 14px 16px;
    margin-bottom: 20px;
}

.tracker-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    text-align: center;
}

.tracker-item {
    background: #ffffff;
    border-radius: 12px;
    padding: 10px 8px;
    border: 1px solid #edf2f7;
}

.tracker-title {
    font-size: 11.5px;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 4px;
}

.tracker-val {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
}

/* Detail Info Tamu */
.guest-info-table {
    width: 100%;
    margin-bottom: 20px;
    font-size: 13.5px;
    border-top: 1px dashed #e2e8f0;
    border-bottom: 1px dashed #e2e8f0;
    padding: 10px 0;
}

.guest-info-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 4px;
    color: #475569;
}

.guest-info-row strong {
    color: #0f172a;
}

/* QR Code: Disembunyikan di layar normal agar UI mobile bersih, HANYA dimunculkan saat Print Tiket / Karcis Fisik */
.qr-container {
    margin: 15px 0 10px;
    display: none;
    flex-direction: column;
    align-items: center;
}

.qr-code-img {
    width: 110px;
    height: 110px;
    padding: 6px;
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.qr-text {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 6px;
}

/* Action Buttons */
.footer-actions {
    background: #f8fafc;
    padding: 18px 20px;
    border-top: 1px solid #edf2f7;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-action-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.btn-custom {
    height: 44px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s ease;
    border: none;
    text-decoration: none;
}

.btn-download-img {
    background: #0d6efd;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
}

.btn-download-img:hover {
    background: #0b5ed7;
    color: #ffffff;
    transform: translateY(-1px);
}

.btn-print {
    background: #198754;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.25);
}

.btn-print:hover {
    background: #157347;
    color: #ffffff;
    transform: translateY(-1px);
}



.btn-skm-survey {
    background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);
    color: #ffffff;
    height: 46px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(253, 126, 20, 0.35);
    transition: all 0.2s ease;
}

.btn-skm-survey:hover {
    background: linear-gradient(135deg, #e8590c 0%, #d9480f 100%);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(253, 126, 20, 0.45);
}

.btn-home {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.btn-home:hover {
    background: #e2e8f0;
    color: #1e293b;
}

/* Sound notification icon */
.sound-toggle {
    font-size: 11px;
    color: #64748b;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 4px;
}

@media print {
    body {
        background: none;
        padding: 0;
    }
    .status-pill-container,
    .footer-actions,
    .sound-toggle,
    .tracker-box,
    .qr-toggle-wrap,
    .qr-copy-btn {
        display: none !important;
    }
    /* QR Code WAJIB tampil saat dicetak ke kertas */
    .qr-container {
        display: flex !important;
        margin: 20px 0 10px;
        page-break-inside: avoid;
    }
    .qr-code-img {
        width: 130px;
        height: 130px;
        border: 1px solid #333;
        box-shadow: none;
    }
    .qr-text {
        color: #000 !important;
        font-size: 11.5px;
        font-weight: 600;
    }
    .card-ticket {
        box-shadow: none;
        border: 1px dashed #333;
        width: 100%;
        max-width: 100%;
        border-radius: 0;
    }
}

</style>

</head>

<body>

<div class="ticket-wrapper">

    <div class="card card-ticket" id="ticketCardToCapture">

        <!-- Header Tiket -->
        <div class="card-header-ticket">
            <img src="assets/img/logo_bps.png" alt="Logo BPS" class="logo-bps">
            <h2>SIRITA</h2>
            <h5>Sistem Informasi Registrasi Tamu dan Antrian</h5>
            <h6>BPS Kabupaten Bangka</h6>
        </div>

        <!-- Status Antrean Real-Time -->
        <div class="status-pill-container" id="statusContainer">
            <?php if($data['status'] === 'Dipanggil'){ ?>
                <div class="status-pill status-dipanggil" id="statusBadge">
                    <i class="fas fa-bullhorn fa-bounce"></i>
                    <span>NOMOR ANDA SEDANG DIPANGGIL!</span>
                </div>
            <?php } elseif($data['status'] === 'Selesai'){ ?>
                <div class="status-pill status-selesai" id="statusBadge">
                    <i class="fas fa-check-circle"></i>
                    <span>Pelayanan Telah Selesai</span>
                </div>
            <?php } else { ?>
                <div class="status-pill status-menunggu" id="statusBadge">
                    <i class="fas fa-clock"></i>
                    <span id="statusText">Menunggu Giliran Panggilan</span>
                </div>
            <?php } ?>
        </div>

        <div class="card-body-ticket">

            <div class="ticket-label">Nomor Antrian Anda</div>

            <div class="nomor-antrian-big" id="nomorAntrianText">
                <?= htmlspecialchars($data['nomor_antrian']); ?>
            </div>

            <div class="nama-layanan-badge">
                <i class="fas fa-layer-group me-1 text-primary"></i>
                <?= htmlspecialchars($data['nama_pelayanan']); ?>
            </div>

            <!-- Live Tracker Box -->
            <div class="tracker-box">
                <div class="tracker-grid">
                    <div class="tracker-item">
                        <div class="tracker-title">Sedang Dilayani di Loket</div>
                        <div class="tracker-val text-primary" id="valNowServing">-</div>
                    </div>
                    <div class="tracker-item">
                        <div class="tracker-title">Antrean di Depan Anda</div>
                        <div class="tracker-val text-warning" id="valSisaAntrean">-</div>
                    </div>
                </div>
                <div class="mt-2 text-center">
                    <span class="text-muted" style="font-size: 11px;">
                        <i class="fas fa-sync-alt fa-spin me-1"></i> Status live diperbarui otomatis
                    </span>
                </div>
            </div>

            <!-- Detail Pengunjung -->
            <div class="guest-info-table">
                <div class="guest-info-row">
                    <span>Nama Pengunjung</span>
                    <strong><?= htmlspecialchars($data['nama']); ?></strong>
                </div>
                <div class="guest-info-row">
                    <span>Nomor Parkir</span>
                    <strong><?= htmlspecialchars($data['nomor_parkir'] ?: '-'); ?></strong>
                </div>
                <div class="guest-info-row">
                    <span>Waktu Pendaftaran</span>
                    <strong><?= date('d-m-Y H:i', strtotime($data['waktu_ambil'])); ?> WIB</strong>
                </div>
            </div>

            <!-- Tombol Opsional Toggle QR Karcis di Layar -->
            <div class="text-center qr-toggle-wrap mb-2">
                <button type="button" class="btn btn-sm btn-link text-decoration-none text-muted py-0" style="font-size: 11.5px;" onclick="toggleQrCode()">
                    <i class="fas fa-qrcode mr-1 text-primary"></i> <span id="toggleQrText">Tampilkan QR Code Karcis</span>
                </button>
            </div>

            <!-- QR Code Akses Cepat & Verifikasi Antrean (Otomatis saat Print / Karcis Fisik) -->
            <div class="qr-container" id="qrContainerBox">
                <a href="<?= htmlspecialchars($live_ticket_url); ?>" target="_blank" title="Klik untuk membuka tautan tiket online" style="text-decoration:none; color:inherit; display:inline-block;">
                    <img 
                        src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=6&data=<?= urlencode($live_ticket_url); ?>" 
                        alt="QR Code Tiket" 
                        class="qr-code-img"
                        id="qrCodeImg"
                        crossorigin="anonymous">
                </a>
                <div class="qr-text font-weight-bold" style="color: #0d6efd; margin-top: 6px; font-weight:700;">
                    <i class="fas fa-qrcode mr-1"></i> Scan untuk Membuka Tiket Live di HP
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    Gunakan kamera HP atau Google Lens untuk memantau status &amp; isi survei
                </div>
                <div class="mt-2 qr-copy-btn">
                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-3" style="font-size: 11.5px; border-radius: 20px;" onclick="salinLinkTiket()">
                        <i class="fas fa-copy mr-1" id="iconSalin"></i> <span id="teksSalin">Salin Link Tiket</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- Tombol Aksi -->
        <div class="footer-actions">

            <!-- Tombol Survei Kepuasan (Muncul selalu / diutamakan saat Selesai) -->
            <a href="survei.php?id_antrian=<?= $id_antrian; ?>" class="btn-skm-survey" id="btnSkmSurvey">
                <i class="fas fa-star"></i>
                <?= $sudah_survei ? 'Lihat / Perbarui Survei Kepuasan' : 'Beri Penilaian &amp; Survei Kepuasan'; ?>
            </a>

            <div class="btn-action-group">
                <button type="button" class="btn-custom btn-download-img" onclick="downloadTiketCanvas()">
                    <i class="fas fa-file-image"></i> Simpan Gambar
                </button>
                <button type="button" class="btn-custom btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak Tiket
                </button>
            </div>



            <a href="registrasi.php" class="btn-custom btn-home">
                <i class="fas fa-user-plus"></i> Registrasi Tamu Baru
            </a>

        </div>

    </div>

</div>

<!-- Canvas tersembunyi untuk menghasilkan gambar tiket resolusi tinggi -->
<canvas id="ticketCanvas" style="display:none;"></canvas>

<script>
const ID_PENGUNJUNG = <?= (int)$data['id_pengunjung']; ?>;
const ID_ANTRIAN = <?= (int)$id_antrian; ?>;
const NOMOR_ANTRIAN = <?= json_encode($data['nomor_antrian']); ?>;
const NAMA_TAMU = <?= json_encode($data['nama']); ?>;
const NAMA_LAYANAN = <?= json_encode($data['nama_pelayanan']); ?>;
const NOMOR_PARKIR = <?= json_encode($data['nomor_parkir'] ?: '-'); ?>;
const WAKTU_DAFTAR = <?= json_encode(date('d-m-Y H:i', strtotime($data['waktu_ambil']))); ?>;
const LIVE_TICKET_URL = <?= json_encode($live_ticket_url); ?>;

// Fungsi Salin Link Tiket
function salinLinkTiket() {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(LIVE_TICKET_URL).then(tampilkanSuksesSalin).catch(fallbackSalin);
    } else {
        fallbackSalin();
    }
}

function fallbackSalin() {
    const input = document.createElement('input');
    input.value = LIVE_TICKET_URL;
    document.body.appendChild(input);
    input.select();
    try {
        document.execCommand('copy');
        tampilkanSuksesSalin();
    } catch (err) {
        prompt("Salin tautan tiket Anda:", LIVE_TICKET_URL);
    }
    document.body.removeChild(input);
}

function tampilkanSuksesSalin() {
    const icon = document.getElementById('iconSalin');
    const teks = document.getElementById('teksSalin');
    if (icon && teks) {
        icon.className = 'fas fa-check mr-1 text-success';
        teks.innerText = 'Link Tersalin!';
        setTimeout(() => {
            icon.className = 'fas fa-copy mr-1';
            teks.innerText = 'Salin Link Tiket';
        }, 2500);
    }
}

// Fungsi Buka/Tutup QR Karcis di Layar
function toggleQrCode() {
    const qrBox = document.getElementById('qrContainerBox');
    const qrText = document.getElementById('toggleQrText');
    if (qrBox) {
        const isShown = window.getComputedStyle(qrBox).display === 'flex';
        if (isShown) {
            qrBox.style.display = 'none';
            if (qrText) qrText.innerText = 'Tampilkan QR Code Karcis';
        } else {
            qrBox.style.display = 'flex';
            if (qrText) qrText.innerText = 'Sembunyikan QR Code Karcis';
        }
    }
}

let previousStatus = <?= json_encode($data['status']); ?>;
let audioPlayed = false;

// Audio Chime synthesizer menggunakan Web Audio API (tidak butuh file audio eksternal)
function playChime() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const now = audioCtx.currentTime;
        
        // Nada 1 (C5)
        const osc1 = audioCtx.createOscillator();
        const gain1 = audioCtx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(523.25, now);
        gain1.gain.setValueAtTime(0.25, now);
        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
        osc1.connect(gain1);
        gain1.connect(audioCtx.destination);
        osc1.start(now);
        osc1.stop(now + 0.6);

        // Nada 2 (G5)
        const osc2 = audioCtx.createOscillator();
        const gain2 = audioCtx.createGain();
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(783.99, now + 0.25);
        gain2.gain.setValueAtTime(0.3, now + 0.25);
        gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.9);
        osc2.connect(gain2);
        gain2.connect(audioCtx.destination);
        osc2.start(now + 0.25);
        osc2.stop(now + 0.9);
    } catch(e) {
        console.log("Audio not allowed yet:", e);
    }
}

// Live Polling Status Antrean
async function pollStatus() {
    try {
        const res = await fetch('get_status_antrian.php?id=' + ID_PENGUNJUNG + '&t=' + Date.now());
        if (!res.ok) return;
        const json = await res.json();

        if (json.success) {
            // Update counter live
            const elServing = document.getElementById('valNowServing');
            const elSisa = document.getElementById('valSisaAntrean');
            if (elServing) elServing.innerText = json.nomor_aktif || '-';
            if (elSisa) {
                if (json.status === 'Menunggu') {
                    elSisa.innerText = json.sisa_antrean + ' orang';
                } else if (json.status === 'Dipanggil') {
                    elSisa.innerText = 'Giliran Anda!';
                } else {
                    elSisa.innerText = 'Selesai';
                }
            }

            // Status Badge update
            const container = document.getElementById('statusContainer');
            if (json.status !== previousStatus) {
                previousStatus = json.status;

                if (json.status === 'Dipanggil') {
                    container.innerHTML = `
                        <div class="status-pill status-dipanggil">
                            <i class="fas fa-bullhorn fa-bounce"></i>
                            <span>NOMOR ANDA SEDANG DIPANGGIL!</span>
                        </div>
                    `;
                    if (!audioPlayed) {
                        playChime();
                        if ('vibrate' in navigator) navigator.vibrate([200, 100, 200, 100, 400]);
                        audioPlayed = true;
                    }
                } else if (json.status === 'Selesai') {
                    container.innerHTML = `
                        <div class="status-pill status-selesai">
                            <i class="fas fa-check-circle"></i>
                            <span>Pelayanan Telah Selesai</span>
                        </div>
                    `;
                    // Sorot tombol survei
                    const btnSkm = document.getElementById('btnSkmSurvey');
                    if (btnSkm) {
                        btnSkm.classList.add('animate-pulse');
                    }
                }
            }
        }
    } catch (e) {
        console.error("Gagal polling status antrean:", e);
    }
}

// Jalankan polling setiap 4 detik
setInterval(pollStatus, 4000);
pollStatus(); // Jalankan sekali saat load

// Fungsi untuk menggambar tiket antrean digital resolusi tinggi menggunakan HTML5 Canvas
function downloadTiketCanvas() {
    const canvas = document.getElementById('ticketCanvas');
    const ctx = canvas.getContext('2d');

    const width = 600;
    const height = 1040;
    canvas.width = width;
    canvas.height = height;

    // Background kartu putih
    ctx.fillStyle = '#ffffff';
    ctx.beginPath();
    ctx.roundRect(0, 0, width, height, 32);
    ctx.fill();

    // Header Gradient Biru
    const grad = ctx.createLinearGradient(0, 0, width, 240);
    grad.addColorStop(0, '#0d6efd');
    grad.addColorStop(1, '#0a58ca');
    ctx.fillStyle = grad;
    ctx.beginPath();
    ctx.roundRect(0, 0, width, 220, [32, 32, 0, 0]);
    ctx.fill();

    // Teks Header
    ctx.textAlign = 'center';
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 36px Poppins, sans-serif';
    ctx.fillText('SIRITA', width / 2, 85);

    ctx.font = '300 18px Poppins, sans-serif';
    ctx.fillStyle = 'rgba(255, 255, 255, 0.9)';
    ctx.fillText('Sistem Informasi Registrasi Tamu dan Antrian', width / 2, 125);

    ctx.font = '600 20px Poppins, sans-serif';
    ctx.fillStyle = '#ffffff';
    ctx.fillText('BPS Kabupaten Bangka', width / 2, 165);

    // Label Nomor Antrian
    ctx.font = '600 16px Poppins, sans-serif';
    ctx.fillStyle = '#6c757d';
    ctx.fillText('NOMOR ANTREAN ANDA', width / 2, 280);

    // Nomor Antrian Besar
    ctx.font = '800 110px Poppins, sans-serif';
    ctx.fillStyle = '#0d6efd';
    ctx.fillText(NOMOR_ANTRIAN, width / 2, 390);

    // Pill Nama Layanan
    ctx.fillStyle = '#f1f5f9';
    ctx.beginPath();
    ctx.roundRect(width / 2 - 220, 420, 440, 50, 16);
    ctx.fill();

    ctx.font = 'bold 19px Poppins, sans-serif';
    ctx.fillStyle = '#0f172a';
    ctx.fillText(NAMA_LAYANAN, width / 2, 452);

    // Garis Pemisah Dashed
    ctx.strokeStyle = '#cbd5e1';
    ctx.lineWidth = 2;
    ctx.setLineDash([8, 8]);
    ctx.beginPath();
    ctx.moveTo(40, 510);
    ctx.lineTo(width - 40, 510);
    ctx.stroke();
    ctx.setLineDash([]);

    // Detail Pengunjung
    ctx.textAlign = 'left';
    ctx.font = '500 18px Poppins, sans-serif';
    ctx.fillStyle = '#64748b';
    ctx.fillText('Nama Pengunjung', 50, 560);
    ctx.fillText('Nomor Parkir', 50, 605);
    ctx.fillText('Waktu Pendaftaran', 50, 650);

    ctx.textAlign = 'right';
    ctx.font = '700 18px Poppins, sans-serif';
    ctx.fillStyle = '#0f172a';
    ctx.fillText(NAMA_TAMU, width - 50, 560);
    ctx.fillText(NOMOR_PARKIR, width - 50, 605);
    ctx.fillText(WAKTU_DAFTAR + ' WIB', width - 50, 650);

    // QR Code Gambar
    const qrImg = document.getElementById('qrCodeImg');
    if (qrImg && qrImg.complete && qrImg.naturalWidth > 0) {
        try {
            ctx.fillStyle = '#f8fafc';
            ctx.beginPath();
            ctx.roundRect(width / 2 - 80, 685, 160, 160, 16);
            ctx.fill();
            ctx.strokeStyle = '#e2e8f0';
            ctx.lineWidth = 1;
            ctx.stroke();

            ctx.drawImage(qrImg, width / 2 - 70, 695, 140, 140);

            ctx.textAlign = 'center';
            ctx.font = '600 13px Poppins, sans-serif';
            ctx.fillStyle = '#0d6efd';
            ctx.fillText('Scan QR Code untuk Membuka Tiket Live di HP', width / 2, 870);
        } catch (e) {
            console.log("Canvas QR skipped", e);
        }
    }

    // Footer Struk
    ctx.fillStyle = '#f8fafc';
    ctx.beginPath();
    ctx.roundRect(0, 900, width, 140, [0, 0, 32, 32]);
    ctx.fill();

    ctx.textAlign = 'center';
    ctx.font = '600 15px Poppins, sans-serif';
    ctx.fillStyle = '#0f172a';
    ctx.fillText('Simpan struk digital ini sebagai bukti antrean Anda.', width / 2, 945);

    ctx.font = '400 13px Poppins, sans-serif';
    ctx.fillStyle = '#64748b';
    ctx.fillText('Kunjungi sirita.bps.go.id • Terima kasih atas kunjungan Anda', width / 2, 985);

    // Unduh File
    const link = document.createElement('a');
    link.download = 'Nomor_Antrian_SIRITA_' + NOMOR_ANTRIAN + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}
</script>

<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>

