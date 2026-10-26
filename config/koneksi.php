<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "bps_buku_tamu";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

/* ==========================================================
   KONFIGURASI URL PUBLIK / ONLINE SIRITA
   - Kosongkan ("") jika ingin deteksi otomatis dari jaringan
   - Atau isi dengan alamat domain / hosting / tunnel online Anda
   Contoh:
   $BASE_PUBLIC_URL = "https://sirita.bpsbangka.go.id";
   $BASE_PUBLIC_URL = "https://nama-tunnel.trycloudflare.com";
========================================================== */
$BASE_PUBLIC_URL = "";