<?php
require_once('./config.php'); 
session_start();

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT * FROM jobs WHERE id = ?'); 
$stmt->bind_param('i',$id); 
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc(); 

if(!$job){ header('Location: index.php'); exit; }
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=htmlspecialchars($job['title'])?> - JobFinder</title>
    
    <!-- CSS Modern -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root { 
            --primary-color: #0f172a; 
            --accent-color: #2563eb; 
            --bg-color: #f8fafc; 
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-color); 
            color: #334155; 
        }
        
        /* Header Section */
        .job-header {
            background: white;
            padding: 40px 0;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 30px;
        }
        
        /* Logo Styling */
        .logo-placeholder {
            width: 80px; height: 80px;
            background: #eff6ff; color: var(--accent-color);
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 2rem; border: 1px solid #e2e8f0;
        }
        .job-logo {
            width: 80px; height: 80px; object-fit: contain;
            border-radius: 16px; border: 1px solid #e2e8f0; padding: 4px; background: white;
        }
        
        /* Cards */
        .content-card {
            background: white; border-radius: 16px; border: 1px solid #e2e8f0;
            padding: 32px; margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }
        
        /* Buttons */
        .btn-apply {
            background-color: var(--accent-color); color: white;
            padding: 14px; border-radius: 12px; font-weight: 700; width: 100%;
            transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }
        .btn-apply:hover { 
            background-color: #1d4ed8; transform: translateY(-2px); color: white;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }
        
        /* Sidebar Icons */
        .meta-icon {
            width: 40px; height: 40px; background: #f8fafc; color: #64748b;
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            margin-right: 15px; font-size: 1.1rem;
        }
        .badge-custom {
            background: #eff6ff; color: var(--accent-color);
            padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <!-- Navbar Simple -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <i class="fas fa-arrow-left me-2"></i>JobFinder
            </a>
            <div class="ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="text-muted small me-2">Halo,</span> <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-bold">Masuk</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Header Detail Lowongan -->
    <div class="job-header">
        <div class="container">
            <div class="d-flex align-items-center gap-4">
                <!-- Logika Tampilkan Logo -->
                <?php 
                    $logo_path = null;
                    if (!empty($job['logo'])) {
                        // Cek di folder company dulu
                        if (file_exists('./company/uploads/' . $job['logo'])) {
                            $logo_path = './company/uploads/' . $job['logo'];
                        } 
                        // Cek fallback di folder admin
                        elseif (file_exists('./admin/uploads/' . $job['logo'])) {
                            $logo_path = './admin/uploads/' . $job['logo'];
                        }
                    }
                ?>
                
                <?php if($logo_path): ?>
                    <img src="<?= htmlspecialchars($logo_path) ?>" class="job-logo" alt="Logo Perusahaan">
                <?php else: ?>
                    <div class="logo-placeholder"><i class="fas fa-building"></i></div>
                <?php endif; ?>

                <div>
                    <h2 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($job['title']) ?></h2>
                    <div class="text-muted d-flex flex-wrap gap-3 small mt-2">
                        <span><i class="fas fa-building me-1 text-primary"></i> <?= htmlspecialchars($job['company']) ?></span>
                        <span><i class="fas fa-map-marker-alt me-1 text-danger"></i> <?= htmlspecialchars($job['location']) ?></span>
                        <span><i class="far fa-clock me-1 text-success"></i> Diposting <?= date('d M Y', strtotime($job['posted_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Konten Utama -->
    <div class="container pb-5">
        <div class="row">
            
            <!-- Kolom Kiri: Deskripsi -->
            <div class="col-lg-8">
                <div class="content-card">
                    <h5 class="fw-bold mb-4 border-bottom pb-3">Deskripsi Pekerjaan</h5>
                    <div class="text-secondary" style="line-height: 1.8; white-space: pre-line; font-size: 0.95rem;">
                        <?= htmlspecialchars($job['description']) ?>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Sidebar Informasi -->
            <div class="col-lg-4">
                <div class="content-card">
                    <h5 class="fw-bold mb-4">Ringkasan Posisi</h5>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="meta-icon"><i class="fas fa-money-bill-wave"></i></div>
                        <div>
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Gaji</small>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($job['salary'] ?: 'Tidak ditampilkan') ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="meta-icon"><i class="fas fa-briefcase"></i></div>
                        <div>
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Tipe</small>
                            <span class="badge-custom"><?= htmlspecialchars($job['type']) ?></span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-4">
                        <div class="meta-icon"><i class="fas fa-layer-group"></i></div>
                        <div>
                            <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Kategori</small>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($job['category']) ?></span>
                        </div>
                    </div>

                    <hr class="my-4">

                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- Tombol Jika Sudah Login -->
                        <a href="apply.php?job_id=<?= $job['id'] ?>" class="btn btn-apply mb-3">
                            Lamar Sekarang <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary w-100 rounded-pill fw-bold py-2">
                            <i class="fas fa-columns me-2"></i> Dashboard Pelamar
                        </a>
                    <?php else: ?>
                        <!-- Tombol Jika Belum Login -->
                        <div class="alert alert-info small mb-3 border-0 bg-light text-muted">
                            <i class="fas fa-info-circle me-1"></i> Anda harus login untuk melamar.
                        </div>
                        <a href="login.php" class="btn btn-apply">
                            Masuk untuk Melamar
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Tips Singkat -->
                <div class="p-4 rounded-4 bg-light border border-light-subtle">
                    <h6 class="fw-bold mb-2"><i class="fas fa-lightbulb text-warning me-2"></i>Tips</h6>
                    <p class="small text-muted mb-0">Pastikan profil dan CV Anda sudah diperbarui sebelum melamar pekerjaan ini.</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>