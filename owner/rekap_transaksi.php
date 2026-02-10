<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?pesan=login_dulu");
    exit;
}

if ($_SESSION['role'] != 'owner') {
    echo "Akses ditolak!";
    exit;
}

$username = $_SESSION['username'];
$dari   = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Transaksi - Sistem Parkir</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 30px 0; }
        .main-card { border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); border: none; background: white; }
        .card-header-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px 30px; border-radius: 20px 20px 0 0; }
        .filter-card { background: white; border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .btn-custom { padding: 10px 25px; border-radius: 10px; font-weight: 500; transition: all 0.3s ease; }
        .btn-custom:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .table thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .table thead th { border: none; padding: 15px; font-weight: 600; }
        .table tbody td { padding: 15px; vertical-align: middle; }
        .form-control, .form-select { border-radius: 10px; border: 2px solid #e2e8f0; padding: 10px 15px; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
        .plat-nomor { background: #1e293b; color: white; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-family: 'Courier New', monospace; letter-spacing: 1px; display: inline-block; }
        .badge-status { padding: 8px 15px; border-radius: 20px; font-weight: 500; font-size: 0.85rem; }
        .ringkasan-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        
        @media print {
            @page { size: A4 landscape; margin: 1cm; }
            body { background: white !important; padding: 0; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .container { max-width: 100% !important; width: 100% !important; }
            .no-print { display: none !important; }
            .main-card { box-shadow: none; }
            * { color: #000 !important; font-weight: 600 !important; }
            
            .table thead { background: #1e293b !important; display: table-header-group !important; -webkit-print-color-adjust: exact !important; }
            .table thead th { background: #1e293b !important; color: #fff !important; font-weight: 700 !important; border: 2px solid #000 !important; padding: 8px 4px !important; font-size: 9pt !important; }
            .table tbody { display: table-row-group !important; }
            .table tbody td { border: 1px solid #000 !important; padding: 6px 4px !important; font-size: 8pt !important; }
            .table tbody tr { display: table-row !important; }
            
            .plat-nomor { background: #000 !important; color: #fff !important; font-weight: 900 !important; border: 2px solid #000 !important; padding: 4px 6px !important; font-size: 8pt !important; -webkit-print-color-adjust: exact !important; }
            .badge, .badge-status { border: 1.5px solid #000 !important; font-weight: 700 !important; padding: 3px 6px !important; font-size: 7pt !important; -webkit-print-color-adjust: exact !important; }
            
            .bg-info { background: #0284c7 !important; color: #fff !important; }
            .bg-primary { background: #1d4ed8 !important; color: #fff !important; }
            .bg-success { background: #059669 !important; color: #fff !important; }
            .bg-warning { background: #f59e0b !important; color: #000 !important; }
            .bg-dark { background: #000 !important; color: #fff !important; }
            
            .card-header-custom { background: #1e293b !important; color: #fff !important; padding: 10px !important; -webkit-print-color-adjust: exact !important; }
            .card-header-custom * { color: #fff !important; font-size: 10pt !important; }
            
            .ringkasan-card { border: 2px solid #000 !important; padding: 15px !important; margin-top: 15px !important; page-break-inside: avoid !important; }
            .ringkasan-card h5 { font-size: 11pt !important; font-weight: 900 !important; border-bottom: 2px solid #000 !important; padding-bottom: 6px !important; }
            .ringkasan-card h6 { font-size: 10pt !important; font-weight: 900 !important; }
            .ringkasan-card table td { border: none !important; padding: 4px 8px !important; font-size: 9pt !important; }
            .ringkasan-card hr { border-top: 2px solid #000 !important; }
            
            .text-success { color: #047857 !important; font-weight: 800 !important; }
            .text-muted { color: #374151 !important; }
            small { font-size: 7pt !important; }
            strong { font-weight: 900 !important; }
            .table { border-collapse: collapse !important; width: 100% !important; font-size: 8pt !important; }
            .bi { font-size: 0.85em !important; }
        }
    </style>
</head>

<body>

<div class="container">
    
    <!-- Header -->
    <div class="mb-4 no-print">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 style="color: white; font-weight: 700;">
                    <i class="bi bi-graph-up me-2"></i>Rekap Transaksi Parkir
                </h2>
                <p style="color: rgba(255,255,255,0.8);" class="mb-0">Laporan transaksi dan pendapatan parkir</p>
            </div>
            <a href="index.php" class="btn btn-light btn-custom">
                <i class="bi bi-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <!-- Filter -->
    <div class="filter-card no-print">
        <h5 class="fw-bold mb-4"><i class="bi bi-funnel me-2"></i>Filter Periode</h5>
        <form method="GET">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold"><i class="bi bi-calendar-event me-1"></i>Dari Tanggal</label>
                    <input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($dari) ?>" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold"><i class="bi bi-calendar-check me-1"></i>Sampai Tanggal</label>
                    <input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($sampai) ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-custom w-100">
                        <i class="bi bi-search me-2"></i>Tampilkan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if($dari && $sampai){ ?>
        
        <?php
        $q = mysqli_query($koneksi,"
            SELECT p.*, k.plat_nomor, k.jenis_kendaraan, a.nama_area
            FROM tb_transaksi p
            JOIN tb_kendaraan k ON p.id_kendaraan = k.id_kendaraan
            JOIN tb_area_parkir a ON p.id_area = a.id_area
            WHERE DATE(p.waktu_masuk) BETWEEN '$dari' AND '$sampai'
            ORDER BY p.waktu_masuk DESC
        ");

        $tot = mysqli_fetch_assoc(mysqli_query($koneksi,"
            SELECT SUM(biaya_total) as total FROM tb_transaksi
            WHERE DATE(waktu_masuk) BETWEEN '$dari' AND '$sampai'
        "));
        
        $jumlahTransaksi = mysqli_num_rows($q);
        
        // Ringkasan
        $selesai = mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) as jml FROM tb_transaksi WHERE DATE(waktu_masuk) BETWEEN '$dari' AND '$sampai' AND waktu_keluar IS NOT NULL"))['jml'];
        $parkir = mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) as jml FROM tb_transaksi WHERE DATE(waktu_masuk) BETWEEN '$dari' AND '$sampai' AND waktu_keluar IS NULL"))['jml'];
        $motor = mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) as jml FROM tb_transaksi p JOIN tb_kendaraan k ON p.id_kendaraan = k.id_kendaraan WHERE DATE(p.waktu_masuk) BETWEEN '$dari' AND '$sampai' AND LOWER(k.jenis_kendaraan) = 'motor'"))['jml'];
        $mobil = mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) as jml FROM tb_transaksi p JOIN tb_kendaraan k ON p.id_kendaraan = k.id_kendaraan WHERE DATE(p.waktu_masuk) BETWEEN '$dari' AND '$sampai' AND LOWER(k.jenis_kendaraan) = 'mobil'"))['jml'];
        ?>
        
        <!-- Info Period -->
        <div class="alert alert-info d-flex align-items-center mb-4 no-print">
            <i class="bi bi-info-circle-fill me-3" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Periode:</strong> <?= date('d/m/Y', strtotime($dari)) ?> - <?= date('d/m/Y', strtotime($sampai)) ?>
                <span class="ms-3">
                    <i class="bi bi-file-earmark-text me-1"></i>
                    <strong><?= $jumlahTransaksi ?></strong> Transaksi
                </span>
            </div>
        </div>
        
        <!-- Tabel -->
        <div class="main-card">
            <div class="card-header-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-table me-2"></i>Detail Transaksi</h5>
                    <button onclick="window.print()" class="btn btn-light btn-sm no-print">
                        <i class="bi bi-printer me-2"></i>Cetak
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                
                <?php if($jumlahTransaksi == 0){ ?>
                    <div style="text-align: center; padding: 60px 20px; color: #64748b;">
                        <i class="bi bi-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                        <h5>Tidak Ada Transaksi</h5>
                        <p>Tidak ada transaksi pada periode yang dipilih</p>
                    </div>
                <?php } else { ?>
                
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="50" class="text-center">No</th>
                                <th>Plat Nomor</th>
                                <th>Jenis</th>
                                <th>Area</th>
                                <th>Waktu Masuk</th>
                                <th>Waktu Keluar</th>
                                <th class="text-center">Durasi</th>
                                <th class="text-end">Biaya</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $no = 1; 
                        while($d = mysqli_fetch_assoc($q)){ 
                        ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td><span class="plat-nomor"><?= strtoupper(htmlspecialchars($d['plat_nomor'])) ?></span></td>
                                <td>
                                    <span class="badge <?= strtolower($d['jenis_kendaraan']) == 'motor' ? 'bg-info' : 'bg-primary' ?>">
                                        <i class="bi bi-<?= strtolower($d['jenis_kendaraan']) == 'motor' ? 'bicycle' : 'car-front' ?> me-1"></i>
                                        <?= htmlspecialchars($d['jenis_kendaraan']) ?>
                                    </span>
                                </td>
                                <td><i class="bi bi-building me-2 text-muted"></i><?= htmlspecialchars($d['nama_area']) ?></td>
                                <td><small class="text-muted"><i class="bi bi-box-arrow-in-right me-1"></i><?= date('d/m/Y H:i', strtotime($d['waktu_masuk'])) ?></small></td>
                                <td>
                                    <?php if($d['waktu_keluar']){ ?>
                                        <small class="text-muted"><i class="bi bi-box-arrow-right me-1"></i><?= date('d/m/Y H:i', strtotime($d['waktu_keluar'])) ?></small>
                                    <?php } else { ?>
                                        <span class="text-muted">-</span>
                                    <?php } ?>
                                </td>
                                <td class="text-center"><span class="badge bg-dark"><i class="bi bi-clock me-1"></i><?= $d['durasi_jam'] ?> jam</span></td>
                                <td class="text-end"><strong class="text-success">Rp <?= number_format($d['biaya_total'], 0, ',', '.') ?></strong></td>
                                <td class="text-center">
                                    <span class="badge-status <?= strtolower($d['status']) == 'keluar' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                        <i class="bi bi-<?= strtolower($d['status']) == 'keluar' ? 'check-circle' : 'hourglass-split' ?> me-1"></i>
                                        <?= ucfirst($d['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <?php } ?>
                
            </div>
        </div>
        
        <!-- Ringkasan -->
        <?php if($jumlahTransaksi > 0){ ?>
        <div class="ringkasan-card mt-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-clipboard-data me-2"></i>RINGKASAN TRANSAKSI</h5>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="fw-semibold">Total Transaksi:</td>
                            <td class="text-end fw-bold"><?= $jumlahTransaksi ?> transaksi</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Transaksi Selesai:</td>
                            <td class="text-end fw-bold"><?= $selesai ?> transaksi</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Kendaraan Masih Parkir:</td>
                            <td class="text-end fw-bold"><?= $parkir ?> kendaraan</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="fw-semibold">Jumlah Motor:</td>
                            <td class="text-end fw-bold"><?= $motor ?> unit</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Jumlah Mobil:</td>
                            <td class="text-end fw-bold"><?= $mobil ?> unit</td>
                        </tr>
                    </table>
                </div>
            </div>
            <hr class="my-3">
            <div class="row">
                <div class="col-md-6"><h6 class="fw-bold">TOTAL PENDAPATAN:</h6></div>
                <div class="col-md-6 text-end"><h5 class="fw-bold text-success mb-0">Rp <?= number_format($tot['total'] ?? 0, 0, ',', '.') ?></h5></div>
            </div>
        </div>
        <?php } ?>
        
    <?php } else { ?>
        
        <div class="main-card">
            <div class="card-body">
                <div style="text-align: center; padding: 60px 20px; color: #64748b;">
                    <i class="bi bi-calendar-range" style="font-size: 4rem; opacity: 0.3;"></i>
                    <h5>Pilih Periode Laporan</h5>
                    <p>Silakan pilih tanggal mulai dan akhir untuk menampilkan rekap transaksi</p>
                </div>
            </div>
        </div>
        
    <?php } ?>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>