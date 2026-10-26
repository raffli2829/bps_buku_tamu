<?php

include "../../config/koneksi.php";

$id = $_GET['id'];

// Tidak mengubah status,
// hanya memperbarui waktu panggil
mysqli_query($conn,"
UPDATE antrian
SET waktu_panggil = NOW()
WHERE id_antrian='$id'
");

header("Location: ../../dashboard.php?page=antrian");

exit;

?>