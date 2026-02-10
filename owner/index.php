<?php
session_start();
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?pesan=login_dulu");
    exit;
}

if ($_SESSION['role'] != 'owner') {
    echo "Akses ditolak!";
    exit;
}
include '../config/Koneksi.php';

/* CEK LOGIN */
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

$username = $_SESSION['username'];
$id_user = $_SESSION['id_user'];

// Ambil data statistik
include '../config/koneksi.php';

// Ambil nama lengkap user
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT nama_lengkap FROM tb_user WHERE id_user = '$id_user'
"));
$nama_lengkap = $user['nama_lengkap'] ?? $username;

// Total pendapatan
$pendapatan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(biaya_total) as total 
    FROM tb_transaksi 
    WHERE LOWER(status) = 'keluar'
"));

// Pendapatan hari ini
$hariIni = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(biaya_total) as total 
    FROM tb_transaksi 
    WHERE LOWER(status) = 'keluar' AND DATE(waktu_keluar) = CURDATE()
"));

// Total transaksi
$totalTransaksi = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) as total FROM tb_transaksi
"));

// Kendaraan aktif parkir
$kendaraanAktif = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) as total 
    FROM tb_transaksi 
    WHERE LOWER(status) = 'masuk'
"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner - Sistem Parkir</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Dashboard CSS -->
    <link rel="stylesheet" href="../admin/dashboard.css">
    
    <style>
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .stats-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        .stats-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }
    </style>
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
                        
                        <a href="rekap_transaksi.php" class="nav-link">
                            <i class="bi bi-graph-up"></i>
                            <span>Rekap Transaksi</span>
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
                        <h4 class="mb-0 fw-bold">Dashboard Owner</h4>
                        <p class="text-muted mb-0 small">Monitoring dan laporan sistem parkir</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end d-none d-md-block">
                            <div class="fw-semibold"><?= htmlspecialchars($nama_lengkap); ?></div>
                            <span class="badge-role badge bg-warning text-dark">
                                <i class="bi bi-star-fill me-1"></i>Owner
                            </span>
                        </div>
                        <div class="user-avatar" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
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
                                <h2 class="fw-bold mb-2">
                                    <i class="bi bi-emoji-smile me-2"></i>Selamat Datang, <?= htmlspecialchars($nama_lengkap); ?>!
                                </h2>
                                <p class="mb-0 opacity-75">Dashboard Owner - Pantau performa bisnis parkir Anda secara real-time</p>
                            </div>
                            <div class="col-md-4 text-end d-none d-md-block">
                                <i class="bi bi-graph-up-arrow" style="font-size: 5rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row g-4 mb-4">
                        
                        <div class="col-md-6 col-lg-3">
                            <div class="stats-card">
                                <div class="stats-icon gradient-success text-white">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                                <div class="stats-value">Rp <?= number_format($pendapatan['total'] ?? 0, 0, ',', '.'); ?></div>
                                <div class="stats-label">
                                    <i class="bi bi-arrow-up-circle me-1"></i>Total Pendapatan
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3">
                            <div class="stats-card">
                                <div class="stats-icon gradient-primary text-white">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                                <div class="stats-value">Rp <?= number_format($hariIni['total'] ?? 0, 0, ',', '.'); ?></div>
                                <div class="stats-label">
                                    <i class="bi bi-calendar-check me-1"></i>Pendapatan Hari Ini
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3">
                            <div class="stats-card">
                                <div class="stats-icon gradient-info text-white">
                                    <i class="bi bi-clipboard-data"></i>
                                </div>
                                <div class="stats-value"><?= number_format($totalTransaksi['total'] ?? 0); ?></div>
                                <div class="stats-label">
                                    <i class="bi bi-list-check me-1"></i>Total Transaksi
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3">
                            <div class="stats-card">
                                <div class="stats-icon gradient-warning text-white">
                                    <i class="bi bi-car-front-fill"></i>
                                </div>
                                <div class="stats-value"><?= number_format($kendaraanAktif['total'] ?? 0); ?></div>
                                <div class="stats-label">
                                    <i class="bi bi-circle-fill me-1 text-success"></i>Kendaraan Parkir Aktif
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- Menu Cards -->
                    <div class="row g-4">
                        
                        <div class="col-md-6">
                            <a href="rekap_transaksi.php" class="text-decoration-none">
                                <div class="card dashboard-card">
                                    <div class="card-body p-4">
                                        <div class="card-icon gradient-primary text-white">
                                            <i class="bi bi-graph-up"></i>
                                        </div>
                                        <h5 class="fw-bold mb-2">Rekap Transaksi</h5>
                                        <p class="text-muted mb-0">Lihat laporan dan rekap transaksi parkir berdasarkan periode tertentu</p>
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