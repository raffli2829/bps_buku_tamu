<?php
include 'config/koneksi.php';

// Helper redirect aman anti-headers-already-sent
if (!function_exists('redirect_ke')) {
    function redirect_ke($url) {
        if (!headers_sent()) {
            header("Location: " . $url);
            exit;
        } else {
            echo "<script>window.location.replace(" . json_encode($url) . ");</script>";
            echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . "'></noscript>";
            exit;
        }
    }
}

// Hitung nomor urutan berikutnya yang otomatis tersedia
$q_max = mysqli_query($conn, "SELECT COALESCE(MAX(urutan), 0) + 1 AS next_urutan FROM pengumuman_display");
$row_max = mysqli_fetch_assoc($q_max);
$next_urutan = (int)($row_max['next_urutan'] ?? 1);

// Notifikasi pesan
$pesan = '';
$pesan_tipe = '';

// PROSES TAMBAH PENGUMUMAN
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $isi_teks = trim($_POST['isi_teks'] ?? '');
    $urutan = (int)($_POST['urutan'] ?? $next_urutan);
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    if ($urutan < 1) {
        $urutan = 1;
    }

    if (!empty($isi_teks)) {
        // Capping agar tidak ada gap urutan kosong yang terlalu jauh
        if ($urutan > $next_urutan) {
            $urutan = $next_urutan;
        }

        $conn->begin_transaction();
        try {
            // Cek apakah nomor urutan target sudah dipakai oleh pengumuman lain
            $cek = $conn->prepare("SELECT COUNT(*) AS total FROM pengumuman_display WHERE urutan = ?");
            $cek->bind_param("i", $urutan);
            $cek->execute();
            $res_cek = $cek->get_result()->fetch_assoc();
            $cek->close();

            if (($res_cek['total'] ?? 0) > 0) {
                // Geser mundur (+1) semua pengumuman yang urutannya >= $urutan
                // ORDER BY urutan DESC wajib agar tidak bentrok dengan UNIQUE KEY di database
                $shift_stmt = $conn->prepare("UPDATE pengumuman_display SET urutan = urutan + 1 WHERE urutan >= ? ORDER BY urutan DESC");
                $shift_stmt->bind_param("i", $urutan);
                $shift_stmt->execute();
                $shift_stmt->close();
            }

            // Simpan pengumuman baru pada slot urutan yang ditentukan
            $stmt = $conn->prepare("INSERT INTO pengumuman_display (isi_teks, status_aktif, urutan, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sii", $isi_teks, $status_aktif, $urutan);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            redirect_ke("dashboard.php?page=pengumuman&pesan=added");
        } catch (Exception $e) {
            $conn->rollback();
            $pesan = 'Gagal menambahkan pengumuman: ' . $e->getMessage();
            $pesan_tipe = 'danger';
        }
    } else {
        $pesan = 'Teks pengumuman tidak boleh kosong.';
        $pesan_tipe = 'warning';
    }
}

