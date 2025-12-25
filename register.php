<?php
// register.php - Dengan Upload Foto (Opsional) & Mata Permanen
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('./config.php');
session_start();

$err = '';
$success = '';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi Input Wajib
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $err = "Semua kolom wajib diisi (kecuali foto).";
    } elseif ($password !== $confirm_password) {
        $err = "Konfirmasi password tidak cocok.";
    } else {
        // Cek Email Terdaftar
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $err = "Email ini sudah terdaftar. Silakan login.";
        } else {
            // --- PROSES UPLOAD FOTO (OPSIONAL) ---
            $photo_filename = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];
                
                if (in_array($ext, $allowed)) {
                    // Nama file unik: user_timestamp_uniqid.ext
                    $new_name = "user_" . time() . "_" . uniqid() . "." . $ext;
                    
                    // Simpan di folder uploads (root)
                    $target_dir = __DIR__ . '/uploads/';
                    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                    
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $new_name)) {
                        $photo_filename = $new_name;
                    }
                } else {
                    $err = "Format foto harus JPG atau PNG.";
                }
            }
            // -------------------------------------

            if (empty($err)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                
                // Simpan ke Database (Kolom photo boleh NULL)
                $stmt = $conn->prepare('INSERT INTO users (name, email, password, photo) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssss', $name, $email, $hashed, $photo_filename);
                
                if ($stmt->execute()) {
                    $success = "Pendaftaran berhasil! Silakan login.";
                } else {
                    $err = "Terjadi kesalahan sistem saat mendaftar.";
                }
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
    <title>Daftar Akun - JobFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #334155; }
        .register-card { border: none; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); background: white; }
        .form-control { padding: 12px; border-radius: 10px; }
        .btn-primary { padding: 12px; border-radius: 10px; font-weight: 700; width: 100%; }
        
        /* HILANGKAN MATA BAWAAN BROWSER */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none !important; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary"><i class="fas fa-briefcase me-2"></i>JobFinder</h3>
                    <p class="text-muted">Buat akun pelamar baru</p>
                </div>
                
                <div class="card register-card p-4">
                    <div class="card-body">
                        <?php if($err): ?>
                            <div class="alert alert-danger py-2 small rounded-3"><i class="fas fa-exclamation-circle me-2"></i> <?=$err?></div>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                            <div class="alert alert-success py-4 small text-center rounded-3">
                                <i class="fas fa-check-circle fa-3x mb-3"></i><br>
                                <h5>Berhasil Mendaftar!</h5>
                                <p><?= $success ?></p>
                                <a href="login.php" class="btn btn-sm btn-success mt-2 fw-bold px-4 rounded-pill">Login Sekarang</a>
                            </div>
                        <?php else: ?>

                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="regPass" class="form-control border-end-0" placeholder="Buat password" required>
                                    <span class="input-group-text bg-white border-start-0" style="cursor:pointer" onclick="togglePass('regPass', 'icon1')">
                                        <i class="fas fa-eye text-muted" id="icon1"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-danger">Konfirmasi Password</label>
                                <div class="input-group">
                                    <input type="password" name="confirm_password" id="regConf" class="form-control border-end-0 border-danger" placeholder="Ulangi password" required>
                                    <span class="input-group-text bg-white border-start-0 border-danger" style="cursor:pointer" onclick="togglePass('regConf', 'icon2')">
                                        <i class="fas fa-eye text-danger" id="icon2"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-secondary">Foto Profil <span class="fw-normal text-muted">(Opsional)</span></label>
                                <input type="file" name="photo" class="form-control" accept="image/*">
                                <div class="form-text small">Format: JPG, PNG. Bisa dikosongkan.</div>
                            </div>

                            <button class="btn btn-primary mb-3 shadow-sm">Daftar Sekarang</button>
                        </form>
                        <?php endif; ?>

                        <div class="text-center border-top pt-3 mt-2">
                            <small class="text-muted">Sudah punya akun? <a href="login.php" class="fw-bold text-decoration-none">Masuk</a></small>
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