<?php
// company/company_post_job.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PATH DIPERBAIKI
require_once('../config.php');
session_start();

if (!isset($_SESSION['company_id'])) {
    header('Location: company_login.php');
    exit;
}

$success = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $location = trim($_POST['location']);
    $category = $_POST['category'];
    $type = $_POST['type'];
    $salary = trim($_POST['salary']);
    $desc = trim($_POST['description']);
    
    $comp_id = $_SESSION['company_id'];
    $comp_name = $_SESSION['company_name']; 
    $status = 'pending';

    $logo_filename = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($ext, $allowed)) {
            $new_name = time() . '_' . uniqid() . '.' . $ext;
            // Upload ke folder 'uploads' di dalam folder company
            $target_dir = __DIR__ . '/uploads/';
            
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $new_name)) {
                $logo_filename = $new_name;
            } else {
                $err = "Gagal mengupload file logo.";
            }
        }
    }

    if (empty($err)) {
        $sql = "INSERT INTO jobs (title, company, company_id, location, category, type, salary, description, logo, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('ssisssssss', $title, $comp_name, $comp_id, $location, $category, $type, $salary, $desc, $logo_filename, $status);
            
            if ($stmt->execute()) {
                echo "<script>alert('Lowongan berhasil dibuat! Status saat ini PENDING menunggu verifikasi Admin.'); window.location='company_dashboard.php';</script>";
                exit;
            } else {
                $err = "Gagal menyimpan: " . $stmt->error;
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pasang Lowongan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .form-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; }
        .form-label { font-weight: 600; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card p-4">
                    <h3 class="fw-bold mb-4 text-center">Buat Lowongan Baru</h3>
                    <?php if($err): ?><div class="alert alert-danger"><?=$err?></div><?php endif; ?>

                    <form method="post" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Judul Posisi</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lokasi</label>
                                <input type="text" name="location" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-select" required>
                                    <option value="IT">IT & Software</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Desain">Desain</option>
                                    <option value="Akuntansi">Akuntansi</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipe</label>
                                <select name="type" class="form-select" required>
                                    <option value="Full Time">Full Time</option>
                                    <option value="Part Time">Part Time</option>
                                    <option value="Internship">Internship</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gaji (Opsional)</label>
                                <input type="text" name="salary" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Logo</label>
                                <input type="file" name="logo" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="5" required></textarea>
                            </div>
                            <div class="col-12 mt-4 d-flex justify-content-between">
                                <a href="company_dashboard.php" class="btn btn-secondary">Batal</a>
                                <button class="btn btn-primary fw-bold px-5">Posting</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>