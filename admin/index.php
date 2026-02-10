<?php
session_start();
include '../config/Koneksi.php';

/* CEK LOGIN */
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?pesan=login_dulu");
    exit;
}

/* CEK STATUS AKUN (INI YANG KURANG) */
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

$role = $_SESSION['role'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'] ?? $username; // Ambil nama lengkap
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sistem Parkir</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Dashboard CSS -->
    <link rel="stylesheet" href="dashboard.css">
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
                        <a href="index.php" class="nav-link active">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                        
                        <?php if ($role == 'admin') { ?>
                        <a href="user.php" class="nav-link">
                            <i class="bi bi-people-fill"></i>
                            <span>Manajemen User</span>
                        </a>
                        
                        <a href="tarif.php" class="nav-link">
                            <i class="bi bi-cash-coin"></i>
                            <span>Data Tarif</span>
                        </a>
                        
                        <a href="area_parkir.php" class="nav-link">
                            <i class="bi bi-building"></i>
                            <span>Area Parkir</span>
                        </a>
                        
                        <a href="kendaraan.php" class="nav-link">
                            <i class="bi bi-car-front-fill"></i>
                            <span>Data Kendaraan</span>
                        </a>
                        
                        <a href="log_aktivitas.php" class="nav-link">
                            <i class="bi bi-clock-history"></i>
                            <span>Log Aktivitas</span>
                        </a>
                        <?php } ?>
                        
                        <hr class="text-secondary my-3">
                        
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
                        <h4 class="mb-0 fw-bold">Dashboard</h4>
                        <p class="text-muted mb-0 small">Selamat datang kembali!</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end d-none d-md-block">
                            <div class="fw-semibold"><?= htmlspecialchars($nama_lengkap); ?></div>
                            <span class="badge-role badge bg-primary"><?= ucfirst($role); ?></span>
                        </div>
                        <div class="user-avatar">
                            <?= strtoupper(substr($nama_lengkap, 0, 1)); ?>
                        </div>
                    </div>
                </div>
                
                <!-- CONTENT AREA -->
                <div class="p-4">
                    
                    <!-- Welcome Card -->
                    <div class="welcome-card">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="fw-bold mb-2">👋 Halo, <?= htmlspecialchars($nama_lengkap); ?>!</h2>
                                <p class="mb-0 opacity-75">Sistem Manajemen Parkir - Kelola semua data parkir dengan mudah dan efisien</p>
                            </div>
                            <div class="col-md-4 text-end d-none d-md-block">
                                <i class="bi bi-clipboard-data" style="font-size: 5rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($role == 'admin') { ?>
                    <!-- Dashboard Cards -->
                    <div class="row g-4">
                        
                        <div class="col-md-6 col-lg-4">
                            <a href="user.php" class="text-decoration-none">
                                <div class="card dashboard-card">
                                    <div class="card-body p-4">
                                        <div class="card-icon gradient-primary text-white">
                                            <i class="bi bi-people-fill"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2">Manajemen User</h5>
                                        <p class="text-muted mb-0">Kelola akun dan hak akses sistem</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <a href="tarif.php" class="text-decoration-none">
                                <div class="card dashboard-card">
                                    <div class="card-body p-4">
                                        <div class="card-icon gradient-success text-white">
                                            <i class="bi bi-cash-coin"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2">Tarif Parkir</h5>
                                        <p class="text-muted mb-0">Aturan dan tarif parkir kendaraan</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <a href="area_parkir.php" class="text-decoration-none">
                                <div class="card dashboard-card">
                                    <div class="card-body p-4">
                                        <div class="card-icon gradient-info text-white">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2">Area Parkir</h5>
                                        <p class="text-muted mb-0">Kapasitas dan zona parkir</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <a href="kendaraan.php" class="text-decoration-none">
                                <div class="card dashboard-card">
                                    <div class="card-body p-4">
                                        <div class="card-icon gradient-warning text-white">
                                            <i class="bi bi-car-front-fill"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2">Data Kendaraan</h5>
                                        <p class="text-muted mb-0">Kendaraan yang sedang parkir</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-md-6 col-lg-4">
                            <a href="log_aktivitas.php" class="text-decoration-none">
                                <div class="card dashboard-card">
                                    <div class="card-body p-4">
                                        <div class="card-icon gradient-danger text-white">
                                            <i class="bi bi-clock-history"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2">Log Aktivitas</h5>
                                        <p class="text-muted mb-0">Riwayat aktivitas sistem</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                    </div>
                    <?php } else { ?>
                    <!-- Petugas/Owner View -->
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Informasi:</strong> Menu terbatas untuk role <?= ucfirst($role); ?>
                    </div>
                    <?php } ?>
                    
                </div>
                
            </div>
            
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>