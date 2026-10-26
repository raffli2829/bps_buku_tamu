<?php

include 'config/koneksi.php';

/* ==========================================================
   MENGAMBIL DATA DARI FORM REGISTRASI
========================================================== */

$nama = mysqli_real_escape_string(
    $conn,
    $_POST['nama'] ?? ''
);

$jenis_kelamin = mysqli_real_escape_string(
    $conn,
    $_POST['jenis_kelamin'] ?? ''
);

$instansi = mysqli_real_escape_string(
    $conn,
    $_POST['instansi'] ?? ''
);

$no_hp = mysqli_real_escape_string(
    $conn,
    $_POST['no_hp'] ?? ''
);

$email = mysqli_real_escape_string(
    $conn,
    $_POST['email'] ?? ''
);

$pekerjaan = mysqli_real_escape_string(
    $conn,
    $_POST['pekerjaan'] ?? ''
);

$usia = mysqli_real_escape_string(
    $conn,
    $_POST['usia'] ?? ''
);

$pendidikan_terakhir = mysqli_real_escape_string(
    $conn,
    $_POST['pendidikan_terakhir'] ?? ''
);

$nomor_parkir = mysqli_real_escape_string(
    $conn,
    $_POST['nomor_parkir'] ?? ''
);

$id_pelayanan = (int) ($_POST['id_pelayanan'] ?? 0);

$tujuan = mysqli_real_escape_string(
    $conn,
    $_POST['tujuan'] ?? ''
);


/* ==========================================================
   TANGGAL DAN JAM REGISTRASI
   Tanggal otomatis mengikuti tanggal saat pengunjung daftar
========================================================== */

$tanggal = date('Y-m-d');
$jam = date('H:i:s');


/* ==========================================================
   VALIDASI PELAYANAN
========================================================== */

if ($id_pelayanan <= 0) {

    echo "
    <script>
        alert('Pelayanan belum dipilih.');
        history.back();
    </script>
    ";

    exit;
}


/* ==========================================================
   AMBIL KODE DAN NAMA PELAYANAN
========================================================== */

$query_pelayanan = mysqli_query($conn, "

    SELECT
        kode,
        nama_pelayanan

    FROM pelayanan

    WHERE id_pelayanan = '$id_pelayanan'

    LIMIT 1

");


if (!$query_pelayanan || mysqli_num_rows($query_pelayanan) == 0) {

    echo "
    <script>
        alert('Data pelayanan tidak ditemukan.');
        history.back();
    </script>
    ";

    exit;
}


$pelayanan = mysqli_fetch_assoc($query_pelayanan);

$kode = $pelayanan['kode'];


/* ==========================================================
   SIMPAN DATA PENGUNJUNG
========================================================== */

$simpan_pengunjung = mysqli_query($conn, "

    INSERT INTO pengunjung (

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

    VALUES (

        '$nama',
        '$jenis_kelamin',
        '$instansi',
        '$no_hp',
        '$email',
        '$pekerjaan',
        '$usia',
        '$pendidikan_terakhir',
        '$nomor_parkir',
        '$id_pelayanan',
        '$tujuan',
        '$tanggal',
        '$jam'

    )

");


/* ==========================================================
   CEK APAKAH DATA PENGUNJUNG BERHASIL DISIMPAN
========================================================== */

if (!$simpan_pengunjung) {

    echo "
    <script>
        alert('Data pengunjung gagal disimpan.');
        history.back();
    </script>
    ";

    exit;
}


/* ==========================================================
   AMBIL ID PENGUNJUNG
========================================================== */

$id_pengunjung = mysqli_insert_id($conn);


/* ==========================================================
   MENCARI NOMOR ANTRIAN TERAKHIR
   BERDASARKAN:
   1. TANGGAL YANG SAMA
   2. PELAYANAN YANG SAMA
========================================================== */

$query_antrian = mysqli_query($conn, "

    SELECT
        a.nomor_antrian

    FROM antrian a

    INNER JOIN pengunjung p
        ON a.id_pengunjung = p.id_pengunjung

    WHERE
        a.id_pelayanan = '$id_pelayanan'
        AND p.tanggal = '$tanggal'

    ORDER BY a.id_antrian DESC

    LIMIT 1

");


/* ==========================================================
   MENENTUKAN NOMOR BERIKUTNYA
========================================================== */

if ($query_antrian && mysqli_num_rows($query_antrian) > 0) {

    $data_antrian = mysqli_fetch_assoc($query_antrian);

    $nomor_lama = $data_antrian['nomor_antrian'];

    /*
       Mengambil angka setelah kode pelayanan.

       Contoh:
       A001 -> 001
       B015 -> 015
       C099 -> 099
    */

    $angka = (int) substr($nomor_lama, 1);

    $angka++;

} else {

    /*
       Jika belum ada antrean untuk
       pelayanan tersebut pada hari ini,
       maka mulai dari 001.
    */

    $angka = 1;

}


/* ==========================================================
   MEMBUAT NOMOR ANTRIAN

   Contoh:
   Kode A + 001 = A001
   Kode B + 001 = B001
   Kode C + 001 = C001
   Kode D + 001 = D001
========================================================== */

$nomor_antrian = $kode . sprintf("%03d", $angka);


/* ==========================================================
   SIMPAN DATA ANTRIAN
========================================================== */

$simpan_antrian = mysqli_query($conn, "

    INSERT INTO antrian (

        id_pengunjung,
        id_pelayanan,
        nomor_antrian,
        status,
        waktu_ambil

    )

    VALUES (

        '$id_pengunjung',
        '$id_pelayanan',
        '$nomor_antrian',
        'Menunggu',
        NOW()

    )

");


/* ==========================================================
   CEK DATA ANTRIAN
========================================================== */

if (!$simpan_antrian) {

    /*
       Jika penyimpanan antrian gagal,
       data pengunjung tetap ada tetapi
       pengguna diberi informasi kesalahan.
    */

    echo "
    <script>

        alert('Data pengunjung berhasil disimpan, tetapi nomor antrian gagal dibuat. Silakan hubungi administrator.');

        window.location='registrasi.php';

    </script>
    ";

    exit;
}


/* ==========================================================
   REDIRECT KE HALAMAN NOMOR ANTRIAN
========================================================== */

header(
    "Location: nomor_antrian.php?id=" . $id_pengunjung
);

exit;

?>