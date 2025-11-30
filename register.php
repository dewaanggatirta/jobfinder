<?php
// register.php - Pendaftaran untuk Pelamar Kerja (User)
require_once('./config.php');
session_start();

$err = '';
$success = '';

// Jika sudah login, lempar ke halaman utama
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi Input
    if (empty($name) || empty($email) || empty($password)) {
        $err = "Semua kolom wajib diisi.";
    } else {
        // Cek apakah email sudah terdaftar
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $err = "Email ini sudah terdaftar. Silakan login.";
        } else {
            // Enkripsi Password & Simpan
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $name, $email, $hashed);
            
            if ($stmt->execute()) {
                $success = "Pendaftaran berhasil! Silakan login.";
            } else {
                $err = "Terjadi kesalahan saat mendaftar.";
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
    <title>Daftar Akun Pelamar - JobFinder</title>
    
    <!-- CSS Modern -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8fafc; 
            color: #334155;
        }
        .register-card { 
            border: none; 
            border-radius: 16px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
            background: white;
        }
        .form-control { 
            padding: 12px; 
            border-radius: 10px; 
            border: 1px solid #e2e8f0;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-primary { 
            padding: 12px; 
            border-radius: 10px; 
            font-weight: 700; 
            background-color: #2563eb; 
            border: none;
            width: 100%;
            transition: all 0.2s;
        }
        .btn-primary:hover { 
            background-color: #1d4ed8; 
            transform: translateY(-2px); 
        }
        .text-primary-custom { color: #2563eb; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary-custom"><i class="fas fa-briefcase me-2"></i>JobFinder</h3>
                    <p class="text-muted">Buat akun untuk mulai melamar kerja.</p>
                </div>
                
                <div class="card register-card p-4">
                    <div class="card-body">
                        <!-- Alert Pesan Error/Sukses -->
                        <?php if($err): ?>
                            <div class="alert alert-danger py-2 small d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i> <?=htmlspecialchars($err)?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                            <div class="alert alert-success py-3 small text-center">
                                <i class="fas fa-check-circle fa-lg mb-2 d-block"></i>
                                <?= $success ?> <br>
                                <a href="login.php" class="btn btn-sm btn-success mt-2 fw-bold w-100">Login Sekarang</a>
                            </div>
                        <?php else: ?>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-secondary">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <button class="btn btn-primary mb-3 shadow-sm">Daftar Sekarang</button>
                        </form>
                        
                        <?php endif; ?>

                        <div class="text-center border-top pt-3 mt-2">
                            <small class="text-muted">Sudah punya akun? <a href="login.php" class="text-decoration-none fw-bold text-primary-custom">Masuk</a></small>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <!-- Link ini mengarah ke folder company/company_register.php -->
                    <a href="company/company_register.php" class="text-decoration-none small text-secondary p-2 d-inline-block rounded hover-bg">
                        Ingin memposting lowongan? <br><strong>Daftar sebagai Perusahaan</strong> <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>
</body>
</html>