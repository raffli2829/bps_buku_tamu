<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

include 'config/koneksi.php';

$id_pengunjung = isset($_GET['id_pengunjung']) ? (int)$_GET['id_pengunjung'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$id_antrian = isset($_GET['id_antrian']) ? (int)$_GET['id_antrian'] : 0;

if ($id_pengunjung <= 0 && $id_antrian <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Parameter ID tidak valid'
    ]);
    exit;
}

// Cari data antrian pengunjung menggunakan Prepared Statements
if ($id_antrian > 0) {
    $stmt = $conn->prepare("
        SELECT 
            a.id_antrian,
            a.id_pengunjung,
            a.id_pelayanan,
            a.nomor_antrian,
            a.status,
            a.waktu_ambil,
            a.waktu_panggil,
            a.waktu_selesai,
            p.nama,
            p.nomor_parkir,
            pl.nama_pelayanan
        FROM antrian a
        JOIN pengunjung p ON a.id_pengunjung = p.id_pengunjung
        JOIN pelayanan pl ON a.id_pelayanan = pl.id_pelayanan
        WHERE a.id_antrian = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $id_antrian);
} else {
    $stmt = $conn->prepare("
        SELECT 
            a.id_antrian,
            a.id_pengunjung,
            a.id_pelayanan,
            a.nomor_antrian,
            a.status,
            a.waktu_ambil,
            a.waktu_panggil,
            a.waktu_selesai,
            p.nama,
            p.nomor_parkir,
            pl.nama_pelayanan
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
    echo json_encode([
        'success' => false,
        'message' => 'Data antrian tidak ditemukan'
    ]);
    exit;
}

$current_id_antrian = (int)$data['id_antrian'];
$id_pelayanan = (int)$data['id_pelayanan'];

// 1. Ambil nomor antrian yang sedang dipanggil saat ini untuk layanan tersebut
$nomor_aktif = "-";
$stmt_aktif = $conn->prepare("
    SELECT nomor_antrian 
    FROM antrian 
    WHERE id_pelayanan = ? AND status = 'Dipanggil' 
    ORDER BY waktu_panggil DESC 
    LIMIT 1
");
$stmt_aktif->bind_param("i", $id_pelayanan);
$stmt_aktif->execute();
$res_aktif = $stmt_aktif->get_result();
if ($row_aktif = $res_aktif->fetch_assoc()) {
    $nomor_aktif = $row_aktif['nomor_antrian'];
}
$stmt_aktif->close();

// 2. Hitung berapa antrian yang sedang menunggu di depan nomor ini
$sisa_antrean = 0;
if ($data['status'] === 'Menunggu') {
    $stmt_sisa = $conn->prepare("
        SELECT COUNT(*) as sisa 
        FROM antrian 
        WHERE id_pelayanan = ? AND status = 'Menunggu' AND id_antrian < ?
    ");
    $stmt_sisa->bind_param("ii", $id_pelayanan, $current_id_antrian);
    $stmt_sisa->execute();
    $res_sisa = $stmt_sisa->get_result();
    if ($row_sisa = $res_sisa->fetch_assoc()) {
        $sisa_antrean = (int)$row_sisa['sisa'];
    }
    $stmt_sisa->close();
}

// 3. Cek apakah pengunjung sudah mengisi survei kepuasan
$sudah_survei = false;
$stmt_survei = $conn->prepare("
    SELECT id_survei 
    FROM survei_kepuasan 
    WHERE id_antrian = ? 
    LIMIT 1
");
$stmt_survei->bind_param("i", $current_id_antrian);
$stmt_survei->execute();
$res_survei = $stmt_survei->get_result();
if ($res_survei->fetch_assoc()) {
    $sudah_survei = true;
}
$stmt_survei->close();

echo json_encode([
    'success' => true,
    'id_antrian' => $current_id_antrian,
    'id_pengunjung' => (int)$data['id_pengunjung'],
    'nomor_antrian' => $data['nomor_antrian'],
    'nama' => $data['nama'],
    'nomor_parkir' => $data['nomor_parkir'] ?? '-',
    'nama_pelayanan' => $data['nama_pelayanan'],
    'status' => $data['status'],
    'nomor_aktif' => $nomor_aktif,
    'sisa_antrean' => $sisa_antrean,
    'waktu_ambil' => date('d-m-Y H:i', strtotime($data['waktu_ambil'])),
    'sudah_survei' => $sudah_survei
], JSON_UNESCAPED_UNICODE);
