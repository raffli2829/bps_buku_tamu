<?php
include '../../config/koneksi.php';

$tanggal1 = isset($_GET['tanggal1']) ? $_GET['tanggal1'] : '';
$tanggal2 = isset($_GET['tanggal2']) ? $_GET['tanggal2'] : '';

$where = "";

if($tanggal1 != "" && $tanggal2 != ""){
    $where = "WHERE p.tanggal BETWEEN '$tanggal1' AND '$tanggal2'";
}

$query = mysqli_query($conn,"
SELECT
p.tanggal,
p.nama,
p.jenis_kelamin,
p.usia,
p.no_hp,
p.email,
p.pekerjaan,
p.pendidikan_terakhir,
p.instansi,
p.nomor_parkir,
pl.nama_pelayanan,
p.tujuan,
a.nomor_antrian,
a.status

FROM pengunjung p

LEFT JOIN pelayanan pl
ON p.id_pelayanan = pl.id_pelayanan

LEFT JOIN antrian a
ON p.id_pengunjung = a.id_pengunjung

$where

ORDER BY p.tanggal DESC,p.jam DESC
");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Pengunjung.xls");
?>

<table border="1">

<tr style="background:#0B5ED7;color:white;">

<th>No</th>
<th>Tanggal</th>
<th>Nama</th>
<th>Jenis Kelamin</th>
<th>Usia</th>
<th>No HP</th>
<th>Email</th>
<th>Pekerjaan</th>
<th>Pendidikan</th>
<th>Instansi</th>
<th>No Parkir</th>
<th>Pelayanan</th>
<th>Tujuan</th>
<th>No Antrian</th>
<th>Status</th>

</tr>

<?php

$no=1;

while($d=mysqli_fetch_assoc($query)){

?>

<tr>

<td><?= $no++; ?></td>

<td><?= date('d-m-Y',strtotime($d['tanggal'])); ?></td>

<td><?= $d['nama']; ?></td>

<td><?= $d['jenis_kelamin']; ?></td>

<td><?= $d['usia']; ?></td>

<td><?= $d['no_hp']; ?></td>

<td><?= $d['email']; ?></td>

<td><?= $d['pekerjaan']; ?></td>

<td><?= $d['pendidikan_terakhir']; ?></td>

<td><?= $d['instansi']; ?></td>

<td><?= $d['nomor_parkir']; ?></td>

<td><?= $d['nama_pelayanan']; ?></td>

<td><?= $d['tujuan']; ?></td>

<td><?= $d['nomor_antrian']; ?></td>

<td><?= $d['status']; ?></td>

</tr>

<?php
}
?>

</table>