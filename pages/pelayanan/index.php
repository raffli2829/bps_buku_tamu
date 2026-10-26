<?php
include 'config/koneksi.php';

$query = mysqli_query($conn,"SELECT * FROM pelayanan ORDER BY kode ASC");
?>

<div class="card">

<div class="card-header bg-primary">

<h3 class="card-title">
Data Pelayanan
</h3>

<div class="card-tools">

<a href="pages/pelayanan/tambah.php" class="btn btn-light btn-sm">

<i class="fas fa-plus"></i>

Tambah Pelayanan

</a>

</div>

</div>

<div class="card-body">

<table id="tabelPelayanan" class="table table-bordered table-striped">

<thead>

<tr>

<th>No</th>
<th>Kode</th>
<th>Nama Pelayanan</th>
<th width="150">Aksi</th>

</tr>

</thead>

<tbody>

<?php
$no=1;

while($d=mysqli_fetch_assoc($query)){
?>

<tr>

<td><?= $no++; ?></td>

<td><?= $d['kode']; ?></td>

<td><?= $d['nama_pelayanan']; ?></td>

<td>

<a href="pages/pelayanan/edit.php?id=<?= $d['id_pelayanan']; ?>" class="btn btn-warning btn-sm">

<i class="fas fa-edit"></i>

</a>

<button
    type="button"
    class="btn btn-danger btn-sm"
    onclick="hapusPelayanan(<?= $d['id_pelayanan']; ?>)">

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
function hapusPelayanan(id) {

    Swal.fire({
        title: 'Hapus Data Pelayanan?',
        text: 'Data yang sudah dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {

        if (result.isConfirmed) {

            window.location.href =
                'pages/pelayanan/hapus.php?id=' + id;

        }

    });

}
</script>