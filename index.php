<?php
// user/index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('./config.php');
session_start();

$locations = ['Jakarta','Bandung','Bali','Surabaya','Yogyakarta'];
$categories = ['IT','Akuntansi','Marketing','Desain','Administrasi','Engineering','Human Resources'];

$loc = $_GET['location'] ?? '';
$cat = $_GET['category'] ?? '';
$q   = $_GET['q'] ?? '';

// Query hanya lowongan ACTIVE
$sql = "SELECT id, title, company, location, category, type, salary, posted_at, IFNULL(logo,'') AS logo FROM jobs WHERE status = 'active'";
$params = [];
$types = '';

if ($q !== '') {
    $sql .= " AND (title LIKE ? OR company LIKE ?)";
    $like = "%{$q}%";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}
if ($loc !== '') {
    $sql .= " AND location = ?";
    $params[] = $loc;
    $types .= 's';
}
if ($cat !== '') {
    $sql .= " AND category = ?";
    $params[] = $cat;
    $types .= 's';
}

$sql .= " ORDER BY posted_at DESC";

// Eksekusi Query
$res = false;
if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $bind_names[] = $types;
        for ($i=0; $i<count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $conn->query($sql);
    }
} else {
    $res = $conn->query($sql);
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JobFinder - Temukan Karir Impian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
        }
        .navbar-brand {
            font-weight: 800;
            color: var(--accent-color) !important;
            letter-spacing: -0.5px;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 80px 0 100px;
            margin-bottom: -60px; /* Agar search bar overlap */
            border-radius: 0 0 30px 30px;
        }
        
        /* Search Bar */
        .search-container {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.1);
        }
        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            padding: 12px 15px;
            border-radius: 10px;
            font-size: 0.95rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        /* Job Card */
        .job-card {
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        .job-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.08);
            border-color: var(--accent-color);
        }
        .logo-box {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            object-fit: contain;
            border: 1px solid #e2e8f0;
        }
        .tag-badge {
            background: #eff6ff;
            color: var(--accent-color);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .salary-text {
            font-weight: 600;
            color: #059669;
            font-size: 0.9rem;
        }
        
        /* Sidebar */
        .sidebar-card {
            background: white;
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        
        /* Button */
        .btn-primary {
            background-color: var(--accent-color);
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-briefcase me-2"></i>JobFinder</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item me-3">
                            <span class="text-muted small">Halo,</span> <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-danger btn-sm rounded-pill px-3" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2">
                            <a class="nav-link fw-semibold text-dark" href="login.php">Masuk</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm rounded-pill px-4" href="register.php">Daftar</a>
                        </li>
                        <!-- Link Admin/Perusahaan -->
                        <li class="nav-item ms-2 border-start ps-2">
                            <a class="btn btn-sm text-secondary" href="company_login.php"><i class="fas fa-building"></i> Perusahaan</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container mt-5">
            <h1 class="display-4 fw-bold mb-3">Temukan Karir Impianmu</h1>
            <p class="lead text-white-50 mb-0">Ribuan lowongan kerja dari perusahaan ternama menunggu Anda.</p>
        </div>
    </section>

    <!-- Search Bar Area -->
    <div class="container position-relative" style="z-index: 10;">
        <div class="search-container mx-auto col-lg-10">
            <form class="row g-3" method="get">
                <div class="col-lg-5 col-md-12">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input name="q" class="form-control border-start-0 ps-0" placeholder="Cari posisi (misal: Web Developer)" value="<?= htmlspecialchars($q) ?>">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                        <select name="location" class="form-select border-start-0 ps-0">
                            <option value="">Semua Lokasi</option>
                            <?php foreach ($locations as $l): ?>
                                <option value="<?= htmlspecialchars($l) ?>" <?= ($loc == $l) ? 'selected' : '' ?>><?= htmlspecialchars($l) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <select name="category" class="form-select">
                        <option value="">Kategori</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= ($cat == $c) ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-12">
                    <button class="btn btn-primary w-100 h-100">Cari Kerja</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5 mt-4">
        <div class="row">
            <!-- Job List -->
            <div class="col-lg-8">
                <h5 class="mb-4 fw-bold text-dark"><i class="fas fa-fire text-warning me-2"></i>Lowongan Terbaru</h5>
                
                <?php if ($res && $res->num_rows > 0): ?>
                    <?php while ($job = $res->fetch_assoc()): ?>
                    <div class="job-card p-4">
                        <div class="d-flex align-items-start">
                            <!-- Logo -->
                            <?php if (!empty($job['logo']) && file_exists('./admin/uploads/'.$job['logo'])): ?>
                                <img src="./admin/uploads/<?= htmlspecialchars($job['logo']) ?>" class="logo-box flex-shrink-0 me-3" alt="Logo">
                            <?php elseif (!empty($job['logo']) && file_exists('./company/uploads/'.$job['logo'])): ?> 
                                <!-- Fallback path check for company uploads -->
                                <img src="./company/uploads/<?= htmlspecialchars($job['logo']) ?>" class="logo-box flex-shrink-0 me-3" alt="Logo">
                            <?php else: ?>
                                <div class="logo-box flex-shrink-0 me-3">
                                    <i class="fas fa-building text-secondary fa-lg"></i>
                                </div>
                            <?php endif; ?>

                            <!-- Content -->
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold text-dark">
                                            <a href="job_detail.php?id=<?= $job['id'] ?>" class="text-decoration-none text-dark stretched-link"><?= htmlspecialchars($job['title']) ?></a>
                                        </h5>
                                        <p class="mb-2 text-muted small">
                                            <i class="fas fa-building me-1"></i> <?= htmlspecialchars($job['company']) ?> &nbsp;•&nbsp; 
                                            <i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($job['location']) ?>
                                        </p>
                                    </div>
                                    <?php if(!empty($job['type'])): ?>
                                    <span class="tag-badge"><?= htmlspecialchars($job['type']) ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3 border-top pt-3">
                                    <div class="d-flex gap-3">
                                        <span class="small text-muted"><i class="fas fa-layer-group me-1"></i> <?= htmlspecialchars($job['category']) ?></span>
                                        <span class="salary-text"><i class="fas fa-money-bill-wave me-1"></i> <?= htmlspecialchars($job['salary']) ?></span>
                                    </div>
                                    <small class="text-muted"><i class="far fa-clock me-1"></i> <?= date('d M Y', strtotime($job['posted_at'])) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" class="mb-3 opacity-50">
                        <h5 class="text-muted">Tidak ada lowongan ditemukan.</h5>
                        <p class="text-muted small">Coba ubah kata kunci pencarian Anda.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Card Tips -->
                <div class="sidebar-card card p-4 mb-4">
                    <h6 class="fw-bold mb-3">Tips Karir</h6>
                    <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-2">
                        <li><i class="fas fa-check-circle text-success me-2"></i> Perbarui CV secara berkala</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> Tulis Cover Letter yang spesifik</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> Riset perusahaan sebelum melamar</li>
                    </ul>
                </div>
                
                <!-- Card Perusahaan Populer -->
                <div class="sidebar-card card p-4">
                    <h6 class="fw-bold mb-3">Perusahaan Terpopuler</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-light text-dark border">Tokopedia</span>
                        <span class="badge bg-light text-dark border">Gojek</span>
                        <span class="badge bg-light text-dark border">Traveloka</span>
                        <span class="badge bg-light text-dark border">Shopee</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 mt-5 border-top text-center">
        <div class="container">
            <p class="text-muted small mb-0">&copy; <?= date('Y') ?> JobFinder Indonesia. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>