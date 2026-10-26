<?php

include "../../config/koneksi.php";

$id = $_GET['id'];

mysqli_query($conn,"
DELETE FROM antrian
WHERE id_pengunjung='$id'
");

mysqli_query($conn,"
DELETE FROM pengunjung
WHERE id_pengunjung='$id'
");

header("Location: ../../dashboard.php?page=pengunjung&hapus=berhasil");
exit;

?>