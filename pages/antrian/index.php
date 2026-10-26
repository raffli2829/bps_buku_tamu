<?php
include 'config/koneksi.php';

$query = mysqli_query($conn,"
SELECT
a.id_antrian,
a.nomor_antrian,
a.status,
p.nama,
p.no_hp,
pl.nama_pelayanan

FROM antrian a

JOIN pengunjung p
ON a.id_pengunjung=p.id_pengunjung

JOIN pelayanan pl
ON a.id_pelayanan=pl.id_pelayanan

ORDER BY a.id_antrian DESC
");
?>

<div class="card">

    <div class="card-header bg-primary">

        <h3 class="card-title">
            Data Antrian
        </h3>

    </div>

    <div class="card-body">

        <table id="tabelAntrian" class="table table-bordered table-striped">

            <thead>

            <tr>

                <th>No</th>
                <th>Nomor</th>
                <th>Nama</th>
                <th>Pelayanan</th>
                <th>Status</th>
                <th>Aksi</th>

            </tr>

            </thead>

            <tbody>

            <?php
            $no=1;

            while($d=mysqli_fetch_assoc($query)){
            ?>

            <tr>

                <td><?= $no++; ?></td>

                <td>
                    <b><?= $d['nomor_antrian']; ?></b>
                </td>

                <td><?= $d['nama']; ?></td>

                <td><?= $d['nama_pelayanan']; ?></td>

                <td>

                    <?php

                    if($d['status']=="Menunggu"){

                        echo "<span class='badge badge-warning'>Menunggu</span>";

                    }elseif($d['status']=="Dipanggil"){

                        echo "<span class='badge badge-primary'>Dipanggil</span>";

                    }else{

                        echo "<span class='badge badge-success'>Selesai</span>";

                    }

                    ?>

                </td>

                <td>

                    <?php if($d['status']=="Menunggu"){ ?>

                        <a href="pages/antrian/panggil.php?id=<?= $d['id_antrian']; ?>"
                           class="btn btn-primary btn-sm">

                            Panggil

                        </a>

                    <?php } ?>

                  <?php if($d['status']=="Dipanggil"){ 
                      $clean_wa = preg_replace('/[^0-9]/', '', $d['no_hp'] ?? '');
                      if (str_starts_with($clean_wa, '0')) {
                          $clean_wa = '62' . substr($clean_wa, 1);
                      }
                      $pesan_panggilan = "Yth. Bpk/Ibu *" . $d['nama'] . "*,\n\nNomor antrean Anda *" . $d['nomor_antrian'] . "* untuk layanan *" . $d['nama_pelayanan'] . "* saat ini *SEDANG DIPANGGIL* di Loket Pelayanan BPS Kabupaten Bangka.\n\nSilakan segera menuju ke loket pelayanan. Terima kasih.";
                      $wa_link = "https://api.whatsapp.com/send?phone=" . urlencode($clean_wa) . "&text=" . urlencode($pesan_panggilan);
                  ?>

<a href="pages/antrian/panggil_ulang.php?id=<?= $d['id_antrian']; ?>"
class="btn btn-warning btn-sm" title="Panggil ulang suara di Display TV">
<i class="fas fa-volume-up"></i>
Panggil Ulang
</a>

<a href="<?= $wa_link; ?>" target="_blank" class="btn btn-success btn-sm" title="Kirim Notifikasi Panggilan WhatsApp ke Tamu">
<i class="fab fa-whatsapp"></i> Notif WA
</a>

<a href="pages/antrian/selesai.php?id=<?= $d['id_antrian']; ?>"
   class="btn btn-secondary btn-sm btnSelesai">
    <i class="fas fa-check"></i> Selesai
</a>

<?php } ?>

                </td>

            </tr>

            <?php } ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.querySelectorAll(".btnSelesai").forEach(function(btn){

    btn.addEventListener("click", function(e){

        e.preventDefault();

        let url=this.href;

        Swal.fire({
            title:"Konfirmasi",
            text:"Apakah Anda yakin antrean ini sudah selesai?",
            icon:"question",
            showCancelButton:true,
            confirmButtonColor:"#28a745",
            cancelButtonColor:"#d33",
            confirmButtonText:"Ya, Selesai",
            cancelButtonText:"Batal"
        }).then((result)=>{

            if(result.isConfirmed){

                window.location.href=url;

            }

        });

    });

});
</script>
            </tbody>

        </table>

    </div>

</div>