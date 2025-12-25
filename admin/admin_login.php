<?php
// admin/login.php
require_once('../config.php');
session_start();

if (isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['username'];
            header("Location: index.php");
            exit;
        } else { $error = "Password salah."; }
    } else { $error = "Username tidak ditemukan."; }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; display: flex; align-items: center; min-height: 100vh; }
        /* FORCE HIDE Browser Eye */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none !important; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4 text-center fw-bold">Login Admin</h4>
                        <?php if ($error): ?><div class="alert alert-danger small py-2 rounded-3 text-center"><?=$error?></div><?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3"><label class="form-label fw-bold small">Username</label><input type="text" name="username" class="form-control" required></div>
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="adminPass" class="form-control border-end-0" required>
                                    <span class="input-group-text bg-white border-start-0" style="cursor:pointer" onclick="togglePass()">
                                        <i class="fas fa-eye text-muted" id="iconAdmin"></i>
                                    </span>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 fw-bold py-2 rounded-3">Masuk Dashboard</button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3"><a href="../index.php" class="text-secondary small fw-bold">&larr; Kembali</a></div>
            </div>
        </div>
    </div>
    <script>
        function togglePass() {
            var input = document.getElementById("adminPass");
            var icon = document.getElementById("iconAdmin");
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