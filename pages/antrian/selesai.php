<?php
include '../../config/koneksi.php';

$id = $_GET['id'];

mysqli_query($conn,"
UPDATE antrian
SET
status='Selesai',
waktu_selesai=NOW()
WHERE id_antrian='$id'
");

header("Location: ../../dashboard.php?page=antrian");
exit;
?>