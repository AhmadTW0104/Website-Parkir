<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include '../config/koneksi.php';

/* ================= CEK LOGIN ================= */
if (!isset($_SESSION['status']) || $_SESSION['status'] != 'login') {
    header("Location: ../index.php?pesan=login_dulu");
    exit;
}

if ($_SESSION['role'] != 'petugas') {
    exit("Akses ditolak!");
}

/* ================= INISIALISASI ================= */
$data  = null;
$struk = null;

/* ================= CEK KENDARAAN ================= */
if (isset($_POST['cek'])) {
    $plat = mysqli_real_escape_string($koneksi, $_POST['plat_nomor']);

    $q = mysqli_query($koneksi, "
        SELECT 
            t.id_parkir, t.waktu_masuk,
            k.plat_nomor, k.jenis_kendaraan, k.warna, k.pemilik,
            tf.tarif_per_jam,
            t.id_area, a.nama_area
        FROM tb_transaksi t
        JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
        JOIN tb_tarif tf ON LOWER(tf.jenis_kendaraan) = LOWER(k.jenis_kendaraan)
        JOIN tb_area_parkir a ON t.id_area = a.id_area
        WHERE k.plat_nomor = '$plat' AND t.waktu_keluar IS NULL
        LIMIT 1
    ");

    if (mysqli_num_rows($q) > 0) {
        $data = mysqli_fetch_assoc($q);
    } else {
        echo "<script>alert('Kendaraan tidak ditemukan atau sudah keluar!');</script>";
    }
}

/* ================= PROSES PARKIR KELUAR ================= */
if (isset($_POST['keluar'])) {
    $id_parkir   = intval($_POST['id_parkir']);
    $id_area     = intval($_POST['id_area']);
    $waktu_masuk = $_POST['waktu_masuk'];
    $tarif       = intval($_POST['tarif']);
    $plat        = $_POST['plat_nomor'];
    $jenis       = $_POST['jenis_kendaraan'];
    $area        = $_POST['nama_area'];

    $waktu_query = mysqli_query($koneksi, "SELECT NOW() as waktu_sekarang");
    $waktu_data = mysqli_fetch_assoc($waktu_query);
    $waktu_keluar = $waktu_data['waktu_sekarang'];

    $durasi = ceil((strtotime($waktu_keluar) - strtotime($waktu_masuk)) / 3600);
    if ($durasi < 1) $durasi = 1;
    $biaya = $durasi * $tarif;

    mysqli_begin_transaction($koneksi);
    try {
        mysqli_query($koneksi, "
            UPDATE tb_transaksi SET
                waktu_keluar = NOW(), durasi_jam = '$durasi',
                biaya_total = '$biaya', status = 'KELUAR'
            WHERE id_parkir = '$id_parkir'
        ");
        mysqli_query($koneksi, "
            UPDATE tb_area_parkir
            SET terisi = IF(terisi > 0, terisi - 1, 0)
            WHERE id_area = '$id_area'
        ");
        mysqli_commit($koneksi);
        
        $struk = [
            'plat' => $plat, 'jenis' => $jenis, 'area' => $area,
            'masuk' => $waktu_masuk, 'keluar' => $waktu_keluar,
            'durasi' => $durasi, 'biaya' => $biaya, 'tarif' => $tarif
        ];
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "<script>alert('Gagal memproses parkir keluar');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parkir Keluar - Sistem Parkir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            padding: 30px 0;
        }
        .main-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            margin-bottom: 25px;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .data-row {
            padding: 15px;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #f093fb;
        }
        .data-label { font-weight: 600; color: #64748b; font-size: 0.9rem; }
        .data-value { font-size: 1.1rem; color: #1e293b; font-weight: 600; }
        .plat-nomor {
            background: #1e293b; color: white;
            padding: 8px 15px; border-radius: 8px;
            font-weight: 700; font-family: 'Courier New', monospace;
            letter-spacing: 2px; display: inline-block;
            border: 3px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .page-title { color: white; font-weight: 700; }
        .page-subtitle { color: rgba(255,255,255,0.8); }
        
        /* STRUK THERMAL 80MM - COMPACT VERSION */
        .struk-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .struk-card {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 20px 50px rgba(16, 185, 129, 0.4);
            width: 100%;
            max-width: 420px;
        }
        .struk-header {
            text-align: center;
            margin-bottom: 15px;
        }
        .struk-header i {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        .struk-header h4 {
            font-size: 1.3rem;
            margin: 5px 0 2px 0;
            font-weight: 700;
        }
        .struk-header .small {
            font-size: 0.75rem;
            opacity: 0.9;
            display: block;
            margin: 2px 0;
        }
        .struk-card table {
            width: 100%;
            margin: 8px 0;
        }
        .struk-card td {
            padding: 4px 0;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        .struk-card td:first-child {
            width: 45%;
        }
        .struk-card td:last-child {
            text-align: right;
            font-weight: 600;
        }
        .struk-card hr {
            border-color: rgba(255,255,255,0.3);
            margin: 10px 0;
            border-style: dashed;
        }
        .struk-plat-display {
            background: rgba(255,255,255,0.2);
            padding: 8px 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 1px;
            display: inline-block;
            border: 2px solid rgba(255,255,255,0.5);
        }
        .struk-total-section {
            background: rgba(255,255,255,0.15);
            padding: 12px;
            border-radius: 10px;
            margin: 12px 0;
            text-align: center;
        }
        .struk-total-label {
            font-size: 0.75rem;
            font-weight: 600;
            opacity: 0.9;
            margin-bottom: 3px;
        }
        .struk-total-amount {
            font-size: 1.8rem;
            font-weight: 900;
            font-family: 'Courier New', monospace;
            line-height: 1;
        }
        .struk-footer {
            text-align: center;
            font-size: 0.75rem;
            opacity: 0.9;
            margin-top: 12px;
        }
        .struk-footer p {
            margin: 3px 0;
        }
        .struk-id {
            background: rgba(0,0,0,0.2);
            padding: 6px 8px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.7rem;
            display: inline-block;
            margin-top: 8px;
        }
        
        /* PRINT STYLES */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print { display: none !important; }
            .struk-container {
                display: block !important;
                padding: 0 !important;
                min-height: auto !important;
            }
            .struk-card {
                background: white !important;
                color: black !important;
                box-shadow: none !important;
                border: 2px solid black !important;
                border-radius: 0 !important;
                padding: 8mm !important;
                width: 110mm !important;
                max-width: 110mm !important;
                margin: 0 auto !important;
            }
            .struk-card * { color: black !important; }
            .struk-card hr {
                border-color: black !important;
                border-style: dashed !important;
            }
            .struk-plat-display {
                background: white !important;
                border: 2px solid black !important;
            }
            .struk-total-section {
                background: white !important;
                border: 2px dashed black !important;
            }
            .struk-total-amount { font-size: 1.5rem !important; }
            .struk-id {
                background: white !important;
                border: 1px solid black !important;
            }
            @page { size: 110mm 220mm; margin: 0; }
        }
    </style>
</head>
<body>

<!-- FORM & DATA (NO PRINT) -->
<div class="container no-print" style="max-width: 900px;">
    <div class="mb-4 text-center">
        <h2 class="page-title"><i class="bi bi-box-arrow-up me-2"></i>Parkir Keluar</h2>
        <p class="page-subtitle mb-3">Proses kendaraan keluar dan hitung biaya parkir</p>
        <a href="dashboard_petugas.php" class="btn btn-light">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
    </div>

    <div class="main-card">
        <div class="card-header-custom">
            <h5 class="mb-0 fw-bold"><i class="bi bi-search me-2"></i>Cek Kendaraan</h5>
        </div>
        <form method="POST">
            <div class="input-group input-group-lg">
                <span class="input-group-text"><i class="bi bi-car-front"></i></span>
                <input type="text" name="plat_nomor" class="form-control text-uppercase" 
                       placeholder="Masukkan Plat Nomor" required>
                <button name="cek" class="btn btn-primary">
                    <i class="bi bi-search me-2"></i>Cek Kendaraan
                </button>
            </div>
        </form>
    </div>

    <?php if ($data) { ?>
    <div class="main-card">
        <div class="card-header-custom">
            <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Data Kendaraan Parkir</h5>
        </div>
        <div class="data-row">
            <div class="data-label">Plat Nomor</div>
            <div class="data-value">
                <span class="plat-nomor"><?= strtoupper(htmlspecialchars($data['plat_nomor'])) ?></span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="data-row">
                    <div class="data-label">Jenis Kendaraan</div>
                    <div class="data-value"><?= htmlspecialchars($data['jenis_kendaraan']) ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="data-row">
                    <div class="data-label">Warna</div>
                    <div class="data-value"><?= htmlspecialchars($data['warna']) ?></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="data-row">
                    <div class="data-label">Area Parkir</div>
                    <div class="data-value"><?= htmlspecialchars($data['nama_area']) ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="data-row">
                    <div class="data-label">Waktu Masuk</div>
                    <div class="data-value"><?= date('d/m/Y H:i', strtotime($data['waktu_masuk'])) ?></div>
                </div>
            </div>
        </div>
        <div class="data-row" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left-color: #f59e0b;">
            <div class="data-label" style="color: #92400e;">Tarif per Jam</div>
            <div class="data-value" style="color: #92400e; font-size: 1.3rem;">
                Rp <?= number_format($data['tarif_per_jam'], 0, ',', '.') ?>
            </div>
        </div>
        <form method="POST" class="mt-4">
            <input type="hidden" name="id_parkir" value="<?= $data['id_parkir'] ?>">
            <input type="hidden" name="id_area" value="<?= $data['id_area'] ?>">
            <input type="hidden" name="waktu_masuk" value="<?= $data['waktu_masuk'] ?>">
            <input type="hidden" name="tarif" value="<?= $data['tarif_per_jam'] ?>">
            <input type="hidden" name="plat_nomor" value="<?= $data['plat_nomor'] ?>">
            <input type="hidden" name="jenis_kendaraan" value="<?= $data['jenis_kendaraan'] ?>">
            <input type="hidden" name="nama_area" value="<?= $data['nama_area'] ?>">
            <div class="d-grid">
                <button name="keluar" class="btn btn-danger btn-lg">
                    <i class="bi bi-box-arrow-up me-2"></i>Proses Parkir Keluar
                </button>
            </div>
        </form>
    </div>
    <?php } ?>
</div>

<!-- STRUK PARKIR COMPACT -->
<?php if ($struk) { ?>
<div class="struk-container">
    <div class="struk-card">
        <!-- Header -->
        <div class="struk-header">
            <i class="bi bi-receipt-cutoff"></i>
            <h4>STRUK PARKIR</h4>
            <span class="small">Sistem Parkir Digital</span>
            <span class="small"><?= date('d/m/Y H:i:s') ?></span>
        </div>

        <hr>

        <!-- Data Kendaraan -->
        <table>
            <tr>
                <td>Plat Nomor</td>
                <td>
                    <span class="struk-plat-display">
                        <?= strtoupper($struk['plat']) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td>Jenis</td>
                <td><?= $struk['jenis'] ?></td>
            </tr>
            <tr>
                <td>Area Parkir</td>
                <td><?= $struk['area'] ?></td>
            </tr>
        </table>

        <hr>

        <!-- Waktu -->
        <table>
            <tr>
                <td>Waktu Masuk</td>
                <td><?= date('d/m/Y H:i', strtotime($struk['masuk'])) ?></td>
            </tr>
            <tr>
                <td>Waktu Keluar</td>
                <td><?= date('d/m/Y H:i', strtotime($struk['keluar'])) ?></td>
            </tr>
        </table>

        <hr>

        <!-- Biaya -->
        <table>
            <tr>
                <td>Durasi Parkir</td>
                <td><?= $struk['durasi'] ?> Jam</td>
            </tr>
            <tr>
                <td>Tarif per Jam</td>
                <td>Rp <?= number_format($struk['tarif'], 0, ',', '.') ?></td>
            </tr>
        </table>

        <!-- Total -->
        <div class="struk-total-section">
            <div class="struk-total-label">TOTAL PEMBAYARAN</div>
            <div class="struk-total-amount">
                Rp <?= number_format($struk['biaya'], 0, ',', '.') ?>
            </div>
        </div>

        <hr>

        <!-- Footer -->
        <div class="struk-footer">
            <p><i class="bi bi-check-circle-fill"></i> <strong>LUNAS</strong></p>
            <p>Terima kasih telah<br>menggunakan layanan parkir kami</p>
            <span class="struk-id">
                ID: TRX-<?= date('YmdHis') ?>-<?= substr(strtoupper($struk['plat']), 0, 4) ?>
            </span>
        </div>

        <!-- Buttons -->
        <div class="text-center mt-3 no-print">
            <button onclick="window.print()" class="btn btn-light w-100 mb-2">
                <i class="bi bi-printer me-2"></i>Cetak Struk
            </button>
            <a href="parkir_keluar.php" class="btn btn-dark w-100 mb-2">
                <i class="bi bi-arrow-left me-2"></i>Transaksi Baru
            </a>
        </div>
    </div>
</div>
<?php } ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>