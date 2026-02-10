<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include '../config/koneksi.php';

// Ambil ENUM jenis_kendaraan dari database
$enumValues = ['motor', 'mobil'];

$enum = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_tarif LIKE 'jenis_kendaraan'");
$enumRow = mysqli_fetch_assoc($enum);

if ($enumRow && preg_match("/^enum\((.*)\)$/", $enumRow['Type'], $matches)) {
    $enumValues = array_map(function($v){
        return trim($v, "'");
    }, explode(',', $matches[1]));
}

// TAMBAH DATA
if (isset($_POST['tambah'])) {
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $tarif = intval($_POST['tarif_per_jam']);
    mysqli_query($koneksi, "INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam) VALUES ('$jenis','$tarif')");
    header("Location: tarif.php");
    exit;
}

// UPDATE DATA
if (isset($_POST['update'])) {
    $id    = intval($_POST['id_tarif']);
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $tarif = intval($_POST['tarif_per_jam']);
    mysqli_query($koneksi, "UPDATE tb_tarif SET jenis_kendaraan='$jenis', tarif_per_jam='$tarif' WHERE id_tarif='$id'");
    header("Location: tarif.php");
    exit;
}

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // ✅ CEK APAKAH TARIF MASIH DIPAKAI
    $cek_transaksi = mysqli_query($koneksi, "
        SELECT COUNT(*) as jumlah 
        FROM tb_transaksi 
        WHERE id_tarif = '$id'
    ");
    
    $data_cek = mysqli_fetch_assoc($cek_transaksi);
    
    if ($data_cek['jumlah'] > 0) {
        // Tarif masih dipakai, tidak bisa dihapus
        echo "<script>
            alert('Tarif parkir tidak dapat dihapus!\\n\\nAlasan: Masih ada {$data_cek['jumlah']} transaksi yang menggunakan tarif ini.\\n\\nSaran: Gunakan fitur Edit untuk mengubah tarif atau buat tarif baru.');
            window.location='tarif.php';
        </script>";
        exit;
    }
    
    // ✅ HAPUS JIKA TIDAK ADA TRANSAKSI
    $hapus = mysqli_query($koneksi, "DELETE FROM tb_tarif WHERE id_tarif='$id'");
    
    if ($hapus) {
        echo "<script>
            alert('Tarif parkir berhasil dihapus!');
            window.location='tarif.php';
        </script>";
    } else {
        echo "<script>
            alert('Gagal menghapus tarif parkir!\\n\\nError: " . addslashes(mysqli_error($koneksi)) . "');
            window.location='tarif.php';
        </script>";
    }
    exit;
}

