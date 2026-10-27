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
            text:'Password berhasil diubah',
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
            text:'Konfirmasi password tidak sesuai'
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

if(isset($_POST['simpan']) && $admin){

    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');

    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    // Jika hanya ganti nama/username
    if(empty($password_baru)){

        $stmt_up = $conn->prepare("UPDATE users SET nama = ?, username = ? WHERE id_user = ?");
        $stmt_up->bind_param("ssi", $nama, $username, $admin['id_user']);
        $stmt_up->execute();
        $stmt_up->close();

        $_SESSION['nama'] = $nama;
        $_SESSION['username'] = $username;

        echo "<script>window.location='dashboard.php?page=akun&update=berhasil';</script>";
        exit;
    }

    // Password lama salah
    if(!password_verify($password_lama,$admin['password'])){
        echo "<script>window.location='dashboard.php?page=akun&error=passwordlama';</script>";
        exit;
    }

    // Konfirmasi tidak sama
    if($password_baru != $konfirmasi){
        echo "<script>window.location='dashboard.php?page=akun&error=konfirmasi';</script>";
        exit;
    }

    // Update password
    $passwordHash = password_hash($password_baru,PASSWORD_DEFAULT);

    $stmt_pw = $conn->prepare("UPDATE users SET nama = ?, username = ?, password = ? WHERE id_user = ?");
    $stmt_pw->bind_param("sssi", $nama, $username, $passwordHash, $admin['id_user']);
    $stmt_pw->execute();
    $stmt_pw->close();

    $_SESSION['nama'] = $nama;
    $_SESSION['username'] = $username;

    echo "<script>window.location='dashboard.php?page=akun&password=berhasil';</script>";
    exit;
}
?>

<div class="card card-primary">

    <div class="card-header">

        <h3 class="card-title">
            Pengaturan Akun Administrator
        </h3>

    </div>

    <form method="POST">

        <div class="card-body">

            <div class="form-group">

                <label>Nama Lengkap</label>

                <input
                    type="text"
                    name="nama"
                    class="form-control"
                    value="<?= htmlspecialchars($admin['nama']); ?>"
                    required>

            </div>

            <div class="form-group">

                <label>Username</label>

                <input
                    type="text"
                    name="username"
                    class="form-control"
                    value="<?= htmlspecialchars($admin['username']); ?>"
                    required>

            </div>

            <hr>

            <h5>Ubah Password (Opsional)</h5>

            <div class="form-group">

                <label>Password Lama</label>

                <input
                    type="password"
                    name="password_lama"
                    class="form-control">

            </div>

            <div class="form-group">

                <label>Password Baru</label>

                <input
                    type="password"
                    name="password_baru"
                    class="form-control">

                <small class="text-muted">
                    Kosongkan jika tidak ingin mengganti password.
                </small>

            </div>

            <div class="form-group">

                <label>Konfirmasi Password Baru</label>

                <input
                    type="password"
                    name="konfirmasi"
                    class="form-control">

            </div>

        </div>

        <div class="card-footer">

            <button
                type="submit"
                name="simpan"
                class="btn btn-primary">

                <i class="fas fa-save"></i>

                Simpan Perubahan

            </button>

        </div>

    </form>

</div>