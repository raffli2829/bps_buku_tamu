<?php
include 'config/koneksi.php';

$id = $_GET['id'];

$query = mysqli_query($conn,"
SELECT
p.*,
pl.nama_pelayanan,
a.nomor_antrian,
a.status,
a.waktu_ambil,
a.waktu_panggil,
a.waktu_selesai

FROM pengunjung p

LEFT JOIN pelayanan pl
ON p.id_pelayanan = pl.id_pelayanan

LEFT JOIN antrian a
ON p.id_pengunjung = a.id_pengunjung

WHERE p.id_pengunjung='$id'
");

$data = mysqli_fetch_assoc($query);

if(!$data){
    echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>";
    return;
}
?>

<div class="card card-primary">

    <div class="card-header">

        <h3 class="card-title">

            <i class="fas fa-user"></i>

            Detail Pengunjung

        </h3>

    </div>

    <div class="card-body">

        <table class="table table-bordered table-striped">

            <tr>
                <th width="280">Nama Lengkap</th>
                <td><?= $data['nama']; ?></td>
            </tr>

            <tr>
                <th>Jenis Kelamin</th>
                <td><?= $data['jenis_kelamin']; ?></td>
            </tr>

            <tr>
                <th>Nomor HP</th>
                <td><?= $data['no_hp']; ?></td>
            </tr>

            <tr>
                <th>Email Aktif</th>
                <td><?= $data['email']; ?></td>
            </tr>

            <tr>
                <th>Pekerjaan</th>
                <td><?= $data['pekerjaan']; ?></td>
            </tr>

            <tr>
                <th>Usia</th>
                <td><?= $data['usia']; ?> Tahun</td>
            </tr>

            <tr>
                <th>Pendidikan Terakhir</th>
                <td><?= $data['pendidikan_terakhir']; ?></td>
            </tr>

            <tr>
                <th>Asal Instansi</th>
                <td><?= $data['instansi']; ?></td>
            </tr>

            <tr>
                <th>Nomor Parkir</th>
                <td><?= $data['nomor_parkir']; ?></td>
            </tr>

            <tr>
                <th>Pelayanan</th>
                <td><?= $data['nama_pelayanan']; ?></td>
            </tr>

            <tr>
                <th>Tujuan</th>
                <td><?= nl2br(htmlspecialchars($data['tujuan'])); ?></td>
            </tr>

            <tr>
                <th>Nomor Antrian</th>
                <td>
                    <span class="badge badge-primary" style="font-size:16px;">
                        <?= $data['nomor_antrian']; ?>
                    </span>
                </td>
            </tr>

            <tr>
                <th>Status</th>
                <td>

                    <?php

                    if($data['status']=="Menunggu"){

                        echo "<span class='badge badge-warning'>Menunggu</span>";

                    }elseif($data['status']=="Dipanggil"){

                        echo "<span class='badge badge-primary'>Dipanggil</span>";

                    }else{

                        echo "<span class='badge badge-success'>Selesai</span>";

                    }

                    ?>

                </td>
            </tr>

            <tr>
                <th>Tanggal Registrasi</th>
                <td><?= date('d-m-Y',strtotime($data['tanggal'])); ?></td>
            </tr>

            <tr>
                <th>Jam Registrasi</th>
                <td><?= $data['jam']; ?></td>
            </tr>

            <tr>
                <th>Waktu Dipanggil</th>
                <td><?= $data['waktu_panggil'] ?: '-'; ?></td>
            </tr>

            <tr>
                <th>Waktu Selesai</th>
                <td><?= $data['waktu_selesai'] ?: '-'; ?></td>
            </tr>

        </table>

    </div>

    <div class="card-footer">

        <a href="dashboard.php?page=pengunjung"
           class="btn btn-secondary">

            <i class="fas fa-arrow-left"></i>

            Kembali

        </a>

        <a href="nomor_antrian.php?id=<?= $data['id_pengunjung']; ?>"
           target="_blank"
           class="btn btn-success">

            <i class="fas fa-print"></i>

            Cetak Nomor Antrian

        </a>

    </div>

</div>