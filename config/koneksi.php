<?php

// Helper membaca variabel Environment (mendukung getenv, $_ENV, dan $_SERVER)
function get_config_val($key, $default = '') {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    if (!empty($_ENV[$key])) return (string)$_ENV[$key];
    if (!empty($_SERVER[$key])) return (string)$_SERVER[$key];
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

// Cegah koneksi jika di Vercel tapi DB_PASSWORD belum diatur
if ($is_vercel && $password === '') {
    die("<!DOCTYPE html>
    <html lang='id'>
    <head><meta charset='UTF-8'><title>Konfigurasi Vercel Diperlukan</title></head>
    <body style='font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;padding:40px 16px;margin:0;'>
        <div style='max-width:620px;margin:0 auto;background:#ffffff;padding:32px;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.08);border-top:5px solid #dc2626;'>
            <h2 style='color:#dc2626;margin-top:0;'>⚠️ Environment Variable DB_PASSWORD Belum Terbaca</h2>
            <p style='color:#334155;'>Aplikasi berhasil mendeteksi TiDB Cloud di Vercel, namun variabel <strong>DB_PASSWORD</strong> masih kosong atau belum disuntikkan oleh Vercel.</p>
            <div style='background:#f1f5f9;padding:16px;border-radius:8px;margin:20px 0;'>
                <strong>Cara Memperbaiki:</strong>
                <ol style='margin:8px 0 0 20px;padding:0;color:#1e293b;line-height:1.7;'>
                    <li>Buka <strong>Vercel Dashboard &rarr; Settings &rarr; Environment Variables</strong>.</li>
                    <li>Tambahkan <strong>Key:</strong> <code>DB_PASSWORD</code> dan <strong>Value:</strong> <em>(Password TiDB Cloud Anda)</em>.</li>
                    <li>Pastikan semua checklist (<strong>Production, Preview, Development</strong>) tercentang.</li>
                    <li>Klik tab <strong>Deployments &rarr; klik tombol titik tiga (...) &rarr; Redeploy</strong>.</li>
                </ol>
            </div>
            <p style='font-size:13px;color:#64748b;margin-bottom:0;'><em>Catatan: Menambah variable di Vercel memerlukan tindakan <strong>Redeploy</strong> agar perubahan diterapkan pada serverless instance aktif.</em></p>
        </div>
    </body>
    </html>");
}

// Inisialisasi koneksi MySQLi
$conn = mysqli_init();

// Mode SSL untuk TiDB Cloud (Port 4000 atau DB_SSL=true)
$is_ssl = ($port === 4000 || strtolower((string)get_config_val('DB_SSL', '')) === 'true' || $host !== 'localhost');

$connected = false;
$err = '';

try {
    if ($is_ssl && $host !== 'localhost') {
        mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
        if (defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT')) {
            @mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        }
        $connected = @mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL);
    } else {
        $connected = @mysqli_real_connect($conn, $host, $user, $password, $database, $port);
    }
} catch (mysqli_sql_exception $e) {
    $connected = false;
    $err = $e->getMessage();
} catch (Throwable $e) {
    $connected = false;
    $err = $e->getMessage();
}

if (!$connected) {
    if (empty($err)) {
        $err = mysqli_connect_error();
    }
    
    // Tampilan ramah pengguna jika terjadi Access Denied (password salah)
    if (stripos($err, 'Access denied') !== false) {
        die("<!DOCTYPE html>
        <html lang='id'>
        <head><meta charset='UTF-8'><title>Autentikasi TiDB Gagal</title></head>
        <body style='font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;padding:40px 16px;margin:0;'>
            <div style='max-width:620px;margin:0 auto;background:#ffffff;padding:32px;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.08);border-top:5px solid #ea580c;'>
                <h2 style='color:#ea580c;margin-top:0;'>⚠️ Password TiDB Cloud Salah (Access Denied)</h2>
                <p style='color:#334155;'>Koneksi berhasil menjangkau gateway TiDB Cloud, namun server menolak autentikasi untuk user <code>" . htmlspecialchars($user) . "</code>. Password yang dikirim tidak sesuai.</p>
                <div style='background:#f1f5f9;padding:16px;border-radius:8px;margin:20px 0;'>
                    <strong>Solusi Praktis:</strong>
                    <ol style='margin:8px 0 0 20px;padding:0;color:#1e293b;line-height:1.7;'>
                        <li>Buka konsol <a href='https://tidbcloud.com' target='_blank' style='color:#0d6efd;'>TiDB Cloud</a>.</li>
                        <li>Masuk ke cluster <strong>sirita-db</strong> &rarr; klik tombol <strong>Connect</strong>.</li>
                        <li>Klik <strong>Reset Password</strong> &rarr; klik tombol copy untuk menyalin password baru.</li>
                        <li>Buka <strong>Vercel Dashboard &rarr; Settings &rarr; Environment Variables</strong>.</li>
                        <li>Ubah nilai <code>DB_PASSWORD</code> dengan password yang baru Anda salin.</li>
                        <li>Buka tab <strong>Deployments &rarr; klik (...) &rarr; Redeploy</strong>.</li>
                    </ol>
                </div>
                <p style='font-size:12px;color:#94a3b8;margin-bottom:0;'>Detail error: " . htmlspecialchars($err) . "</p>
            </div>
        </body>
        </html>");
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
