<?php
session_start();
include '../config/koneksi.php';

/* ============ CEK LOGIN ============ */
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?pesan=login_dulu");
    exit;
}

/* ============ SIMPAN DATA ============ */
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_area']);
    $kap  = intval($_POST['kapasitas']);
    $terisi = 0;

    mysqli_query($koneksi,
        "INSERT INTO tb_area_parkir (nama_area, kapasitas, terisi)
         VALUES ('$nama', '$kap', '$terisi')"
    );

    header("location:area_parkir.php");
    exit;
}

/* ============ HAPUS DATA ============ */
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // ✅ CEK APAKAH AREA MASIH DIPAKAI
    $cek_transaksi = mysqli_query($koneksi, "
        SELECT COUNT(*) as jumlah 
        FROM tb_transaksi 
        WHERE id_area = '$id'
    ");
    
    $data_cek = mysqli_fetch_assoc($cek_transaksi);
    
    if ($data_cek['jumlah'] > 0) {
        // Area masih dipakai, tidak bisa dihapus
        echo "<script>
            alert('Area parkir tidak dapat dihapus!\\n\\nAlasan: Masih ada {$data_cek['jumlah']} transaksi yang menggunakan area ini.\\n\\nSaran: Ubah kapasitas menjadi 0 untuk menonaktifkan area.');
            window.location='area_parkir.php';
        </script>";
        exit;
    }
    
    // ✅ HAPUS JIKA TIDAK ADA TRANSAKSI
    $hapus = mysqli_query($koneksi, "DELETE FROM tb_area_parkir WHERE id_area='$id'");
    
    if ($hapus) {
        echo "<script>
            alert('Area parkir berhasil dihapus!');
            window.location='area_parkir.php';
        </script>";
    } else {
        echo "<script>
            alert('Gagal menghapus area parkir!\\n\\nError: " . addslashes(mysqli_error($koneksi)) . "');
            window.location='area_parkir.php';
        </script>";
    }
    exit;
}

