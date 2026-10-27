<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../login.php");
    exit;
}

include "../../config/koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 1. Hapus survei kepuasan terkait jika ada
    $stmt_s = $conn->prepare("DELETE FROM survei_kepuasan WHERE id_pengunjung = ?");
    $stmt_s->bind_param("i", $id);
    $stmt_s->execute();
    $stmt_s->close();

    // 2. Hapus data antrian pengunjung
    $stmt_a = $conn->prepare("DELETE FROM antrian WHERE id_pengunjung = ?");
    $stmt_a->bind_param("i", $id);
    $stmt_a->execute();
    $stmt_a->close();

    // 3. Hapus data pengunjung
    $stmt_p = $conn->prepare("DELETE FROM pengunjung WHERE id_pengunjung = ?");
    $stmt_p->bind_param("i", $id);
    $stmt_p->execute();
    $stmt_p->close();

    header("Location: ../../dashboard.php?page=pengunjung&hapus=berhasil");
    exit;
}

header("Location: ../../dashboard.php?page=pengunjung");
exit;