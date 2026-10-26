<?php

session_start();

include "config/koneksi.php";

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

// Cari user berdasarkan username
$query = mysqli_query($conn, "
SELECT * FROM users
WHERE username='$username'
LIMIT 1
");

if(mysqli_num_rows($query) > 0){

    $data = mysqli_fetch_assoc($query);

    // Cek password yang di-hash
    if(password_verify($password, $data['password'])){

        $_SESSION['login'] = true;
        $_SESSION['nama']  = $data['nama'];

        header("Location: dashboard.php");
        exit;

    }

}

// Jika login gagal
header("Location: login.php?pesan=gagal&username=".urlencode($username));
exit;

?>