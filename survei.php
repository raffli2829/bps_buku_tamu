<?php
include 'config/koneksi.php';

$id_antrian = isset($_GET['id_antrian']) ? (int)$_GET['id_antrian'] : 0;
$id_pengunjung = isset($_GET['id_pengunjung']) ? (int)$_GET['id_pengunjung'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($id_antrian <= 0 && $id_pengunjung <= 0) {
    header("Location: registrasi.php");
    exit;
}

// Ambil data antrian dan pengunjung
if ($id_antrian > 0) {
    $stmt = $conn->prepare("
        SELECT a.id_antrian, a.id_pengunjung, a.nomor_antrian, a.status, p.nama, pl.nama_pelayanan
        FROM antrian a
        JOIN pengunjung p ON a.id_pengunjung = p.id_pengunjung
        JOIN pelayanan pl ON a.id_pelayanan = pl.id_pelayanan
        WHERE a.id_antrian = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $id_antrian);
} else {
    $stmt = $conn->prepare("
        SELECT a.id_antrian, a.id_pengunjung, a.nomor_antrian, a.status, p.nama, pl.nama_pelayanan
        FROM antrian a
        JOIN pengunjung p ON a.id_pengunjung = p.id_pengunjung
        JOIN pelayanan pl ON a.id_pelayanan = pl.id_pelayanan
        WHERE a.id_pengunjung = ?
        ORDER BY a.id_antrian DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $id_pengunjung);
}

$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$stmt->close();

if (!$data) {
    die("Data pelayanan antrian tidak ditemukan.");
}

$id_antrian = (int)$data['id_antrian'];
$id_pengunjung = (int)$data['id_pengunjung'];

// Cek apakah pengunjung sudah pernah mengisi survei untuk antrian ini
$stmt_cek = $conn->prepare("SELECT * FROM survei_kepuasan WHERE id_antrian = ? LIMIT 1");
$stmt_cek->bind_param("i", $id_antrian);
$stmt_cek->execute();
$res_cek = $stmt_cek->get_result();
$survei_ada = $res_cek->fetch_assoc();
$stmt_cek->close();

$sukses = isset($_GET['sukses']) && $_GET['sukses'] == '1';
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<title>Survei Kepuasan Masyarakat (SKM) | BPS Kabupaten Bangka</title>

<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
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
    padding: 24px 16px 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.survei-container {
    width: 100%;
    max-width: 540px;
    margin: auto;
    position: relative;
    z-index: 2;
}

.card-survei {
    border: none;
    border-radius: 24px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 18px 45px rgba(0, 0, 0, 0.22);
}

.card-header-survei {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: #ffffff;
    padding: 24px 20px 20px;
    text-align: center;
}

.card-header-survei .logo-img {
    width: 65px;
    height: auto;
    margin-bottom: 10px;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.2));
}

.card-header-survei h2 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 2px;
}

.card-header-survei p {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
}

.ticket-pill {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.35);
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    margin-top: 10px;
}

.card-body-survei {
    padding: 26px 24px 30px;
}

/* Rating Bintang Interaktif */
.star-rating-box {
    text-align: center;
    margin-bottom: 24px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 16px;
    border: 1px solid #e9ecef;
}

.star-rating {
    display: inline-flex;
    flex-direction: row-reverse;
    justify-content: center;
    gap: 8px;
    font-size: 38px;
    cursor: pointer;
}

.star-rating input {
    display: none;
}

.star-rating label {
    color: #dee2e6;
    transition: color 0.2s ease, transform 0.2s ease;
    cursor: pointer;
    margin: 0;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #ffc107;
    transform: scale(1.1);
}

.rating-text {
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    margin-top: 8px;
    min-height: 22px;
}

/* Pertanyaan Indikator Pelayanan */
.question-group {
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px dashed #e9ecef;
}

.question-group:last-of-type {
    border-bottom: none;
    padding-bottom: 0;
}

.question-label {
    font-size: 13.5px;
    font-weight: 600;
    color: #343a40;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.radio-pill-group {
    display: flex;
    gap: 6px;
    justify-content: space-between;
}

.radio-pill-item {
    flex: 1;
}

.radio-pill-item input {
    display: none;
}

.radio-pill-item label {
    display: block;
    text-align: center;
    padding: 8px 4px;
    background: #f1f3f5;
    border: 1px solid #ced4da;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    color: #495057;
    cursor: pointer;
    transition: all 0.2s ease;
    margin: 0;
}

.radio-pill-item input:checked + label {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #ffffff;
    box-shadow: 0 3px 8px rgba(13, 110, 253, 0.3);
}

/* Textarea Saran */
.textarea-saran {
    width: 100%;
    border: 1px solid #ced4da;
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 14px;
    outline: none;
    resize: vertical;
    min-height: 90px;
    transition: border-color 0.2s ease;
}

.textarea-saran:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
}

