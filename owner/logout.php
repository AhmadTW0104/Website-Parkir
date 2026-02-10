<?php
session_start();
include '../config/koneksi.php';
include '../config/fungsi_log.php';

if (isset($_SESSION['status']) && $_SESSION['status'] == 'login') {

    $id_user = $_SESSION['id_user'];
    $role    = $_SESSION['role'];

    logAktivitas(
        $koneksi,
        $id_user,
        "Logout dari sistem sebagai $role"
    );
}

// Hapus semua session
session_unset();
session_destroy();

// Kembali ke halaman login
header("location:index.php?pesan=logout");
exit;
?>