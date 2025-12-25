<?php
// company/company_profile.php - FINAL (Ada Ganti Password)
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('../config.php');
session_start();

if (!isset($_SESSION['company_id'])) { header('Location: company_login.php'); exit; }

$comp_id = $_SESSION['company_id'];
$success = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['company_name']);
    $pass = $_POST['password'];
    $conf_pass = $_POST['confirm_password'];
    
    if (empty($name)) { $err = "Nama perusahaan wajib diisi."; } 
    else {
        $sql = "UPDATE companies SET company_name = ?";
        $params = [$name];
        $types = "s";

        // Ganti Password
        if (!empty($pass)) {
            if ($pass !== $conf_pass) {
                $err = "Konfirmasi password tidak cocok.";
            } else {
                $sql .= ", password = ?";
                $params[] = password_hash($pass, PASSWORD_DEFAULT);
                $types .= "s";
            }
        }

        // Upload Logo
        if (empty($err) && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $new_name = "logo_" . time() . "_" . uniqid() . "." . $ext;
                $target_dir = __DIR__ . '/uploads/';
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $new_name)) {
                    $sql .= ", logo = ?";
                    $params[] = $new_name;
                    $types .= "s";
                    $_SESSION['company_logo'] = $new_name;
                }
            } else { $err = "Format logo harus JPG/PNG."; }
        }

        if (empty($err)) {
            $sql .= " WHERE id = ?";
            $params[] = $comp_id;
            $types .= "i";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $success = "Profil berhasil diperbarui!";
                $_SESSION['company_name'] = $name; 
            } else { $err = "Database error."; }
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->bind_param('i', $comp_id);
$stmt->execute();
$comp = $stmt->get_result()->fetch_assoc();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Profil Perusahaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .form-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .logo-preview { width: 150px; height: 150px; object-fit: contain; border: 2px dashed #cbd5e1; padding: 5px; }
        input[type="password"]::-ms-reveal, input[type="password"]::-ms-clear { display: none; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom py-3 mb-4 sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="company_dashboard.php"><i class="fas fa-arrow-left me-2"></i> Dashboard</a>
            <span class="navbar-text fw-bold text-dark">Edit Profil</span>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle me-2"></i> <?=$success?></div><?php endif; ?>
                <?php if($err): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?=$err?></div><?php endif; ?>

                <div class="form-card p-4 p-md-5">
                    <form method="post" enctype="multipart/form-data">
                        <div class="text-center mb-5">
                            <?php if(!empty($comp['logo']) && file_exists(__DIR__.'/uploads/'.$comp['logo'])): ?>
                                <img src="./uploads/<?= htmlspecialchars($comp['logo']) ?>" class="logo-preview mb-3 shadow-sm">
                            <?php else: ?>
                                <div class="logo-preview d-inline-flex align-items-center justify-content-center mb-3 text-muted bg-light"><i class="fas fa-image fa-2x opacity-25"></i></div>
                            <?php endif; ?>
                            <div class="w-75 mx-auto"><input type="file" name="logo" class="form-control form-control-sm" accept="image/*"></div>
                        </div>

                        <div class="mb-4"><label class="form-label fw-bold small">Nama Perusahaan</label><input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($comp['company_name']) ?>" required></div>

                        <hr class="my-4">
                        <p class="text-muted small fw-bold mb-3">GANTI PASSWORD (OPSIONAL)</p>

                        <div class="mb-3">
                            <label class="form-label small">Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="password" id="newPass" class="form-control border-end-0" placeholder="Kosongkan jika tidak diganti">
                                <span class="input-group-text bg-white border-start-0" style="cursor:pointer" onclick="togglePass('newPass', 'icon3')"><i class="fas fa-eye text-muted" id="icon3"></i></span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small text-danger">Konfirmasi Password</label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" id="confNewPass" class="form-control border-end-0" placeholder="Ketik ulang password baru">
                                <span class="input-group-text bg-white border-start-0" style="cursor:pointer" onclick="togglePass('confNewPass', 'icon4')"><i class="fas fa-eye text-danger" id="icon4"></i></span>
                            </div>
                        </div>

                        <div class="d-grid mt-4"><button type="submit" class="btn btn-primary fw-bold shadow-sm">Simpan Perubahan</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function togglePass(inputId, iconId) {
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            if (input.type === "password") { input.type = "text"; icon.classList.remove("fa-eye"); icon.classList.add("fa-eye-slash"); } 
            else { input.type = "password"; icon.classList.remove("fa-eye-slash"); icon.classList.add("fa-eye"); }
        }
    </script>
</body>
</html>