// PROSES EDIT PENGUMUMAN
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id_pengumuman = (int)($_POST['id_pengumuman'] ?? 0);
    $isi_teks = trim($_POST['isi_teks'] ?? '');
    $new_urutan = (int)($_POST['urutan'] ?? 1);
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    if ($new_urutan < 1) {
        $new_urutan = 1;
    }

    if ($id_pengumuman > 0 && !empty($isi_teks)) {
        // Ambil data urutan lama dari pengumuman yang sedang diedit
        $q_old = $conn->prepare("SELECT urutan FROM pengumuman_display WHERE id_pengumuman = ? LIMIT 1");
        $q_old->bind_param("i", $id_pengumuman);
        $q_old->execute();
        $res_old = $q_old->get_result()->fetch_assoc();
        $q_old->close();

        if ($res_old) {
            $old_urutan = (int)$res_old['urutan'];

            // Batasi urutan maksimal agar sesuai total jumlah pengumuman
            $q_count = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pengumuman_display");
            $r_count = mysqli_fetch_assoc($q_count);
            $total_pengumuman = (int)($r_count['total'] ?? 1);

            if ($new_urutan > $total_pengumuman) {
                $new_urutan = $total_pengumuman;
            }

            $conn->begin_transaction();
            try {
                if ($new_urutan !== $old_urutan) {
                    // 1. Kosongkan slot dengan mengubah urutan item yang diedit ke 0 sementara waktu
                    $temp_stmt = $conn->prepare("UPDATE pengumuman_display SET urutan = 0 WHERE id_pengumuman = ?");
                    $temp_stmt->bind_param("i", $id_pengumuman);
                    $temp_stmt->execute();
                    $temp_stmt->close();

                    if ($new_urutan < $old_urutan) {
                        // Dipindahkan ke nomor lebih awal (misal: urutan 5 mau pindah ke 2)
                        // Pengumuman antara 2 s.d. 4 (urutan >= new_urutan AND urutan < old_urutan)
                        // otomatis mundur (+1) ke belakang: 2 jadi 3, 3 jadi 4, 4 jadi 5. Nomor 1 tetap 1.
                        $shift_stmt = $conn->prepare("UPDATE pengumuman_display SET urutan = urutan + 1 WHERE urutan >= ? AND urutan < ? ORDER BY urutan DESC");
                        $shift_stmt->bind_param("ii", $new_urutan, $old_urutan);
                        $shift_stmt->execute();
                        $shift_stmt->close();
                    } else {
                        // Dipindahkan ke nomor lebih akhir (misal: urutan 2 mau pindah ke 5)
                        // Pengumuman antara 3 s.d. 5 (urutan > old_urutan AND urutan <= new_urutan)
                        // otomatis maju (-1): 3 jadi 2, 4 jadi 3, 5 jadi 4.
                        $shift_stmt = $conn->prepare("UPDATE pengumuman_display SET urutan = urutan - 1 WHERE urutan > ? AND urutan <= ? ORDER BY urutan ASC");
                        $shift_stmt->bind_param("ii", $old_urutan, $new_urutan);
                        $shift_stmt->execute();
                        $shift_stmt->close();
                    }
                }

                // 2. Simpan perubahan pengumuman ke urutan baru
                $stmt = $conn->prepare("UPDATE pengumuman_display SET isi_teks = ?, status_aktif = ?, urutan = ? WHERE id_pengumuman = ?");
                $stmt->bind_param("siii", $isi_teks, $status_aktif, $new_urutan, $id_pengumuman);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                redirect_ke("dashboard.php?page=pengumuman&pesan=edited");
            } catch (Exception $e) {
                $conn->rollback();
                $pesan = 'Gagal memperbarui pengumuman: ' . $e->getMessage();
                $pesan_tipe = 'danger';
            }
        } else {
            $pesan = 'Data pengumuman tidak ditemukan di database.';
            $pesan_tipe = 'danger';
        }
    } else {
        $pesan = 'Data edit tidak valid atau teks pengumuman kosong.';
        $pesan_tipe = 'warning';
    }
}

// PROSES TOGGLE STATUS
if (isset($_GET['aksi']) && $_GET['aksi'] === 'toggle') {
    $id = (int)($_GET['id'] ?? 0);
    $status = (int)($_GET['status'] ?? 0);
    $new_status = $status === 1 ? 0 : 1;

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE pengumuman_display SET status_aktif = ? WHERE id_pengumuman = ?");
        $stmt->bind_param("ii", $new_status, $id);
        $stmt->execute();
        $stmt->close();
        redirect_ke("dashboard.php?page=pengumuman&pesan=status_updated");
    }
}

// PROSES HAPUS
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $conn->begin_transaction();
        try {
            $q_del = $conn->prepare("SELECT urutan FROM pengumuman_display WHERE id_pengumuman = ? LIMIT 1");
            $q_del->bind_param("i", $id);
            $q_del->execute();
            $del_res = $q_del->get_result()->fetch_assoc();
            $q_del->close();

            if ($del_res) {
                $del_urutan = (int)$del_res['urutan'];
                $stmt = $conn->prepare("DELETE FROM pengumuman_display WHERE id_pengumuman = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();

                // Rapikan nomor urutan setelahnya (geser maju -1) agar urutan selalu konsisten tanpa gap
                $shift_stmt = $conn->prepare("UPDATE pengumuman_display SET urutan = urutan - 1 WHERE urutan > ? ORDER BY urutan ASC");
                $shift_stmt->bind_param("i", $del_urutan);
                $shift_stmt->execute();
                $shift_stmt->close();
            }

            $conn->commit();
            redirect_ke("dashboard.php?page=pengumuman&pesan=deleted");
        } catch (Exception $e) {
            $conn->rollback();
            $pesan = 'Gagal menghapus pengumuman: ' . $e->getMessage();
            $pesan_tipe = 'danger';
        }
    }
}

