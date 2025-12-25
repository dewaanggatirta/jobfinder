<?php
// dashboard.php - FINAL (Dengan Pesan Feedback)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('./config.php');
session_start();

if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// 1. Ambil Profil (Termasuk Foto)
$stmt_user = $conn->prepare("SELECT name, email, created_at, photo FROM users WHERE id = ?");
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$res_user = $stmt_user->get_result();
$user_data = $res_user->fetch_assoc();

// 2. Ambil Riwayat Lamaran (Termasuk kolom FEEDBACK)
$sql = "SELECT a.id as app_id, a.cv_file, a.applied_at, a.status as app_status, a.feedback,
               j.title, j.company, j.location, j.logo, j.id as job_id
        FROM applications a 
        JOIN jobs j ON a.job_id = j.id 
        WHERE a.user_id = ? 
        ORDER BY a.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Dashboard - JobFinder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root { --primary-color: #0f172a; --accent-color: #2563eb; --bg-color: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-color); color: #334155; }
        
        .navbar { background: white; border-bottom: 1px solid #e2e8f0; padding: 1rem 0; }
        .profile-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 30px; margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); position: relative; }
        .avatar-circle { width: 80px; height: 80px; background: #eff6ff; color: var(--accent-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; border: 4px solid #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1); object-fit: cover; }
        
        .app-card { background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 24px; margin-bottom: 20px; transition: all 0.2s; }
        .app-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); border-color: var(--accent-color); }
        .job-logo { width: 60px; height: 60px; border-radius: 12px; object-fit: contain; border: 1px solid #e2e8f0; padding: 4px; background: white; }
        
        .badge-status { padding: 8px 16px; border-radius: 30px; font-weight: 600; font-size: 0.8rem; }
        .st-pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .st-approved { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .st-rejected { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php"><i class="fas fa-briefcase me-2"></i>JobFinder</a>
            <div class="d-flex align-items-center gap-3">
                <span class="d-none d-md-block text-muted small">Halo, <strong><?= htmlspecialchars($user_name) ?></strong></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <div class="profile-card">
                    <div class="position-absolute top-0 end-0 m-4">
                        <a href="edit_profile.php" class="btn btn-light btn-sm rounded-pill border fw-bold text-primary shadow-sm"><i class="fas fa-user-edit me-1"></i> Edit Profil</a>
                    </div>
                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <?php if(!empty($user_data['photo']) && file_exists(__DIR__.'/uploads/'.$user_data['photo'])): ?>
                            <img src="./uploads/<?= htmlspecialchars($user_data['photo']) ?>" class="avatar-circle">
                        <?php else: ?>
                            <div class="avatar-circle"><?= strtoupper(substr($user_data['name'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <div>
                            <h3 class="fw-bold mb-1"><?= htmlspecialchars($user_data['name']) ?></h3>
                            <div class="text-secondary mb-2"><i class="fas fa-envelope me-2"></i> <?= htmlspecialchars($user_data['email']) ?></div>
                            <div class="small text-muted"><i class="fas fa-calendar-alt me-2"></i> Bergabung sejak <?= date('d M Y', strtotime($user_data['created_at'])) ?></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Riwayat Lamaran Saya</h4>
                    <a href="index.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i class="fas fa-search me-2"></i>Cari Lowongan</a>
                </div>

                <?php if ($res->num_rows > 0): ?>
                    <?php while($r = $res->fetch_assoc()): ?>
                    <div class="app-card">
                        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                            <div class="d-flex align-items-start gap-3">
                                <?php 
                                    $logo_path = null;
                                    if (!empty($r['logo'])) {
                                        if (file_exists('./admin/uploads/logos/' . $r['logo'])) $logo_path = './admin/uploads/logos/' . $r['logo'];
                                        elseif (file_exists('./company/uploads/' . $r['logo'])) $logo_path = './company/uploads/' . $r['logo'];
                                    }
                                ?>
                                <?php if ($logo_path): ?>
                                    <img src="<?= htmlspecialchars($logo_path) ?>" class="job-logo">
                                <?php else: ?>
                                    <div class="job-logo d-flex align-items-center justify-content-center bg-light text-secondary"><i class="fas fa-building"></i></div>
                                <?php endif; ?>

                                <div>
                                    <h5 class="fw-bold mb-1"><a href="job_detail.php?id=<?= $r['job_id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($r['title']) ?></a></h5>
                                    <div class="text-muted small"><?= htmlspecialchars($r['company']) ?> • <?= htmlspecialchars($r['location']) ?></div>
                                    <div class="text-muted small mt-1"><i class="far fa-calendar-alt me-1"></i> Melamar: <?= date('d M Y, H:i', strtotime($r['applied_at'])) ?></div>
                                    
                                    <?php if(!empty($r['feedback'])): ?>
                                        <div class="mt-3 p-3 rounded-3 border <?php echo ($r['app_status'] == 'approved') ? 'bg-success-subtle border-success-subtle text-success-emphasis' : 'bg-danger-subtle border-danger-subtle text-danger-emphasis'; ?>">
                                            <div class="d-flex gap-2">
                                                <i class="fas fa-comment-alt mt-1"></i>
                                                <div>
                                                    <strong class="d-block small mb-1">Pesan dari Perusahaan:</strong>
                                                    <span class="small fst-italic">"<?= htmlspecialchars($r['feedback']) ?>"</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="text-end">
                                <div class="mb-2">
                                    <?php if($r['app_status'] == 'approved'): ?>
                                        <span class="badge-status st-approved"><i class="fas fa-check-circle me-1"></i> Diterima</span>
                                    <?php elseif($r['app_status'] == 'rejected'): ?>
                                        <span class="badge-status st-rejected"><i class="fas fa-times-circle me-1"></i> Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge-status st-pending"><i class="fas fa-clock me-1"></i> Menunggu</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <a href="./uploads/<?= urlencode($r['cv_file']) ?>" target="_blank" class="btn btn-sm btn-light text-primary border fw-bold rounded-pill"><i class="fas fa-file-pdf me-1"></i> Cek CV</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="bg-white p-5 rounded-4 border shadow-sm d-inline-block">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-50">
                            <h5>Belum ada lamaran.</h5>
                            <a href="index.php" class="btn btn-primary rounded-pill px-4 mt-3">Mulai Melamar</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>