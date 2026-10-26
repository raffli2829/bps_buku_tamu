<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../config/koneksi.php';

if(isset($_GET['id'])){

    $id = $_GET['id'];

    // Cek apakah data ada
    $cek = mysqli_query($conn,"SELECT * FROM pelayanan WHERE id_pelayanan='$id'");

    if(mysqli_num_rows($cek) > 0){

        mysqli_query($conn,"
        DELETE FROM pelayanan
        WHERE id_pelayanan='$id'
        ");

    }

}

header("Location: ../../dashboard.php?page=pelayanan");
exit;
?>