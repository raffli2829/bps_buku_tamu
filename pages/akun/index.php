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

// Ambil data admin
$admin = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users LIMIT 1"));

if(isset($_POST['simpan'])){

    $nama = mysqli_real_escape_string($conn,$_POST['nama']);
    $username = mysqli_real_escape_string($conn,$_POST['username']);

    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    // Jika hanya ganti nama/username
    if(empty($password_baru)){

        mysqli_query($conn,"
        UPDATE users
        SET
            nama='$nama',
            username='$username'
        WHERE id_user='".$admin['id_user']."'
        ");

        $_SESSION['nama']=$nama;

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

    mysqli_query($conn,"
    UPDATE users
    SET
        nama='$nama',
        username='$username',
        password='$passwordHash'
    WHERE id_user='".$admin['id_user']."'
    ");

    $_SESSION['nama']=$nama;

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