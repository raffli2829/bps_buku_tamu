<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../../config/koneksi.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="alert alert-danger">ID pengunjung tidak ditemukan.</div>';
    exit;
}

$id = (int) $_GET['id'];


/* =====================================================
   AMBIL DATA PENGUNJUNG
===================================================== */

$query = mysqli_query($conn, "
    SELECT *
    FROM pengunjung
    WHERE id_pengunjung = $id
");

if (!$query) {
    echo '<div class="alert alert-danger">
            Error Database: ' . mysqli_error($conn) . '
          </div>';
    exit;
}

$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo '<div class="alert alert-danger">
            Data pengunjung tidak ditemukan.
          </div>';
    exit;
}


/* =========================
   PROSES SIMPAN PERUBAHAN
========================= */

if (isset($_POST['simpan'])) {

    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal'] ?? '');
    $nama = mysqli_real_escape_string($conn, $_POST['nama'] ?? '');
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin'] ?? '');
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $pekerjaan = mysqli_real_escape_string($conn, $_POST['pekerjaan'] ?? '');
    $usia = mysqli_real_escape_string($conn, $_POST['usia'] ?? '');
    $pendidikan = mysqli_real_escape_string($conn, $_POST['pendidikan_terakhir'] ?? '');
    $instansi = mysqli_real_escape_string($conn, $_POST['instansi'] ?? '');
    $nomor_parkir = mysqli_real_escape_string($conn, $_POST['nomor_parkir'] ?? '');
    $tujuan = mysqli_real_escape_string($conn, $_POST['tujuan'] ?? '');
    $id_pelayanan = (int) $_POST['id_pelayanan'];

    $sql = "
        UPDATE pengunjung SET
            tanggal = '$tanggal',
            nama = '$nama',
            jenis_kelamin = '$jenis_kelamin',
            no_hp = '$no_hp',
            email = '$email',
            pekerjaan = '$pekerjaan',
            usia = '$usia',
            pendidikan_terakhir = '$pendidikan',
            instansi = '$instansi',
            nomor_parkir = '$nomor_parkir',
            id_pelayanan='$id_pelayanan',
            tujuan = '$tujuan'
        WHERE id_pengunjung = $id
    ";

       $update = mysqli_query($conn, $sql);

if($update){

    // Ambil data antrian lama
    $antrian = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT *
    FROM antrian
    WHERE id_pengunjung='$id'
    "));

    // Jika pelayanan atau tanggal berubah
if(
    $antrian['id_pelayanan'] != $id_pelayanan ||
    $data['tanggal'] != $tanggal
){

        // Ambil kode pelayanan baru
        $pel = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT kode
        FROM pelayanan
        WHERE id_pelayanan='$id_pelayanan'
        "));

        $kode = $pel['kode'];

        // Cari nomor antrean terakhir pada pelayanan baru di tanggal yang sama
        $cek = mysqli_query($conn,"
        SELECT a.nomor_antrian
        FROM antrian a
        INNER JOIN pengunjung p
        ON p.id_pengunjung=a.id_pengunjung
        WHERE
        a.id_pelayanan='$id_pelayanan'
        AND p.tanggal='$tanggal'
        ORDER BY a.id_antrian DESC
        LIMIT 1
        ");

        if(mysqli_num_rows($cek)>0){

            $last = mysqli_fetch_assoc($cek);

            $angka = substr($last['nomor_antrian'],1);

            $angka++;

        }else{

            $angka = 1;

        }

        $nomorBaru = $kode.sprintf("%03d",$angka);

        // Update tabel antrian
        mysqli_query($conn,"
        UPDATE antrian
        SET
            id_pelayanan='$id_pelayanan',
            nomor_antrian='$nomorBaru',
            waktu_ambil=NOW()
        WHERE id_pengunjung='$id'
        ");

    }

    echo "
    <script>
        window.location='dashboard.php?page=pengunjung&update=berhasil';
    </script>
    ";

    exit;

}else{

    echo "
    <div class='alert alert-danger'>
        Gagal menyimpan perubahan.<br>
        ".mysqli_error($conn)."
    </div>
    ";

}

} // menutup if (isset($_POST['simpan']))
?>


<div class="card">

    <div class="card-header bg-warning">

        <h3 class="card-title">

            <i class="fas fa-edit"></i>

            Edit Data Pengunjung

        </h3>

    </div>


    <form method="POST">

        <div class="card-body">

            <div class="row">


                <!-- IDENTITAS -->

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
                        value="<?= htmlspecialchars($data['tanggal'] ?? '') ?>"
                        required>

                </div>


                <!-- NAMA -->

                <div class="col-md-6 mb-3">

                    <label>Nama Lengkap</label>

                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        value="<?= htmlspecialchars($data['nama'] ?? '') ?>"
                        required>

                </div>


                <!-- JENIS KELAMIN -->

                <div class="col-md-6 mb-3">

                    <label>Jenis Kelamin</label>

                    <select
                        name="jenis_kelamin"
                        class="form-control"
                        required>

                        <option value="Laki-laki"
                        <?= ($data['jenis_kelamin'] ?? '') == 'Laki-laki' ? 'selected' : '' ?>>
                            Laki-laki
                        </option>

                        <option value="Perempuan"
                        <?= ($data['jenis_kelamin'] ?? '') == 'Perempuan' ? 'selected' : '' ?>>
                            Perempuan
                        </option>

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
                        value="<?= htmlspecialchars($data['usia'] ?? '') ?>"
                        required>

                </div>


                <!-- NO HP -->

                <div class="col-md-6 mb-3">

                    <label>No HP</label>

                    <input
                        type="text"
                        name="no_hp"
                        maxlength="15"
                        class="form-control"
                        value="<?= htmlspecialchars($data['no_hp'] ?? '') ?>"
                        required>

                </div>


                <!-- EMAIL -->

                <div class="col-md-6 mb-3">

                    <label>Email Aktif</label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars($data['email'] ?? '') ?>">

                </div>


                <!-- PEKERJAAN -->

                <div class="col-md-6 mb-3">

                    <label>Pekerjaan</label>

                    <input
                        type="text"
                        name="pekerjaan"
                        class="form-control"
                        value="<?= htmlspecialchars($data['pekerjaan'] ?? '') ?>">

                </div>


                <!-- PENDIDIKAN -->

<div class="col-md-6 mb-3">

    <label>Pendidikan Terakhir</label>

    <select
        name="pendidikan_terakhir"
        class="form-control"
        required>

        <option value="">-- Pilih Pendidikan --</option>

        <option value="SMP sederajat"
        <?= ($data['pendidikan_terakhir'] ?? '') == 'SMP sederajat' ? 'selected' : '' ?>>
            SMP sederajat
        </option>

        <option value="SMA sederajat"
        <?= ($data['pendidikan_terakhir'] ?? '') == 'SMA sederajat' ? 'selected' : '' ?>>
            SMA sederajat
        </option>

        <option value="Diploma 1-3"
        <?= ($data['pendidikan_terakhir'] ?? '') == 'Diploma 1-3' ? 'selected' : '' ?>>
            Diploma 1-3
        </option>

        <option value="D4/S1"
        <?= ($data['pendidikan_terakhir'] ?? '') == 'D4/S1' ? 'selected' : '' ?>>
            D4/S1
        </option>

        <option value="S2"
        <?= ($data['pendidikan_terakhir'] ?? '') == 'S2' ? 'selected' : '' ?>>
            S2
        </option>

        <option value="S3"
        <?= ($data['pendidikan_terakhir'] ?? '') == 'S3' ? 'selected' : '' ?>>
            S3
        </option>

    </select>

</div>

                <!-- INSTANSI -->

                <div class="col-md-6 mb-3">

                    <label>Asal Instansi</label>

                    <input
                        type="text"
                        name="instansi"
                        class="form-control"
                        value="<?= htmlspecialchars($data['instansi'] ?? '') ?>">

                </div>


                <!-- NOMOR PARKIR -->

                <div class="col-md-6 mb-3">

                    <label>Nomor Parkir</label>

                    <input
                        type="text"
                        name="nomor_parkir"
                        class="form-control"
                        value="<?= htmlspecialchars($data['nomor_parkir'] ?? '') ?>">

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

                <div class="col-md-12 mb-3">

    <label>Jenis Pelayanan</label>

    <select
        name="id_pelayanan"
        class="form-control"
        required>

        <?php

        $pelayanan = mysqli_query($conn,"
        SELECT *
        FROM pelayanan
        ORDER BY kode ASC
        ");

        while($p=mysqli_fetch_assoc($pelayanan)){

        ?>

        <option
            value="<?= $p['id_pelayanan']; ?>"
            <?= ($p['id_pelayanan']==$data['id_pelayanan']) ? 'selected' : ''; ?>>

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
                        required><?= htmlspecialchars($data['tujuan'] ?? '') ?></textarea>

                </div>


            </div>

        </div>


        <!-- FOOTER -->

        <div class="card-footer">

            <button
                type="submit"
                name="simpan"
                class="btn btn-primary">

                <i class="fas fa-save"></i>

                Simpan Perubahan

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
