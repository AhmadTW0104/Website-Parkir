<?php
session_start();
include '../config/koneksi.php';

/* ========== CEK LOGIN & ROLE ========== */
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?pesan=login_dulu");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    exit("Akses ditolak!");
}

/* ========== SINKRONISASI DATA AREA PARKIR ========== */
if (isset($_GET['sinkron'])) {
    mysqli_query($koneksi, "
        UPDATE tb_area_parkir ap
        SET terisi = (
            SELECT COUNT(*) 
            FROM tb_transaksi t
            WHERE t.id_area = ap.id_area 
            AND LOWER(t.status) = 'masuk'
        )
    ");
    
    echo "<script>
        alert('Data area parkir berhasil disinkronkan!');
        window.location='kendaraan.php';
    </script>";
    exit;
}

/* ========== TAMBAH KENDARAAN MANUAL (ADMIN) ========== */
if (isset($_POST['tambah'])) {

    $plat     = mysqli_real_escape_string($koneksi, $_POST['plat_nomor']);
    $jenis    = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $warna    = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $pemilik  = mysqli_real_escape_string($koneksi, $_POST['pemilik']);
    $id_area  = intval($_POST['id_area']);
    $id_user  = $_SESSION['id_user'];

    $cekPlat = mysqli_query($koneksi, "
        SELECT k.plat_nomor 
        FROM tb_transaksi t
        JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
        WHERE k.plat_nomor = '$plat' 
        AND LOWER(t.status) = 'masuk'
    ");

    if (mysqli_num_rows($cekPlat) > 0) {
        echo "<script>alert('Kendaraan dengan plat $plat masih sedang parkir!');</script>";
    } else {

        $area = mysqli_fetch_assoc(mysqli_query($koneksi, "
            SELECT kapasitas, terisi, nama_area 
            FROM tb_area_parkir 
            WHERE id_area = '$id_area'
        "));

        if ($area['terisi'] >= $area['kapasitas']) {
            echo "<script>alert('Area parkir {$area['nama_area']} sudah penuh!');</script>";
        } else {

            mysqli_begin_transaction($koneksi);

            try {

                mysqli_query($koneksi, "
                    INSERT INTO tb_kendaraan
                    (plat_nomor, jenis_kendaraan, warna, pemilik, id_user)
                    VALUES
                    ('$plat','$jenis','$warna','$pemilik','$id_user')
                ");

                $id_kendaraan = mysqli_insert_id($koneksi);

                $tarif = mysqli_fetch_assoc(mysqli_query($koneksi, "
                    SELECT id_tarif 
                    FROM tb_tarif 
                    WHERE LOWER(jenis_kendaraan) = LOWER('$jenis')
                    LIMIT 1
                "));

                $id_tarif = $tarif['id_tarif'] ?? null;

                mysqli_query($koneksi, "
                    INSERT INTO tb_transaksi
                    (id_kendaraan, waktu_masuk, status, id_user, id_area, id_tarif)
                    VALUES
                    ('$id_kendaraan', NOW(), 'masuk', '$id_user', '$id_area', '$id_tarif')
                ");

                mysqli_query($koneksi, "
                    UPDATE tb_area_parkir
                    SET terisi = terisi + 1
                    WHERE id_area = '$id_area'
                ");

                mysqli_commit($koneksi);

                echo "<script>
                    alert('Kendaraan berhasil ditambahkan!');
                    window.location='kendaraan.php';
                </script>";

            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                echo "<script>alert('Gagal menambah kendaraan: {$e->getMessage()}');</script>";
            }
        }
    }
}

/* ========== AMBIL DATA KENDARAAN YANG MASIH PARKIR ========== */
$data = mysqli_query($koneksi, "
    SELECT 
        k.plat_nomor,
        k.jenis_kendaraan,
        k.warna,
        k.pemilik,
        u.username,
        a.nama_area,
        t.waktu_masuk
    FROM tb_transaksi t
    JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
    JOIN tb_area_parkir a ON t.id_area = a.id_area
    JOIN tb_user u ON t.id_user = u.id_user
    WHERE LOWER(t.status) = 'masuk'
    ORDER BY t.waktu_masuk DESC
");

$area = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kendaraan - Sistem Parkir</title>
    
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
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
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
        
        .plat-nomor {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            display: inline-block;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .badge-vehicle {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .time-badge {
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #64748b;
        }
    </style>
</head>

<body>
<div class="container">
    
    <!-- Header Section -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="page-title">
                    <i class="bi bi-car-front-fill me-2"></i>Data Kendaraan Parkir
                </h2>
                <p class="page-subtitle mb-0">Kelola kendaraan yang sedang parkir</p>
            </div>
            <div class="d-flex gap-2">
                <button onclick="return confirm('Sinkronkan ulang data area parkir?') && (window.location='?sinkron=1')" 
                        class="btn btn-warning btn-custom">
                    <i class="bi bi-arrow-repeat me-2"></i>Sinkronkan
                </button>
                <a href="index.php" class="btn btn-light btn-custom">
                    <i class="bi bi-arrow-left me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <!-- Form Card -->
    <div class="form-card">
        <h5 class="fw-bold mb-4">
            <i class="bi bi-plus-circle me-2"></i>Tambah Kendaraan Parkir
        </h5>
        
        <form method="post">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-car-front me-1"></i>Plat Nomor
                    </label>
                    <input type="text" name="plat_nomor" class="form-control text-uppercase" 
                           placeholder="B 1234 CD" required style="font-weight: 600; letter-spacing: 1px;">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-tag me-1"></i>Jenis
                    </label>
                    <select name="jenis_kendaraan" class="form-select" required>
                        <option value="">Pilih</option>
                        <option value="Motor">🏍️ Motor</option>
                        <option value="Mobil">🚗 Mobil</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-palette me-1"></i>Warna
                    </label>
                    <input type="text" name="warna" class="form-control" 
                           placeholder="Hitam" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-person me-1"></i>Pemilik
                    </label>
                    <input type="text" name="pemilik" class="form-control" 
                           placeholder="Nama">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-building me-1"></i>Area Parkir
                    </label>
                    <select name="id_area" class="form-select" required>
                        <option value="">Pilih Area</option>
                        <?php 
                        mysqli_data_seek($area, 0);
                        while($a=mysqli_fetch_assoc($area)){ 
                            $sisa = $a['kapasitas'] - $a['terisi'];
                        ?>
                            <option value="<?= $a['id_area']; ?>">
                                <?= $a['nama_area']; ?> (Sisa: <?= $sisa ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-1">
                    <label class="form-label fw-semibold">&nbsp;</label>
                    <button type="submit" name="tambah" class="btn btn-primary btn-custom w-100">
                    <i class="bi bi-plus-lg"></i>
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
                    <i class="bi bi-list-check me-2"></i>Daftar Kendaraan Parkir Aktif
                </h5>
                <span class="badge bg-light text-dark">
                    Total: <?= mysqli_num_rows($data) ?> Unit
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Plat Nomor</th>
                            <th>Jenis</th>
                            <th>Warna</th>
                            <th>Pemilik</th>
                            <th>Area Parkir</th>
                            <th>Waktu Masuk</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php if(mysqli_num_rows($data) == 0){ ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0">Tidak ada kendaraan yang sedang parkir</p>
                        </td>
                    </tr>
                    <?php } ?>

                    <?php $no=1; while($k=mysqli_fetch_assoc($data)){ ?>
                    <tr>
                        <td class="text-center fw-bold"><?= $no++; ?></td>
                        <td>
                            <span class="plat-nomor"><?= strtoupper(htmlspecialchars($k['plat_nomor'])); ?></span>
                        </td>
                        <td>
                            <?php
                            $jenisClass = '';
                            $jenisIcon = '';
                            if(strtolower($k['jenis_kendaraan']) == 'motor') {
                                $jenisClass = 'bg-info';
                                $jenisIcon = 'bicycle';
                            } else {
                                $jenisClass = 'bg-primary';
                                $jenisIcon = 'car-front';
                            }
                            ?>
                            <span class="badge-vehicle <?= $jenisClass ?>">
                                <i class="bi bi-<?= $jenisIcon ?> me-1"></i>
                                <?= htmlspecialchars($k['jenis_kendaraan']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <i class="bi bi-palette me-1"></i>
                                <?= htmlspecialchars($k['warna']); ?>
                            </span>
                        </td>
                        <td>
                            <i class="bi bi-person-circle me-2 text-muted"></i>
                            <?= htmlspecialchars($k['pemilik'] ?: '-'); ?>
                        </td>
                        <td>
                            <span class="badge bg-dark bg-opacity-75">
                                <i class="bi bi-building me-1"></i>
                                <?= htmlspecialchars($k['nama_area']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="time-badge">
                                <i class="bi bi-clock me-1"></i>
                                <?= date('d/m/Y H:i', strtotime($k['waktu_masuk'])); ?>
                            </div>
                        </td>
                        <td>
                            <i class="bi bi-person-badge me-1 text-muted"></i>
                            <?= htmlspecialchars($k['username']); ?>
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