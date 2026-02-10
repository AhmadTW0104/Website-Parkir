<?php
session_start();
include '../config/koneksi.php';

/* ========== CEK LOGIN & ROLE ========== */
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:../index.php?pesan=login_dulu");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    echo "Akses ditolak!";
    exit;
}

/* ========== AMBIL DATA LOG ========== */
$data = mysqli_query($koneksi,"
    SELECT 
        l.id_log,
        u.username,
        l.aktivitas,
        l.waktu_aktivitas
    FROM tb_log_aktivitas l
    LEFT JOIN tb_user u ON l.id_user = u.id_user
    ORDER BY l.waktu_aktivitas DESC
");

$totalLog = mysqli_num_rows($data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - Sistem Parkir</title>
    
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
        
        .page-title {
            color: white;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .page-subtitle {
            color: rgba(255,255,255,0.8);
            font-size: 0.95rem;
        }
        
        .log-item {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            border-left: 4px solid #667eea;
        }
        
        .log-item:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
            transform: translateX(5px);
        }
        
        .log-time {
            color: #64748b;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .log-user {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .log-activity {
            color: #1e293b;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .timeline-dot {
            width: 12px;
            height: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }
        
        .stats-badge {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stats-number {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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
                    <i class="bi bi-clock-history me-2"></i>Log Aktivitas Sistem
                </h2>
                <p class="page-subtitle mb-0">Riwayat semua aktivitas pengguna di sistem</p>
            </div>
            <a href="index.php" class="btn btn-light btn-custom">
                <i class="bi bi-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <!-- Stats Card -->
    <div class="mb-4">
        <div class="stats-badge">
            <i class="bi bi-card-list" style="font-size: 2rem; color: #667eea;"></i>
            <div>
                <div class="stats-number"><?= $totalLog ?></div>
                <div class="text-muted small">Total Aktivitas</div>
            </div>
        </div>
    </div>
    
    <!-- Log Timeline -->
    <div class="main-card">
        <div class="card-header-custom">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-list-ul me-2"></i>Riwayat Aktivitas
            </h5>
        </div>
        <div class="card-body p-4">
            
            <?php if($totalLog == 0){ ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-3 mb-0">Belum ada aktivitas yang tercatat</p>
                </div>
            <?php } ?>
            
            <?php 
            $no = 1; 
            while($l = mysqli_fetch_assoc($data)) { 
                // Tentukan icon berdasarkan aktivitas
                $icon = 'circle';
                $borderColor = '#667eea';
                
                $aktivitas = strtolower($l['aktivitas']);
                if(strpos($aktivitas, 'login') !== false) {
                    $icon = 'box-arrow-in-right';
                    $borderColor = '#10b981';
                } elseif(strpos($aktivitas, 'logout') !== false) {
                    $icon = 'box-arrow-right';
                    $borderColor = '#ef4444';
                } elseif(strpos($aktivitas, 'tambah') !== false || strpos($aktivitas, 'insert') !== false) {
                    $icon = 'plus-circle';
                    $borderColor = '#3b82f6';
                } elseif(strpos($aktivitas, 'update') !== false || strpos($aktivitas, 'edit') !== false) {
                    $icon = 'pencil-square';
                    $borderColor = '#f59e0b';
                } elseif(strpos($aktivitas, 'hapus') !== false || strpos($aktivitas, 'delete') !== false) {
                    $icon = 'trash';
                    $borderColor = '#dc2626';
                }
            ?>
            
            <div class="log-item" style="border-left-color: <?= $borderColor ?>;">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div class="log-user">
                        <i class="bi bi-person-circle"></i>
                        <span><?= htmlspecialchars($l['username'] ?? 'System'); ?></span>
                    </div>
                    <div class="log-time">
                        <i class="bi bi-clock"></i>
                        <span><?= date('d/m/Y H:i:s', strtotime($l['waktu_aktivitas'])); ?></span>
                    </div>
                </div>
                <div class="log-activity">
                    <i class="bi bi-<?= $icon ?>" style="color: <?= $borderColor ?>; margin-right: 8px;"></i>
                    <?= htmlspecialchars($l['aktivitas']); ?>
                </div>
            </div>
            
            <?php } ?>
            
        </div>
    </div>
    
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>