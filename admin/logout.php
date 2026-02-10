<?php 
// mengaktifkan session
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
 
// menghapus semua session
session_destroy();
 
// mengalihkan halaman ke halaman login, dengan memberi parameter pesan yang berisi string "logout"
header("location:../index.php?pesan=logout");
?>