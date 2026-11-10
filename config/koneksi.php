<?php

// Helper membaca variabel Environment (mendukung getenv, $_ENV, dan $_SERVER)
function get_config_val($key, $default = '') {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    if (!empty($_ENV[$key])) return $_ENV[$key];
    if (!empty($_SERVER[$key])) return $_SERVER[$key];
    return $default;
}

// Deteksi apakah sedang berjalan di lingkungan Vercel / Cloud Serverless
$is_vercel = !empty(get_config_val('VERCEL')) || !empty(get_config_val('AWS_LAMBDA_FUNCTION_NAME')) || is_dir('/var/task');

// Default konfigurasi: jika di Vercel, otomatis gunakan TiDB Cloud
$default_host = $is_vercel ? 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com' : 'localhost';
$default_port = $is_vercel ? 4000 : 3306;
$default_user = $is_vercel ? '47rl44GFwCeHL4N.root' : 'root';
$default_db   = 'bps_buku_tamu';

$host     = get_config_val('DB_HOST', $default_host);
$user     = get_config_val('DB_USER', $default_user);
$password = get_config_val('DB_PASSWORD', '');
$database = get_config_val('DB_NAME', $default_db);
$port     = (int)get_config_val('DB_PORT', $default_port);

// Inisialisasi koneksi MySQLi
$conn = mysqli_init();

// Mode SSL untuk TiDB Cloud (Port 4000 atau DB_SSL=true)
$is_ssl = ($port === 4000 || strtolower((string)get_config_val('DB_SSL', '')) === 'true' || $host !== 'localhost');

if ($is_ssl && $host !== 'localhost') {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    // Hindari kegagalan verifikasi SSL jika container serverless belum terpasang custom CA bundle
    if (defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT')) {
        @mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    }
    $connected = @mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL);
} else {
    $connected = @mysqli_real_connect($conn, $host, $user, $password, $database, $port);
}

if (!$connected) {
    $err = mysqli_connect_error();
    if ($is_vercel && empty($password)) {
        die("<div style='font-family:sans-serif;padding:30px;line-height:1.6;'>
            <h2 style='color:#dc2626;'>Database TiDB Cloud Belum Terhubung</h2>
            <p>Password database belum terbaca di Vercel. Pastikan Anda telah menambahkan Environment Variable <code>DB_PASSWORD</code> di <strong>Vercel Settings &rarr; Environment Variables</strong>, lalu klik <strong>Redeploy</strong>.</p>
        </div>");
    }
    die("Koneksi database gagal: " . $err);
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
