<?php

// Helper membaca variabel Environment (mendukung getenv, $_ENV, dan $_SERVER)
function get_config_val($key, $default = '') {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    if (!empty($_ENV[$key])) return $_ENV[$key];
    if (!empty($_SERVER[$key])) return $_SERVER[$key];
    return $default;
}

// Konfigurasi Database (otomatis TiDB di Vercel atau localhost Laragon)
$host     = get_config_val('DB_HOST', 'localhost');
$user     = get_config_val('DB_USER', 'root');
$password = get_config_val('DB_PASSWORD', '');
$database = get_config_val('DB_NAME', 'bps_buku_tamu');
$port     = (int)get_config_val('DB_PORT', 3306);

// Inisialisasi koneksi MySQLi
$conn = mysqli_init();

// Mode SSL untuk TiDB Cloud (Port 4000 atau DB_SSL=true)
$is_ssl = ($port === 4000 || strtolower((string)get_config_val('DB_SSL', '')) === 'true');

if ($is_ssl) {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    // Hindari kegagalan SSL jika container serverless belum terpasang custom CA
    if (defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT')) {
        @mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    }
    $connected = @mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL);
} else {
    $connected = @mysqli_real_connect($conn, $host, $user, $password, $database, $port);
}

if (!$connected) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

/* ==========================================================
   KONFIGURASI URL PUBLIK / ONLINE SIRITA
   - Otomatis menggunakan VERCEL_URL jika deploy di Vercel
   - Atau isi manual lewat Environment Variable APP_URL
========================================================== */
$vercel_url = get_config_val('VERCEL_URL', '');
if (!empty($vercel_url)) {
    $BASE_PUBLIC_URL = "https://" . rtrim($vercel_url, '/');
} else {
    $BASE_PUBLIC_URL = get_config_val('APP_URL', '');
}
