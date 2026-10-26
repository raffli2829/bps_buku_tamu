<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

include "config/koneksi.php";

header("Content-Type: application/json");

// ===========================
// Data yang sedang dipanggil
// ===========================

$q = mysqli_query($conn,"
SELECT
    a.nomor_antrian,
    a.waktu_panggil,
    p.nama_pelayanan
FROM antrian a
JOIN pelayanan p
ON a.id_pelayanan = p.id_pelayanan
WHERE a.status='Dipanggil'
ORDER BY a.waktu_panggil DESC
LIMIT 1
");

$panggil = mysqli_fetch_assoc($q);

// ===========================
// Daftar antrean menunggu
// ===========================

$list = [];

$q2 = mysqli_query($conn,"
SELECT nomor_antrian
FROM antrian
WHERE status='Menunggu'
ORDER BY id_antrian ASC
LIMIT 3
");

while($d = mysqli_fetch_assoc($q2)){
    $list[] = $d['nomor_antrian'];
}

// ===========================
// Kirim JSON
// ===========================

echo json_encode([

    "nomor"     => $panggil['nomor_antrian'] ?? "-",

    "pelayanan" => $panggil['nama_pelayanan'] ?? "",

    "waktu"     => $panggil['waktu_panggil'] ?? "",

    "menunggu"  => $list

]);
