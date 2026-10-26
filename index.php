<?php
session_start();

if(isset($_SESSION['login'])){
    header("Location: dashboard.php");
    exit;
}

$username = "";

if(isset($_GET['username'])){
    $username = $_GET['username'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<title>Login Admin | SIRITA</title>

<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

* {
    font-family: 'Poppins', sans-serif;
    box-sizing: border-box;
}

html, body {
    width: 100%;
    min-height: 100%;
    overflow-x: hidden;
}

body {
    min-height: 100vh;
    min-height: 100dvh;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background:
        radial-gradient(circle at 10% 20%, rgba(255,255,255,0.18) 0, transparent 25%),
        radial-gradient(circle at 90% 80%, rgba(255,255,255,0.15) 0, transparent 25%),
        linear-gradient(135deg, #0d6efd, #0b5ed7, #084298);
    position: relative;
    padding: 20px 16px;
}

/* Background dekoratif yang terisolasi agar tidak merusak lebar layar mobile */
.bg-decor {
    position: fixed;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 1;
}

.bg-decor::before {
    content: "";
    position: absolute;
    width: 380px;
    height: 380px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    top: -120px;
    left: -80px;
}

.bg-decor::after {
    content: "";
    position: absolute;
    width: 460px;
    height: 460px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.07);
    bottom: -180px;
    right: -100px;
}

/* Wrapper card login selalu di tengah sempurna */
.login-container {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 410px;
    margin: auto;
}

.card-login {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card-header-custom {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: #ffffff;
    padding: 24px 20px 20px;
    text-align: center;
    border-bottom: none;
}

.card-header-custom .logo-img {
    width: 65px;
    height: auto;
    margin-bottom: 10px;
    filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.15));
}

.card-header-custom h2 {
    font-size: 21px;
    font-weight: 700;
    margin-bottom: 2px;
    letter-spacing: 0.5px;
}

.card-header-custom p {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    font-weight: 400;
}

.card-body-custom {
    padding: 24px 22px 28px;
}

.form-label {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 6px;
}

.input-box-group {
    display: flex;
    align-items: center;
    border: 1px solid #ced4da;
    border-radius: 10px;
    background: #ffffff;
    transition: all 0.2s ease;
    overflow: hidden;
}

.input-box-group:focus-within {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.18);
}

.input-box-icon {
    width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 14px;
    background: #f8f9fa;
    border-right: 1px solid #e9ecef;
    align-self: stretch;
}

.input-box-field {
    flex: 1;
    min-width: 0;
    border: none;
    outline: none;
    padding: 11px 14px;
    font-size: 14px;
    color: #212529;
    background: transparent;
}

.input-box-field::placeholder {
    color: #adb5bd;
}

.btn-toggle-pwd {
    border: none;
    background: transparent;
    color: #6c757d;
    padding: 0 14px;
    font-size: 14px;
    cursor: pointer;
    align-self: stretch;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s ease;
}

.btn-toggle-pwd:hover {
    color: #0d6efd;
}

.btn-primary-login {
    height: 46px;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    letter-spacing: 0.3px;
    background: #0d6efd;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
    transition: all 0.2s ease;
}

.btn-primary-login:hover {
    background: #0b5ed7;
    box-shadow: 0 6px 16px rgba(13, 110, 253, 0.35);
    transform: translateY(-1px);
}

.btn-regis-guest {
    height: 46px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.3px;
    background: #198754;
    color: #ffffff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.25);
    transition: all 0.2s ease;
}

.btn-regis-guest:hover {
    background: #157347;
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(25, 135, 84, 0.35);
    transform: translateY(-1px);
}

.divider-text {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 18px 0;
    color: #adb5bd;
    font-size: 12px;
    font-weight: 500;
}

.divider-text::before,
.divider-text::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #e9ecef;
}

.divider-text::before {
    margin-right: 12px;
}

.divider-text::after {
    margin-left: 12px;
}

@media (max-width: 576px) {
    body {
        padding: 16px 14px;
    }

    .card-body-custom {
        padding: 20px 18px 24px;
    }

    .card-header-custom {
        padding: 20px 16px 18px;
    }

    .card-header-custom h2 {
        font-size: 19px;
    }

    .card-header-custom .logo-img {
        width: 58px;
        margin-bottom: 8px;
    }
}

</style>

</head>

<body>

<!-- Efek dekoratif terisolasi agar aman di layar mobile -->
<div class="bg-decor"></div>

<div class="login-container">

    <div class="card card-login">

        <div class="card-header-custom">
            <img src="assets/img/logo_bps.png" alt="Logo BPS" class="logo-img">
            <h2>LOGIN ADMIN</h2>
            <p>SIRITA - BPS Kabupaten Bangka</p>
        </div>

        <div class="card-body-custom">

            <?php if(isset($_GET['pesan'])){ ?>
            <div class="alert alert-danger d-flex align-items-center py-2 px-3 rounded-3 mb-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                <div class="small fw-medium">Username atau Password Salah!</div>
            </div>
            <?php } ?>

            <form action="proses_login.php" method="POST" autocomplete="on">

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-box-group">
                        <div class="input-box-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="input-box-field"
                            placeholder="Masukkan username"
                            value="<?= htmlspecialchars($username); ?>"
                            autocomplete="username"
                            required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-box-group">
                        <div class="input-box-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="input-box-field"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required>
                        <button
                            type="button"
                            class="btn-toggle-pwd"
                            onclick="lihatPassword()"
                            title="Tampilkan / Sembunyikan Password"
                            aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-primary-login w-100 mt-4">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>

                <div class="divider-text">
                    <span>ATAU</span>
                </div>

                <a href="registrasi.php" class="btn btn-success btn-regis-guest w-100">
                    <i class="fas fa-user-plus"></i> Registrasi Tamu
                </a>

            </form>

            <div class="text-center mt-4">
                <small class="text-muted" style="font-size: 11px;">
                    &copy; <?= date('Y'); ?> BPS Kabupaten Bangka
                </small>
            </div>

        </div>

    </div>

</div>

<script>

function lihatPassword(){
    let pass = document.getElementById("password");
    let icon = document.getElementById("icon");

    if(pass.type === "password"){
        pass.type = "text";
        icon.className = "fas fa-eye-slash";
    }else{
        pass.type = "password";
        icon.className = "fas fa-eye";
    }
}

</script>

<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>