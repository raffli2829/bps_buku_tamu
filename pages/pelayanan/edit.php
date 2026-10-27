<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../login.php");
    exit;
}

include '../../config/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: ../../dashboard.php?page=pelayanan");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM pelayanan WHERE id_pelayanan = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: ../../dashboard.php?page=pelayanan");
    exit;
}

if(isset($_POST['update'])){

    $kode = strtoupper(trim($_POST['kode'] ?? ''));
    $nama = trim($_POST['nama_pelayanan'] ?? '');

    if (!empty($kode) && !empty($nama)) {
        $stmt_up = $conn->prepare("UPDATE pelayanan SET kode = ?, nama_pelayanan = ? WHERE id_pelayanan = ?");
        $stmt_up->bind_param("ssi", $kode, $nama, $id);
        $stmt_up->execute();
        $stmt_up->close();
    }

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
value="<?= htmlspecialchars($data['kode']); ?>"
required>

</div>

<div class="form-group">

<label>Nama Pelayanan</label>

<input
type="text"
name="nama_pelayanan"
class="form-control"
value="<?= htmlspecialchars($data['nama_pelayanan']); ?>"
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