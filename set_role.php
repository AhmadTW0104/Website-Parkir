<?php
session_start();

if (!isset($_GET['role'])) {
    header("location:pilih_dashboard.php");
    exit;
}

$role = $_GET['role'];

$_SESSION['role'] = $role;

if ($role == 'admin') {
    header("location:admin/index.php");
} elseif ($role == 'petugas') {
    header("location:petugas/dashboard_petugas.php");
} elseif ($role == 'owner') {
    header("location:owner/index.php");
} else {
    header("location:pilih_dashboard.php");
}
exit;