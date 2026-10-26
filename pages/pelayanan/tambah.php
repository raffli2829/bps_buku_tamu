<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../config/koneksi.php';

if(isset($_POST['simpan'])){

    $kode = mysqli_real_escape_string($conn,$_POST['kode']);
    $nama = mysqli_real_escape_string($conn,$_POST['nama_pelayanan']);

    mysqli_query($conn,"
    INSERT INTO pelayanan(
        kode,
        nama_pelayanan
    )
    VALUES(
        '$kode',
        '$nama'
    )
    ");

    header("Location: ../../dashboard.php?page=pelayanan");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<title>Tambah Pelayanan</title>

<link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../../dist/css/adminlte.min.css">

</head>

<body class="hold-transition">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card card-primary">

<div class="card-header">

<h3 class="card-title">

Tambah Pelayanan

</h3>

</div>

<form method="POST">

<div class="card-body">

<div class="form-group">

<label>Kode Pelayanan</label>

<input
type="text"
name="kode"
maxlength="1"
class="form-control"
required>

</div>

<div class="form-group">

<label>Nama Pelayanan</label>

<input
type="text"
name="nama_pelayanan"
class="form-control"
required>

</div>

</div>

<div class="card-footer">

<button
type="submit"
name="simpan"
class="btn btn-primary">

<i class="fas fa-save"></i>

Simpan

</button>

<a
href="../../dashboard.php?page=pelayanan"
class="btn btn-secondary">

Kembali

</a>

</div>

</form>

</div>

</div>

</div>

</div>

<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../dist/js/adminlte.min.js"></script>

</body>
</html>