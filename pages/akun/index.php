<?php
include 'config/koneksi.php';

// Notifikasi SweetAlert
if(isset($_GET['update']) && $_GET['update']=="berhasil"){
    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        Swal.fire({
            icon:'success',
            title:'Berhasil',
            text:'Data akun berhasil diperbarui',
            timer:1800,
            showConfirmButton:false
        });
    });
    </script>";
}

if(isset($_GET['password']) && $_GET['password']=="berhasil"){
    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        Swal.fire({
            icon:'success',
            title:'Berhasil',
            text:'Password dan akun berhasil diperbarui',
            timer:1800,
            showConfirmButton:false
        });
    });
    </script>";
}

if(isset($_GET['error']) && $_GET['error']=="passwordlama"){
    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        Swal.fire({
            icon:'error',
            title:'Gagal',
            text:'Password lama salah'
        });
    });
    </script>";
}

if(isset($_GET['error']) && $_GET['error']=="konfirmasi"){
    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        Swal.fire({
            icon:'error',
            title:'Gagal',
            text:'Konfirmasi password baru tidak sesuai'
        });
    });
    </script>";
}

if(isset($_GET['error']) && $_GET['error']=="fotoformat"){
    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        Swal.fire({
            icon:'error',
            title:'Format Tidak Didukung',
            text:'Format foto harus berupa JPG, JPEG, PNG, atau WEBP'
        });
    });
    </script>";
}

if(isset($_GET['error']) && $_GET['error']=="fotosize"){
    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        Swal.fire({
            icon:'error',
            title:'Ukuran Terlalu Besar',
            text:'Ukuran file foto maksimal adalah 2 MB'
        });
    });
    </script>";
}

if(isset($_GET['error']) && $_GET['error']=="fotogagal"){
    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        Swal.fire({
            icon:'error',
            title:'Gagal Unggah',
            text:'Terjadi kesalahan saat menyimpan foto ke server'
        });
    });
    </script>";
}

// Ambil data admin yang sedang login
$user_id = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 0;
$admin = null;
if ($user_id > 0) {
    $stmt_u = $conn->prepare("SELECT * FROM users WHERE id_user = ? LIMIT 1");
    $stmt_u->bind_param("i", $user_id);
    $stmt_u->execute();
    $admin = $stmt_u->get_result()->fetch_assoc();
    $stmt_u->close();
}
if (!$admin) {
    $admin = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users LIMIT 1"));
}

// Tentukan foto profil aktif atau default
$defaultFoto = 'assets/img/default-avatar.jpg';
$fotoProfil = $defaultFoto;
if (!empty($admin['foto']) && file_exists('assets/img/uploads/' . $admin['foto'])) {
    $fotoProfil = 'assets/img/uploads/' . $admin['foto'];
}

if(isset($_POST['simpan']) && $admin){

    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');

    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    $foto_baru = $admin['foto'];
    $foto_diubah = false;

    $uploadDir = 'assets/img/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // 1. Cek jika opsi hapus foto dicentang
    if (!empty($_POST['hapus_foto'])) {
        if (!empty($admin['foto']) && file_exists($uploadDir . $admin['foto'])) {
            @unlink($uploadDir . $admin['foto']);
        }
        $foto_baru = null;
        $foto_diubah = true;
    }

    // 2. Cek jika ada file foto baru diunggah
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK && $_FILES['foto']['size'] > 0) {
        $fileSize = $_FILES['foto']['size'];
        $fileTmp  = $_FILES['foto']['tmp_name'];
        $fileName = $_FILES['foto']['name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validasi ukuran (maks 2MB)
        if ($fileSize > 2 * 1024 * 1024) {
            echo "<script>window.location='dashboard.php?page=akun&error=fotosize';</script>";
            exit;
        }

        // Validasi ekstensi
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($fileExt, $allowedExts)) {
            echo "<script>window.location='dashboard.php?page=akun&error=fotoformat';</script>";
            exit;
        }

        // Validasi tipe MIME riil
        $imageInfo = @getimagesize($fileTmp);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if ($imageInfo === false || !in_array($imageInfo['mime'], $allowedMimes)) {
            echo "<script>window.location='dashboard.php?page=akun&error=fotoformat';</script>";
            exit;
        }

        // Buat nama file unik dan aman
        $cleanFileName = 'admin_' . $admin['id_user'] . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
        $destination = $uploadDir . $cleanFileName;

        if (move_uploaded_file($fileTmp, $destination)) {
            // Hapus file foto lama jika ada
            if (!empty($admin['foto']) && file_exists($uploadDir . $admin['foto'])) {
                @unlink($uploadDir . $admin['foto']);
            }
            $foto_baru = $cleanFileName;
            $foto_diubah = true;
        } else {
            echo "<script>window.location='dashboard.php?page=akun&error=fotogagal';</script>";
            exit;
        }
    }

    // 3. Proses ganti password jika password baru diisi
    if(!empty($password_baru)){

        // Validasi password lama
        if(!password_verify($password_lama, $admin['password'])){
            echo "<script>window.location='dashboard.php?page=akun&error=passwordlama';</script>";
            exit;
        }

        // Validasi konfirmasi password
        if($password_baru !== $konfirmasi){
            echo "<script>window.location='dashboard.php?page=akun&error=konfirmasi';</script>";
            exit;
        }

        $passwordHash = password_hash($password_baru, PASSWORD_DEFAULT);

        if ($foto_diubah) {
            $stmt_pw = $conn->prepare("UPDATE users SET nama = ?, username = ?, password = ?, foto = ? WHERE id_user = ?");
            $stmt_pw->bind_param("ssssi", $nama, $username, $passwordHash, $foto_baru, $admin['id_user']);
        } else {
            $stmt_pw = $conn->prepare("UPDATE users SET nama = ?, username = ?, password = ? WHERE id_user = ?");
            $stmt_pw->bind_param("sssi", $nama, $username, $passwordHash, $admin['id_user']);
        }

        $stmt_pw->execute();
        $stmt_pw->close();

        $_SESSION['nama'] = $nama;
        $_SESSION['username'] = $username;
        if ($foto_diubah) {
            $_SESSION['foto'] = $foto_baru ?? '';
        }

        echo "<script>window.location='dashboard.php?page=akun&password=berhasil';</script>";
        exit;
    }

    // 4. Jika password baru tidak diisi (hanya nama, username, atau foto)
    if ($foto_diubah) {
        $stmt_up = $conn->prepare("UPDATE users SET nama = ?, username = ?, foto = ? WHERE id_user = ?");
        $stmt_up->bind_param("sssi", $nama, $username, $foto_baru, $admin['id_user']);
    } else {
        $stmt_up = $conn->prepare("UPDATE users SET nama = ?, username = ? WHERE id_user = ?");
        $stmt_up->bind_param("ssi", $nama, $username, $admin['id_user']);
    }

    $stmt_up->execute();
    $stmt_up->close();

    $_SESSION['nama'] = $nama;
    $_SESSION['username'] = $username;
    if ($foto_diubah) {
        $_SESSION['foto'] = $foto_baru ?? '';
    }

    echo "<script>window.location='dashboard.php?page=akun&update=berhasil';</script>";
    exit;
}
?>

