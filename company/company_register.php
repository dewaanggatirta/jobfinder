<?php
// company/company_register.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');
session_start();

if (isset($_SESSION['company_id'])) {
    header('Location: company_dashboard.php');
    exit;
}

$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['company_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password)) {
        $err = "Semua kolom wajib diisi.";
    } elseif ($password !== $confirm_password) {
        $err = "Konfirmasi password tidak cocok.";
    } else {
        // Cek Email
        $stmt = $conn->prepare("SELECT id FROM companies WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $err = "Email perusahaan ini sudah terdaftar.";
        } else {
            // Proses Upload Logo
            $logo_filename = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];
                if (in_array($ext, $allowed)) {
                    $new_name = "logo_" . time() . "_" . uniqid() . "." . $ext;
                    $target_dir = __DIR__ . '/uploads/';
                    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $new_name)) {
                        $logo_filename = $new_name;
                    }
                }
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert = $conn->prepare("INSERT INTO companies (company_name, email, password, logo) VALUES (?, ?, ?, ?)");
            $insert->bind_param('ssss', $name, $email, $hashed_password, $logo_filename);
            
            if ($insert->execute()) {
                $success = "Pendaftaran berhasil! Silakan login.";
            } else {
                $err = "Gagal mendaftar: " . $conn->error;
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Perusahaan - JobFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; }
        
        /* MEMATIKAN MATA DEFAULT BROWSER */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold text-primary">Daftar Akun Perusahaan</h4>
                            <p class="text-muted small">Bergabunglah untuk memposting lowongan</p>
                        </div>
                        
                        <?php if ($err): ?>
                            <div class="alert alert-danger text-center small py-2 rounded-3">
                                <i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($err) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success text-center py-4 rounded-3">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success"></i><br>
                                <h5>Berhasil!</h5>
                                <p><?= $success ?></p>
                                <a href="company_login.php" class="btn btn-success fw-bold w-100 mt-2">Login Sekarang</a>
                            </div>
                        <?php else: ?>

                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Nama Perusahaan</label>
                                <input type="text" name="company_name" class="form-control" required placeholder="Contoh: PT. Maju Mundur">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Email Perusahaan</label>
                                <input type="email" name="email" class="form-control" required placeholder="hrd@perusahaan.com">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="regPass" class="form-control border-end-0" required placeholder="Buat password">
                                    <span class="input-group-text bg-white border-start-0" style="cursor:pointer" onclick="togglePass('regPass', 'icon1')">
                                        <i class="fas fa-eye text-muted" id="icon1"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-danger">Konfirmasi Password</label>
                                <div class="input-group">
                                    <input type="password" name="confirm_password" id="regConf" class="form-control border-end-0 border-danger" required placeholder="Ulangi password">
                                    <span class="input-group-text bg-white border-start-0 border-danger" style="cursor:pointer" onclick="togglePass('regConf', 'icon2')">
                                        <i class="fas fa-eye text-danger" id="icon2"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Upload Logo Perusahaan</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <div class="form-text small">Format: JPG, PNG. Bisa dikosongkan (diisi nanti).</div>
                            </div>
                            
                            <button class="btn btn-success w-100 fw-bold py-2 rounded-3 shadow-sm">Daftar Sekarang</button>
                        </form>
                        <?php endif; ?>
                        
                        <div class="text-center mt-3 pt-3 border-top">
                            <small class="text-muted">Sudah punya akun? <a href="company_login.php" class="fw-bold text-decoration-none">Masuk</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>