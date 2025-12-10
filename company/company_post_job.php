<?php
// company/company_post_job.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan path config benar (naik satu folder)
require_once('../config.php');
session_start();

// Cek apakah user sudah login sebagai perusahaan
if (!isset($_SESSION['company_id'])) {
    header('Location: company_login.php');
    exit;
}

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

    // 1. AMBIL LOGO DARI PROFIL PERUSAHAAN (Otomatis)
    $stmt_logo = $conn->prepare("SELECT logo FROM companies WHERE id = ?");
    $stmt_logo->bind_param('i', $comp_id);
    $stmt_logo->execute();
    $res_logo = $stmt_logo->get_result();
    $row_logo = $res_logo->fetch_assoc();
    
    // Simpan nama file logo ke variabel (bisa null jika belum upload di profil)
    $logo_to_use = $row_logo['logo'] ?? null;

    // 2. SIMPAN LOWONGAN KE DATABASE
    $sql = "INSERT INTO jobs (title, company, company_id, location, category, type, salary, description, logo, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param('ssisssssss', $title, $comp_name, $comp_id, $location, $category, $type, $salary, $desc, $logo_to_use, $status);
        
        if ($stmt->execute()) {
            echo "<script>alert('Lowongan berhasil dibuat! Menggunakan logo dari profil Anda.'); window.location='company_dashboard.php';</script>";
            exit;
        } else {
            $err = "Gagal menyimpan: " . $stmt->error;
        }
    } else {
        $err = "Database Error: " . $conn->error;
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
                    
                    <!-- Info bahwa logo otomatis -->
                    <div class="alert alert-info border-0 small">
                        <i class="fas fa-info-circle me-1"></i> Logo lowongan akan otomatis menggunakan <strong>Logo Profil Perusahaan</strong>. 
                        <a href="company_profile.php" class="fw-bold text-decoration-none">Ganti logo di sini</a>.
                    </div>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Posisi</label>
                            <input type="text" name="title" class="form-control" required placeholder="Contoh: Staff Admin">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Lokasi</label>
                                <input type="text" name="location" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Kategori</label>
                                <select name="category" class="form-select" required>
                                    <option value="IT & Software">IT & Software</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Desain">Desain</option>
                                    <option value="Akuntansi">Akuntansi</option>
                                    <option value="Administrasi">Administrasi</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Human Resources">Human Resources</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tipe</label>
                                <select name="type" class="form-select" required>
                                    <option value="Full Time">Full Time</option>
                                    <option value="Part Time">Part Time</option>
                                    <option value="Internship">Internship</option>
                                    <option value="Freelance">Freelance</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Gaji</label>
                                <input type="text" name="salary" class="form-control" placeholder="Opsional">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="5" required></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="company_dashboard.php" class="btn btn-secondary">Batal</a>
                            <button class="btn btn-primary fw-bold px-5">Posting</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>