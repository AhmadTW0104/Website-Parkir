<?php
session_start();
include '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?pesan=login_dulu");
    exit;
}

/* CEK STATUS AKUN */
$id_user = $_SESSION['id_user'];

$query = mysqli_query($koneksi,
    "SELECT status_aktif FROM tb_user WHERE id_user='$id_user'"
);
$data = mysqli_fetch_assoc($query);

if ($data['status_aktif'] == 0) {
    session_destroy();
    echo "<script>
        alert('Akun kamu sudah dinonaktifkan!');
        location.href='../index.php';
    </script>";
    exit;
}

// Cek role
if ($_SESSION['role'] != 'petugas') {
    header("location:../index.php?pesan=akses_ditolak");
    exit;
}

$username = $_SESSION['username'];
$id_user = $_SESSION['id_user'];

// Ambil nama lengkap user
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT nama_lengkap FROM tb_user WHERE id_user = '$id_user'
"));
$nama_lengkap = $user['nama_lengkap'] ?? $username;

// Statistik untuk petugas
$kendaraanAktif = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) as total 
    FROM tb_transaksi 
    WHERE LOWER(status) = 'masuk'
"));

$transaksiHariIni = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) as total 
    FROM tb_transaksi 
    WHERE DATE(waktu_masuk) = CURDATE()
"));

$kendaraanKeluar = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) as total 
    FROM tb_transaksi 
    WHERE LOWER(status) = 'keluar' AND DATE(waktu_keluar) = CURDATE()
"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Sistem Parkir</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Dashboard CSS -->
    <link rel="stylesheet" href="dashboard_petugas.css">
</head>

<body>
    
    <div class="container-fluid">
        <div class="row">
            
            <!-- SIDEBAR -->
            <div class="col-md-2 col-lg-2 px-0 sidebar">
                <div class="p-4">
                    <div class="logo-text">
                        <div class="logo-icon">
                            <i class="bi bi-p-square-fill"></i>
                        </div>
                        Website Parkir
                    </div>
                    
                    <nav class="nav flex-column mt-4">
                        <a href="dashboard_petugas.php" class="nav-link active">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                        
                        <a href="parkir_masuk.php" class="nav-link">
                            <i class="bi bi-box-arrow-in-down"></i>
                            <span>Parkir Masuk</span>
                        </a>
                        
                        <a href="parkir_keluar.php" class="nav-link">
                            <i class="bi bi-box-arrow-up"></i>
                            <span>Parkir Keluar</span>
                        </a>
                        
                        <a href="riwayat.php" class="nav-link">
                            <i class="bi bi-clock-history"></i>
                            <span>Riwayat Parkir</span>
                        </a>
                        
                        <hr class="sidebar-divider">
                        
                        <a href="logout.php" class="nav-link text-danger" onclick="return confirm('Yakin ingin logout?')">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- MAIN CONTENT -->
            <div class="col-md-10 col-lg-10 px-0 main-content">
                
                <!-- TOP NAVBAR -->
                <div class="top-navbar d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold">Dashboard Petugas</h4>
                        <p class="text-muted mb-0 small">Kelola transaksi parkir masuk dan keluar</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end d-none d-md-block">
                            <div class="fw-semibold"><?= htmlspecialchars($nama_lengkap); ?></div>
                            <span class="badge-role badge bg-info">
                                <i class="bi bi-person-badge me-1"></i>Petugas
                            </span>
                        </div>
                        <div class="user-avatar" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <?= strtoupper(substr($nama_lengkap, 0, 1)); ?>
                        </div>
                    </div>
                </div>
                
                <!-- CONTENT AREA -->
                <div class="p-4">
                    
                    <!-- Welcome Card -->
                    <div class="welcome-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="fw-bold mb-2">
                                    <i class="bi bi-hand-thumbs-up me-2"></i>Selamat Bertugas, <?= htmlspecialchars($nama_lengkap); ?>!
                                </h2>
                                <p class="mb-0 opacity-75">Kelola transaksi parkir dengan cepat dan akurat</p>
                            </div>
                            <div class="col-md-4 text-end d-none d-md-block">
                                <i class="bi bi-person-workspace" style="font-size: 5rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row g-4 mb-4">
                        
                        <div class="col-md-6 col-lg-4">
                            <div class="stats-card">
                                <div class="stats-icon gradient-warning text-white">
                                    <i class="bi bi-car-front-fill"></i>
                                </div>
                                <div class="stats-value"><?= number_format($kendaraanAktif['total'] ?? 0); ?></div>
                                <div class="stats-label">
                                    <i class="bi bi-circle-fill me-1 text-success"></i>Kendaraan Sedang Parkir
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <div class="stats-card">
                                <div class="stats-icon gradient-info text-white">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                                <div class="stats-value"><?= number_format($transaksiHariIni['total'] ?? 0); ?></div>
                                <div class="stats-label">
                                    <i class="bi bi-calendar-check me-1"></i>Transaksi Hari Ini
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <div class="stats-card">
                                <div class="stats-icon gradient-success text-white">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="stats-value"><?= number_format($kendaraanKeluar['total'] ?? 0); ?></div>
                                <div class="stats-label">
                                    <i class="bi bi-box-arrow-right me-1"></i>Kendaraan Keluar Hari Ini
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- Menu Cards -->
                    <div class="row g-4">
                        
                        <div class="col-md-6 col-lg-4">
                            <a href="parkir_masuk.php" class="text-decoration-none">
                                <div class="card dashboard-card">
                                    <div class="card-body p-4">
                                        <div class="card-icon gradient-primary text-white">
                                            <i class="bi bi-box-arrow-in-down"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2">Parkir Masuk</h5>
                                        <p class="text-muted mb-0">Input kendaraan yang masuk ke area parkir</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <a href="parkir_keluar.php" class="text-decoration-none">
                                <div class="card dashboard-card">
                                    <div class="card-body p-4">
                                        <div class="card-icon gradient-success text-white">
                                            <i class="bi bi-box-arrow-up"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2">Parkir Keluar</h5>
                                        <p class="text-muted mb-0">Proses kendaraan keluar dan hitung biaya parkir</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <a href="riwayat.php" class="text-decoration-none">
                                <div class="card dashboard-card">
                                    <div class="card-body p-4">
                                        <div class="card-icon gradient-info text-white">
                                            <i class="bi bi-clock-history"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2">Riwayat Parkir</h5>
                                        <p class="text-muted mb-0">Lihat riwayat transaksi parkir</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                    </div>
                    
                </div>
                
            </div>
            
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>