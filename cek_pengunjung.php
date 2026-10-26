<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

include 'config/koneksi.php';

// Sanitasi parameter nomor HP (hanya angka)
$no_hp = preg_replace('/[^0-9]/', '', $_GET['no_hp'] ?? '');

if (strlen($no_hp) < 8 || strlen($no_hp) > 16) {
    echo json_encode([
        'found' => false,
        'message' => 'Nomor HP minimal 8 digit angka'
    ]);
    exit;
}

// Cari data pengunjung terakhir berdasarkan nomor HP menggunakan Prepared Statements
$stmt = $conn->prepare("
    SELECT 
        nama,
        jenis_kelamin,
        instansi,
        email,
        pekerjaan,
        usia,
        pendidikan_terakhir
    FROM pengunjung
    WHERE no_hp = ?
    ORDER BY id_pengunjung DESC
    LIMIT 1
");

if (!$stmt) {
    echo json_encode([
        'found' => false,
        'message' => 'Kesalahan database'
    ]);
    exit;
}

$stmt->bind_param("s", $no_hp);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'found' => true,
        'data' => [
            'nama' => $row['nama'] ?? '',
            'jenis_kelamin' => $row['jenis_kelamin'] ?? '',
            'instansi' => $row['instansi'] ?? '',
            'email' => $row['email'] ?? '',
            'pekerjaan' => $row['pekerjaan'] ?? '',
            'usia' => $row['usia'] ? (int)$row['usia'] : '',
            'pendidikan_terakhir' => $row['pendidikan_terakhir'] ?? ''
        ]
    ]);
} else {
    echo json_encode([
        'found' => false,
        'message' => 'Data tidak ditemukan'
    ]);
}

$stmt->close();
