<?php
include 'config/koneksi.php';

$query = mysqli_query($conn,"
SELECT
    p.id_pengunjung,
    p.nama,
    p.jenis_kelamin,
    p.instansi,
    p.no_hp,
    p.email,
    p.pekerjaan,
    p.usia,
    p.pendidikan_terakhir,
    p.nomor_parkir,
    pl.nama_pelayanan,
    a.nomor_antrian,
    a.status,
    p.tanggal,
    p.jam

FROM pengunjung p

LEFT JOIN pelayanan pl
ON p.id_pelayanan = pl.id_pelayanan

LEFT JOIN antrian a
ON p.id_pengunjung = a.id_pengunjung

ORDER BY p.id_pengunjung DESC
");
?>
<?php if(isset($_GET['update']) && $_GET['update'] == 'berhasil'){ ?>

<script>
document.addEventListener("DOMContentLoaded", function(){

    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data pengunjung berhasil diperbarui',
        timer: 1800,
        showConfirmButton: false
    });

});
</script>

<?php } ?>

<?php if(isset($_GET['hapus']) && $_GET['hapus']=="berhasil"){ ?>

<script>
document.addEventListener("DOMContentLoaded",function(){

Swal.fire({

icon:'success',
title:'Berhasil',
text:'Data pengunjung berhasil dihapus',
timer:1800,
showConfirmButton:false

});

});
</script>

<?php } ?>
<?php if(isset($_GET['tambah']) && $_GET['tambah']=="berhasil"){ ?>

<script>

document.addEventListener("DOMContentLoaded",function(){

Swal.fire({

icon:'success',

title:'Berhasil',

text:'Data pengunjung berhasil ditambahkan',

timer:1800,

showConfirmButton:false

});

});

</script>

<?php } ?>
<div class="card">

<div class="card-header bg-primary">

<h3 class="card-title">

Data Pengunjung

</h3>

</div>

<div class="row mb-3">

    <div class="col-md-12 text-right">

        <a href="dashboard.php?page=pengunjung_tambah"
           class="btn btn-primary">

            <i class="fas fa-plus"></i>

            Tambah Pengunjung

        </a>

    </div>

</div>

<div class="card-body">

<table id="tabelPengunjung" class="table table-bordered table-striped">

<thead>

<tr>

<th>No</th>
<th>Nama</th>
<th>JK</th>
<th>No HP</th>
<th>Instansi</th>
<th>Pelayanan</th>
<th>No Antrian</th>
<th>Status</th>
<th>Tanggal</th>
<th>Aksi</th>

</tr>

</thead>

<tbody>

<?php

$no=1;

while($d=mysqli_fetch_assoc($query)){

?>

<tr>

<td><?= $no++; ?></td>

<td><?= $d['nama']; ?></td>

<td><?= $d['jenis_kelamin']; ?></td>

<td><?= $d['no_hp']; ?></td>

<td><?= $d['instansi']; ?></td>

<td><?= $d['nama_pelayanan']; ?></td>

<td>

<b><?= $d['nomor_antrian']; ?></b>

</td>

<td>

<?php

if($d['status']=="Menunggu"){

echo "<span class='badge badge-warning'>Menunggu</span>";

}elseif($d['status']=="Dipanggil"){

echo "<span class='badge badge-primary'>Dipanggil</span>";

}else{

echo "<span class='badge badge-success'>Selesai</span>";

}

?>

</td>

<td>

<?= date('d-m-Y',strtotime($d['tanggal'])); ?>

</td>

<td>

<a href="dashboard.php?page=pengunjung_detail&id=<?= $d['id_pengunjung']; ?>"
class="btn btn-info btn-sm">

<i class="fas fa-eye"></i>

</a>

<a href="dashboard.php?page=pengunjung_edit&id=<?= $d['id_pengunjung']; ?>"
   class="btn btn-warning btn-sm">

<i class="fas fa-edit"></i>

</a>

<button
type="button"
class="btn btn-danger btn-sm"
onclick="hapusPengunjung(<?= $d['id_pengunjung']; ?>)">

<i class="fas fa-trash"></i>

</button>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<script>

function hapusPengunjung(id){

Swal.fire({

title:'Hapus Data?',

text:'Data yang sudah dihapus tidak dapat dikembalikan.',

icon:'warning',

showCancelButton:true,

confirmButtonColor:'#dc3545',

cancelButtonColor:'#6c757d',

confirmButtonText:'Ya, Hapus',

cancelButtonText:'Batal'

}).then((result)=>{

if(result.isConfirmed){

window.location='pages/pengunjung/hapus.php?id='+id;

}

});

}

</script>