<?php

include "config/koneksi.php";

$query=mysqli_query($conn,"
SELECT
a.nomor_antrian,
p.nama_pelayanan

FROM antrian a

JOIN pelayanan p
ON a.id_pelayanan=p.id_pelayanan

WHERE a.status='Dipanggil'

ORDER BY a.waktu_panggil DESC

LIMIT 1
");

$data=mysqli_fetch_assoc($query);

echo json_encode([

"nomor"=>$data['nomor_antrian'] ?? "",
"pelayanan"=>$data['nama_pelayanan'] ?? ""

]);

?>