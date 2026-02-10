<?php
session_start();
include 'config/koneksi.php';
include 'config/fungsi_log.php';

$username = $_POST['username'];
$password = $_POST['password'];
$role     = $_POST['role'];

// Query cek user + role
$query = mysqli_query($koneksi, "
    SELECT * FROM tb_user 
    WHERE username='$username' 
    AND password='$password' 
    AND role='$role'
");

$data = mysqli_fetch_assoc($query);
$cek  = mysqli_num_rows($query);

if ($cek > 0) {

    $_SESSION['id_user']  = $data['id_user'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['nama_lengkap'] = $data['nama_lengkap']; 
    $_SESSION['role']     = $data['role'];
    $_SESSION['status']   = "login";

  logAktivitas(
        $koneksi,
        $_SESSION['id_user'],
        "Login berhasil sebagai ".$_SESSION['role']
    );
    
    // ARAHKAN SESUAI ROLE
    if ($data['role'] == 'admin') {
        header("location:admin/index.php");
    } elseif ($data['role'] == 'petugas') {
        header("location:petugas/dashboard_petugas.php");
    } elseif ($data['role'] == 'owner') {
        header("location:owner/index.php");
    }

} else {
    header("location:index.php?pesan=gagal");
}
?>