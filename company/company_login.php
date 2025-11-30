<?php
// company/company_login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PATH DIPERBAIKI: Naik satu level ke root untuk cari config.php
require_once('../config.php');
session_start();

if (isset($_SESSION['company_id'])) {
    header('Location: company_dashboard.php');
    exit;
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $err = "Email dan password wajib diisi.";
    } else {
        $stmt = $conn->prepare('SELECT id, company_name, password FROM companies WHERE email = ?');
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['company_id'] = $row['id'];
                    $_SESSION['company_name'] = $row['company_name'];
                    $_SESSION['role'] = 'company';
                    header('Location: company_dashboard.php');
                    exit;
                } else {
                    $err = 'Password salah.';
                }
            } else {
                $err = 'Email perusahaan tidak terdaftar.';
            }
        } else {
            $err = "Query Error: " . $conn->error;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Perusahaan - JobFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="text-center mb-4">
                    <h3>Portal Perusahaan</h3>
                    <p class="text-muted">Pasang lowongan dan temukan talenta terbaik.</p>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <?php if ($err): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Email Perusahaan</label>
                                <input type="email" name="email" class="form-control" required placeholder="hrd@perusahaan.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button class="btn btn-primary w-100 mb-3">Masuk Dashboard</button>
                        </form>
                        <hr>
                        <div class="text-center">
                            <small class="text-muted">Belum punya akun? <a href="company_register.php">Daftar di sini</a>.</small>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <!-- LINK KEMBALI DIPERBAIKI (NAIK KE ROOT) -->
                    <a href="../index.php" class="text-decoration-none text-secondary">&larr; Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>