<div class="card card-primary card-outline shadow-sm">

    <div class="card-header bg-white">
        <h3 class="card-title font-weight-bold text-primary">
            <i class="fas fa-user-edit mr-2"></i> Pengaturan Akun Administrator
        </h3>
    </div>

    <form method="POST" enctype="multipart/form-data">

        <div class="card-body">

            <!-- Opsi Edit Foto Profil -->
            <div class="form-group text-center mb-4 pb-3 border-bottom">
                <label class="d-block font-weight-bold mb-2">Foto Profil</label>
                <div class="d-inline-block position-relative mb-2">
                    <img id="previewAvatar"
                         src="<?= htmlspecialchars($fotoProfil); ?>"
                         alt="Foto Profil"
                         class="img-circle elevation-2"
                         style="width: 125px; height: 125px; object-fit: cover; border: 3px solid #0B5ED7; background-color: #f4f6f9;">
                </div>

                <div class="mx-auto" style="max-width: 420px;">
                    <div class="custom-file text-left">
                        <input type="file"
                               name="foto"
                               id="inputFotoProfil"
                               class="custom-file-input"
                               accept="image/jpeg,image/png,image/webp,image/jpg"
                               onchange="tampilkanPreview(this)">
                        <label class="custom-file-label" for="inputFotoProfil" id="labelFotoProfil">
                            Pilih file foto baru...
                        </label>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Format: JPG, JPEG, PNG, WEBP (Maksimal 2 MB). Bila tidak ada foto, otomatis menggunakan avatar default.
                    </small>

                    <?php if (!empty($admin['foto']) && file_exists('assets/img/uploads/' . $admin['foto'])): ?>
                        <div class="form-check mt-2 text-left">
                            <input type="checkbox" name="hapus_foto" value="1" class="form-check-input" id="checkHapusFoto">
                            <label class="form-check-label text-danger font-weight-bold" for="checkHapusFoto">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus foto kustom & kembali ke foto default
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text"
                       name="nama"
                       class="form-control"
                       value="<?= htmlspecialchars($admin['nama']); ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text"
                       name="username"
                       class="form-control"
                       value="<?= htmlspecialchars($admin['username']); ?>"
                       required>
            </div>

            <hr class="my-4">

            <h5 class="text-secondary font-weight-bold mb-3">
                <i class="fas fa-lock mr-2"></i> Ubah Password (Opsional)
            </h5>

            <div class="form-group">
                <label>Password Lama</label>
                <input type="password"
                       name="password_lama"
                       class="form-control"
                       placeholder="Masukkan password saat ini">
            </div>

            <div class="form-group">
                <label>Password Baru</label>
                <input type="password"
                       name="password_baru"
                       class="form-control"
                       placeholder="Masukkan password baru">
                <small class="text-muted">
                    Kosongkan jika tidak ingin mengganti password.
                </small>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <input type="password"
                       name="konfirmasi"
                       class="form-control"
                       placeholder="Ketik ulang password baru">
            </div>

        </div>

        <div class="card-footer bg-light">
            <button type="submit"
                    name="simpan"
                    class="btn btn-primary px-4">
                <i class="fas fa-save mr-1"></i>
                Simpan Perubahan
            </button>
        </div>

    </form>

</div>

<script>
function tampilkanPreview(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var label = document.getElementById('labelFotoProfil');
        if (label) {
            label.textContent = file.name;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('previewAvatar');
            if (img) {
                img.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    }
}
</script>