// Cek parameter URL untuk notifikasi
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] === 'status_updated') {
        $pesan = 'Status aktif pengumuman berhasil diubah!';
        $pesan_tipe = 'info';
    } elseif ($_GET['pesan'] === 'deleted') {
        $pesan = 'Pengumuman berhasil dihapus dan nomor urutan otomatis dirapikan!';
        $pesan_tipe = 'success';
    } elseif ($_GET['pesan'] === 'edited') {
        $pesan = 'Pengumuman berhasil diperbarui dan urutan otomatis disesuaikan!';
        $pesan_tipe = 'success';
    } elseif ($_GET['pesan'] === 'added') {
        $pesan = 'Pengumuman baru berhasil ditambahkan dan urutan otomatis disesuaikan!';
        $pesan_tipe = 'success';
    }
}

// Ambil semua daftar pengumuman urut berdasarkan kolom urutan
$query_pengumuman = mysqli_query($conn, "SELECT * FROM pengumuman_display ORDER BY urutan ASC, id_pengumuman DESC");

// Peta data urutan yang sudah terpakai untuk validasi real-time di Javascript
$urutan_terpakai_map = [];
$daftar_angka_terpakai = [];
$q_urutan_semua = mysqli_query($conn, "SELECT id_pengumuman, urutan FROM pengumuman_display ORDER BY urutan ASC");
while ($u = mysqli_fetch_assoc($q_urutan_semua)) {
    $urutan_terpakai_map[(int)$u['id_pengumuman']] = (int)$u['urutan'];
    $daftar_angka_terpakai[] = (int)$u['urutan'];
}

// Hitung kembali next_urutan terbaru untuk ditampilkan pada input formulir
$next_urutan = !empty($daftar_angka_terpakai) ? (max($daftar_angka_terpakai) + 1) : 1;

// Ambil pengumuman yang aktif untuk live preview simulator
$query_aktif = mysqli_query($conn, "SELECT isi_teks FROM pengumuman_display WHERE status_aktif = 1 ORDER BY urutan ASC, id_pengumuman ASC");
$preview_list = [];
while ($pa = mysqli_fetch_assoc($query_aktif)) {
    $preview_list[] = htmlspecialchars($pa['isi_teks'], ENT_QUOTES, 'UTF-8');
}
$preview_text = !empty($preview_list) ? implode(" &nbsp;&bull;&nbsp; ", $preview_list) : "Belum ada pengumuman aktif";
?>

