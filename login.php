<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("location:../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

$q = mysqli_query($koneksi, 
    "SELECT status_aktif FROM tb_user WHERE id_user='$id_user'"
);

$data = mysqli_fetch_assoc($q);

if ($data['status_aktif'] == 0) {
    session_destroy();
    echo "<script>
        alert('Akun sudah dinonaktifkan oleh admin');
        location.href='../login.php';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Parkir</title>
 <link rel="stylesheet" href="style.css">

</head>
<body>

<div class="wrapper">
    <div class="login-card">
        <h2>Login Sistem Parkir</h2>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" name="login">Login</button>
</body>
</html>
