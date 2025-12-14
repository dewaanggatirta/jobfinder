<?php
// company/company_view_applicants.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PATH DIPERBAIKI
require_once('../config.php');
session_start();

if (!isset($_SESSION['company_id'])) { header('Location: company_login.php'); exit; }

$job_id = intval($_GET['job_id'] ?? 0);
$comp_id = $_SESSION['company_id'];

// --- LOGIKA TERIMA/TOLAK ---
if (isset($_GET['action']) && isset($_GET['app_id'])) {
    $action = $_GET['action'];
    $app_id = intval($_GET['app_id']);
    $new_status = ($action == 'accept') ? 'approved' : 'rejected';
    
    $sql_update = "UPDATE applications a 
                   JOIN jobs j ON a.job_id = j.id
                   SET a.status = ? 
                   WHERE a.id = ? AND j.company_id = ?";
                   
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param('sii', $new_status, $app_id, $comp_id);
    
    if ($stmt_update->execute()) {
        header("Location: company_view_applicants.php?job_id=" . $job_id);
        exit;
    }
}

// --- AMBIL DATA ---
$stmt = $conn->prepare("SELECT title FROM jobs WHERE id = ? AND company_id = ?");
$stmt->bind_param('ii', $job_id, $comp_id);
$stmt->execute();
$res_job = $stmt->get_result();

if ($res_job->num_rows === 0) { die("Data tidak ditemukan."); }
$job_data = $res_job->fetch_assoc();

$stmt_app = $conn->prepare("SELECT a.*, u.name, u.email FROM applications a JOIN users u ON a.user_id = u.id WHERE a.job_id = ? ORDER BY a.applied_at DESC");
$stmt_app->bind_param('i', $job_id);
$stmt_app->execute();
$applicants = $stmt_app->get_result();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pelamar: <?= htmlspecialchars($job_data['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .card-pelamar { background: white; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 16px; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .st-approved { background: #dcfce7; color: #166534; }
        .st-rejected { background: #fee2e2; color: #991b1b; }
        .st-pending { background: #fef9c3; color: #854d0e; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top bg-white border-bottom py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="company_dashboard.php"><i class="fas fa-arrow-left me-2"></i> Kembali</a>
            <span class="navbar-text fw-bold text-dark">Pelamar: <?= htmlspecialchars($job_data['title']) ?></span>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if ($applicants->num_rows > 0): ?>
                    <h6 class="text-muted mb-4 fw-bold text-uppercase small">Daftar Kandidat (<?= $applicants->num_rows ?>)</h6>
                    
                    <?php while($row = $applicants->fetch_assoc()): ?>
                    <div class="card-pelamar p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($row['name']) ?></h5>
                                        <?php if($row['status'] == 'approved'): ?>
                                            <span class="status-badge st-approved"><i class="fas fa-check"></i> Diterima</span>
                                        <?php elseif($row['status'] == 'rejected'): ?>
                                            <span class="status-badge st-rejected"><i class="fas fa-times"></i> Ditolak</span>
                                        <?php else: ?>
                                            <span class="status-badge st-pending"><i class="fas fa-clock"></i> Menunggu</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars($row['email']) ?> • <?= date('d M Y, H:i', strtotime($row['applied_at'])) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <!-- PATH CV DIPERBAIKI: Naik satu level ke ../uploads/ -->
                                <?php 
                                    // Asumsi CV diupload user (root/uploads)
                                    $cv_path = '../uploads/' . $row['cv_file']; 
                                ?>
                                <?php if(file_exists($cv_path)): ?>
                                    <a href="<?= $cv_path ?>" target="_blank" class="btn btn-light border btn-sm fw-bold">
                                        <i class="fas fa-file-pdf me-1 text-danger"></i> CV
                                    </a>
                                <?php endif; ?>

                                <?php if($row['status'] == 'pending'): ?>
                                    <a href="company_view_applicants.php?job_id=<?= $job_id ?>&action=accept&app_id=<?= $row['id'] ?>" class="btn btn-success btn-sm fw-bold">Terima</a>
                                    <a href="company_view_applicants.php?job_id=<?= $job_id ?>&action=reject&app_id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm fw-bold">Tolak</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">Belum ada pelamar.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>