<?php
session_start();
include '../config/koneksi.php';

/* ========== CEK LOGIN & ROLE PETUGAS ========== */
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?pesan=login_dulu");
    exit;
}

if ($_SESSION['role'] != 'petugas') {
    exit("Akses ditolak!");
}

/* ========== AMBIL DATA RIWAYAT PARKIR ========== */
$data = mysqli_query($koneksi, "
    SELECT 
        k.plat_nomor,
        k.jenis_kendaraan,
        k.warna,
        a.nama_area,
        t.waktu_masuk,
        t.waktu_keluar,
        t.biaya_total,
        t.durasi_jam,
        u.username
    FROM tb_transaksi t
    JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
    JOIN tb_area_parkir a ON t.id_area = a.id_area
    JOIN tb_user u ON t.id_user = u.id_user
    WHERE LOWER(t.status) = 'keluar'
    ORDER BY t.waktu_keluar DESC
    LIMIT 100
");

// Hitung statistik
$total_query = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(biaya_total), 0) as total 
    FROM tb_transaksi 
    WHERE LOWER(status) = 'keluar' 
    AND DATE(waktu_keluar) = CURDATE()
");
$total_pendapatan = mysqli_fetch_assoc($total_query)['total'] ?? 0;

$count_query = mysqli_query($koneksi, "
    SELECT COUNT(*) as total 
    FROM tb_transaksi 
    WHERE LOWER(status) = 'keluar' 
    AND DATE(waktu_keluar) = CURDATE()
");
$total_transaksi = mysqli_fetch_assoc($count_query)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Parkir - Sistem Parkir</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Dashboard CSS -->
    <link rel="stylesheet" href="dashboard_petugas.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            padding: 30px 0;
        }
        
        .stats-row {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            flex: 1;
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            font-family: 'Courier New', monospace;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<div class="container" style="max-width: 1400px;">
    
    <!-- Header Section -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="page-title">
                    <i class="bi bi-clock-history me-2"></i>Riwayat Parkir
                </h2>
                <p class="page-subtitle mb-3">Data kendaraan yang telah keluar dari area parkir</p>
            </div>
            <a href="dashboard_petugas.php" class="btn btn-light btn-custom">
                <i class="bi bi-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-icon gradient-info text-white">
                <i class="bi bi-receipt"></i>
            </div>
            <div class="stat-label">
                <i class="bi bi-calendar-check me-1"></i>Transaksi Hari Ini
            </div>
            <div class="stat-value"><?= number_format($total_transaksi) ?></div>
        </div>
        
        <div class="stat-box">
            <div class="stat-icon gradient-success text-white">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="stat-label">
                <i class="bi bi-currency-dollar me-1"></i>Pendapatan Hari Ini
            </div>
            <div class="stat-value">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
        </div>
    </div>
    
    <!-- Table Card -->
    <div class="main-card">
        <div class="card-header-custom">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-list-check me-2"></i>Daftar Riwayat Parkir (100 Terakhir)
            </h5>
        </div>
        
        <div class="table-responsive">
            <?php if ($data && mysqli_num_rows($data) > 0) { ?>
            <table class="table table-hover mb-0">
                <thead style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #1e293b;">
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th>Plat Nomor</th>
                        <th>Jenis</th>
                        <th>Warna</th>
                        <th>Area</th>
                        <th>Waktu Masuk</th>
                        <th>Waktu Keluar</th>
                        <th class="text-center">Durasi</th>
                        <th class="text-end">Total Biaya</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $no = 1;
                while ($r = mysqli_fetch_assoc($data)) {
                    $jenisClass = strtolower($r['jenis_kendaraan']) == 'motor' ? 'bg-info' : 'bg-primary';
                    $jenisIcon = strtolower($r['jenis_kendaraan']) == 'motor' ? 'bicycle' : 'car-front';
                ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $no++; ?></td>
                        <td>
                            <span class="plat-nomor"><?= strtoupper(htmlspecialchars($r['plat_nomor'])); ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $jenisClass ?>">
                                <i class="bi bi-<?= $jenisIcon ?> me-1"></i>
                                <?= htmlspecialchars($r['jenis_kendaraan']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <i class="bi bi-palette me-1"></i>
                                <?= htmlspecialchars($r['warna']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success">
                                <i class="bi bi-building me-1"></i>
                                <?= htmlspecialchars($r['nama_area']); ?>
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">
                                <i class="bi bi-box-arrow-in-down me-1"></i>
                                <?= date('d/m/Y H:i', strtotime($r['waktu_masuk'])); ?>
                            </small>
                        </td>
                        <td>
                            <small class="text-muted">
                                <i class="bi bi-box-arrow-up me-1"></i>
                                <?= date('d/m/Y H:i', strtotime($r['waktu_keluar'])); ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-dark">
                                <i class="bi bi-clock me-1"></i>
                                <?= $r['durasi_jam']; ?> jam
                            </span>
                        </td>
                        <td class="text-end">
                            <strong class="text-success" style="font-family: 'Courier New', monospace;">
                                Rp <?= number_format($r['biaya_total'], 0, ',', '.'); ?>
                            </strong>
                        </td>
                        <td>
                            <small class="text-muted">
                                <i class="bi bi-person-badge me-1"></i>
                                <?= htmlspecialchars($r['username']); ?>
                            </small>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php } else { ?>
            <div class="empty-state" style="text-align: center; padding: 80px 20px;">
                <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3; color: #64748b;"></i>
                <h4 class="mt-3 fw-bold">Belum Ada Riwayat</h4>
                <p class="text-muted">Belum ada transaksi parkir yang tercatat dalam sistem</p>
            </div>
            <?php } ?>
        </div>
    </div>
    
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>