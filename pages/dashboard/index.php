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
   STATISTIK SKM (SURVEI)
========================== */

$skmStats = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_responden,
        AVG(rating_pelayanan) as rata_rating
    FROM survei_kepuasan
"));
$totalResponden = (int)($skmStats['total_responden'] ?? 0);
$avgRating = $totalResponden > 0 ? round((float)$skmStats['rata_rating'], 2) : 0;
$ikmPersen = $totalResponden > 0 ? round(($avgRating / 5) * 100, 1) : 0;

$mutuText = 'Belum Ada Data';
$mutuBadge = 'secondary';
if ($totalResponden > 0) {
    if ($avgRating >= 4.5) {
        $mutuText = 'Sangat Baik (A)';
        $mutuBadge = 'success';
    } elseif ($avgRating >= 3.5) {
        $mutuText = 'Baik (B)';
        $mutuBadge = 'primary';
    } elseif ($avgRating >= 2.5) {
        $mutuText = 'Kurang Baik (C)';
        $mutuBadge = 'warning';
    } else {
        $mutuText = 'Tidak Baik (D)';
        $mutuBadge = 'danger';
    }
}


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

<!-- WIDGET INDEKS KEPUASAN MASYARAKAT (IKM) -->
<div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #0d3b66 0%, #1e5288 100%); color: white; border-radius: 12px;">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center mb-2 flex-wrap" style="gap: 8px;">
                    <span class="badge badge-light text-primary px-3 py-1 font-weight-bold"><i class="fas fa-award mr-1"></i> INDEKS KEPUASAN MASYARAKAT (IKM)</span>
                    <span class="badge badge-<?= $mutuBadge; ?> px-2 py-1"><i class="fas fa-certificate mr-1"></i> Mutu: <?= $mutuText; ?></span>
                </div>
                <h4 class="font-weight-bold mb-1">Kualitas Pelayanan PST BPS Kabupaten Bangka</h4>
                <p class="mb-0 text-white-50" style="font-size: 0.95rem;">
                    Berdasarkan masukan nyata dari <b><?= number_format($totalResponden); ?></b> responden pengunjung yang telah mengisi survei kepuasan digital.
                </p>
            </div>
            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                <div class="d-inline-flex" style="gap: 12px;">
                    <div class="text-center bg-white p-2 px-3 rounded shadow-sm text-dark">
                        <div style="font-size: 1.6rem; font-weight: 800; color: #f59e0b; line-height: 1.1;">
                            <i class="fas fa-star text-warning"></i> <?= $totalResponden > 0 ? number_format($avgRating, 1) : '-'; ?> <small style="font-size: 0.75rem; color: #6c757d;">/ 5.0</small>
                        </div>
                        <small class="text-muted font-weight-bold d-block mt-1">Skor Rata-Rata</small>
                    </div>
                    <div class="text-center bg-white p-2 px-3 rounded shadow-sm text-dark">
                        <div style="font-size: 1.6rem; font-weight: 800; color: #10b981; line-height: 1.1;">
                            <?= $totalResponden > 0 ? $ikmPersen . '%' : '-'; ?>
                        </div>
                        <small class="text-muted font-weight-bold d-block mt-1">Konversi IKM</small>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="dashboard.php?page=survei" class="btn btn-sm btn-light font-weight-bold text-primary shadow-sm">
                        <i class="fas fa-chart-line mr-1"></i> Analisis Detail Survei &rarr;
                    </a>
                </div>
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