<?php
// company/company_edit_job.php
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

$job_id = intval($_GET['id'] ?? 0);
$comp_id = $_SESSION['company_id'];
$err = '';

// 1. Ambil data job saat ini (Pastikan milik perusahaan yg login)
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ? AND company_id = ?");
$stmt->bind_param('ii', $job_id, $comp_id);
$stmt->execute();
$res = $stmt->get_result();
$job = $res->fetch_assoc();

// Jika job tidak ditemukan atau bukan milik perusahaan ini
if (!$job) {
    echo "<script>alert('Data tidak ditemukan atau Anda tidak berhak mengedit lowongan ini!'); window.location='company_dashboard.php';</script>";
    exit;
}

// 2. Proses Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $location = trim($_POST['location']);
    $category = $_POST['category'];
    $type = $_POST['type'];
    $salary = trim($_POST['salary']);
    $desc = trim($_POST['description']);
    
    // Validasi sederhana
    if (empty($title) || empty($location) || empty($desc)) {
        $err = "Judul, Lokasi, dan Deskripsi wajib diisi.";
    } else {
        $sql_update = "UPDATE jobs SET title=?, location=?, category=?, type=?, salary=?, description=? WHERE id=? AND company_id=?";
        $stmt_up = $conn->prepare($sql_update);
        $stmt_up->bind_param('ssssssii', $title, $location, $category, $type, $salary, $desc, $job_id, $comp_id);
        
        if ($stmt_up->execute()) {
            $_SESSION['msg'] = "Lowongan berhasil diperbarui!";
            header('Location: company_dashboard.php');
            exit;
        } else {
            $err = "Gagal mengupdate data: " . $stmt_up->error;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Edit Lowongan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .form-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .form-label { font-weight: 600; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card p-4 p-md-5">
                    <h3 class="fw-bold mb-4 text-center">Edit Lowongan</h3>
                    
                    <?php if($err): ?>
                        <div class="alert alert-danger rounded-3"><?= htmlspecialchars($err) ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Judul Posisi</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($job['title']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lokasi</label>
                                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($job['location']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-select" required>
                                    <option value="IT & Software" <?= $job['category'] == 'IT & Software' ? 'selected' : '' ?>>IT & Software</option>
                                    <option value="Marketing" <?= $job['category'] == 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                                    <option value="Desain" <?= $job['category'] == 'Desain' ? 'selected' : '' ?>>Desain</option>
                                    <option value="Akuntansi" <?= $job['category'] == 'Akuntansi' ? 'selected' : '' ?>>Akuntansi</option>
                                    <option value="Administrasi" <?= $job['category'] == 'Administrasi' ? 'selected' : '' ?>>Administrasi</option>
                                    <option value="Engineering" <?= $job['category'] == 'Engineering' ? 'selected' : '' ?>>Engineering</option>
                                    <option value="Human Resources" <?= $job['category'] == 'Human Resources' ? 'selected' : '' ?>>Human Resources</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipe</label>
                                <select name="type" class="form-select" required>
                                    <option value="Full Time" <?= $job['type'] == 'Full Time' ? 'selected' : '' ?>>Full Time</option>
                                    <option value="Part Time" <?= $job['type'] == 'Part Time' ? 'selected' : '' ?>>Part Time</option>
                                    <option value="Contract" <?= $job['type'] == 'Contract' ? 'selected' : '' ?>>Contract</option>
                                    <option value="Internship" <?= $job['type'] == 'Internship' ? 'selected' : '' ?>>Internship</option>
                                    <option value="Freelance" <?= $job['type'] == 'Freelance' ? 'selected' : '' ?>>Freelance</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gaji</label>
                                <input type="text" name="salary" class="form-control" value="<?= htmlspecialchars($job['salary']) ?>">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($job['description']) ?></textarea>
                            </div>
                            
                            <div class="col-12 mt-4 d-flex justify-content-between">
                                <a href="company_dashboard.php" class="btn btn-secondary px-4 fw-bold">Batal</a>
                                <button type="submit" class="btn btn-warning fw-bold px-5 text-white">Update Lowongan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>