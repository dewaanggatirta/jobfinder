<?php
// admin_login.php - KHUSUS UNTUK ADMIN
require_once('./config.php');
session_start();

// Jika sudah login, lempar ke dashboard admin
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/jobs.php');
    exit;
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Cek ke tabel ADMIN, bukan users
    $stmt = $conn->prepare('SELECT id, username, password FROM admin WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        // Verifikasi password
        if (password_verify($password, $row['password'])) {
            // Set session khusus admin
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $row['username'];
            
            // Arahkan ke folder admin/jobs.php
            header('Location: admin/jobs.php');
            exit;
        } else {
            $err = 'Password salah.';
        }
    } else {
        $err = 'Username tidak ditemukan.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Administrator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #343a40; color: #fff; }
        .card { border: none; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <h3>JobFinder Admin</h3>
                </div>
                <div class="card shadow-lg text-dark">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-3 text-center">Login Admin</h4>
                        
                        <?php if($err): ?>
                            <div class="alert alert-danger text-center"><?= htmlspecialchars($err) ?></div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="admin" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="******" required>
                            </div>
                            <button class="btn btn-dark w-100 py-2">Masuk Dashboard</button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="index.php" class="text-white-50 text-decoration-none">&larr; Kembali ke Website Utama</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>