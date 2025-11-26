<?php
// 1. Anti-Blank: Nyalakan Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('./config.php');
session_start();

// 2. Cek Login
if (!isset($_SESSION['company_id'])) {
    header('Location: company_login.php');
    exit;
}

$job_id = intval($_GET['job_id'] ?? 0);
$comp_id = $_SESSION['company_id'];
$comp_name = $_SESSION['company_name'] ?? 'Perusahaan';

$error_msg = '';
$applicants = null;
$job_title = '';

try {
    // 3. Validasi: Cek apakah lowongan ini milik perusahaan yang login
    $check_sql = "SELECT title FROM jobs WHERE id = ? AND company_id = ?";
    $stmt = $conn->prepare($check_sql);
    
    if (!$stmt) {
        throw new Exception("Gagal Prepare Query Job: " . $conn->error);
    }

    $stmt->bind_param('ii', $job_id, $comp_id);
    $stmt->execute();
    $res_job = $stmt->get_result();

    if ($res_job->num_rows === 0) {
        die('<div class="alert alert-danger m-4">Akses Ditolak! Lowongan tidak ditemukan atau bukan milik Anda. <a href="company_dashboard.php">Kembali</a></div>');
    }

    $job_data = $res_job->fetch_assoc();
    $job_title = $job_data['title'];

    // 4. Ambil Data Pelamar
    $sql_app = "SELECT a.*, u.name as pelamar_name, u.email as pelamar_email 
                FROM applications a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.job_id = ? 
                ORDER BY a.applied_at DESC";
    
    $stmt_app = $conn->prepare($sql_app);
    
    if (!$stmt_app) {
        throw new Exception("Gagal Prepare Query Applicants: " . $conn->error);
    }

    $stmt_app->bind_param('i', $job_id);
    $stmt_app->execute();
    $applicants = $stmt_app->get_result();

} catch (Exception $e) {
    $error_msg = $e->getMessage();
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pelamar: <?= htmlspecialchars($job_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .table td { vertical-align: middle; }
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
                <?= htmlspecialchars($comp_name) ?>
            </span>
        </div>
    </nav>

    <div class="container pb-5">
        
        <!-- Jika ada Error Sistem -->
        <?php if ($error_msg): ?>
            <div class="alert alert-danger">
                <h4>Terjadi Kesalahan!</h4>
                <p><?= htmlspecialchars($error_msg) ?></p>
            </div>
        <?php endif; ?>

        <div class="card shadow border-0 rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-users me-2"></i> Pelamar untuk posisi: <strong><?= htmlspecialchars($job_title) ?></strong>
                </h5>
                <?php if ($applicants && $applicants->num_rows > 0): ?>
                    <span class="badge bg-info text-dark"><?= $applicants->num_rows ?> Pelamar</span>
                <?php endif; ?>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">No</th>
                                <th>Nama Pelamar</th>
                                <th>Email</th>
                                <th>Tanggal Melamar</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($applicants && $applicants->num_rows > 0): ?>
                                <?php $no = 1; while($row = $applicants->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4"><?= $no++ ?></td>
                                    <td class="fw-bold text-dark">
                                        <?= htmlspecialchars($row['pelamar_name']) ?>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($row['pelamar_email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($row['pelamar_email']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?= date('d M Y, H:i', strtotime($row['applied_at'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php 
                                            // Cek file ada atau tidak (opsional, agar link tidak rusak)
                                            $file_path = './uploads/' . $row['cv_file'];
                                            $file_exists = file_exists($file_path);
                                        ?>
                                        
                                        <a href="<?= $file_path ?>" 
                                           class="btn btn-sm btn-outline-danger <?= !$file_exists ? 'disabled' : '' ?>" 
                                           target="_blank"
                                           title="Download CV PDF">
                                            <i class="fas fa-file-pdf me-1"></i> Lihat CV
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="py-4">
                                            <i class="fas fa-user-slash fa-3x mb-3 text-secondary"></i><br>
                                            <h5>Belum ada pelamar masuk.</h5>
                                            <small>Tunggu beberapa saat hingga pencari kerja melamar.</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>