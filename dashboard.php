<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">

                <div class="col-sm-6">
                    <h1>
                        <?php
                        if (!isset($_GET['page'])) {
                            echo "Dashboard";
                        } else {
                            echo ucfirst($_GET['page']);
                        }
                        ?>
                    </h1>
                </div>

            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <?php

            if (!isset($_GET['page'])) {

                include 'pages/dashboard/index.php';

            } else {

                switch ($_GET['page']) {

    case 'pelayanan':
        include 'pages/pelayanan/index.php';
        break;

    case 'pengunjung':
        include 'pages/pengunjung/index.php';
        break;

    case 'pengunjung_tambah':
        include 'pages/pengunjung/tambah.php';
        break;

    case 'pengunjung_detail':
        include 'pages/pengunjung/detail.php';
        break;
    
    case 'pengunjung_edit':
        include 'pages/pengunjung/edit.php';
        break;

    case 'antrian':
        include 'pages/antrian/index.php';
        break;

    case 'laporan':
        include 'pages/laporan/index.php';
        break;
        
    case 'akun':
        include 'pages/akun/index.php';
        break;

    default:
        include 'pages/dashboard/index.php';
        break;

}

            }

            ?>

        </div>
    </section>

</div>

<?php
include 'includes/footer.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.querySelectorAll(".btnLogout").forEach(function(btn){

    btn.addEventListener("click", function(e){

        e.preventDefault();

        let url = this.href;

        Swal.fire({
            title: "Konfirmasi Logout",
            text: "Apakah Anda yakin ingin keluar dari sistem?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Ya, Logout",
            cancelButtonText: "Batal"
        }).then((result) => {

            if (result.isConfirmed) {
                window.location.href = url;
            }

        });

    });

});
</script>