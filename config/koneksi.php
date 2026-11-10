<?php

// Helper membaca variabel Environment (mendukung getenv, $_ENV, dan $_SERVER)
function get_config_val($keys, $default = '') {
    if (!is_array($keys)) {
        $keys = [$keys];
    }
    foreach ($keys as $k) {
        $val = getenv($k);
        if ($val !== false && $val !== '') return (string)$val;
        if (!empty($_ENV[$k])) return (string)$_ENV[$k];
        if (!empty($_SERVER[$k])) return (string)$_SERVER[$k];
    }
    return $default;
}

// Deteksi apakah sedang berjalan di lingkungan Vercel / Cloud Serverless
$is_vercel = !empty(get_config_val('VERCEL')) || !empty(get_config_val('AWS_LAMBDA_FUNCTION_NAME')) || is_dir('/var/task');

// Default konfigurasi: jika di Vercel, otomatis gunakan TiDB Cloud
$default_host = $is_vercel ? 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com' : 'localhost';
$default_port = $is_vercel ? 4000 : 3306;
$default_user = $is_vercel ? '47rl44GFwCeHL4N.root' : 'root';
$default_db   = 'bps_buku_tamu';

// Baca variabel (fleksibel: mendukung DB_PASSWORD maupun PASSWORD, DB_HOST maupun HOST, dll.)
$host     = get_config_val(['DB_HOST', 'HOST'], $default_host);
$user     = get_config_val(['DB_USER', 'USERNAME', 'USER'], $default_user);
$password = get_config_val(['DB_PASSWORD', 'PASSWORD'], '');
$database = get_config_val(['DB_NAME', 'DATABASE'], $default_db);
$port     = (int)get_config_val(['DB_PORT', 'PORT'], $default_port);

// Validasi jika di Vercel tapi password masih kosong atau masih bernilai teks sensor '<PASSWORD>'
if ($is_vercel && ($password === '' || trim($password) === '<PASSWORD>')) {
    $is_placeholder = (trim($password) === '<PASSWORD>');
    die("<!DOCTYPE html>
    <html lang='id'>
    <head><meta charset='UTF-8'><title>Konfigurasi Password Diperlukan</title></head>
    <body style='font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;padding:40px 16px;margin:0;'>
        <div style='max-width:640px;margin:0 auto;background:#ffffff;padding:32px;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.08);border-top:5px solid #ea580c;'>
            <h2 style='color:#ea580c;margin-top:0;'>⚠️ " . ($is_placeholder ? "Teks &lt;PASSWORD&gt; Terdeteksi (Bukan Password Asli)" : "Environment Variable Password Belum Terisi") . "</h2>
            <p style='color:#334155;'>
                " . ($is_placeholder 
                    ? "Nilai variable <code>PASSWORD</code> di Vercel saat ini adalah teks literal <strong>&lt;PASSWORD&gt;</strong>. Teks tersebut adalah sensor keamanan dari TiDB Cloud, bukan password yang sebenarnya." 
                    : "Variable <code>PASSWORD</code> atau <code>DB_PASSWORD</code> di Vercel masih kosong.") . "
            </p>
            <div style='background:#f1f5f9;padding:18px;border-radius:10px;margin:20px 0;'>
                <strong>Langkah Mudah Mendapatkan Password Asli:</strong>
                <ol style='margin:10px 0 0 20px;padding:0;color:#1e293b;line-height:1.8;'>
                    <li>Buka konsol <a href='https://tidbcloud.com' target='_blank' style='color:#0d6efd;font-weight:600;'>TiDB Cloud</a> pada popup <strong>Connect</strong>.</li>
                    <li>Klik tautan biru <strong>Reset Password</strong> (di atas kotak Parameters).</li>
                    <li>TiDB akan membuat password acak baru dan menampilkan tombol <strong>Copy</strong> (dua kotak tumpuk). Klik tombol tersebut untuk menyalin password asli.</li>
                    <li>Buka <strong>Vercel &rarr; Settings &rarr; Environment Variables</strong>.</li>
                    <li>Ubah nilai variable <code>PASSWORD</code> (atau <code>DB_PASSWORD</code>) dengan menempelkan (paste) password asli tersebut.</li>
                    <li>Klik tab <strong>Deployments &rarr; klik tombol titik tiga (...) &rarr; Redeploy</strong>.</li>
                </ol>
            </div>
        </div>
    </body>
    </html>");
}

// Inisialisasi koneksi MySQLi
$conn = mysqli_init();

// Mode SSL untuk TiDB Cloud (Port 4000 atau DB_SSL=true)
$is_ssl = ($port === 4000 || strtolower((string)get_config_val(['DB_SSL', 'SSL'], '')) === 'true' || $host !== 'localhost');

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
    
    // Tampilan ramah pengguna jika terjadi Access Denied
    if (stripos($err, 'Access denied') !== false) {
        die("<!DOCTYPE html>
        <html lang='id'>
        <head><meta charset='UTF-8'><title>Autentikasi TiDB Gagal</title></head>
        <body style='font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;padding:40px 16px;margin:0;'>
            <div style='max-width:620px;margin:0 auto;background:#ffffff;padding:32px;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.08);border-top:5px solid #ea580c;'>
                <h2 style='color:#ea580c;margin-top:0;'>⚠️ Password TiDB Cloud Tidak Cocok (Access Denied)</h2>
                <p style='color:#334155;'>Koneksi berhasil menjangkau gateway TiDB Cloud, namun server menolak autentikasi untuk user <code>" . htmlspecialchars($user) . "</code>. Password yang tersimpan di Vercel tidak cocok.</p>
                <div style='background:#f1f5f9;padding:16px;border-radius:8px;margin:20px 0;'>
                    <strong>Solusi:</strong>
                    <ol style='margin:8px 0 0 20px;padding:0;color:#1e293b;line-height:1.7;'>
                        <li>Buka konsol <a href='https://tidbcloud.com' target='_blank' style='color:#0d6efd;'>TiDB Cloud</a> &rarr; klik tombol <strong>Connect</strong>.</li>
                        <li>Klik <strong>Reset Password</strong> &rarr; salin password baru.</li>
                        <li>Buka <strong>Vercel &rarr; Settings &rarr; Environment Variables</strong> &rarr; ubah <code>PASSWORD</code>.</li>
                        <li>Klik tab <strong>Deployments &rarr; Redeploy</strong>.</li>
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
========================================================== */
$vercel_url = get_config_val('VERCEL_URL', '');
if (!empty($vercel_url)) {
    $BASE_PUBLIC_URL = "https://" . rtrim($vercel_url, '/');
} else {
    $BASE_PUBLIC_URL = get_config_val('APP_URL', '');
}
