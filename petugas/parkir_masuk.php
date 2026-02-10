<?php
session_start();
include '../config/koneksi.php';

/* ================= CEK LOGIN & ROLE ================= */
if (!isset($_SESSION['status']) || $_SESSION['status'] != 'login') {
    header("Location: ../index.php?pesan=login_dulu");
    exit;
}

if ($_SESSION['role'] != 'petugas') {
    exit("Akses ditolak!");
}

/* ================= PROSES SIMPAN ================= */
if (isset($_POST['simpan'])) {

    $plat     = mysqli_real_escape_string($koneksi, $_POST['plat_nomor']);
    $jenis    = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $warna    = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $pemilik  = mysqli_real_escape_string($koneksi, $_POST['pemilik']);
    $id_area  = intval($_POST['id_area']);
    $id_user  = $_SESSION['id_user'];

    // 1️⃣ CEK APAKAH KENDARAAN SEDANG PARKIR
    $cek_parkir = mysqli_query($koneksi, "
        SELECT k.plat_nomor 
        FROM tb_transaksi t
        JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
        WHERE k.plat_nomor = '$plat' 
        AND LOWER(t.status) = 'masuk'
    ");

    if (mysqli_num_rows($cek_parkir) > 0) {
        echo "<script>alert('Kendaraan dengan plat $plat masih sedang parkir!');</script>";
    } else {

        // 2️⃣ CEK KAPASITAS AREA
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

                // 3️⃣ CEK APAKAH PLAT NOMOR SUDAH ADA DI DATABASE
                $cek_kendaraan = mysqli_query($koneksi, "
                    SELECT id_kendaraan FROM tb_kendaraan 
                    WHERE plat_nomor = '$plat'
                ");

                if (mysqli_num_rows($cek_kendaraan) > 0) {
                    // Plat sudah ada, gunakan data yang sudah ada
                    $kendaraan_data = mysqli_fetch_assoc($cek_kendaraan);
                    $id_kendaraan = $kendaraan_data['id_kendaraan'];
                    
                    // Update data kendaraan (jenis, warna, pemilik mungkin berubah)
                    mysqli_query($koneksi, "
                        UPDATE tb_kendaraan SET
                            jenis_kendaraan = '$jenis',
                            warna = '$warna',
                            pemilik = '$pemilik',
                            id_user = '$id_user'
                        WHERE id_kendaraan = '$id_kendaraan'
                    ");
                } else {
                    // Plat baru, insert kendaraan baru
                    mysqli_query($koneksi, "
                        INSERT INTO tb_kendaraan
                        (plat_nomor, jenis_kendaraan, warna, pemilik, id_user)
                        VALUES
                        ('$plat', '$jenis', '$warna', '$pemilik', '$id_user')
                    ");
                    $id_kendaraan = mysqli_insert_id($koneksi);
                }

                // 4️⃣ AMBIL ID TARIF SESUAI JENIS
                $qTarif = mysqli_query($koneksi, "
                    SELECT id_tarif 
                    FROM tb_tarif 
                    WHERE LOWER(jenis_kendaraan) = LOWER('$jenis')
                    LIMIT 1
                ");
                $tarif = mysqli_fetch_assoc($qTarif);
                $id_tarif = $tarif['id_tarif'] ?? null;

                // 5️⃣ SIMPAN TRANSAKSI PARKIR MASUK
                mysqli_query($koneksi, "
                    INSERT INTO tb_transaksi
                    (id_kendaraan, waktu_masuk, status, id_tarif, id_user, id_area)
                    VALUES
                    ('$id_kendaraan', NOW(), 'MASUK', '$id_tarif', '$id_user', '$id_area')
                ");

                // 6️⃣ UPDATE AREA PARKIR
                mysqli_query($koneksi, "
                    UPDATE tb_area_parkir
                    SET terisi = terisi + 1
                    WHERE id_area = '$id_area'
                ");

                mysqli_commit($koneksi);

                echo "<script>
                    alert('Kendaraan berhasil parkir masuk!');
                    window.location='dashboard_petugas.php';
                </script>";

            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                echo "<script>alert('Gagal parkir masuk: {$e->getMessage()}');</script>";
            }
        }
    }
}

// Ambil data area parkir
$area = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parkir Masuk - Sistem Parkir</title>
    
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
            padding: 30px 0;
        }
        
        .form-card {
            max-width: 700px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Header Section -->
    <div class="mb-4 text-center">
        <h2 class="page-title">
            <i class="bi bi-box-arrow-in-down me-2"></i>Parkir Masuk
        </h2>
        <p class="page-subtitle mb-3">Input data kendaraan yang masuk area parkir</p>
        <a href="dashboard_petugas.php" class="btn btn-light btn-custom">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
    </div>
    
    <!-- Form Card -->
    <div class="form-card">
        
        <div class="form-header">
            <div class="form-icon mx-auto" style="background: rgba(255,255,255,0.2);">
                <i class="bi bi-car-front-fill"></i>
            </div>
            <h4 class="mb-1 fw-bold">Form Kendaraan Masuk</h4>
            <p class="mb-0 small opacity-75">Lengkapi data kendaraan dengan benar</p>
        </div>

        <form method="POST">
            
            <div class="mb-4">
                <label>
                    <i class="bi bi-123"></i>
                    Plat Nomor
                </label>
                <input type="text" 
                       name="plat_nomor" 
                       class="form-control text-uppercase" 
                       placeholder="Contoh: B 1234 XYZ" 
                       style="font-weight: 600; letter-spacing: 1px;"
                       required>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>Masukkan nomor plat kendaraan
                </small>
            </div>

            <div class="mb-4">
                <label>
                    <i class="bi bi-car-front"></i>
                    Jenis Kendaraan
                </label>
                <select name="jenis_kendaraan" class="form-select" required>
                    <option value="">-- Pilih Jenis Kendaraan --</option>
                    <option value="Motor">🏍️ Motor</option>
                    <option value="Mobil">🚗 Mobil</option>
                </select>
            </div>

            <div class="mb-4">
                <label>
                    <i class="bi bi-palette"></i>
                    Warna Kendaraan
                </label>
                <input type="text" 
                       name="warna" 
                       class="form-control" 
                       placeholder="Contoh: Hitam, Putih, Merah" 
                       required>
            </div>

            <div class="mb-4">
                <label>
                    <i class="bi bi-person"></i>
                    Nama Pemilik
                </label>
                <input type="text" 
                       name="pemilik" 
                       class="form-control" 
                       placeholder="Nama pemilik kendaraan (opsional)">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>Opsional - boleh dikosongkan
                </small>
            </div>

            <div class="mb-4">
                <label>
                    <i class="bi bi-building"></i>
                    Area Parkir
                </label>
                <select name="id_area" class="form-select" required>
                    <option value="">-- Pilih Area Parkir --</option>
                    <?php
                    while ($a = mysqli_fetch_assoc($area)) {
                        $sisa = $a['kapasitas'] - $a['terisi'];
                        $disabled = $sisa <= 0 ? 'disabled' : '';
                        $status = $sisa <= 0 ? '(PENUH)' : "(Sisa: $sisa slot)";
                    ?>
                        <option value="<?= $a['id_area'] ?>" <?= $disabled ?>>
                            <?= htmlspecialchars($a['nama_area']) ?> <?= $status ?>
                        </option>
                    <?php } ?>
                </select>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>Pilih area parkir yang masih tersedia
                </small>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="simpan" class="btn btn-primary btn-custom btn-lg">
                    <i class="bi bi-save me-2"></i>Simpan Parkir Masuk
                </button>
                <a href="dashboard_petugas.php" class="btn btn-secondary btn-custom">
                    <i class="bi bi-x-circle me-2"></i>Batal
                </a>
            </div>

        </form>
        
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>