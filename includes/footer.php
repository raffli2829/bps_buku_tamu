<footer class="main-footer">

    <strong>
        Copyright &copy; <?= date('Y'); ?>
        SIRITA - BPS Kabupaten Bangka.
    </strong>

    <div class="float-right d-none d-sm-inline-block">
        Versi 1.0
    </div>

</footer>

</div>

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>

<!-- Bootstrap -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE -->
<script src="dist/js/adminlte.min.js"></script>

<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function(){

    if ($("#tabelPelayanan").length) {
        $("#tabelPelayanan").DataTable({
            responsive:true,
            autoWidth:false
        });
    }

    if ($("#tabelPengunjung").length) {
        $("#tabelPengunjung").DataTable({
            responsive:true,
            autoWidth:false
        });
    }

    if ($("#tabelAntrian").length) {
        $("#tabelAntrian").DataTable({
            responsive:true,
            autoWidth:false
        });
    }
    if ($("#tabelLaporan").length) {
    $("#tabelLaporan").DataTable({
        responsive:true,
        autoWidth:false
    });
}

});
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>