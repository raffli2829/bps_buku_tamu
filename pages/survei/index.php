<?php
include 'config/koneksi.php';

$tanggal1 = isset($_GET['tanggal1']) ? $_GET['tanggal1'] : '';
$tanggal2 = isset($_GET['tanggal2']) ? $_GET['tanggal2'] : '';

// Query Statistik Keseluruhan
$stat_query = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_responden,
        AVG(rating_pelayanan) as avg_rating,
        AVG(keramahan_petugas) as avg_ramah,
        AVG(kecepatan_layanan) as avg_cepat,
        AVG(kemudahan_prosedur) as avg_mudah,
        SUM(rating_pelayanan = 5) as b5,
        SUM(rating_pelayanan = 4) as b4,
        SUM(rating_pelayanan = 3) as b3,
        SUM(rating_pelayanan = 2) as b2,
        SUM(rating_pelayanan = 1) as b1
    FROM survei_kepuasan
");
$stats = mysqli_fetch_assoc($stat_query);

$total_responden = (int)($stats['total_responden'] ?? 0);
$avg_rating = $total_responden > 0 ? round($stats['avg_rating'], 2) : 0;
$avg_ramah = $total_responden > 0 ? round($stats['avg_ramah'], 2) : 0;
$avg_cepat = $total_responden > 0 ? round($stats['avg_cepat'], 2) : 0;
$avg_mudah = $total_responden > 0 ? round($stats['avg_mudah'], 2) : 0;

// Menentukan Kategori Mutu Pelayanan
$mutu_huruf = 'A';
$mutu_teks = 'Sangat Baik (Sangat Puas)';
$mutu_badge = 'badge-success';

if ($avg_rating < 2.0) {
    $mutu_huruf = 'D';
    $mutu_teks = 'Tidak Baik';
    $mutu_badge = 'badge-danger';
} elseif ($avg_rating < 3.0) {
    $mutu_huruf = 'C';
    $mutu_teks = 'Kurang Baik';
    $mutu_badge = 'badge-warning';
} elseif ($avg_rating < 4.0) {
    $mutu_huruf = 'B';
    $mutu_teks = 'Baik (Puas)';
    $mutu_badge = 'badge-primary';
}

// Filter Tanggal
$filter_sql = "";
if (!empty($tanggal1) && !empty($tanggal2)) {
    $t1 = mysqli_real_escape_string($conn, $tanggal1 . ' 00:00:00');
    $t2 = mysqli_real_escape_string($conn, $tanggal2 . ' 23:59:59');
    $filter_sql = "WHERE s.waktu_survei BETWEEN '$t1' AND '$t2'";
}

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
    ORDER BY s.id_survei DESC
");
?>

<div class="row">

    <!-- Card 1: Nilai Rata-Rata IKM -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-primary">
            <div class="inner">
                <h3><?= $avg_rating > 0 ? number_format($avg_rating, 2) : '-'; ?> <sup style="font-size: 18px;">/ 5.0</sup></h3>
                <p>Indeks Kepuasan (IKM)</p>
            </div>
            <div class="icon">
                <i class="fas fa-star"></i>
            </div>
            <span class="small-box-footer">
                <span class="badge <?= $mutu_badge; ?> px-2 py-1"><?= $mutu_huruf; ?> - <?= $mutu_teks; ?></span>
            </span>
        </div>
    </div>

    <!-- Card 2: Total Responden -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3><?= number_format($total_responden); ?></h3>
                <p>Total Responden SKM</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
            <span class="small-box-footer">
                Partisipasi Pengunjung
            </span>
        </div>
    </div>

    <!-- Card 3: Keramahan Petugas -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3><?= $avg_ramah > 0 ? number_format($avg_ramah, 2) : '-'; ?></h3>
                <p>Keramahan Petugas</p>
            </div>
            <div class="icon">
                <i class="fas fa-smile"></i>
            </div>
            <span class="small-box-footer">
                Skor Aspek Pelayanan
            </span>
        </div>
    </div>

    <!-- Card 4: Kecepatan Layanan -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3><?= $avg_cepat > 0 ? number_format($avg_cepat, 2) : '-'; ?></h3>
                <p>Kecepatan Layanan</p>
            </div>
            <div class="icon">
                <i class="fas fa-stopwatch"></i>
            </div>
            <span class="small-box-footer">
                Skor Aspek Pelayanan
            </span>
        </div>
    </div>

</div>

