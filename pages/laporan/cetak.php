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
p.*,
pl.nama_pelayanan,
a.nomor_antrian,
a.status

FROM pengunjung p

LEFT JOIN pelayanan pl
ON p.id_pelayanan=pl.id_pelayanan

LEFT JOIN antrian a
ON p.id_pengunjung=a.id_pengunjung

$where

ORDER BY p.tanggal DESC,p.jam DESC
");
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Cetak Laporan</title>

<style>

body{
    font-family:Arial,Helvetica,sans-serif;
    margin:35px;
    color:#000;
}

.kop{
    width:100%;
    border-bottom:3px solid black;
    padding-bottom:10px;
    margin-bottom:20px;
}

.logo{
    width:80px;
}

.judul{
    text-align:center;
}

.judul h2,
.judul h3,
.judul h4,
.judul p{
    margin:3px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    border:1px solid black;
    padding:7px;
    font-size:12px;
    vertical-align:top;
}

th{
    background:#e9ecef;
}

.tujuan{
    margin-top:6px;
    font-size:11px;
    line-height:18px;
}

.ttd{
    width:300px;
    float:right;
    margin-top:50px;
    text-align:center;
}

</style>

</head>

<body onload="window.print()">

<table class="kop" border="0">

<tr>

<td width="90">

<img src="../../assets/img/logo_bps.png" class="logo">

</td>

<td class="judul">

<h2>BADAN PUSAT STATISTIK</h2>

<h3>KABUPATEN BANGKA</h3>

<h4>LAPORAN DATA PENGUNJUNG</h4>

<?php
if($tanggal1!="" && $tanggal2!=""){
?>

<p>

Periode :

<b>

<?= date('d-m-Y',strtotime($tanggal1)); ?>

s/d

<?= date('d-m-Y',strtotime($tanggal2)); ?>

</b>

</p>

<?php } ?>

</td>

</tr>

</table>

<table>

<thead>

<tr>

<th width="40">No</th>

<th width="90">Tanggal</th>

<th width="170">Identitas Pengunjung</th>

<th width="140">Pelayanan</th>

<th width="80">Antrian</th>

<th width="90">Status</th>

</tr>

</thead>

<tbody>

<?php

$no=1;

while($d=mysqli_fetch_assoc($query)){

?>

<tr>

<td align="center">

<?= $no++; ?>

</td>

<td align="center">

<?= date('d-m-Y',strtotime($d['tanggal'])); ?>

</td>

<td>

<b>Nama</b> :
<?= $d['nama']; ?>

<br>

<b>JK</b> :
<?= $d['jenis_kelamin']; ?>

<br>

<b>No HP</b> :
<?= $d['no_hp']; ?>

<br>

<b>Email</b> :
<?= $d['email']; ?>

<br>

<b>Pekerjaan</b> :
<?= $d['pekerjaan']; ?>

<br>

<b>Usia</b> :
<?= $d['usia']; ?> Tahun

<br>

<b>Pendidikan</b> :
<?= $d['pendidikan_terakhir']; ?>

<br>

<b>Instansi</b> :
<?= $d['instansi']; ?>

<br>

<b>Nomor Parkir</b> :
<?= $d['nomor_parkir']; ?>

<div class="tujuan">

<b>Tujuan Kunjungan :</b>

<br>

<?= nl2br(htmlspecialchars($d['tujuan'])); ?>

</div>

</td>

<td>

<?= $d['nama_pelayanan']; ?>

</td>

<td align="center">

<b>

<?= $d['nomor_antrian']; ?>

</b>

</td>

<td align="center">

<?= $d['status']; ?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

<div class="ttd">

Bangka,

<?= date('d-m-Y'); ?>

<br><br><br><br><br>

<b>__________________________</b>

</div>

</body>

</html>