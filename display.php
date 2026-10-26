<?php
include "config/koneksi.php";

$panggil = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT
    a.nomor_antrian,
    p.nama_pelayanan
FROM antrian a
JOIN pelayanan p
ON a.id_pelayanan = p.id_pelayanan
WHERE a.status='Dipanggil'
ORDER BY a.waktu_panggil DESC
LIMIT 1
"));

$menunggu = mysqli_query($conn,"
SELECT nomor_antrian
FROM antrian
WHERE status='Menunggu'
ORDER BY id_antrian ASC
LIMIT 3
");
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<title>Display Antrian SIRITA</title>

<link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
<link rel="stylesheet" href="dist/css/adminlte.min.css">

<style>

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    background:linear-gradient(135deg,#eef6ff,#dceeff,#bfdcff);
    min-height:100vh;
    color:#1e293b;
}

.container-fluid{
    padding:15px 20px 60px;
}

.header{
    text-align:center;
    padding-top:5px;
    margin-bottom:10px;
}

.logo{
    width:70px;
    margin-bottom:5px;
}

.header h2{
    font-size:28px;
    font-weight:700;
    color:#1e3a8a;
    margin:5px 0;
}

.header h4{
    font-size:18px;
    font-weight:700;
    color:#2563eb;
    margin:2px 0;
}

.header h5{
    font-size:16px;
    color:#64748b;
    margin:2px 0;
}

.card{
    border:none;
    border-radius:25px;
    background:rgba(255,255,255,.90);
    backdrop-filter:blur(15px);
    box-shadow:0 20px 40px rgba(0,0,0,.15);
}

.card-header{
    background:#2563eb!important;
    color:#fff;
    border:none;
}

.card-body{
    padding:20px;
}

.nomor{
    font-size:110px;
    font-weight:700;
    color:#2563eb;
    line-height:1;
    animation:zoom .7s;
}

@keyframes zoom{

0%{
    transform:scale(.5);
    opacity:0;
}

100%{
    transform:scale(1);
    opacity:1;
}

}

.pelayanan{
    margin-top:10px;
    font-size:34px;
    font-weight:600;
    color:#334155;
}

.antrian-item{
    background:#22c55e;
    color:#fff;
    padding:15px;
    border-radius:15px;
    margin-bottom:12px;
    text-align:center;
    font-size:34px;
    font-weight:bold;
}

.card-space{
    margin-bottom:25px;
}

.clock{
    font-size:52px;
    font-weight:700;
    color:#1f2937;
}

#tanggal{
    font-size:22px;
    color:#64748b;
}

.running{
    position:fixed;
    left:0;
    bottom:0;
    width:100%;
    height:42px;
    line-height:42px;
    background:#1d4ed8;
    color:white;
    font-size:18px;
    font-weight:500;
    z-index:999;
}

/* Overlay Aktifkan Audio */

#overlayAudio{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,.7);
    display:flex;
    justify-content:center;
    align-items:center;
    z-index:99999;
}

.audio-box{
    width:470px;
    background:#fff;
    border-radius:20px;
    padding:40px;
    text-align:center;
    box-shadow:0 20px 40px rgba(0,0,0,.3);
}

.audio-box h2{
    color:#2563eb;
    margin-bottom:15px;
}

.audio-box p{
    color:#555;
    margin-bottom:25px;
}

.audio-box button{
    border:none;
    background:#2563eb;
    color:#fff;
    padding:15px 35px;
    border-radius:12px;
    font-size:20px;
    font-weight:bold;
    cursor:pointer;
}

.audio-box button:hover{
    background:#1d4ed8;
}

</style>

</head>

<body>

<div class="container-fluid">

<div class="header">

<img src="assets/img/logo_bps.png" class="logo">

<h2>BPS KABUPATEN BANGKA</h2>

<h4>SIRITA</h4>

<h5>Sistem Informasi Registrasi Tamu dan Antrian</h5>

</div>

<div class="row">

<div class="col-md-8">

<div class="card">

<div class="card-body text-center">

<h2 style="color:#2563eb;font-weight:bold;margin-bottom:25px;">
SEDANG DIPANGGIL
</h2>

<div class="nomor" id="nomorDipanggil">
<?= $panggil['nomor_antrian'] ?? "-"; ?>
</div>

<div class="pelayanan" id="pelayananDipanggil">
<?= $panggil['nama_pelayanan'] ?? ""; ?>
</div>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card card-space">

<div class="card-header">

<h4>ANTRIAN MENUNGGU</h4>

</div>

<div class="card-body" id="listMenunggu">

<?php
while($d=mysqli_fetch_assoc($menunggu)){
    echo "<div class='antrian-item'>".$d['nomor_antrian']."</div>";
}
?>

</div>

</div>

<div class="card" style="background:#FFC107;">

<div class="card-body text-center">

<div id="jam" class="clock"></div>

<div id="tanggal"></div>

</div>

</div>

</div>

</div>

</div>

<div class="running">

<marquee behavior="scroll" direction="left">
Selamat Datang di BPS Kabupaten Bangka • Silakan menunggu hingga nomor Anda dipanggil • Terima kasih atas kunjungan Anda
</marquee>