/* ============ AMBIL DATA ============ */
$data = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir ORDER BY id_area ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Area Parkir - Sistem Parkir</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 0;
        }
        
        .main-card {
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: none;
            overflow: hidden;
            background: white;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            border: none;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .btn-custom {
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table thead th {
            border: none;
            padding: 15px;
            font-weight: 600;
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .badge-slot {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 10px 15px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .page-title {
            color: white;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .page-subtitle {
            color: rgba(255,255,255,0.8);
            font-size: 0.95rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .progress-custom {
            height: 25px;
            border-radius: 10px;
            background: #e2e8f0;
        }
        
        .progress-bar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            font-weight: 600;
        }
    </style>
</head>

<body>

<div class="container">
    
    <!-- Header Section -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="page-title">
                    <i class="bi bi-building me-2"></i>Area Parkir
                </h2>
                <p class="page-subtitle mb-0">Kelola area dan kapasitas tempat parkir</p>
            </div>
            <a href="index.php" class="btn btn-light btn-custom">
                <i class="bi bi-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <?php
        $totalArea = mysqli_num_rows($data);
        $totalKapasitas = 0;
        $totalTerisi = 0;
        mysqli_data_seek($data, 0);
        while($stat = mysqli_fetch_assoc($data)) {
            $totalKapasitas += $stat['kapasitas'];
            $totalTerisi += $stat['terisi'];
        }
        $totalKosong = $totalKapasitas - $totalTerisi;
        $persentaseTerisi = $totalKapasitas > 0 ? ($totalTerisi / $totalKapasitas * 100) : 0;
        mysqli_data_seek($data, 0);
        ?>
        
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= $totalArea ?></div>
                <div class="text-muted fw-semibold">Total Area</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= $totalKapasitas ?></div>
                <div class="text-muted fw-semibold">Total Kapasitas</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-danger"><?= $totalTerisi ?></div>
                <div class="text-muted fw-semibold">Sedang Terisi</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number text-success"><?= $totalKosong ?></div>
                <div class="text-muted fw-semibold">Slot Kosong</div>
            </div>
        </div>
    </div>
    
    <!-- Form Card -->
    <div class="form-card">
        <h5 class="fw-bold mb-4">
            <i class="bi bi-plus-circle me-2"></i>Tambah Area Parkir Baru
        </h5>
        
        <form method="post">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-geo-alt me-1"></i>Nama Area Parkir
                    </label>
                    <input type="text" name="nama_area" class="form-control" 
                           placeholder="Contoh: Area A, Zona Utara, Lantai 1" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-bar-chart me-1"></i>Kapasitas (Jumlah Slot)
                    </label>
                    <input type="number" name="kapasitas" class="form-control" 
                           placeholder="Contoh: 50" min="1" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-semibold">&nbsp;</label>
                    <button type="submit" name="simpan" class="btn btn-primary btn-custom w-100">
                        <i class="bi bi-save me-2"></i>Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Table Card -->
    <div class="main-card">
        <div class="card-header-custom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-table me-2"></i>Data Area Parkir
                </h5>
                <span class="badge bg-light text-dark">
                    Total: <?= $totalArea ?> Area
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Nama Area</th>
                            <th class="text-center">Kapasitas</th>
                            <th class="text-center">Terisi</th>
                            <th>Status Ketersediaan</th>
                            <th width="100" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    mysqli_data_seek($data, 0);
                    while ($a = mysqli_fetch_assoc($data)) {
                        $sisa = $a['kapasitas'] - $a['terisi'];
                        $persen = $a['kapasitas'] > 0 ? ($a['terisi'] / $a['kapasitas'] * 100) : 0;
                        
                        if ($persen >= 90) {
                            $statusClass = 'bg-danger';
                            $statusText = 'Penuh / Hampir Penuh';
                        } elseif ($persen >= 70) {
                            $statusClass = 'bg-warning';
                            $statusText = 'Tersedia Terbatas';
                        } else {
                            $statusClass = 'bg-success';
                            $statusText = 'Tersedia Banyak';
                        }
                        
                        // ✅ CEK APAKAH AREA INI PERNAH DIPAKAI
                        $cek_usage = mysqli_query($koneksi, "
                            SELECT COUNT(*) as jumlah 
                            FROM tb_transaksi 
                            WHERE id_area = '{$a['id_area']}'
                        ");
                        $usage = mysqli_fetch_assoc($cek_usage);
                        $pernah_dipakai = $usage['jumlah'] > 0;
                    ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $no++; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center me-3" 
                                         style="width: 45px; height: 45px;">
                                        <i class="bi bi-building" style="font-size: 1.3rem;"></i>
                                    </div>
                                    <span class="fw-semibold"><?= htmlspecialchars($a['nama_area']); ?></span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info badge-slot">
                                    <i class="bi bi-grid-3x3 me-1"></i><?= $a['kapasitas']; ?> Slot
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-dark badge-slot">
                                    <i class="bi bi-car-front me-1"></i><?= $a['terisi']; ?> Unit
                                </span>
                            </td>
                            <td>
                                <div class="mb-2">
                                    <?php if ($sisa <= 0) { ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>PENUH
                                        </span>
                                    <?php } else { ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Tersedia: <?= $sisa ?> Slot
                                        </span>
                                    <?php } ?>
                                </div>
                                <div class="progress progress-custom">
                                    <div class="progress-bar progress-bar-custom <?= $statusClass ?>" 
                                         role="progressbar" 
                                         style="width: <?= $persen ?>%"
                                         aria-valuenow="<?= $persen ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?= number_format($persen, 0) ?>%
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    <i class="bi bi-info-circle me-1"></i><?= $statusText ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <?php if ($pernah_dipakai) { ?>
                                    <!-- Area pernah dipakai, tidak bisa dihapus -->
                                    <button class="btn btn-secondary btn-sm" disabled title="Area ini sudah pernah dipakai dan tidak dapat dihapus">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                <?php } else { ?>
                                    <!-- Area belum pernah dipakai, bisa dihapus -->
                                    <a href="?hapus=<?= $a['id_area']; ?>"
                                       onclick="return confirm('Yakin ingin menghapus area parkir <?= htmlspecialchars($a['nama_area']); ?>?')"
                                       class="btn btn-danger btn-sm"
                                       title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    
                    <?php if (mysqli_num_rows($data) == 0) { ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-3 mb-0">Belum ada data area parkir</p>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>