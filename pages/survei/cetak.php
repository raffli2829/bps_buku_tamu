<?php
include '../../config/koneksi.php';

$tanggal1 = isset($_GET['tanggal1']) ? $_GET['tanggal1'] : '';
$tanggal2 = isset($_GET['tanggal2']) ? $_GET['tanggal2'] : '';

$filter_sql = "";
if (!empty($tanggal1) && !empty($tanggal2)) {
    $t1 = mysqli_real_escape_string($conn, $tanggal1 . ' 00:00:00');
    $t2 = mysqli_real_escape_string($conn, $tanggal2 . ' 23:59:59');
    $filter_sql = "WHERE s.waktu_survei BETWEEN '$t1' AND '$t2'";
}

// Ambil data ulasan & survei kepuasan
$query_list = mysqli_query($conn, "
    SELECT 
        s.*,
        a.nomor_antrian,
        p.nama as nama_pengunjung,
        pl.nama_pelayanan
    FROM survei_kepuasan s
    JOIN antrian a ON s.id_antrian = a.id_antrian
    JOIN pengunjung p ON s.id_pengunjung = p.id_pengunjung
    JOIN pelayanan pl ON a.id_pelayanan = pl.id_pelayanan
    $filter_sql
    ORDER BY s.waktu_survei DESC
");

// Query ringkasan statistik untuk periode ini
$stat_sql = "
    SELECT 
        COUNT(*) as total_responden,
        AVG(rating_pelayanan) as avg_rating,
        AVG(keramahan_petugas) as avg_ramah,
        AVG(kecepatan_layanan) as avg_cepat,
        AVG(kemudahan_prosedur) as avg_mudah
    FROM survei_kepuasan s
    $filter_sql
";
$stat_res = mysqli_query($conn, $stat_sql);
$stats = mysqli_fetch_assoc($stat_res);
$total = (int)($stats['total_responden'] ?? 0);
$avg_rating = $total > 0 ? round($stats['avg_rating'], 2) : 0;
$avg_ramah = $total > 0 ? round($stats['avg_ramah'], 2) : 0;
$avg_cepat = $total > 0 ? round($stats['avg_cepat'], 2) : 0;
$avg_mudah = $total > 0 ? round($stats['avg_mudah'], 2) : 0;

$mutu = 'Sangat Baik (Sangat Puas)';
if ($avg_rating < 2.0) {
    $mutu = 'Tidak Baik';
} elseif ($avg_rating < 3.0) {
    $mutu = 'Kurang Baik';
} elseif ($avg_rating < 4.0) {
    $mutu = 'Baik (Puas)';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Hasil Survei Kepuasan Masyarakat</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 25px 35px;
            color: #000;
        }

        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px solid #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }

        .kop-table td {
            border: none;
            padding: 4px;
            vertical-align: middle;
        }

        .logo {
            width: 85px;
            display: block;
        }

        .judul {
            text-align: center;
        }

        .judul h2 {
            margin: 2px 0;
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .judul h3 {
            margin: 2px 0;
            font-size: 13pt;
            font-weight: bold;
        }

        .judul h4 {
            margin: 3px 0 2px 0;
            font-size: 11pt;
            font-weight: 600;
            text-transform: uppercase;
        }

        .judul p {
            margin: 3px 0 0 0;
            font-size: 10pt;
        }

        .summary-box {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .summary-box td {
            border: 1px solid #000;
            padding: 6px 10px;
            background: #f8f9fa;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }

        table.data-table th, 
        table.data-table td {
            border: 1px solid #000;
            padding: 7px 6px;
            vertical-align: top;
        }

        table.data-table th {
            background: #e9ecef;
            text-align: center;
            font-weight: bold;
            color: #000;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        .aspek-item {
            margin-bottom: 2px;
            line-height: 1.35;
        }

        .saran-text {
            font-style: italic;
            line-height: 1.35;
        }

        .ttd {
            width: 280px;
            float: right;
            margin-top: 35px;
            text-align: center;
            font-size: 10pt;
            page-break-inside: avoid;
        }

        @media print {
            body {
                margin: 15mm 15mm;
            }
            .no-print {
                display: none !important;
            }
            table.data-table th {
                background: #e9ecef !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .summary-box td {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- KOP SURAT SESUAI STANDAR BPS KABUPATEN BANGKA -->
    <table class="kop-table">
        <tr>
            <td width="95" align="center">
                <img src="../../assets/img/logo_bps.png" class="logo" alt="Logo BPS">
            </td>
            <td class="judul">
                <h2>BADAN PUSAT STATISTIK</h2>
                <h3>KABUPATEN BANGKA</h3>
                <h4>LAPORAN REKAPITULASI SURVEI KEPUASAN MASYARAKAT (SKM)</h4>
                <?php if (!empty($tanggal1) && !empty($tanggal2)) { ?>
                    <p>Periode: <b><?= date('d-m-Y', strtotime($tanggal1)); ?></b> s/d <b><?= date('d-m-Y', strtotime($tanggal2)); ?></b></p>
                <?php } else { ?>
                    <p>Periode: <b>Semua Waktu</b></p>
                <?php } ?>
            </td>
        </tr>
    </table>

    <!-- RINGKASAN REKAP STATISTIK -->
    <table class="summary-box">
        <tr>
            <td width="25%"><strong>Total Responden:</strong> <?= number_format($total); ?> Orang</td>
            <td width="35%"><strong>Indeks Kepuasan (IKM):</strong> <?= $avg_rating > 0 ? $avg_rating . ' / 5.0' : '-'; ?> (<?= $mutu; ?>)</td>
            <td width="40%">
                <strong>Rata-rata Rincian Aspek:</strong><br>
                Keramahan: <b><?= $avg_ramah; ?>/5</b> | 
                Kecepatan: <b><?= $avg_cepat; ?>/5</b> | 
                Kemudahan: <b><?= $avg_mudah; ?>/5</b>
            </td>
        </tr>
    </table>

    <!-- TABEL DATA REKAPITULASI -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="35">No</th>
                <th width="85">Waktu Survei</th>
                <th width="75">Antrian</th>
                <th width="150">Nama Pengunjung</th>
                <th width="130">Pelayanan</th>
                <th width="80">Rating</th>
                <th width="140">Rincian Aspek</th>
                <th>Kritik &amp; Saran</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if (mysqli_num_rows($query_list) > 0) {
                while ($row = mysqli_fetch_assoc($query_list)) {
            ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td class="text-center">
                    <b><?= date('d-m-Y', strtotime($row['waktu_survei'])); ?></b><br>
                    <small style="color: #444;"><?= date('H:i', strtotime($row['waktu_survei'])); ?> WIB</small>
                </td>
                <td class="text-center font-bold">
                    <?= htmlspecialchars($row['nomor_antrian']); ?>
                </td>
                <td>
                    <b><?= htmlspecialchars($row['nama_pengunjung']); ?></b>
                </td>
                <td>
                    <?= htmlspecialchars($row['nama_pelayanan']); ?>
                </td>
                <td class="text-center">
                    <span style="font-size: 13pt; color: #f59e0b;"><?= str_repeat('★', (int)$row['rating_pelayanan']); ?></span><br>
                    <b><?= $row['rating_pelayanan']; ?> / 5</b>
                </td>
                <td>
                    <div class="aspek-item">• Keramahan: <b><?= $row['keramahan_petugas']; ?>/5</b></div>
                    <div class="aspek-item">• Kecepatan: <b><?= $row['kecepatan_layanan']; ?>/5</b></div>
                    <div class="aspek-item">• Kemudahan: <b><?= $row['kemudahan_prosedur']; ?>/5</b></div>
                </td>
                <td>
                    <?php if (!empty($row['saran'])) { ?>
                        <div class="saran-text">"<?= nl2br(htmlspecialchars($row['saran'])); ?>"</div>
                    <?php } else { ?>
                        <span style="color:#777; font-style:italic;">- Tidak ada saran -</span>
                    <?php } ?>
                </td>
            </tr>
            <?php 
                } 
            } else { 
            ?>
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px;">
                    <em>Tidak ada data survei kepuasan yang tercatat pada periode ini.</em>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <div class="ttd">
        Sungailiat, <?= date('d-m-Y'); ?><br>
        Mengetahui,<br>
        Kepala BPS Kabupaten Bangka
        <br><br><br><br><br>
        <b>__________________________</b><br>
        NIP. ........................................
    </div>

</body>
</html>