.btn-kirim-survei {
    height: 48px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    background: linear-gradient(135deg, #198754 0%, #157347 100%);
    color: #ffffff;
    border: none;
    box-shadow: 0 5px 15px rgba(25, 135, 84, 0.35);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-kirim-survei:hover {
    background: linear-gradient(135deg, #157347 0%, #0f5132 100%);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 7px 18px rgba(25, 135, 84, 0.45);
}

.btn-kembali-antrian {
    display: block;
    text-align: center;
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    text-decoration: none;
    margin-top: 14px;
}

.btn-kembali-antrian:hover {
    color: #0d6efd;
    text-decoration: underline;
}

/* Layar Sukses / Sudah Pernah Isi */
.success-box {
    text-align: center;
    padding: 20px 10px;
}

.success-icon {
    font-size: 65px;
    color: #198754;
    margin-bottom: 15px;
    animation: bounce 0.6s ease;
}

@keyframes bounce {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.15); }
    100% { transform: scale(1); opacity: 1; }
}

@media (max-width: 576px) {
    .card-body-survei {
        padding: 20px 16px;
    }
    .star-rating {
        font-size: 34px;
        gap: 6px;
    }
    .radio-pill-item label {
        font-size: 11px;
        padding: 7px 2px;
    }
}

</style>

</head>

<body>

<div class="survei-container">

    <div class="card card-survei">

        <div class="card-header-survei">
            <img src="assets/img/logo_bps.png" alt="Logo BPS" class="logo-img">
            <h2>SURVEI KEPUASAN MASYARAKAT</h2>
            <p>BPS Kabupaten Bangka</p>
            <div class="ticket-pill">
                <i class="fas fa-ticket-alt me-1"></i> Antrean: <b><?= htmlspecialchars($data['nomor_antrian']); ?></b> (<?= htmlspecialchars($data['nama']); ?>)
            </div>
        </div>

        <div class="card-body-survei">

            <?php if($survei_ada || $sukses){ ?>

            <!-- Tampilan jika sudah pernah mengisi / baru saja kirim -->
            <div class="success-box">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Terima Kasih Banyak!</h4>
                <p class="text-secondary small mb-4">
                    Penilaian dan masukan Anda sangat berharga dalam mendukung peningkatan mutu Pelayanan Statistik Terpadu di <b>BPS Kabupaten Bangka</b>.
                </p>

                <?php if($survei_ada){ ?>
                <div class="alert alert-light border p-3 rounded-4 mb-4 text-start">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small">Rating yang Diberikan:</span>
                        <div class="text-warning">
                            <?php for($i=1; $i<=5; $i++){ ?>
                                <i class="fas fa-star <?= $i <= $survei_ada['rating_pelayanan'] ? 'text-warning' : 'text-muted opacity-25'; ?>"></i>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if(!empty($survei_ada['saran'])){ ?>
                    <div class="mt-2 text-dark small fst-italic">
                        "<?= htmlspecialchars($survei_ada['saran']); ?>"
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>

                <a href="nomor_antrian.php?id=<?= $id_pengunjung; ?>" class="btn btn-primary w-100 py-2 rounded-3 fw-semibold">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Tiket Antrean
                </a>
            </div>

            <?php } else { ?>

            <!-- Formulir Survei Kepuasan -->
            <form action="proses_survei.php" method="POST">

                <input type="hidden" name="id_antrian" value="<?= $id_antrian; ?>">
                <input type="hidden" name="id_pengunjung" value="<?= $id_pengunjung; ?>">

                <!-- Rating Utama Bintang 1-5 -->
                <div class="star-rating-box">
                    <label class="d-block fw-bold text-dark small mb-2">
                        Bagaimana kepuasan Anda terhadap pelayanan kami secara keseluruhan? <span class="text-danger">*</span>
                    </label>

                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating_pelayanan" value="5" required onclick="updateRatingText(5)">
                        <label for="star5" title="5 Bintang - Sangat Puas"><i class="fas fa-star"></i></label>

                        <input type="radio" id="star4" name="rating_pelayanan" value="4" onclick="updateRatingText(4)">
                        <label for="star4" title="4 Bintang - Puas"><i class="fas fa-star"></i></label>

                        <input type="radio" id="star3" name="rating_pelayanan" value="3" onclick="updateRatingText(3)">
                        <label for="star3" title="3 Bintang - Cukup"><i class="fas fa-star"></i></label>

                        <input type="radio" id="star2" name="rating_pelayanan" value="2" onclick="updateRatingText(2)">
                        <label for="star2" title="2 Bintang - Tidak Puas"><i class="fas fa-star"></i></label>

                        <input type="radio" id="star1" name="rating_pelayanan" value="1" onclick="updateRatingText(1)">
                        <label for="star1" title="1 Bintang - Sangat Tidak Puas"><i class="fas fa-star"></i></label>
                    </div>

                    <div id="ratingText" class="rating-text">Pilih Bintang Penilaian (1 - 5)</div>
                </div>

                <!-- Indikator 1: Keramahan Petugas -->
                <div class="question-group">
                    <div class="question-label">
                        <i class="fas fa-user-friends text-primary"></i>
                        <span>1. Keramahan &amp; Kesopanan Petugas</span>
                    </div>
                    <div class="radio-pill-group">
                        <div class="radio-pill-item">
                            <input type="radio" id="ramah1" name="keramahan_petugas" value="1">
                            <label for="ramah1">Buruk</label>
                        </div>
                        <div class="radio-pill-item">
                            <input type="radio" id="ramah2" name="keramahan_petugas" value="2">
                            <label for="ramah2">Kurang</label>
                        </div>
                        <div class="radio-pill-item">
                            <input type="radio" id="ramah3" name="keramahan_petugas" value="3">
                            <label for="ramah3">Cukup</label>
                        </div>
                        <div class="radio-pill-item">
                            <input type="radio" id="ramah4" name="keramahan_petugas" value="4">
                            <label for="ramah4">Baik</label>
                        </div>
                        <div class="radio-pill-item">
                            <input type="radio" id="ramah5" name="keramahan_petugas" value="5" checked>
                            <label for="ramah5">S. Baik</label>
                        </div>
                    </div>
                </div>

                <!-- Indikator 2: Kecepatan Layanan -->
                <div class="question-group">
                    <div class="question-label">
                        <i class="fas fa-stopwatch text-success"></i>
                        <span>2. Kecepatan &amp; Ketepatan Waktu Pelayanan</span>
                    </div>
                    <div class="radio-pill-group">
                        <div class="radio-pill-item">
                            <input type="radio" id="cepat1" name="kecepatan_layanan" value="1">
                            <label for="cepat1">Buruk</label>
                        </div>
                        <div class="radio-pill-item">
                            <input type="radio" id="cepat2" name="kecepatan_layanan" value="2">
                            <label for="cepat2">Kurang</label>
                        </div>
                        <div class="radio-pill-item">
                            <input type="radio" id="cepat3" name="kecepatan_layanan" value="3">
                            <label for="cepat3">Cukup</label>
                        </div>
                        <div class="radio-pill-item">
                            <input type="radio" id="cepat4" name="kecepatan_layanan" value="4">
                            <label for="cepat4">Cepat</label>
                        </div>
                        <div class="radio-pill-item">
                            <input type="radio" id="cepat5" name="kecepatan_layanan" value="5" checked>
                            <label for="cepat5">S. Cepat</label>
                        </div>
                    </div>
                </div>

                <!-- Indikator 3: Kemudahan Prosedur -->
                <div class="question-group">
                    <div class="question-label">
                        <i class="fas fa-tasks text-info"></i>
                        <span>3. Kemudahan Prosedur &amp; Persyaratan</span>
                    </div>
                    <div class="radio-pill-group">
                        <div class="radio-pill-item">
                            <input type="radio" id="mudah1" name="kemudahan_prosedur" value="1">
                            <label for="mudah1">Sulit</label>
                        </div>
                        <div class="radio-pill-item">
                            <input type="radio" id="mudah2" name="kemudahan_prosedur" value="2">
                            <label for="mudah2">Kurang</label>
                        </div>
                        <div class="radio-pill-item">
                            <input type="radio" id="mudah3" name="kemudahan_prosedur" value="3">
                            <label for="mudah3">Cukup</label>
                        </div>
                        <div class="radio-pill-item">
                            <input type="radio" id="mudah4" name="kemudahan_prosedur" value="4">
                            <label for="mudah4">Mudah</label>
                        </div>
                        <div class="radio-pill-item">
                            <input type="radio" id="mudah5" name="kemudahan_prosedur" value="5" checked>
                            <label for="mudah5">S. Mudah</label>
                        </div>
                    </div>
                </div>

                <!-- Saran & Masukan -->
                <div class="mb-4">
                    <label class="form-label font-weight-bold text-dark small mb-1">
                        Kritik, Saran, atau Masukan untuk BPS Kabupaten Bangka (Opsional):
                    </label>
                    <textarea 
                        name="saran" 
                        class="textarea-saran" 
                        placeholder="Tuliskan pengalaman atau saran Anda agar pelayanan kami semakin prima..."></textarea>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" class="btn btn-kirim-survei w-100">
                    <i class="fas fa-paper-plane"></i> Kirim Penilaian Survei
                </button>

                <a href="nomor_antrian.php?id=<?= $id_pengunjung; ?>" class="btn-kembali-antrian">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Nomor Antrean
                </a>

            </form>

            <?php } ?>

        </div>

    </div>

</div>

<script>
function updateRatingText(val) {
    const el = document.getElementById('ratingText');
    const labels = {
        1: '😞 1 Bintang: Sangat Tidak Puas',
        2: '🙁 2 Bintang: Kurang Puas',
        3: '😐 3 Bintang: Cukup Puas',
        4: '😊 4 Bintang: Puas / Baik',
        5: '🤩 5 Bintang: Sangat Puas / Sangat Baik'
    };
    el.innerHTML = labels[val] || '';
    el.style.color = val >= 4 ? '#198754' : (val === 3 ? '#fd7e14' : '#dc3545');
}
</script>

<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
