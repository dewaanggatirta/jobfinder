<?php
// company/company_profile.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan path config benar (naik satu folder)
require_once('../config.php');
session_start();

// Cek Login
if (!isset($_SESSION['company_id'])) {
    header('Location: company_login.php');
    exit;
}

$comp_id = $_SESSION['company_id'];
$success = '';
$err = '';

// 1. PROSES UPDATE PROFIL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['company_name']);
    
    // --- PROSES GANTI LOGO ---
    $logo_sql_part = ""; 
    // Parameter awal hanya Nama Perusahaan
    $params = [$name];
    $types = "s";

    // Cek jika ada file logo yang diupload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        
        if (in_array($ext, $allowed)) {
            // Buat nama file unik
            $new_name = "logo_" . time() . "_" . uniqid() . "." . $ext;
            
            // Simpan di folder uploads dalam folder company
            $target_dir = __DIR__ . '/uploads/';
            
            // Buat folder jika belum ada
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $new_name)) {
                // Tambahkan query update logo
                $logo_sql_part = ", logo = ?";
                $params[] = $new_name;
                $types .= "s";
                
                // Update session logo (opsional)
                $_SESSION['company_logo'] = $new_name;
            } else {
                $err = "Gagal upload logo baru.";
            }
        } else {
            $err = "Format logo harus JPG atau PNG.";
        }
    }
    // -------------------------

    if (empty($err)) {
        // Tambahkan ID ke params untuk WHERE clause
        $params[] = $comp_id;
        $types .= "i";

        // Query Update
        $sql = "UPDATE companies SET company_name = ? $logo_sql_part WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $success = "Profil berhasil diperbarui!";
            $_SESSION['company_name'] = $name; // Update nama di session agar navbar berubah
        } else {
            $err = "Gagal update database: " . $stmt->error;
        }
    }
}

// 2. AMBIL DATA PERUSAHAAN SAAT INI (Untuk ditampilkan di form)
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- CSS Modern -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #334155; }
        
        .form-card { 
            background: white; border-radius: 16px; border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); 
        }
        
        .logo-preview { 
            width: 150px; height: 150px; object-fit: contain; border-radius: 12px; 
            border: 2px dashed #cbd5e1; padding: 5px; background: #fff;
        }
        
        .form-control { padding: 12px; border-radius: 10px; }
        .btn-primary { padding: 12px; border-radius: 10px; font-weight: 600; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom py-3 mb-4 sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="company_dashboard.php">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
            </a>
            <span class="navbar-text fw-bold text-dark">Edit Profil Perusahaan</span>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                
                <!-- Pesan Sukses/Error -->
                <?php if($success): ?>
                    <div class="alert alert-success rounded-3 d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i> <?= $success ?>
                    </div>
                <?php endif; ?>
                <?php if($err): ?>
                    <div class="alert alert-danger rounded-3 d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= $err ?>
                    </div>
                <?php endif; ?>

                <div class="form-card p-4 p-md-5">
                    <form method="post" enctype="multipart/form-data">
                        
                        <!-- Bagian Ganti Logo -->
                        <div class="text-center mb-5">
                            <label class="form-label d-block fw-bold mb-3 text-secondary">Logo Perusahaan Saat Ini</label>
                            
                            <?php if(!empty($comp['logo']) && file_exists(__DIR__.'/uploads/'.$comp['logo'])): ?>
                                <img src="./uploads/<?= htmlspecialchars($comp['logo']) ?>" class="logo-preview mb-3 shadow-sm">
                            <?php else: ?>
                                <div class="logo-preview d-inline-flex align-items-center justify-content-center mb-3 text-muted bg-light">
                                    <i class="fas fa-image fa-2x opacity-25"></i>
                                    <span class="ms-2 small">No Logo</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="w-75 mx-auto">
                                <label class="form-label small text-muted mb-1">Ganti Logo (Opsional):</label>
                                <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                                <div class="form-text small">Format: JPG, PNG. Maks 2MB.</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-secondary">Nama Perusahaan</label>
                            <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($comp['company_name']) ?>" required>
                        </div>

                        <!-- Data lain (Alamat, Web, dll) sudah dihapus sesuai permintaan -->

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>