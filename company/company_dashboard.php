<?php
// company/company_dashboard.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');
session_start();

if (!isset($_SESSION['company_id'])) {
    header('Location: company_login.php');
    exit;
}

$comp_id = $_SESSION['company_id'];
$comp_name = isset($_SESSION['company_name']) ? $_SESSION['company_name'] : 'Perusahaan';

// Ambil data lowongan
$sql = "SELECT j.*, 
        (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) as total_pelamar 
        FROM jobs j 
        WHERE j.company_id = ? 
        ORDER BY j.posted_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $comp_id);
$stmt->execute();
$res = $stmt->get_result();

// Hitung statistik
$total_jobs = $res->num_rows;
$total_applicants = 0;
$jobs_data = [];
while($row = $res->fetch_assoc()){
    $total_applicants += $row['total_pelamar'];
    $jobs_data[] = $row;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Dashboard Perusahaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root { --bg-color: #f8fafc; --accent-color: #2563eb; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-color); color: #334155; }
        
        .navbar { background: white; border-bottom: 1px solid #e2e8f0; padding: 1rem 0; }
        .stat-card { background: white; border-radius: 16px; padding: 24px; border: 1px solid #eff6ff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .content-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .status-active { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef9c3; color: #854d0e; }
        .status-inactive { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand text-primary fw-bold" href="#"><i class="fas fa-briefcase me-2"></i>JobFinder <span class="fw-light text-muted ms-1">Business</span></a>
            
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-md-block">Halo, 
                    <!-- NAMA PERUSAHAAN JADI LINK -->
                    <a href="company_profile.php" class="text-decoration-none fw-bold text-dark border-bottom border-primary">
                        <?= htmlspecialchars($comp_name) ?>
                    </a>
                </span>
                
                <!-- TOMBOL PROFIL (YANG KAMU CARI) -->
                <a href="company_profile.php" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                    <i class="fas fa-user-edit me-1"></i> Profil
                </a>

                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Stats -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="stat-card">
                    <h3 class="fw-bold mb-1"><?= $total_jobs ?></h3>
                    <p class="text-muted mb-0 small">Lowongan Diposting</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <h3 class="fw-bold mb-1"><?= $total_applicants ?></h3>
                    <p class="text-muted mb-0 small">Total Pelamar Masuk</p>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Daftar Lowongan</h4>
            <a href="company_post_job.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-plus me-2"></i>Buat Lowongan
            </a>
        </div>

        <?php if(isset($_SESSION['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="content-card">
            <div class="table-responsive">
                <table class="table mb-0 table-hover">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Posisi & Lokasi</th>
                            <th>Status</th>
                            <th>Pelamar</th>
                            <th>Tanggal Posting</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($jobs_data) > 0): ?>
                            <?php foreach ($jobs_data as $row): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($row['title']) ?></div>
                                    <div class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($row['location']) ?></div>
                                </td>
                                <td>
                                    <?php 
                                        if($row['status'] == 'active') echo '<span class="status-badge status-active">Active</span>';
                                        elseif($row['status'] == 'pending') echo '<span class="status-badge status-pending">Pending</span>';
                                        else echo '<span class="status-badge status-inactive">Inactive</span>';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary rounded-pill px-3 py-2"><?= $row['total_pelamar'] ?></span>
                                </td>
                                <td class="text-muted small">
                                    <?= date('d M Y', strtotime($row['posted_at'])) ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="company_view_applicants.php?job_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary fw-bold">Lihat Pelamar</a>
                                        <a href="company_edit_job.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                        <a href="company_delete_job.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Hapus lowongan ini?')"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada lowongan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>