<div class="container-fluid">

    <?php if (!empty($pesan)) { ?>
    <div class="alert alert-<?= $pesan_tipe; ?> alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 10px;">
        <i class="fas fa-info-circle mr-2"></i> <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php } ?>

    <!-- SIMULATOR LIVE RUNNING TEXT DISPLAY TV -->
    <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between py-2">
            <div class="font-weight-bold" style="font-size: 13.5px;">
                <i class="fas fa-tv mr-2 text-warning"></i> Pratinjau Running Text di Layar Display TV
            </div>
            <a href="display.php" target="_blank" class="btn btn-xs btn-outline-light">
                <i class="fas fa-external-link-alt mr-1"></i> Buka Display TV &rarr;
            </a>
        </div>
        <div class="card-body p-0" style="background: #1d4ed8; color: #ffffff;">
            <div style="height: 42px; line-height: 42px; overflow: hidden; font-size: 15px; font-weight: 500;">
                <marquee behavior="scroll" direction="left" scrollamount="6">
                    <?= $preview_text; ?>
                </marquee>
            </div>
        </div>
    </div>

    <div class="row">

        <!-- Form Tambah Pengumuman -->
        <div class="col-lg-4 mb-4">
            <div class="card card-primary card-outline shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white">
                    <h5 class="card-title font-weight-bold m-0" style="color: #0d6efd;">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Pengumuman Baru
                    </h5>
                </div>
                <form id="formTambahPengumuman" action="dashboard.php?page=pengumuman" method="POST">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Teks Pengumuman <span class="text-danger">*</span></label>
                            <textarea 
                                name="isi_teks" 
                                id="tambah_isi_teks"
                                class="form-control" 
                                rows="4" 
                                placeholder="Contoh: Jam pelayanan PST hari ini dibuka pukul 08.00 s.d 15.30 WIB..." 
                                required 
                                style="border-radius: 8px;"></textarea>
                            <small class="text-muted">Teks ini akan berjalan di bagian bawah layar Display TV antrean.</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">Urutan Tampil <span class="text-danger">*</span></label>
                            <input 
                                type="number" 
                                name="urutan" 
                                id="tambah_urutan" 
                                class="form-control" 
                                min="1" 
                                max="999" 
                                value="<?= $next_urutan; ?>" 
                                required 
                                style="border-radius: 8px;">
                            <div id="infoShiftTambah" class="mt-2" style="display: none;">
                                <div class="alert alert-info py-2 px-3 mb-0 shadow-xs" style="font-size: 12px; border-radius: 8px; background-color: #e0f2fe; border-color: #bae6fd; color: #0369a1;">
                                    <i class="fas fa-info-circle mr-1 text-primary"></i> 
                                    <span id="infoShiftTambahText">Nomor urutan ini sudah terpakai. Pengumuman pada nomor ini dan seterusnya akan <strong>otomatis digeser mundur</strong> (+1).</span>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Angka lebih kecil tayang lebih awal. Jika nomor sudah ada, urutan lama akan <strong>otomatis digeser mundur</strong>.
                            </small>
                        </div>
                        <div class="form-group mb-0">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="switchStatus" name="status_aktif" value="1" checked>
                                <label class="custom-control-label font-weight-bold" for="switchStatus">Langsung Aktifkan di Display TV</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-right" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="submit" id="btnSubmitTambah" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="border-radius: 8px;">
                            <i class="fas fa-save mr-1"></i> Simpan Pengumuman
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Daftar Pengumuman -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h5 class="card-title font-weight-bold m-0" style="color: #1e293b;">
                        <i class="fas fa-list-alt mr-2 text-primary"></i> Daftar Pengumuman Display TV
                    </h5>
                    <span class="badge badge-primary px-2 py-1 font-weight-bold">
                        Total: <?= mysqli_num_rows($query_pengumuman); ?>
                    </span>
                </div>
                <div class="card-body p-0" style="overflow-x: auto;">
                    <table class="table table-hover table-striped mb-0" style="table-layout: fixed; width: 100%;">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 70px;" class="text-center text-nowrap">Urutan</th>
                                <th>Isi Pengumuman</th>
                                <th style="width: 95px;" class="text-center text-nowrap">Status</th>
                                <th style="width: 95px;" class="text-center text-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_pengumuman) > 0) { ?>
                                <?php while ($p = mysqli_fetch_assoc($query_pengumuman)) { ?>
                                <tr>
                                    <td class="text-center align-middle" style="width: 70px;">
                                        <span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 13px; border-radius: 6px;" title="Urutan ke-<?= (int)$p['urutan']; ?>">
                                            #<?= (int)$p['urutan']; ?>
                                        </span>
                                    </td>
                                    <td class="align-middle" style="word-break: break-word; overflow-wrap: anywhere;">
                                        <div style="font-size: 13.5px; line-height: 1.5; color: #1e293b; word-break: break-word; overflow-wrap: anywhere;">
                                            <?= htmlspecialchars($p['isi_teks'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-clock mr-1"></i> Dibuat: <?= date('d-m-Y H:i', strtotime($p['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td class="text-center align-middle text-nowrap" style="width: 95px;">
                                        <?php if ($p['status_aktif'] == 1) { ?>
                                            <a href="dashboard.php?page=pengumuman&aksi=toggle&id=<?= $p['id_pengumuman']; ?>&status=1" 
                                               class="badge badge-success px-2 py-1 text-decoration-none shadow-sm" 
                                               title="Klik untuk Matikan / Nonaktifkan">
                                                <i class="fas fa-toggle-on mr-1"></i> Aktif
                                            </a>
                                        <?php } else { ?>
                                            <a href="dashboard.php?page=pengumuman&aksi=toggle&id=<?= $p['id_pengumuman']; ?>&status=0" 
                                               class="badge badge-secondary px-2 py-1 text-decoration-none shadow-sm" 
                                               title="Klik untuk Hidupkan / Aktifkan">
                                                <i class="fas fa-toggle-off mr-1"></i> Nonaktif
                                            </a>
                                        <?php } ?>
                                    </td>
                                    <td class="text-center align-middle text-nowrap" style="width: 95px;">
                                        <!-- Tombol Edit & Hapus Compact Group -->
                                        <div class="btn-group btn-group-sm shadow-sm" role="group">
                                            <button type="button" 
                                                    class="btn btn-warning btnEditPengumuman" 
                                                    data-id="<?= (int)$p['id_pengumuman']; ?>"
                                                    data-teks="<?= htmlspecialchars($p['isi_teks'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-urutan="<?= (int)$p['urutan']; ?>"
                                                    data-status="<?= (int)$p['status_aktif']; ?>"
                                                    title="Edit Pengumuman">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-danger btnHapusPengumuman" 
                                                    data-id="<?= (int)$p['id_pengumuman']; ?>"
                                                    data-teks="<?= htmlspecialchars($p['isi_teks'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                    title="Hapus Pengumuman">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-2 d-block text-secondary"></i>
                                        Belum ada data pengumuman. Silakan tambahkan pengumuman baru melalui formulir di samping.
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- MODAL EDIT PENGUMUMAN (DITEMPATKAN DI LUAR TABEL AGAR BEBAS DARI BACKDROP BUG BOOTSTRAP) -->
<div class="modal fade" id="modalEditPengumuman" tabindex="-1" role="dialog" aria-labelledby="modalEditPengumumanTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg" style="border-radius: 14px; overflow: hidden; border: none;">
            <div class="modal-header bg-primary text-white d-flex align-items-center py-3">
                <h5 class="modal-title font-weight-bold" id="modalEditPengumumanTitle">
                    <i class="fas fa-edit mr-2 text-warning"></i> Edit Pengumuman Display TV
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditPengumuman" action="dashboard.php?page=pengumuman" method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id_pengumuman" id="edit_id_pengumuman" value="">
                <input type="hidden" id="edit_old_urutan" value="">
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Teks Pengumuman <span class="text-danger">*</span></label>
                        <textarea 
                            name="isi_teks" 
                            id="edit_isi_teks" 
                            class="form-control" 
                            rows="4" 
                            placeholder="Ketik isi pengumuman..." 
                            required 
                            style="border-radius: 8px;"></textarea>
                        <small class="text-muted">Teks yang akan berjalan di bagian bawah layar Display TV antrean.</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Urutan Tampil <span class="text-danger">*</span></label>
                        <input 
                            type="number" 
                            name="urutan" 
                            id="edit_urutan" 
                            class="form-control" 
                            min="1" 
                            max="999" 
                            required 
                            style="border-radius: 8px;">
                        <div id="infoShiftEdit" class="mt-2" style="display: none;">
                            <div class="alert alert-info py-2 px-3 mb-0 shadow-xs" style="font-size: 12px; border-radius: 8px; background-color: #e0f2fe; border-color: #bae6fd; color: #0369a1;">
                                <i class="fas fa-exchange-alt mr-1 text-primary"></i> 
                                <span id="infoShiftEditText">Nomor urutan ini sudah terpakai oleh pengumuman lain. Jika disimpan, pengumuman lain akan <strong>otomatis digeser</strong> untuk memberi ruang.</span>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Nomor urutan menentukan giliran tayang (angka lebih kecil tayang lebih awal). Jika memilih nomor yang sudah terpakai, nomor pengumuman lain akan <strong>otomatis digeser</strong>.
                        </small>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_status_aktif" name="status_aktif" value="1">
                            <label class="custom-control-label font-weight-bold text-dark" for="edit_status_aktif">
                                Tampilkan di Display TV (Aktif)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal" style="border-radius: 8px;">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" id="btnSubmitEdit" class="btn btn-primary font-weight-bold px-4 shadow-sm" style="border-radius: 8px;">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Data nomor urutan yang sudah dipakai di database
var DATA_URUTAN = <?= json_encode($urutan_terpakai_map); ?>;

(function () {
    function initPengumumanEvents() {
        if (typeof $ === 'undefined') {
            setTimeout(initPengumumanEvents, 100);
            return;
        }

        // ==========================================================
        // 1. INFO REAL-TIME PADA FORM TAMBAH
        // ==========================================================
        function cekUrutanTambah() {
            var val = parseInt($('#tambah_urutan').val(), 10);
            var isTaken = false;

            for (var key in DATA_URUTAN) {
                if (DATA_URUTAN[key] === val) {
                    isTaken = true;
                    break;
                }
            }

            if (isTaken) {
                $('#infoShiftTambahText').html('Nomor urutan <strong>#' + val + '</strong> sudah terpakai. Jika disimpan, pengumuman pada nomor #' + val + ' dan seterusnya akan <strong>otomatis digeser mundur (+1)</strong>.');
                $('#infoShiftTambah').slideDown(150);
            } else {
                $('#infoShiftTambah').slideUp(150);
            }
        }

        $('#tambah_urutan').on('input change', cekUrutanTambah);

        // ==========================================================
        // 2. INFO REAL-TIME PADA FORM EDIT
        // ==========================================================
        function cekUrutanEdit() {
            var currentId = parseInt($('#edit_id_pengumuman').val(), 10);
            var oldUrutan = parseInt($('#edit_old_urutan').val(), 10);
            var val = parseInt($('#edit_urutan').val(), 10);

            if (isNaN(val) || val < 1) {
                $('#infoShiftEdit').slideUp(150);
                return;
            }

            // Jika nilai sama dengan urutan aslinya, tidak ada pergeseran
            if (val === oldUrutan) {
                $('#infoShiftEdit').slideUp(150);
                return;
            }

            var isCollision = false;
            for (var key in DATA_URUTAN) {
                if (parseInt(key, 10) !== currentId && DATA_URUTAN[key] === val) {
                    isCollision = true;
                    break;
                }
            }

            if (isCollision) {
                if (val < oldUrutan) {
                    $('#infoShiftEditText').html('Nomor urutan <strong>#' + val + '</strong> sudah terpakai. Pengumuman pada nomor #' + val + ' s.d. #' + (oldUrutan - 1) + ' akan <strong>otomatis mundur (+1)</strong> ke belakang, sedangkan nomor sebelum #' + val + ' tetap.');
                } else {
                    $('#infoShiftEditText').html('Nomor urutan <strong>#' + val + '</strong> sudah terpakai. Pengumuman di antara posisi tersebut akan <strong>otomatis maju (-1)</strong> mengisi posisi yang ditinggalkan.');
                }
                $('#infoShiftEdit').slideDown(150);
            } else {
                $('#infoShiftEdit').slideUp(150);
            }
        }

        $('#edit_urutan').on('input change', cekUrutanEdit);

        // ==========================================================
        // 3. EVENT BUKA MODAL EDIT
        // ==========================================================
        $(document).off('click', '.btnEditPengumuman').on('click', '.btnEditPengumuman', function () {
            var btn = $(this);
            var id = btn.attr('data-id');
            var teks = btn.attr('data-teks');
            var urutan = btn.attr('data-urutan');
            var status = btn.attr('data-status');

            $('#edit_id_pengumuman').val(id);
            $('#edit_old_urutan').val(urutan);
            $('#edit_isi_teks').val(teks);
            $('#edit_urutan').val(urutan).removeClass('is-invalid');
            $('#infoShiftEdit').hide();
            $('#edit_status_aktif').prop('checked', parseInt(status, 10) === 1);

            $('#modalEditPengumuman').modal('show');
        });

        // ==========================================================
        // 4. EVENT HAPUS PENGUMUMAN DENGAN SWEETALERT2
        // ==========================================================
        $(document).off('click', '.btnHapusPengumuman').on('click', '.btnHapusPengumuman', function () {
            var id = $(this).attr('data-id');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: "Hapus Pengumuman?",
                    text: "Apakah Anda yakin ingin menghapus pengumuman ini secara permanen dari Display TV? Nomor urutan pengumuman setelahnya akan otomatis dirapikan.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#dc3545",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "<i class='fas fa-trash-alt mr-1'></i> Ya, Hapus!",
                    cancelButtonText: "Batal"
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = "dashboard.php?page=pengumuman&aksi=hapus&id=" + id;
                    }
                });
            } else {
                if (confirm("Apakah Anda yakin ingin menghapus pengumuman ini?")) {
                    window.location.href = "dashboard.php?page=pengumuman&aksi=hapus&id=" + id;
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPengumumanEvents);
    } else {
        initPengumumanEvents();
    }
})();
</script>