</div>
<script>

// ======================
// JAM
// ======================

function waktu(){

    const sekarang = new Date();

    document.getElementById("jam").innerHTML =
        sekarang.toLocaleTimeString('id-ID');

    document.getElementById("tanggal").innerHTML =
        sekarang.toLocaleDateString('id-ID',{
            weekday:'long',
            day:'numeric',
            month:'long',
            year:'numeric'
        });

}

setInterval(waktu,1000);

waktu();


// ======================
// AUDIO
// ======================

let audioAktif=false;
let queueSuara=[];
let sedangMemutar=false;
let waktuTerakhir="";
let player = null;
function aktifkanAudio(){

    audioAktif = true;

    // Membuka izin audio browser
    const audio = new Audio("assets/audio/tingtong.mp3");

    audio.volume = 0;

    audio.play().then(()=>{

        audio.pause();

        audio.currentTime = 0;

        audio.volume = 1;

        console.log("Audio berhasil diaktifkan");

    });

    document.getElementById("overlayAudio").style.display = "none";
}

function masukkanQueue(data){

    if(!audioAktif){
        return;
    }

    queueSuara.push(data);

    if(!sedangMemutar){

        putarQueue();

    }

}

function playAudio(src){

    return new Promise(resolve=>{

        const audio = new Audio(src);

        let selesai = false;

        function finish(){

            if(selesai) return;

            selesai = true;

            resolve();

        }

        audio.onended = finish;

        audio.onerror = finish;

        audio.play().catch(finish);

        // Pengaman jika onended tidak pernah terpanggil
        setTimeout(finish, 10000);

    });

}

function filePelayanan(nama){

    switch(nama){

        case "Kunjungan":
            return "kunjungan.mp3";

        case "PPID(Pejabat Pengelola Informasi dan Dokumentasi)":
            return "ppid.mp3";

        case "PST(Pelayanan Statistik Terpadu)":
            return "pst.mp3";

        case "Pengaduan":
            return "pengaduan.mp3";

        default:
            return "";

    }

}

async function putarQueue(){

    if(queueSuara.length==0){

        sedangMemutar=false;

        return;

    }

    sedangMemutar=true;

    let data=queueSuara.shift();

    await playAudio("assets/audio/tingtong.mp3");

    await playAudio("assets/audio/perhatian.mp3");

    await playAudio("assets/audio/nomor_antrian.mp3");

    for(let i=0;i<data.nomor.length;i++){

        let karakter=data.nomor.charAt(i);

        await playAudio("assets/audio/"+karakter+".mp3");

    }

    await playAudio("assets/audio/silakan_menuju.mp3");

    console.log("Pelayanan dari database:", data.pelayanan);

    let pelayanan=filePelayanan(data.pelayanan);

    if(pelayanan!=""){

        await playAudio("assets/audio/pelayanan/"+pelayanan);

    }

await playAudio("assets/audio/terima_kasih.mp3");
await playAudio("assets/audio/tingtong_akhir.mp3");

sedangMemutar=false;

putarQueue();

}


// ======================
// UPDATE DISPLAY
// ======================

let sedangRequest = false;

function updateDisplay(){

    // Jangan membuat request baru jika request sebelumnya belum selesai
    if(sedangRequest){
        return;
    }

    sedangRequest = true;

    fetch("get_display.php?t=" + new Date().getTime(), {
        cache: "no-store"
    })

    .then(res => {

        if(!res.ok){
            throw new Error("HTTP Error: " + res.status);
        }

        return res.json();

    })

    .then(data => {
        console.log("DATA DITERIMA:", data.nomor, data.waktu);

        // Update nomor yang sedang dipanggil
        document.getElementById("nomorDipanggil").innerHTML = data.nomor;

        // Update pelayanan
        document.getElementById("pelayananDipanggil").innerHTML = data.pelayanan;

        // Update daftar antrean menunggu
        let html = "";

        data.menunggu.forEach(function(item){

            html += "<div class='antrian-item'>" + item + "</div>";

        });

        document.getElementById("listMenunggu").innerHTML = html;

        // Jika ada panggilan baru
        if(data.nomor != "-"){

            if(waktuTerakhir != data.waktu){
                console.log("PANGGILAN BARU:", data.nomor, data.waktu);

                waktuTerakhir = data.waktu;

                masukkanQueue(data);

            }

        }

    })

    .catch(error => {

        console.error("Gagal mengambil data:", error);

    })

    .finally(() => {

        // Request selesai, boleh melakukan request berikutnya
        sedangRequest = false;

    });

}

// Jalankan pertama kali
updateDisplay();

// Cek database setiap 1 detik
setInterval(updateDisplay, 1000);

</script>

<div id="overlayAudio">

<div class="audio-box">

<h2>🔊 Aktifkan Audio</h2>

<p>

Klik tombol di bawah ini satu kali agar sistem dapat memanggil antrean secara otomatis.

</p>

<button onclick="aktifkanAudio()">

AKTIFKAN SUARA

</button>

</div>

</div>

</body>

</html>