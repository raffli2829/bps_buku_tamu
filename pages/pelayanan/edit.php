<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../config/koneksi.php';

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT * FROM pelayanan
WHERE id_pelayanan='$id'
"));

if(isset($_POST['update'])){

    $kode = mysqli_real_escape_string($conn,$_POST['kode']);
    $nama = mysqli_real_escape_string($conn,$_POST['nama_pelayanan']);

    mysqli_query($conn,"
    UPDATE pelayanan
    SET
        kode='$kode',
        nama_pelayanan='$nama'
    WHERE id_pelayanan='$id'
    ");

    header("Location: ../../dashboard.php?page=pelayanan");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<title>Edit Pelayanan</title>

<link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="../../dist/css/adminlte.min.css">

</head>

<body class="hold-transition">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card card-warning">

<div class="card-header">

<h3 class="card-title">
Edit Pelayanan
</h3>

</div>

<form method="POST">

<div class="card-body">

<div class="form-group">

<label>Kode Pelayanan</label>

<input
type="text"
name="kode"
class="form-control"
maxlength="1"
value="<?= $data['kode']; ?>"
required>

</div>

<div class="form-group">

<label>Nama Pelayanan</label>

<input
type="text"
name="nama_pelayanan"
class="form-control"
value="<?= $data['nama_pelayanan']; ?>"
required>

</div>

</div>

<div class="card-footer">

<button
type="submit"
name="update"
class="btn btn-warning">

<i class="fas fa-save"></i>

Update

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