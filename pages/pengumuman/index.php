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

    if (!empty($isi_teks) && $urutan > 0) {
        // Cek validasi: nomor urutan tidak boleh ada yang sama
        $cek_stmt = $conn->prepare("SELECT id_pengumuman FROM pengumuman_display WHERE urutan = ? LIMIT 1");
        $cek_stmt->bind_param("i", $urutan);
        $cek_stmt->execute();
        $cek_result = $cek_stmt->get_result();

        if ($cek_result && $cek_result->num_rows > 0) {
            $pesan = "Gagal! Nomor urutan #{$urutan} sudah digunakan oleh pengumuman lain. Setiap pengumuman wajib memiliki nomor urutan yang berbeda (Rekomendasi urutan berikutnya: #{$next_urutan}).";
            $pesan_tipe = 'danger';
            $cek_stmt->close();
        } else {
            $cek_stmt->close();
            $stmt = $conn->prepare("INSERT INTO pengumuman_display (isi_teks, status_aktif, urutan, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sii", $isi_teks, $status_aktif, $urutan);
            if ($stmt->execute()) {
                $stmt->close();
                redirect_ke("dashboard.php?page=pengumuman&pesan=added");
            } else {
                $pesan = 'Gagal menambahkan pengumuman: ' . $conn->error;
                $pesan_tipe = 'danger';
                $stmt->close();
            }
        }
    } else {
        $pesan = 'Teks pengumuman dan nomor urutan tidak boleh kosong.';
        $pesan_tipe = 'warning';
    }
}

