<?php
// company/company_login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');
session_start();

if (isset($_SESSION['company_id'])) {
    header('Location: company_dashboard.php');
    exit;
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); // Pakai ?? '' untuk mencegah error undefined
    $password = $_POST['password'] ?? '';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; display: flex; align-items: center; min-height: 100vh; }
        
        /* HILANGKAN MATA BAWAAN BROWSER */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary">Portal Perusahaan</h3>
                    <p class="text-muted">Pasang lowongan dan temukan talenta terbaik.</p>
                </div>
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <?php if ($err): ?>
                            <div class="alert alert-danger text-center small py-2 rounded-3">
                                <i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($err) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Email Perusahaan</label>
                                <input type="email" name="email" class="form-control" required placeholder="hrd@perusahaan.com">
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="compPass" class="form-control border-end-0" required placeholder="Masukkan password">
                                    <span class="input-group-text bg-white border-start-0" style="cursor:pointer" onclick="togglePass('compPass', 'iconComp')">
                                        <i class="fas fa-eye text-muted" id="iconComp"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <button class="btn btn-primary w-100 mb-3 fw-bold py-2 rounded-3">Masuk Dashboard</button>
                        </form>
                        
                        <div class="text-center border-top pt-3">
                            <small class="text-muted">Belum punya akun? <a href="company_register.php" class="fw-bold text-decoration-none">Daftar di sini</a>.</small>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="../index.php" class="text-decoration-none text-secondary small fw-bold">&larr; Kembali ke Beranda</a>
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