$data = mysqli_query($koneksi, "SELECT * FROM tb_tarif");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarif Parkir - Sistem Parkir</title>
    
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
        
        .vehicle-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .price-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
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
                    <i class="bi bi-cash-coin me-2"></i>Tarif Parkir
                </h2>
                <p class="page-subtitle mb-0">Kelola tarif parkir per jenis kendaraan</p>
            </div>
            <a href="index.php" class="btn btn-light btn-custom">
                <i class="bi bi-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <!-- Form Card -->
    <div class="form-card">
        <h5 class="fw-bold mb-4">
            <i class="bi bi-plus-circle me-2"></i>Tambah Tarif Baru
        </h5>
        
        <form method="post">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-car-front me-1"></i>Jenis Kendaraan
                    </label>
                    <select name="jenis_kendaraan" class="form-select" required>
                        <option value="">Pilih Jenis Kendaraan</option>
                        <?php foreach ($enumValues as $v): ?>
                            <option value="<?= $v ?>">
                                <?php
                                $icon = '';
                                switch(strtolower($v)) {
                                    case 'motor':
                                        $icon = '🏍️';
                                        break;
                                    case 'mobil':
                                        $icon = '🚗';
                                        break;
                                    default:
                                        $icon = '🚗';
                                }
                                echo $icon . ' ' . ucfirst($v);
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-cash-stack me-1"></i>Tarif per Jam (Rp)
                    </label>
                    <input type="number" name="tarif_per_jam" class="form-control" 
                           placeholder="Contoh: 5000" min="0" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label fw-semibold">&nbsp;</label>
                    <button type="submit" name="tambah" class="btn btn-primary btn-custom w-100">
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
                    <i class="bi bi-table me-2"></i>Daftar Tarif Parkir
                </h5>
                <span class="badge bg-light text-dark">
                    Total: <?= mysqli_num_rows($data) ?> Tarif
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Jenis Kendaraan</th>
                            <th class="text-center">Tarif per Jam</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $no = 1; 
                    while ($d = mysqli_fetch_assoc($data)) { 
                        $jenis = strtolower($d['jenis_kendaraan']);
                        $iconColor = '';
                        $icon = '';
                        
                        switch($jenis) {
                            case 'motor':
                                $iconColor = 'bg-info bg-opacity-10 text-info';
                                $icon = 'bi-bicycle';
                                break;
                            case 'mobil':
                                $iconColor = 'bg-primary bg-opacity-10 text-primary';
                                $icon = 'bi-car-front';
                                break;
                            default:
                                $iconColor = 'bg-secondary bg-opacity-10 text-secondary';
                                $icon = 'bi-car-front';
                        }
                        
                        // ✅ CEK APAKAH TARIF INI PERNAH DIPAKAI
                        $cek_usage = mysqli_query($koneksi, "
                            SELECT COUNT(*) as jumlah 
                            FROM tb_transaksi 
                            WHERE id_tarif = '{$d['id_tarif']}'
                        ");
                        $usage = mysqli_fetch_assoc($cek_usage);
                        $pernah_dipakai = $usage['jumlah'] > 0;
                    ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $no++; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="vehicle-icon <?= $iconColor ?>">
                                        <i class="bi <?= $icon ?>"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="fw-semibold"><?= ucfirst($d['jenis_kendaraan']); ?></div>
                                        <small class="text-muted">Kendaraan roda <?= $jenis == 'motor' ? 'dua' : 'empat' ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="price-badge">
                                    <i class="bi bi-currency-dollar me-1"></i>
                                    Rp <?= number_format($d['tarif_per_jam'], 0, ',', '.'); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-warning btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#edit<?= $d['id_tarif']; ?>"
                                            title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <?php if ($pernah_dipakai) { ?>
                                        <!-- Tarif sudah pernah dipakai, tidak bisa dihapus -->
                                        <button class="btn btn-secondary btn-sm" 
                                                disabled 
                                                title="Tarif ini sudah digunakan di <?= $usage['jumlah'] ?> transaksi dan tidak dapat dihapus">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    <?php } else { ?>
                                        <!-- Tarif belum pernah dipakai, bisa dihapus -->
                                        <a href="?hapus=<?= $d['id_tarif']; ?>" 
                                           onclick="return confirm('Yakin ingin menghapus tarif untuk <?= ucfirst($d['jenis_kendaraan']); ?>?')" 
                                           class="btn btn-danger btn-sm"
                                           title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL EDIT -->
                        <div class="modal fade" id="edit<?= $d['id_tarif']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="border-radius: 15px; border: none;">
                                    <form method="post">
                                        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                                            <h5 class="modal-title fw-bold">
                                                <i class="bi bi-pencil-square me-2"></i>Edit Tarif Parkir
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <input type="hidden" name="id_tarif" value="<?= $d['id_tarif']; ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-car-front me-1"></i>Jenis Kendaraan
                                                </label>
                                                <select name="jenis_kendaraan" class="form-select" required>
                                                    <?php foreach ($enumValues as $v): ?>
                                                        <option value="<?= $v ?>" <?= $v == $d['jenis_kendaraan'] ? 'selected' : '' ?>>
                                                            <?= ucfirst($v) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-cash-stack me-1"></i>Tarif per Jam (Rp)
                                                </label>
                                                <input type="number" name="tarif_per_jam" class="form-control" 
                                                       value="<?= $d['tarif_per_jam']; ?>" min="0" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer" style="border: none;">
                                            <button type="button" class="btn btn-secondary btn-custom" data-bs-dismiss="modal">
                                                <i class="bi bi-x-circle me-2"></i>Batal
                                            </button>
                                            <button type="submit" name="update" class="btn btn-primary btn-custom">
                                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    
                    <?php if (mysqli_num_rows($data) == 0) { ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-3 mb-0">Belum ada data tarif parkir</p>
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