<?php
// 1. Anti-Blank: Nyalakan Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('./config.php');
session_start();

// 2. Cek Login Perusahaan
if (!isset($_SESSION['company_id'])) {
    header('Location: company_login.php');
    exit;
}

$success = '';
$err = '';

// 3. Proses Form saat tombol Simpan ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $location = trim($_POST['location']);
    $category = $_POST['category'];
    $type = $_POST['type'];
    $salary = trim($_POST['salary']);
    $desc = trim($_POST['description']);
    
    // Ambil data dari Session
    $comp_id = $_SESSION['company_id'];
    $comp_name = $_SESSION['company_name']; 
    $status = 'pending'; // Default pending agar diverifikasi admin

    // 4. Handle Upload Logo (Opsional)
    $logo_filename = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($ext, $allowed)) {
            // Generate nama file unik
            $new_name = time() . '_' . uniqid() . '.' . $ext;
            $target_dir = __DIR__ . '/uploads/';
            
            // Buat folder uploads jika belum ada
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $new_name)) {
                $logo_filename = $new_name;
            } else {
                $err = "Gagal mengupload file logo.";
            }
        } else {
            $err = "Format logo harus JPG, PNG, atau GIF.";
        }
    }

    // Jika tidak ada error, simpan ke database
    if (empty($err)) {
        $sql = "INSERT INTO jobs (title, company, company_id, location, category, type, salary, description, logo, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('ssisssssss', $title, $comp_name, $comp_id, $location, $category, $type, $salary, $desc, $logo_filename, $status);
            
            if ($stmt->execute()) {
                echo "<script>alert('Lowongan berhasil dibuat! Status saat ini PENDING menunggu verifikasi Admin.'); window.location='company_dashboard.php';</script>";
                exit;
            } else {
                $err = "Gagal menyimpan ke database: " . $stmt->error;
            }
        } else {
            $err = "Query Error (Mungkin kolom logo/company_id belum ada?): " . $conn->error;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Lowongan Kerja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .card-header { background-color: #343a40; color: white; }
        .form-label { font-weight: 600; font-size: 0.9rem; }
        .form-control:focus, .form-select:focus { border-color: #80bdff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); }
    </style>
</head>
<body>
    <!-- Navbar Sederhana -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="company_dashboard.php">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
            </a>
            <span class="navbar-text text-white">
                <?= htmlspecialchars($_SESSION['company_name'] ?? 'Perusahaan') ?>
            </span>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Judul Halaman -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="text-dark">Tambah Lowongan Kerja</h3>
                </div>

                <?php if ($err): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <strong>Terjadi Kesalahan:</strong> <?= htmlspecialchars($err) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Form Card -->
                <div class="card shadow border-0 rounded-3">
                    <div class="card-header py-3">
                        <h5 class="mb-0 card-title"><i class="fas fa-edit me-2"></i> Form Lowongan Kerja</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" enctype="multipart/form-data">
                            
                            <div class="row">
                                <!-- Kolom Kiri -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-danger">Judul Lowongan *</label>
                                        <input type="text" name="title" class="form-control" placeholder="Contoh: Frontend Developer" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-danger">Nama Perusahaan *</label>
                                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($_SESSION['company_name'] ?? '') ?>" readonly>
                                        <small class="text-muted">Otomatis terisi sesuai akun Anda.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-danger">Lokasi *</label>
                                        <input type="text" name="location" class="form-control" placeholder="Contoh: Jakarta Selatan" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label text-danger">Kategori *</label>
                                        <select name="category" class="form-select" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            <option value="IT">IT & Software</option>
                                            <option value="Marketing">Marketing</option>
                                            <option value="Akuntansi">Akuntansi</option>
                                            <option value="Desain">Desain</option>
                                            <option value="Administrasi">Administrasi</option>
                                            <option value="Engineering">Engineering</option>
                                            <option value="Human Resources">Human Resources</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-danger">Tipe Pekerjaan *</label>
                                        <select name="type" class="form-select" required>
                                            <option value="">-- Pilih Tipe --</option>
                                            <option value="Full Time">Full Time</option>
                                            <option value="Part Time">Part Time</option>
                                            <option value="Contract">Contract</option>
                                            <option value="Internship">Internship</option>
                                            <option value="Freelance">Freelance</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Gaji</label>
                                        <input type="text" name="salary" class="form-control" placeholder="Contoh: IDR 5.000.000 - 8.000.000">
                                        <small class="text-muted">Opsional. Kosongkan jika tidak ingin ditampilkan.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Logo Perusahaan (Opsional)</label>
                                        <input type="file" name="logo" class="form-control" accept="image/*">
                                        <small class="text-muted">Format: JPG, PNG. Maks 2MB.</small>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <label class="form-label text-danger">Deskripsi Pekerjaan *</label>
                                <textarea name="description" class="form-control" rows="6" placeholder="Masukkan deskripsi lengkap tentang pekerjaan ini, kualifikasi, dan tanggung jawab..." required></textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="company_dashboard.php" class="btn btn-secondary px-4">Batal</a>
                                <button type="submit" class="btn btn-primary px-5 fw-bold">
                                    <i class="fas fa-paper-plane me-2"></i> Simpan & Posting
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>