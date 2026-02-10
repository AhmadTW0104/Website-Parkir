<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistem Parkir Digital</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Animated Background -->
    <div class="bg-circle"></div>
    <div class="bg-circle"></div>
    <div class="bg-circle"></div>

    <div class="login-container">
        <div class="login-card">
            
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="bi bi-p-square-fill"></i>
                </div>
                <h1 class="logo-title">Website Parkir</h1>
                <p class="logo-subtitle">Silahkan login untuk melanjutkan</p>
            </div>

            <!-- Alert Messages -->
            <?php 
            if(isset($_GET['pesan'])){
                if($_GET['pesan'] == "gagal"){
                    echo '<div class="alert-custom alert-error">
                            <i class="bi bi-x-circle-fill"></i>
                            <span>Username, Password, atau Role salah!</span>
                          </div>';
                }else if($_GET['pesan'] == "logout"){
                    echo '<div class="alert-custom alert-success">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Anda berhasil logout.</span>
                          </div>';
                }else if($_GET['pesan'] == "login_dulu"){
                    echo '<div class="alert-custom alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Silahkan login terlebih dahulu.</span>
                          </div>';
                }
            }
            ?>

            <!-- Login Form -->
            <form action="periksa_login.php" method="POST">
                
                <!-- Username -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-person"></i>
                        Username
                    </label>
                    <div class="position-relative">
                        <input type="text" 
                               name="username" 
                               class="form-control" 
                               placeholder="Masukkan username"
                               required
                               autofocus>
                        <i class="bi bi-at input-icon"></i>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-lock"></i>
                        Password
                    </label>
                    <div class="position-relative">
                        <input type="password" 
                               name="password" 
                               id="password"
                               class="form-control" 
                               placeholder="Masukkan password"
                               required>
                        <i class="bi bi-eye input-icon password-toggle" 
                           id="togglePassword"
                           onclick="togglePassword()"></i>
                    </div>
                </div>
                
                <!-- Role Selection -->
                <div class="mb-4">
                    <label class="form-label">
                        <i class="bi bi-shield-check"></i>
                        Login Sebagai
                    </label>
                    <select name="role" class="form-select" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">👨‍💼 Admin</option>
                        <option value="petugas">👮 Petugas</option>
                        <option value="owner">👔 Owner</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    <span style="position: relative; z-index: 1;">LOGIN</span>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p>
                    <i class="bi bi-shield-lock me-1"></i>
                    © 2026 Sistem Parkir Digital
                </p>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toggle Password Script -->
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const toggle = document.getElementById('togglePassword');
            
            if (password.type === 'password') {
                password.type = 'text';
                toggle.classList.remove('bi-eye');
                toggle.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                toggle.classList.remove('bi-eye-slash');
                toggle.classList.add('bi-eye');
            }
        }
    </script>

</body>
</html>