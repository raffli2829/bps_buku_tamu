<?php
include 'config/koneksi.php';

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT
pengunjung.nama,
pengunjung.nomor_parkir,
pelayanan.nama_pelayanan,
antrian.nomor_antrian,
antrian.waktu_ambil
FROM pengunjung
JOIN antrian
ON pengunjung.id_pengunjung=antrian.id_pengunjung
JOIN pelayanan
ON pelayanan.id_pelayanan=antrian.id_pelayanan
WHERE pengunjung.id_pengunjung='$id'
"));
if (!$data) {
    die("Data antrian tidak ditemukan untuk ID = ".$id);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Nomor Antrian - SIRITA</title>

<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="dist/css/adminlte.min.css">

<style>

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

*{
font-family:'Poppins',sans-serif;
}

body{

background:linear-gradient(135deg,#0d6efd,#198754);

display:flex;

justify-content:center;

align-items:center;

min-height:100vh;

}

.card{

width:700px;

border:none;

border-radius:25px;

box-shadow:0 20px 45px rgba(0,0,0,.25);

overflow:hidden;

}

.card-header{

background:#0d6efd;

color:white;

text-align:center;

padding:30px;

}

.logo{

width:90px;

margin-bottom:15px;

}

.nomor{

font-size:95px;

font-weight:bold;

color:#0d6efd;

animation:zoom .8s;

}

@keyframes zoom{

0%{

transform:scale(.3);

opacity:0;

}

100%{

transform:scale(1);

opacity:1;

}

}

.info{

font-size:18px;

margin-top:15px;

}

.footer{

background:#f8f9fa;

padding:20px;

text-align:center;

}

.btn-custom{

border-radius:10px;

padding:10px 30px;

font-weight:600;

}

</style>

</head>

<body>

<div class="card">

<div class="card-header">

<img src="assets/img/logo_bps.png" class="logo">

<h2>SIRITA</h2>

<h5>Sistem Informasi Registrasi Tamu dan Antrian</h5>

<h6>BPS Kabupaten Bangka</h6>

</div>

<div class="card-body text-center">

<h5>Nomor Antrian Anda</h5>

<div class="nomor">

<?= $data['nomor_antrian']; ?>

</div>

<hr>

<h4>

<?= $data['nama_pelayanan']; ?>

</h4>

<div class="info">

<b>Nama :</b>

<?= $data['nama']; ?>

</div>

<div class="info">

<b>Nomor Parkir :</b>

<?= $data['nomor_parkir']; ?>

</div>

<div class="info">

<b>Waktu Registrasi :</b>

<?= date('d-m-Y H:i',strtotime($data['waktu_ambil'])); ?>

</div>

<div class="alert alert-success mt-4">

Silakan menunggu hingga nomor Anda dipanggil.

</div>

</div>

<div class="footer">

<button onclick="window.print()" class="btn btn-success btn-custom">

<i class="fas fa-print"></i>

Cetak

</button>

<a href="registrasi.php" class="btn btn-primary btn-custom">

<i class="fas fa-home"></i>

Kembali

</a>

</div>

</div>

</body>

</html>