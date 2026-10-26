<?php
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: registrasi.php");
    exit;
}

$id_antrian = isset($_POST['id_antrian']) ? (int)$_POST['id_antrian'] : 0;
$id_pengunjung = isset($_POST['id_pengunjung']) ? (int)$_POST['id_pengunjung'] : 0;

if ($id_antrian <= 0 || $id_pengunjung <= 0) {
    header("Location: registrasi.php");
    exit;
}

// Validasi rating
$rating_pelayanan = isset($_POST['rating_pelayanan']) ? (int)$_POST['rating_pelayanan'] : 5;
if ($rating_pelayanan < 1) $rating_pelayanan = 1;
if ($rating_pelayanan > 5) $rating_pelayanan = 5;

$keramahan_petugas = isset($_POST['keramahan_petugas']) ? (int)$_POST['keramahan_petugas'] : 5;
if ($keramahan_petugas < 1) $keramahan_petugas = 1;
if ($keramahan_petugas > 5) $keramahan_petugas = 5;

$kecepatan_layanan = isset($_POST['kecepatan_layanan']) ? (int)$_POST['kecepatan_layanan'] : 5;
if ($kecepatan_layanan < 1) $kecepatan_layanan = 1;
if ($kecepatan_layanan > 5) $kecepatan_layanan = 5;

$kemudahan_prosedur = isset($_POST['kemudahan_prosedur']) ? (int)$_POST['kemudahan_prosedur'] : 5;
if ($kemudahan_prosedur < 1) $kemudahan_prosedur = 1;
if ($kemudahan_prosedur > 5) $kemudahan_prosedur = 5;

$saran = trim($_POST['saran'] ?? '');
if (strlen($saran) > 1000) {
    $saran = substr($saran, 0, 1000);
}

// Cek apakah antrian valid
$stmt_check_antrian = $conn->prepare("SELECT id_antrian FROM antrian WHERE id_antrian = ? AND id_pengunjung = ? LIMIT 1");
$stmt_check_antrian->bind_param("ii", $id_antrian, $id_pengunjung);
$stmt_check_antrian->execute();
$res_antrian = $stmt_check_antrian->get_result();
if (!$res_antrian->fetch_assoc()) {
    $stmt_check_antrian->close();
    header("Location: registrasi.php");
    exit;
}
$stmt_check_antrian->close();

// Cek duplikasi survei
$stmt_cek = $conn->prepare("SELECT id_survei FROM survei_kepuasan WHERE id_antrian = ? LIMIT 1");
$stmt_cek->bind_param("i", $id_antrian);
$stmt_cek->execute();
$res_cek = $stmt_cek->get_result();
if ($res_cek->fetch_assoc()) {
    $stmt_cek->close();
    // Sudah pernah isi
    header("Location: survei.php?id_antrian=" . $id_antrian . "&sukses=1");
    exit;
}
$stmt_cek->close();

// Simpan data survei menggunakan Prepared Statements
$stmt_insert = $conn->prepare("
    INSERT INTO survei_kepuasan (
        id_antrian,
        id_pengunjung,
        rating_pelayanan,
        keramahan_petugas,
        kecepatan_layanan,
        kemudahan_prosedur,
        saran,
        waktu_survei
    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
");

if (!$stmt_insert) {
    error_log("Database Prepare Error: " . $conn->error);
    header("Location: survei.php?id_antrian=" . $id_antrian);
    exit;
}

$stmt_insert->bind_param(
    "iiiiiss",
    $id_antrian,
    $id_pengunjung,
    $rating_pelayanan,
    $keramahan_petugas,
    $kecepatan_layanan,
    $kemudahan_prosedur,
    $saran
);

$success = $stmt_insert->execute();
$stmt_insert->close();

if ($success) {
    header("Location: survei.php?id_antrian=" . $id_antrian . "&sukses=1");
} else {
    header("Location: survei.php?id_antrian=" . $id_antrian . "&error=1");
}
exit;
