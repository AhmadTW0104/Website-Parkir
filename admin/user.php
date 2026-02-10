<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php?pesan=login_dulu");
    exit;
}

include '../config/koneksi.php';

/* ================= TAMBAH USER ================= */
if (isset($_POST['tambah'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $role     = mysqli_real_escape_string($koneksi, $_POST['role']);
    $status   = mysqli_real_escape_string($koneksi, $_POST['status_aktif']);

    mysqli_query($koneksi, "
        INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif)
        VALUES ('$nama','$username','$password','$role','$status')
    ");

    header("Location: user.php");
    exit;
}

/* ================= UPDATE USER ================= */
if (isset($_POST['update'])) {
    $id       = intval($_POST['id_user']);
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role     = mysqli_real_escape_string($koneksi, $_POST['role']);
    $status   = mysqli_real_escape_string($koneksi, $_POST['status_aktif']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    if ($password != "") {
        mysqli_query($koneksi, "
            UPDATE tb_user SET
            nama_lengkap='$nama',
            username='$username',
            password='$password',
            role='$role',
            status_aktif='$status'
            WHERE id_user='$id'
        ");
    } else {
        mysqli_query($koneksi, "
            UPDATE tb_user SET
            nama_lengkap='$nama',
            username='$username',
            role='$role',
            status_aktif='$status'
            WHERE id_user='$id'
        ");
    }

    header("Location: user.php");
    exit;
}

/* ================= HAPUS USER ================= */
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM tb_user WHERE id_user='$id'");
    header("Location: user.php");
    exit;
}

/* ================= AMBIL DATA EDIT ================= */
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $edit = mysqli_fetch_assoc(
        mysqli_query($koneksi, "SELECT * FROM tb_user WHERE id_user='$id'")
    );
}

$data = mysqli_query($koneksi, "SELECT * FROM tb_user ORDER BY id_user ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Sistem Parkir</title>
    
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
        
        .table-custom {
            background: white;
            border-radius: 10px;
            overflow: hidden;
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
        
        .badge-custom {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
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
        
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        
        .password-text {
            font-family: monospace;
            font-size: 0.85rem;
            background: #f8fafc;
            padding: 5px 10px;
            border-radius: 5px;
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
                    <i class="bi bi-people-fill me-2"></i>Manajemen User
                </h2>
                <p class="page-subtitle mb-0">Kelola pengguna dan hak akses sistem</p>
            </div>
            <a href="index.php" class="btn btn-light btn-custom">
                <i class="bi bi-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <!-- Form Card -->
    <div class="form-card">
        <h5 class="fw-bold mb-4">
            <i class="bi bi-<?= isset($edit) ? 'pencil-square' : 'plus-circle' ?> me-2"></i>
            <?= isset($edit) ? 'Edit User' : 'Tambah User Baru'; ?>
        </h5>
        
        <form method="post">
            <?php if (isset($edit)) { ?>
                <input type="hidden" name="id_user" value="<?= $edit['id_user']; ?>">
            <?php } ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-person me-1"></i>Nama Lengkap
                    </label>
                    <input type="text" name="nama_lengkap" class="form-control"
                           placeholder="Masukkan nama lengkap"
                           value="<?= htmlspecialchars($edit['nama_lengkap'] ?? ''); ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-at me-1"></i>Username
                    </label>
                    <input type="text" name="username" class="form-control"
                           placeholder="Masukkan username"
                           value="<?= htmlspecialchars($edit['username'] ?? ''); ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-key me-1"></i>Password
                    </label>
                    <input type="password" name="password" class="form-control"
                           placeholder="<?= isset($edit) ? 'Kosongkan jika tidak diubah' : 'Masukkan password'; ?>"
                           <?= isset($edit) ? '' : 'required'; ?>>
                    <?php if (isset($edit)) { ?>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>Kosongkan jika tidak ingin mengubah password
                        </small>
                    <?php } ?>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-shield-check me-1"></i>Role
                    </label>
                    <select name="role" class="form-select" required>
                        <option value="">Pilih Role</option>
                        <option value="admin"   <?= ($edit['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="petugas" <?= ($edit['role'] ?? '') == 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                        <option value="owner"   <?= ($edit['role'] ?? '') == 'owner' ? 'selected' : ''; ?>>Owner</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-toggle-on me-1"></i>Status
                    </label>
                    <select name="status_aktif" class="form-select" required>
                        <option value="1" <?= ($edit['status_aktif'] ?? '') == 1 ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?= ($edit['status_aktif'] ?? '') == 0 ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <?php if (isset($edit)) { ?>
                            <button type="submit" name="update" class="btn btn-warning btn-custom">
                                <i class="bi bi-save me-2"></i>Update User
                            </button>
                            <a href="user.php" class="btn btn-secondary btn-custom">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                        <?php } else { ?>
                            <button type="submit" name="tambah" class="btn btn-primary btn-custom">
                                <i class="bi bi-plus-circle me-2"></i>Tambah User
                            </button>
                            <button type="reset" class="btn btn-secondary btn-custom">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Table Card -->
    <div class="main-card">
        <div class="card-header-custom">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-table me-2"></i>Data Pengguna Sistem
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="60" class="text-center">ID</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Role</th>
                            <th class="text-center">Status</th>
                            <th width="180" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $no = 1;
                    while ($u = mysqli_fetch_assoc($data)) { 
                    ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $u['id_user']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px; font-weight: 600;">
                                        <?= strtoupper(substr($u['nama_lengkap'], 0, 1)); ?>
                                    </div>
                                    <span class="fw-semibold"><?= htmlspecialchars($u['nama_lengkap']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted">
                                    <i class="bi bi-at me-1"></i><?= htmlspecialchars($u['username']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="password-text"><?= htmlspecialchars($u['password']); ?></span>
                            </td>
                            <td>
                                <?php
                                $roleClass = '';
                                $roleIcon = '';
                                switch($u['role']) {
                                    case 'admin':
                                        $roleClass = 'bg-danger';
                                        $roleIcon = 'shield-fill-check';
                                        break;
                                    case 'petugas':
                                        $roleClass = 'bg-info';
                                        $roleIcon = 'person-badge';
                                        break;
                                    case 'owner':
                                        $roleClass = 'bg-warning';
                                        $roleIcon = 'star-fill';
                                        break;
                                }
                                ?>
                                <span class="badge-custom <?= $roleClass ?>">
                                    <i class="bi bi-<?= $roleIcon ?> me-1"></i>
                                    <?= ucfirst($u['role']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?= $u['status_aktif'] == 1
                                    ? '<span class="badge-custom bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>'
                                    : '<span class="badge-custom bg-secondary"><i class="bi bi-x-circle me-1"></i>Nonaktif</span>'; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?= $u['id_user']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="?hapus=<?= $u['id_user']; ?>"
                                       onclick="return confirm('Yakin ingin menghapus user <?= htmlspecialchars($u['nama_lengkap']); ?>?')"
                                       class="btn btn-danger btn-sm" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    
                    <?php if (mysqli_num_rows($data) == 0) { ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-3 mb-0">Belum ada data user</p>
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