// PROSES EDIT PENGUMUMAN
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id_pengumuman = (int)($_POST['id_pengumuman'] ?? 0);
    $isi_teks = trim($_POST['isi_teks'] ?? '');
    $urutan = (int)($_POST['urutan'] ?? 1);
    $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

    if ($id_pengumuman > 0 && !empty($isi_teks) && $urutan > 0) {
        // Cek validasi: nomor urutan tidak boleh sama dengan pengumuman LAIN
        $cek_stmt = $conn->prepare("SELECT id_pengumuman FROM pengumuman_display WHERE urutan = ? AND id_pengumuman != ? LIMIT 1");
        $cek_stmt->bind_param("ii", $urutan, $id_pengumuman);
        $cek_stmt->execute();
        $cek_result = $cek_stmt->get_result();

        if ($cek_result && $cek_result->num_rows > 0) {
            $pesan = "Gagal memperbarui! Nomor urutan #{$urutan} sudah digunakan oleh pengumuman lain. Nomor urutan tidak boleh ada yang sama.";
            $pesan_tipe = 'danger';
            $cek_stmt->close();
        } else {
            $cek_stmt->close();
            $stmt = $conn->prepare("UPDATE pengumuman_display SET isi_teks = ?, status_aktif = ?, urutan = ? WHERE id_pengumuman = ?");
            $stmt->bind_param("siii", $isi_teks, $status_aktif, $urutan, $id_pengumuman);
            if ($stmt->execute()) {
                $stmt->close();
                redirect_ke("dashboard.php?page=pengumuman&pesan=edited");
            } else {
                $pesan = 'Gagal memperbarui pengumuman: ' . $conn->error;
                $pesan_tipe = 'danger';
                $stmt->close();
            }
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
        $stmt = $conn->prepare("DELETE FROM pengumuman_display WHERE id_pengumuman = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        redirect_ke("dashboard.php?page=pengumuman&pesan=deleted");
    }
}

// Cek parameter URL untuk notifikasi
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] === 'status_updated') {
        $pesan = 'Status aktif pengumuman berhasil diubah!';
        $pesan_tipe = 'info';
    } elseif ($_GET['pesan'] === 'deleted') {
        $pesan = 'Pengumuman berhasil dihapus!';
        $pesan_tipe = 'success';
    } elseif ($_GET['pesan'] === 'edited') {
        $pesan = 'Pengumuman berhasil diperbarui!';
        $pesan_tipe = 'success';
    } elseif ($_GET['pesan'] === 'added') {
        $pesan = 'Pengumuman baru berhasil ditambahkan!';
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
                                class="form-control" 
                                rows="4" 
                                placeholder="Contoh: Jam pelayanan PST hari ini dibuka pukul 08.00 s.d 15.30 WIB..." 
                                required 
                                style="border-radius: 8px;"></textarea>
                            <small class="text-muted">Teks ini akan berjalan di bagian bawah layar Display TV antrean.</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold d-flex justify-content-between align-items-center">
                                <span>Urutan Tampil <span class="text-danger">*</span></span>
                                <span class="badge badge-light border text-primary px-2 py-1" style="font-size: 11px;">
                                    Tersedia: #<?= $next_urutan; ?>
                                </span>
                            </label>
                            <input 
                                type="number" 
                                name="urutan" 
                                id="tambah_urutan" 
                                class="form-control" 
                                value="<?= $next_urutan; ?>" 
                                min="1" 
                                max="999" 
                                required 
                                style="border-radius: 8px;">
                            <div id="pesanDuplikatTambah" class="text-danger font-weight-bold mt-1" style="display: none; font-size: 12px;">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Nomor urutan ini sudah digunakan! Tidak boleh sama.
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle mr-1 text-primary"></i> <strong>Nomor urutan tidak boleh sama</strong>. Angka lebih kecil akan tayang lebih awal.
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
                        <div id="pesanDuplikatEdit" class="text-danger font-weight-bold mt-1" style="display: none; font-size: 12px;">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Nomor urutan ini sudah terpakai oleh pengumuman lain!
                        </div>
                        <small class="text-muted d-block mt-1">
                            <strong>Nomor urutan tidak boleh sama</strong> dengan pengumuman lain (angka lebih kecil tayang lebih awal).
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
const DATA_URUTAN = <?= json_encode($urutan_terpakai_map); ?>;

(function () {
    function initPengumumanEvents() {
        if (typeof $ === 'undefined') {
            setTimeout(initPengumumanEvents, 100);
            return;
        }

        // ==========================================================
        // 1. VALIDASI REAL-TIME UNIK PADA FORM TAMBAH
        // ==========================================================
        function cekUrutanTambah() {
            var val = parseInt($('#tambah_urutan').val(), 10);
            var isDuplicate = false;

            for (var key in DATA_URUTAN) {
                if (DATA_URUTAN[key] === val) {
                    isDuplicate = true;
                    break;
                }
            }

            if (isDuplicate) {
                $('#tambah_urutan').addClass('is-invalid');
                $('#pesanDuplikatTambah').show();
                return false;
            } else {
                $('#tambah_urutan').removeClass('is-invalid');
                $('#pesanDuplikatTambah').hide();
                return true;
            }
        }

        $('#tambah_urutan').on('input change', cekUrutanTambah);

        $('#formTambahPengumuman').on('submit', function (e) {
            if (!cekUrutanTambah()) {
                e.preventDefault();
                var val = $('#tambah_urutan').val();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: "Nomor Urutan Duplikat!",
                        text: "Nomor urutan #" + val + " sudah digunakan oleh pengumuman lain. Setiap pengumuman wajib memiliki nomor urutan yang berbeda.",
                        icon: "warning",
                        confirmButtonColor: "#0d6efd"
                    });
                } else {
                    alert("Nomor urutan #" + val + " sudah digunakan!");
                }
            }
        });

        // ==========================================================
        // 2. VALIDASI REAL-TIME UNIK PADA FORM EDIT
        // ==========================================================
        function cekUrutanEdit() {
            var currentId = parseInt($('#edit_id_pengumuman').val(), 10);
            var val = parseInt($('#edit_urutan').val(), 10);
            var isDuplicate = false;

            for (var key in DATA_URUTAN) {
                if (parseInt(key, 10) !== currentId && DATA_URUTAN[key] === val) {
                    isDuplicate = true;
                    break;
                }
            }

            if (isDuplicate) {
                $('#edit_urutan').addClass('is-invalid');
                $('#pesanDuplikatEdit').show();
                return false;
            } else {
                $('#edit_urutan').removeClass('is-invalid');
                $('#pesanDuplikatEdit').hide();
                return true;
            }
        }

        $('#edit_urutan').on('input change', cekUrutanEdit);

        $('#formEditPengumuman').on('submit', function (e) {
            if (!cekUrutanEdit()) {
                e.preventDefault();
                var val = $('#edit_urutan').val();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: "Nomor Urutan Duplikat!",
                        text: "Nomor urutan #" + val + " sudah digunakan oleh pengumuman lain. Nomor urutan tidak boleh sama!",
                        icon: "warning",
                        confirmButtonColor: "#0d6efd"
                    });
                } else {
                    alert("Nomor urutan #" + val + " sudah digunakan!");
                }
            }
        });

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
            $('#edit_isi_teks').val(teks);
            $('#edit_urutan').val(urutan).removeClass('is-invalid');
            $('#pesanDuplikatEdit').hide();
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
                    text: "Apakah Anda yakin ingin menghapus pengumuman ini secara permanen dari Display TV?",
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
