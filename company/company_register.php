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
            // --- PROSES UPLOAD LOGO SAAT DAFTAR ---
            $logo_filename = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];
                if (in_array($ext, $allowed)) {
                    // Nama file unik
                    $new_name = "logo_" . time() . "_" . uniqid() . "." . $ext;
                    
                    // Simpan di folder uploads dalam folder company
                    $target_dir = __DIR__ . '/uploads/';
                    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                    
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $new_name)) {
                        $logo_filename = $new_name;
                    }
                }
            }
            // -------------------------------------

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Simpan ke database (termasuk logo)
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
    <title>Daftar Perusahaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h4 class="text-center mb-4">Daftar Akun Perusahaan</h4>
                        
                        <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?> <a href="company_login.php">Login</a></div>
                        <?php else: ?>

                        <!-- PENTING: enctype="multipart/form-data" AGAR BISA UPLOAD FOTO -->
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Perusahaan</label>
                                <input type="text" name="company_name" class="form-control" required placeholder="Contoh: PT. Maju Mundur">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Perusahaan</label>
                                <input type="email" name="email" class="form-control" required placeholder="hrd@perusahaan.com">
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Konfirmasi</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                            
                            <!-- INPUT LOGO PERUSAHAAN -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Upload Logo Perusahaan</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <div class="form-text">Format: JPG, PNG. Bisa dikosongkan (diisi nanti).</div>
                            </div>
                            
                            <button class="btn btn-success w-100">Daftar Sekarang</button>
                        </form>
                        <?php endif; ?>
                        
                        <div class="text-center mt-3">
                            <small>Sudah punya akun? <a href="company_login.php">Masuk</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>