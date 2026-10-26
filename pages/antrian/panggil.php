<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../config/koneksi.php';

$id = $_GET['id'];

$sql = "UPDATE antrian
SET
status='Dipanggil',
waktu_panggil=NOW()
WHERE id_antrian='$id'";

if(mysqli_query($conn,$sql)){
    header("Location: ../../dashboard.php?page=antrian");
    exit;
}else{
    echo mysqli_error($conn);
}