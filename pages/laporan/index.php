<?php
include 'config/koneksi.php';

$tanggal1 = isset($_GET['tanggal1']) ? $_GET['tanggal1'] : '';
$tanggal2 = isset($_GET['tanggal2']) ? $_GET['tanggal2'] : '';
?>

<div class="card">

<div class="card-header bg-primary">

<h3 class="card-title">
Laporan Pengunjung
</h3>

</div>

<div class="card-body">

<form method="GET" action="dashboard.php">

<input type="hidden" name="page" value="laporan">

<div class="row">

<div class="col-md-4">

<label>Tanggal Awal</label>

<input
type="date"
name="tanggal1"
class="form-control"
value="<?= $tanggal1; ?>">

</div>

<div class="col-md-4">

<label>Tanggal Akhir</label>

<input
type="date"
name="tanggal2"
class="form-control"
value="<?= $tanggal2; ?>">

</div>

<div class="col-md-4">

<label>&nbsp;</label>

<button class="btn btn-primary btn-block">

<i class="fas fa-search"></i>

Tampilkan

</button>

</div>

</div>

</form>

<hr>
<?php

$where="";

if($tanggal1!="" && $tanggal2!=""){

$where="WHERE p.tanggal BETWEEN '$tanggal1' AND '$tanggal2'";

}

$query=mysqli_query($conn,"
SELECT

p.nama,
p.jenis_kelamin,
p.instansi,
p.no_hp,
p.email,
p.pekerjaan,
p.usia,
p.pendidikan_terakhir,
p.nomor_parkir,
p.tujuan,
pl.nama_pelayanan,
a.nomor_antrian,
a.status,
p.tanggal

FROM pengunjung p

LEFT JOIN pelayanan pl
ON p.id_pelayanan=pl.id_pelayanan

LEFT JOIN antrian a
ON p.id_pengunjung=a.id_pengunjung

$where

ORDER BY p.tanggal DESC

");

?>

<table
id="tabelLaporan"
class="table table-bordered table-striped">

<thead>

<tr>

<th>Tanggal</th>
<th>Nama</th>
<th>JK</th>
<th>No HP</th>
<th>Instansi</th>
<th>Pelayanan</th>
<th>Tujuan</th>
<th>Antrian</th>
<th>Status</th>

</tr>

</thead>

<tbody>

<?php

$no=1;

while($d=mysqli_fetch_assoc($query)){

?>

<tr>

<td><?= date('d-m-Y',strtotime($d['tanggal'])); ?></td>

<td><?= $d['nama']; ?></td>

<td><?= $d['jenis_kelamin']; ?></td>

<td><?= $d['no_hp']; ?></td>

<td><?= $d['instansi']; ?></td>

<td><?= $d['nama_pelayanan']; ?></td>

<td><?= $d['tujuan']; ?></td>

<td><?= $d['nomor_antrian']; ?></td>

<td><?= $d['status']; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

<br>

<a

href="pages/laporan/cetak.php?tanggal1=<?= $tanggal1; ?>&tanggal2=<?= $tanggal2; ?>"

target="_blank"

class="btn btn-success">

<i class="fas fa-print"></i>

Cetak Laporan

</a>
<a
href="pages/laporan/export_excel.php?tanggal1=<?= $tanggal1; ?>&tanggal2=<?= $tanggal2; ?>"
class="btn btn-success">

<i class="fas fa-file-excel"></i>

Export Excel

</a>

</div>

</div>