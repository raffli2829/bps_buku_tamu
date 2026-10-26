<?php
include 'config/koneksi.php';

/* ==========================
   CARD STATISTIK
========================== */

$totalPengunjung = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM pengunjung
"));

$menunggu = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM antrian
WHERE status='Menunggu'
"));

$dipanggil = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM antrian
WHERE status='Dipanggil'
"));

$selesai = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM antrian
WHERE status='Selesai'
"));

/* ==========================
   GRAFIK STATUS
========================== */

$grafik = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT
SUM(status='Menunggu') menunggu,
SUM(status='Dipanggil') dipanggil,
SUM(status='Selesai') selesai
FROM antrian
"));

/* ==========================
   GRAFIK PELAYANAN
========================== */

$pelayanan = mysqli_query($conn,"
SELECT
pelayanan.nama_pelayanan,
COUNT(antrian.id_antrian) jumlah

FROM pelayanan

LEFT JOIN antrian
ON pelayanan.id_pelayanan=antrian.id_pelayanan

GROUP BY pelayanan.id_pelayanan
");

$labelPelayanan=[];
$dataPelayanan=[];

while($p=mysqli_fetch_assoc($pelayanan)){

    $labelPelayanan[]=$p['nama_pelayanan'];
    $dataPelayanan[]=$p['jumlah'];

}

/* ==========================
   TABEL
========================== */

$antrian = mysqli_query($conn,"
SELECT

a.nomor_antrian,
p.nama,
pl.nama_pelayanan,
a.status

FROM antrian a

JOIN pengunjung p
ON a.id_pengunjung=p.id_pengunjung

JOIN pelayanan pl
ON a.id_pelayanan=pl.id_pelayanan

ORDER BY a.id_antrian DESC

LIMIT 5
");
?>

<div class="alert alert-primary">

<h4>Selamat Datang di SIRITA</h4>

Sistem Informasi Registrasi Tamu dan Antrian<br>
BPS Kabupaten Bangka

</div>

<div class="row">

<div class="col-lg-3">

<div class="small-box bg-info">

<div class="inner">

<h3><?= $totalPengunjung['total']; ?></h3>

<p>Total Pengunjung</p>

</div>

<div class="icon">

<i class="fas fa-users"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-warning">

<div class="inner">

<h3><?= $menunggu['total']; ?></h3>

<p>Menunggu</p>

</div>

<div class="icon">

<i class="fas fa-clock"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-primary">

<div class="inner">

<h3><?= $dipanggil['total']; ?></h3>

<p>Dipanggil</p>

</div>

<div class="icon">

<i class="fas fa-bullhorn"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-success">

<div class="inner">

<h3><?= $selesai['total']; ?></h3>

<p>Selesai</p>

</div>

<div class="icon">

<i class="fas fa-check-circle"></i>

</div>

</div>

</div>

</div>

<div class="row">

<div class="col-md-6">

<div class="card">

<div class="card-header bg-primary">

<h3 class="card-title">

Grafik Status Antrian

</h3>

</div>

<div class="card-body">

<canvas id="grafikStatus" height="250"></canvas>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card">

<div class="card-header bg-success">

<h3 class="card-title">

Grafik Pelayanan

</h3>

</div>

<div class="card-body">

<canvas id="grafikPelayanan" height="250"></canvas>

</div>

</div>

</div>

</div>

<div class="card">

<div class="card-header bg-primary">

<h3 class="card-title">

5 Antrian Terbaru

</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<thead>

<tr>

<th>Nomor</th>
<th>Nama</th>
<th>Pelayanan</th>
<th>Status</th>

</tr>

</thead>

<tbody>

<?php while($d=mysqli_fetch_assoc($antrian)){ ?>

<tr>

<td><b><?= $d['nomor_antrian']; ?></b></td>

<td><?= $d['nama']; ?></td>

<td><?= $d['nama_pelayanan']; ?></td>

<td><?= $d['status']; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<script>

document.addEventListener("DOMContentLoaded", function(){

new Chart(document.getElementById("grafikStatus"),{

type:"bar",

data:{
labels:["Menunggu","Dipanggil","Selesai"],
datasets:[{
data:[
<?= (int)$grafik['menunggu']; ?>,
<?= (int)$grafik['dipanggil']; ?>,
<?= (int)$grafik['selesai']; ?>
],
backgroundColor:[
"#ffc107",
"#007bff",
"#28a745"
]
}]
},

options:{
responsive:true,
plugins:{
legend:{
display:false
}
}
}

});

new Chart(document.getElementById("grafikPelayanan"),{

type:"pie",

data:{
labels:<?= json_encode($labelPelayanan); ?>,
datasets:[{
data:<?= json_encode($dataPelayanan); ?>
}]
},

options:{
responsive:true
}

});

});

</script>