<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../login.php");
    exit;
}

include '../../config/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Cek apakah pelayanan masih digunakan oleh data pengunjung atau antrian
    $cek_pengunjung = mysqli_query($conn, "SELECT id_pengunjung FROM pengunjung WHERE id_pelayanan = $id LIMIT 1");
    $cek_antrian = mysqli_query($conn, "SELECT id_antrian FROM antrian WHERE id_pelayanan = $id LIMIT 1");

    if (mysqli_num_rows($cek_pengunjung) > 0 || mysqli_num_rows($cek_antrian) > 0) {
        // Jangan hapus jika ada relasi data agar tidak merusak foreign key dan integritas data
        header("Location: ../../dashboard.php?page=pelayanan&hapus=gagal_terkait");
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM pelayanan WHERE id_pelayanan = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../../dashboard.php?page=pelayanan&hapus=berhasil");
    exit;
}

header("Location: ../../dashboard.php?page=pelayanan");
exit;