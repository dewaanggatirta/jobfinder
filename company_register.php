<?php
// Tampilkan Error agar tidak blank jika ada masalah
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('./config.php');
session_start();

// Jika sudah login, lempar ke dashboard
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

    // Validasi sederhana
    if (empty($name) || empty($email) || empty($password)) {
        $err = "Semua kolom wajib diisi.";
    } elseif ($password !== $confirm_password) {
        $err = "Konfirmasi password tidak cocok.";
    } else {
        // 1. Cek apakah email sudah terdaftar
        $stmt = $conn->prepare("SELECT id FROM companies WHERE email = ?");
        
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $err = "Email perusahaan ini sudah terdaftar.";
            } else {
                // 2. Jika email aman, lakukan Insert
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $insert = $conn->prepare("INSERT INTO companies (company_name, email, password) VALUES (?, ?, ?)");
                
                // PENTING: Cek jika prepare gagal (penyebab umum blank screen)
                if ($insert) {
                    $insert->bind_param('sss', $name, $email, $hashed_password);
                    if ($insert->execute()) {
                        $success = "Pendaftaran berhasil! Silakan login.";
                    } else {
                        $err = "Gagal menyimpan data: " . $insert->error;
                    }
                } else {
                    // Tampilkan error query jika prepare gagal
                    $err = "Database Error (Insert): " . $conn->error;
                }
            }
        } else {
            // Tampilkan error query jika prepare select gagal
            $err = "Database Error (Check Email): " . $conn->error;
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
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="text-center mb-4">
                    <h3>Daftar Akun Perusahaan</h3>
                    <p class="text-muted">Bergabunglah untuk memposting lowongan pekerjaan.</p>
                </div>
                
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <?php if ($err): ?>
                            <div class="alert alert-danger">
                                <strong>Terjadi Kesalahan:</strong><br>
                                <?= htmlspecialchars($err) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?= $success ?> <br>
                                <a href="company_login.php" class="fw-bold text-success">Klik di sini untuk Login</a>
                            </div>
                        <?php else: ?>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Perusahaan</label>
                                <input type="text" name="company_name" class="form-control" required placeholder="PT. Mencari Cinta Sejati">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Perusahaan</label>
                                <input type="email" name="email" class="form-control" required placeholder="hrd@perusahaan.com">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Konfirmasi Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                            
                            <button class="btn btn-success w-100 mb-3">Daftar Sekarang</button>
                        </form>
                        <?php endif; ?>

                        <hr>
                        <div class="text-center">
                            <small class="text-muted">Sudah punya akun? <a href="company_login.php">Masuk di sini</a>.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>