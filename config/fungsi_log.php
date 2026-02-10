<?php
function logAktivitas($koneksi, $id_user, $aktivitas)
{
    mysqli_query($koneksi, "
        INSERT INTO tb_log_aktivitas (id_user, aktivitas, waktu_aktivitas)
        VALUES ('$id_user', '$aktivitas', NOW())
    ");
}
?>