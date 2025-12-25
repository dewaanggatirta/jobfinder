<?php
// edit_profile.php - FIX FINAL ARGUMENT COUNT ERROR
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('./config.php');
session_start();

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$user_id = $_SESSION['user_id'];
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];
    $conf_pass = $_POST['confirm_password'];
    
    if (empty($name) || empty($email)) {
        $err = "Nama dan Email wajib diisi.";
    } else {
        $sql = "UPDATE users SET name=?, email=?";
        $params = [$name, $email];
        $types = "ss"; // Awal: String, String (Nama, Email)

        // LOGIKA GANTI PASSWORD
        if (!empty($pass)) {
            if ($pass !== $conf_pass) {
                $err = "Konfirmasi password baru tidak cocok!";
            } else {
                $sql .= ", password=?";
                $params[] = password_hash($pass, PASSWORD_DEFAULT);
                $types .= "s"; // Tambah String (Password)
            }
        }

        // LOGIKA UPLOAD FOTO
        if (empty($err) && isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $new_name = "user_" . $user_id . "_" . time() . "." . $ext;
                $target_dir = __DIR__ . '/uploads/';
                if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $new_name)) {
                    $sql .= ", photo=?";
                    $params[] = $new_name;
                    $types .= "s"; // Tambah String (Foto)
                }
            } else {
                $err = "Format foto harus JPG/PNG.";
            }
        }

        if (empty($err)) {
            $sql .= " WHERE id=?";
            $params[] = $user_id;
            $types .= "i"; // <--- WAJIB ADA: Menambahkan tipe Integer untuk ID
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $name; 
                $msg = "Profil berhasil diperbarui!";
            } else {
                $err = "Gagal update: " . $conn->error;
            }
        }
    }
}

// Ambil Data User Terbaru
$stmt = $conn->prepare("SELECT name, email, photo FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Edit Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #334155; }
        .form-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .preview-img { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .btn-upload { position: relative; overflow: hidden; cursor: pointer; }
        .btn-upload input[type=file] { position: absolute; top: 0; right: 0; min-width: 100%; min-height: 100%; opacity: 0; cursor: pointer; }
        
        /* HILANGKAN MATA BAWAAN BROWSER */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <a href="dashboard.php" class="text-decoration-none text-secondary fw-bold mb-3 d-inline-block">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
                </a>

                <div class="form-card p-4 p-md-5">
                    <h4 class="fw-bold mb-4 text-center">Edit Profil Saya</h4>
                    
                    <?php if($msg): ?><div class="alert alert-success rounded-3"><i class="fas fa-check-circle me-2"></i> <?=$msg?></div><?php endif; ?>
                    <?php if($err): ?><div class="alert alert-danger rounded-3"><i class="fas fa-exclamation-circle me-2"></i> <?=$err?></div><?php endif; ?>

                    <form method="post" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <?php if (!empty($user['photo']) && file_exists(__DIR__ . '/uploads/' . $user['photo'])): ?>
                                <img src="./uploads/<?= htmlspecialchars($user['photo']) ?>" class="preview-img mb-3" id="imgPreview">
                            <?php else: ?>
                                <div class="preview-img d-inline-flex align-items-center justify-content-center bg-light text-secondary mb-3 fs-1 fw-bold border" id="placeholderPreview">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                                <img src="" class="preview-img mb-3 d-none" id="imgPreviewHidden">
                            <?php endif; ?>
                            <div>
                                <span class="btn btn-outline-primary btn-sm rounded-pill px-3 btn-upload">
                                    <i class="fas fa-camera me-1"></i> Ganti Foto
                                    <input type="file" name="photo" accept="image/*" onchange="previewFile(this)">
                                </span>
                            </div>
                        </div>

                        <div class="mb-3"><label class="form-label fw-bold small text-secondary">Nama Lengkap</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required></div>
                        <div class="mb-4"><label class="form-label fw-bold small text-secondary">Alamat Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required></div>
                        
                        <hr class="my-4">
                        <p class="text-muted small fw-bold mb-3">GANTI PASSWORD (OPSIONAL)</p>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="password" id="newPass" class="form-control border-end-0" placeholder="Kosongkan jika tidak diganti">
                                <span class="input-group-text bg-white border-start-0" style="cursor:pointer" onclick="togglePass('newPass', 'icon3')">
                                    <i class="fas fa-eye text-muted" id="icon3"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-danger">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" id="confNewPass" class="form-control border-end-0" placeholder="Ketik ulang password baru">
                                <span class="input-group-text bg-white border-start-0" style="cursor:pointer" onclick="togglePass('confNewPass', 'icon4')">
                                    <i class="fas fa-eye text-danger" id="icon4"></i>
                                </span>
                            </div>
                        </div>

                        <button class="btn btn-primary w-100 fw-bold py-2 rounded-3 shadow-sm">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewFile(input) {
            var file = input.files[0];
            if(file){
                var reader = new FileReader();
                reader.onload = function(){
                    var img = document.getElementById('imgPreview');
                    var placeholder = document.getElementById('placeholderPreview');
                    var imgHidden = document.getElementById('imgPreviewHidden');
                    if(img){ img.src = reader.result; } 
                    else if(imgHidden) { if(placeholder) placeholder.classList.add('d-none'); imgHidden.classList.remove('d-none'); imgHidden.src = reader.result; }
                }
                reader.readAsDataURL(file);
            }
        }

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