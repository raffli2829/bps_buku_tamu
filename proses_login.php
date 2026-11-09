<?php

session_start();

include "config/koneksi.php";

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Cari user berdasarkan username dengan prepared statement
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$query = $stmt->get_result();

if($query && $query->num_rows > 0){

    $data = $query->fetch_assoc();
    $stmt->close();

    // Cek password yang di-hash
    if(password_verify($password, $data['password'])){

        $_SESSION['login']   = true;
        $_SESSION['id_user'] = (int)$data['id_user'];
        $_SESSION['nama']    = $data['nama'];
        $_SESSION['username']= $data['username'];
        $_SESSION['foto']    = $data['foto'] ?? '';

        header("Location: dashboard.php");
        exit;

    }

} else {
    $stmt->close();
}

// Jika login gagal
header("Location: login.php?pesan=gagal&username=".urlencode($username));
exit;
