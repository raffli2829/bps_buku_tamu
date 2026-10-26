<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../../config/koneksi.php';

/* =====================================================
   AMBIL DATA PELAYANAN
===================================================== */

$pelayanan = mysqli_query($conn,"
SELECT *
FROM pelayanan
ORDER BY kode ASC
");

/* =====================================================
   PROSES SIMPAN DATA
===================================================== */

if(isset($_POST['simpan'])){

    $tanggal = mysqli_real_escape_string($conn,$_POST['tanggal']);
    $nama = mysqli_real_escape_string($conn,$_POST['nama']);
    $jenis_kelamin = mysqli_real_escape_string($conn,$_POST['jenis_kelamin']);
    $usia = mysqli_real_escape_string($conn,$_POST['usia']);
    $no_hp = mysqli_real_escape_string($conn,$_POST['no_hp']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $pekerjaan = mysqli_real_escape_string($conn,$_POST['pekerjaan']);
    $pendidikan = mysqli_real_escape_string($conn,$_POST['pendidikan_terakhir']);
    $instansi = mysqli_real_escape_string($conn,$_POST['instansi']);
    $nomor_parkir = mysqli_real_escape_string($conn,$_POST['nomor_parkir']);
    $tujuan = mysqli_real_escape_string($conn,$_POST['tujuan']);
    $id_pelayanan = (int)$_POST['id_pelayanan'];

    $jam = date("H:i:s");

    mysqli_query($conn,"
    INSERT INTO pengunjung
    (
        nama,
        jenis_kelamin,
        instansi,
        no_hp,
        email,
        pekerjaan,
        usia,
        pendidikan_terakhir,
        nomor_parkir,
        id_pelayanan,
        tujuan,
        tanggal,
        jam
    )
    VALUES
    (
        '$nama',
        '$jenis_kelamin',
        '$instansi',
        '$no_hp',
        '$email',
        '$pekerjaan',
        '$usia',
        '$pendidikan',
        '$nomor_parkir',
        '$id_pelayanan',
        '$tujuan',
        '$tanggal',
        '$jam'
    )
    ");

    $id_pengunjung = mysqli_insert_id($conn);

    $dataPelayanan = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT kode
    FROM pelayanan
    WHERE id_pelayanan='$id_pelayanan'
    "));

    $kode = $dataPelayanan['kode'];

    $cek = mysqli_query($conn,"
    SELECT nomor_antrian
    FROM antrian
    WHERE id_pelayanan='$id_pelayanan'
    ORDER BY id_antrian DESC
    LIMIT 1
    ");

    if(mysqli_num_rows($cek)>0){

        $last = mysqli_fetch_assoc($cek);
        $angka = substr($last['nomor_antrian'],1);
        $angka++;

    }else{

        $angka = 1;

    }

    $nomor = $kode.sprintf("%03d",$angka);

    mysqli_query($conn,"
    INSERT INTO antrian
    (
        id_pengunjung,
        id_pelayanan,
        nomor_antrian,
        status,
        waktu_ambil
    )
    VALUES
    (
        '$id_pengunjung',
        '$id_pelayanan',
        '$nomor',
        'Menunggu',
        NOW()
    )
    ");

    echo "
    <script>
        window.location='dashboard.php?page=pengunjung&tambah=berhasil';
    </script>
    ";

    exit;

}
?>

<div class="card">

<div class="card-header bg-primary">

<h3 class="card-title">

<i class="fas fa-user-plus"></i>

Tambah Data Pengunjung

</h3>

</div>

<form method="POST">

<div class="card-body">

<div class="row">

<!-- IDENTITAS PENGUNJUNG -->

<div class="col-md-12">

    <div class="alert alert-primary">

        <h5>

            <i class="fas fa-user"></i>

            Identitas Pengunjung

        </h5>

    </div>

</div>


<!-- TANGGAL -->

<div class="col-md-6 mb-3">

    <label>Tanggal</label>

    <input
        type="date"
        name="tanggal"
        class="form-control"
        value="<?= date('Y-m-d'); ?>"
        required>

</div>


<!-- NAMA -->

<div class="col-md-6 mb-3">

    <label>Nama Lengkap</label>

    <input
        type="text"
        name="nama"
        class="form-control"
        required>

</div>


<!-- JENIS KELAMIN -->

<div class="col-md-6 mb-3">

    <label>Jenis Kelamin</label>

    <select
        name="jenis_kelamin"
        class="form-control"
        required>

        <option value="">-- Pilih Jenis Kelamin --</option>

        <option value="Laki-laki">Laki-laki</option>

        <option value="Perempuan">Perempuan</option>

    </select>

</div>


<!-- USIA -->

<div class="col-md-6 mb-3">

    <label>Usia</label>

    <input
        type="number"
        name="usia"
        min="1"
        max="120"
        class="form-control"
        required>

</div>


<!-- NO HP -->

<div class="col-md-6 mb-3">

    <label>Nomor HP</label>

    <input
        type="text"
        name="no_hp"
        maxlength="15"
        class="form-control"
        oninput="this.value=this.value.replace(/[^0-9]/g,'')"
        required>

</div>


<!-- EMAIL -->

<div class="col-md-6 mb-3">

    <label>Email Aktif</label>

    <input
        type="email"
        name="email"
        class="form-control">

</div>


<!-- PEKERJAAN -->

<div class="col-md-6 mb-3">

    <label>Pekerjaan</label>

    <input
        type="text"
        name="pekerjaan"
        class="form-control">

</div>


<!-- PENDIDIKAN -->

<div class="col-md-6 mb-3">

    <label>Pendidikan Terakhir</label>

    <select
    name="pendidikan_terakhir"
    class="form-control"
    required>

    <option value="">-- Pilih Pendidikan --</option>

    <option value="SMP sederajat">SMP sederajat</option>

    <option value="SMA sederajat">SMA sederajat</option>

    <option value="Diploma 1-3">Diploma 1-3</option>

    <option value="D4/S1">D4/S1</option>

    <option value="S2">S2</option>

    <option value="S3">S3</option>

</select>

</div>

<!-- INSTANSI -->

<div class="col-md-6 mb-3">

    <label>Asal Instansi</label>

    <input
        type="text"
        name="instansi"
        class="form-control"
        required>

</div>


<!-- NOMOR PARKIR -->

<div class="col-md-6 mb-3">

    <label>Nomor Parkir</label>

    <input
        type="text"
        name="nomor_parkir"
        class="form-control"
        placeholder="Nomor yang diberikan satpam">

</div>

<!-- INFORMASI KUNJUNGAN -->

<div class="col-md-12 mt-3">

    <div class="alert alert-success">

        <h5>

            <i class="fas fa-building"></i>

            Informasi Kunjungan

        </h5>

    </div>

</div>



<!-- PELAYANAN -->

<div class="col-md-12 mb-3">

    <label>Jenis Pelayanan</label>

    <select
        name="id_pelayanan"
        class="form-control"
        required>

        <option value="">-- Pilih Pelayanan --</option>

        <?php while($p = mysqli_fetch_assoc($pelayanan)){ ?>

        <option value="<?= $p['id_pelayanan']; ?>">

            <?= $p['kode']; ?> - <?= $p['nama_pelayanan']; ?>

        </option>

        <?php } ?>

    </select>

</div>


<!-- TUJUAN -->

<div class="col-md-12 mb-3">

    <label>Tujuan Kunjungan</label>

    <textarea
        name="tujuan"
        class="form-control"
        rows="5"
        required></textarea>

</div>
<!-- TOMBOL -->

</div>

</div>

<div class="card-footer">

    <button
        type="submit"
        name="simpan"
        class="btn btn-primary">

        <i class="fas fa-save"></i>

        Simpan Data

    </button>

    <a
        href="dashboard.php?page=pengunjung"
        class="btn btn-secondary">

        <i class="fas fa-arrow-left"></i>

        Kembali

    </a>

</div>

</form>

</div>