<!-- Distribusi Bintang Penilaian -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-chart-bar mr-1 text-primary"></i> Distribusi Penilaian Pengunjung
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $ratings_data = [
                        ['label' => '5 Bintang (Sangat Puas)', 'count' => (int)($stats['b5'] ?? 0), 'color' => 'bg-success'],
                        ['label' => '4 Bintang (Puas)', 'count' => (int)($stats['b4'] ?? 0), 'color' => 'bg-primary'],
                        ['label' => '3 Bintang (Cukup)', 'count' => (int)($stats['b3'] ?? 0), 'color' => 'bg-warning'],
                        ['label' => '2 Bintang (Kurang)', 'count' => (int)($stats['b2'] ?? 0), 'color' => 'bg-orange'],
                        ['label' => '1 Bintang (S. Tidak Puas)', 'count' => (int)($stats['b1'] ?? 0), 'color' => 'bg-danger'],
                    ];

                    foreach ($ratings_data as $rd) {
                        $pct = $total_responden > 0 ? round(($rd['count'] / $total_responden) * 100, 1) : 0;
                    ?>
                    <div class="col-md-12 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="font-weight-semibold text-dark" style="font-size: 13.5px;">
                                <?= $rd['label']; ?>
                            </span>
                            <span class="text-muted small">
                                <strong><?= $rd['count']; ?></strong> ulasan (<?= $pct; ?>%)
                            </span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 6px;">
                            <div class="progress-bar <?= $rd['color']; ?>" role="progressbar" style="width: <?= $pct; ?>%" aria-valuenow="<?= $pct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter dan Tabel Data Ulasan -->
<div class="card card-outline card-primary shadow-sm">

    <div class="card-header">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-list-alt mr-1 text-primary"></i> Data Ulasan &amp; Kritik/Saran Pengunjung
        </h3>
        <div class="card-tools">
            <a href="pages/survei/cetak.php?tanggal1=<?= urlencode($tanggal1); ?>&tanggal2=<?= urlencode($tanggal2); ?>" target="_blank" class="btn btn-outline-secondary btn-sm mr-2">
                <i class="fas fa-print mr-1"></i> Cetak Rekap
            </a>
        </div>
    </div>

    <div class="card-body">

        <!-- Form Filter Tanggal -->
        <form method="GET" action="dashboard.php" class="mb-4">
            <input type="hidden" name="page" value="survei">
            <div class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="small font-weight-bold text-muted">Tanggal Awal</label>
                    <input type="date" name="tanggal1" class="form-control" value="<?= htmlspecialchars($tanggal1); ?>">
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small font-weight-bold text-muted">Tanggal Akhir</label>
                    <input type="date" name="tanggal2" class="form-control" value="<?= htmlspecialchars($tanggal2); ?>">
                </div>
                <div class="col-md-4 mb-2">
                    <button type="submit" class="btn btn-primary mr-1">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <?php if(!empty($tanggal1) || !empty($tanggal2)){ ?>
                    <a href="dashboard.php?page=survei" class="btn btn-default">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                    <?php } ?>
                </div>
            </div>
        </form>

        <!-- Tabel Ulasan -->
        <div class="table-responsive">
            <table id="tabelSurvei" class="table table-bordered table-hover table-striped">
                <thead class="bg-light">
                    <tr>
                        <th width="40">No</th>
                        <th width="140">Waktu Survei</th>
                        <th width="90">No. Antrean</th>
                        <th>Nama Pengunjung</th>
                        <th>Pelayanan</th>
                        <th width="130">Rating Bintang</th>
                        <th>Rincian Aspek</th>
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
                        <td>
                            <span class="small font-weight-bold text-dark">
                                <?= date('d-m-Y', strtotime($row['waktu_survei'])); ?>
                            </span><br>
                            <small class="text-muted"><?= date('H:i', strtotime($row['waktu_survei'])); ?> WIB</small>
                        </td>
                        <td class="text-center font-weight-bold text-primary">
                            <?= htmlspecialchars($row['nomor_antrian']); ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['nama_pengunjung']); ?></strong>
                        </td>
                        <td>
                            <span class="badge badge-light border">
                                <?= htmlspecialchars($row['nama_pelayanan']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="text-warning text-nowrap">
                                <?php for($i=1; $i<=5; $i++){ ?>
                                    <i class="fas fa-star <?= $i <= $row['rating_pelayanan'] ? 'text-warning' : 'text-muted opacity-25'; ?>" style="font-size: 13px;"></i>
                                <?php } ?>
                            </div>
                            <small class="font-weight-bold text-muted">Skor: <?= $row['rating_pelayanan']; ?>/5</small>
                        </td>
                        <td class="small">
                            <div>Keramahan: <strong><?= $row['keramahan_petugas']; ?>/5</strong></div>
                            <div>Kecepatan: <strong><?= $row['kecepatan_layanan']; ?>/5</strong></div>
                            <div>Kemudahan: <strong><?= $row['kemudahan_prosedur']; ?>/5</strong></div>
                        </td>
                        <td>
                            <?php if (!empty($row['saran'])) { ?>
                                <span class="text-dark font-italic">
                                    "<?= htmlspecialchars($row['saran']); ?>"
                                </span>
                            <?php } else { ?>
                                <span class="text-muted small fst-italic">- Tidak ada saran -</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php 
                        } 
                    } else { 
                    ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="fas fa-info-circle mr-1"></i> Belum ada data survei kepuasan yang tercatat.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<script>
$(function () {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#tabelSurvei')) {
        $('#tabelSurvei').DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "language": {
                "search": "Cari Ulasan:",
                "paginate": {
                    "previous": "Sebelumnya",
                    "next": "Berikutnya"
                },
                "emptyTable": "Tidak ada data ulasan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ ulasan",
                "lengthMenu": "Tampilkan _MENU_ data per halaman"
            }
        });
    }
});
</script>
