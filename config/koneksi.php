<?php

// Baca konfigurasi dari Environment Variables (saat di Vercel/Hosting Cloud),
// atau fallback ke default localhost Laragon (saat dijalankan di laptop).
$host     = getenv('DB_HOST') ?: 'localhost';
$user     = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
$database = getenv('DB_NAME') ?: 'bps_buku_tamu';
$port     = getenv('DB_PORT') ? (int)getenv('DB_PORT') : 3306;

// Inisialisasi koneksi MySQLi
$conn = mysqli_init();

// Jika menggunakan TiDB Cloud / SSL port 4000 atau variabel DB_SSL diset
if ($port === 4000 || !empty(getenv('DB_SSL'))) {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    $connected = @mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL);
} else {
    $connected = @mysqli_real_connect($conn, $host, $user, $password, $database, $port);
}

if (!$connected) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

/* ==========================================================
   KONFIGURASI URL PUBLIK / ONLINE SIRITA
   - Kosongkan ("") jika ingin deteksi otomatis dari jaringan
   - Atau isi dengan alamat domain / hosting / tunnel online Anda
   Contoh:
   $BASE_PUBLIC_URL = "https://sirita.bpsbangka.go.id";
   $BASE_PUBLIC_URL = "https://bps-buku-tamu.vercel.app";
========================================================== */
$BASE_PUBLIC_URL = getenv('APP_URL') ?: "";