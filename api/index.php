<?php
// Set session save path to /tmp for Vercel Serverless environment
if (is_dir('/tmp') && is_writable('/tmp')) {
    @ini_set('session.save_path', '/tmp');
}

$root = dirname(__DIR__);

// Ambil path request tanpa parameter query string
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = urldecode($uri);

// Tentukan target file berdasarkan URI yang diminta
if ($uri === '/' || $uri === '' || $uri === '/index' || $uri === '/index.php') {
    $target = $root . '/index.php';
} else {
    $target = $root . $uri;
    if (is_dir($target)) {
        $target = rtrim($target, '/') . '/index.php';
    } elseif (!file_exists($target) && file_exists($target . '.php')) {
        $target = $target . '.php';
    }
}

// Pastikan file target valid dan berada dalam direktori proyek
$realTarget = realpath($target);
$realRoot   = realpath($root);

if ($realTarget && strpos($realTarget, $realRoot) === 0 && is_file($realTarget)) {
    // Ubah direktori kerja ke lokasi file target agar relative path & includes berfungsi
    chdir(dirname($realTarget));

    // Jika file PHP, eksekusi file tersebut
    if (pathinfo($realTarget, PATHINFO_EXTENSION) === 'php') {
        require $realTarget;
        exit;
    }

    // Jika file statis yang lolos ke router
    $ext = pathinfo($realTarget, PATHINFO_EXTENSION);
    $mimes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
    ];
    $contentType = $mimes[$ext] ?? (function_exists('mime_content_type') ? mime_content_type($realTarget) : 'application/octet-stream');
    header('Content-Type: ' . $contentType);
    readfile($realTarget);
    exit;
}

http_response_code(404);
echo "404 - Halaman tidak ditemukan: " . htmlspecialchars($uri);
