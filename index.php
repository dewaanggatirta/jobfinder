<?php
// index.php (ROOT FOLDER)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('./config.php');
session_start();

// --- 1. HITUNG STATISTIK REAL-TIME ---
$res_count_jobs = $conn->query("SELECT COUNT(*) FROM jobs WHERE status = 'active'");
$count_jobs = $res_count_jobs->fetch_row()[0];

$res_count_comp = $conn->query("SELECT COUNT(*) FROM companies");
$count_companies = $res_count_comp->fetch_row()[0];

$res_count_users = $conn->query("SELECT COUNT(*) FROM users");
$count_users = $res_count_users->fetch_row()[0];
// -------------------------------------

$locations = ['Jakarta','Bandung','Bali','Surabaya','Yogyakarta'];

// Kategori disesuaikan dengan database
$categories = [
    'IT & Software', 
    'Akuntansi',
    'Marketing',
    'Desain',
    'Administrasi',
    'Engineering',
    'Human Resources'
];

$loc = $_GET['location'] ?? '';
$cat = $_GET['category'] ?? '';
$q   = $_GET['q'] ?? '';

// Query hanya lowongan ACTIVE
$sql = "SELECT id, title, company, location, category, type, salary, posted_at, IFNULL(logo,'') AS logo FROM jobs WHERE status = 'active'";
$params = [];
$types = '';

// LOGIKA PENCARIAN
if ($q !== '') {
    $sql .= " AND (title LIKE ? OR company LIKE ? OR location LIKE ?)";
    $like = "%{$q}%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #0f172a; --accent-color: #2563eb; --bg-color: #f1f5f9; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-color); color: #334155; }
        .navbar { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0,0,0,0.05); padding: 15px 0; }
        .navbar-brand { font-weight: 800; color: var(--accent-color) !important; letter-spacing: -0.5px; font-size: 1.5rem; }
        .hero-section {
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.7)), url('https://images.unsplash.com/photo-1497215728101-856f4ea42174?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover; background-position: center; color: white;
            padding: 120px 0 160px; border-radius: 0 0 50px 50px; position: relative; margin-bottom: 40px;
        }
        .search-wrapper { margin-top: -80px; position: relative; z-index: 10; }
        .search-container { background: white; padding: 15px; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); border: 1px solid rgba(0,0,0,0.05); }
        .form-control, .form-select { border: 1px solid #e2e8f0; padding: 15px; border-radius: 12px; font-size: 1rem; }
        .form-control:focus, .form-select:focus { border-color: var(--accent-color); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        .category-pill {
            background: white; border: 1px solid #e2e8f0; padding: 10px 20px; border-radius: 50px;
            font-size: 0.9rem; color: #64748b; text-decoration: none; transition: all 0.3s;
            display: inline-block; margin: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .category-pill:hover { background: var(--accent-color); color: white; border-color: var(--accent-color); transform: translateY(-2px); }
        .job-card {
            background: white; border: none; border-radius: 16px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 20px; position: relative; overflow: hidden; border: 1px solid transparent;
        }
        .job-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px -5px rgba(0,0,0,0.08); border-color: #bfdbfe; }
        .logo-box {
            width: 65px; height: 65px; border-radius: 14px; background: #f8fafc;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid #f1f5f9; padding: 5px;
        }
        .logo-box img { width: 100%; height: 100%; object-fit: contain; border-radius: 8px; }
        .sidebar-card {
            background: white; border-radius: 16px; border: none; padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 25px;
        }
        .stats-section { background: white; padding: 60px 0; margin-top: 60px; border-top: 1px solid #e2e8f0; }
        .stat-item h3 { font-size: 2.5rem; font-weight: 800; color: var(--accent-color); margin-bottom: 0; }
        .stat-item p { color: #64748b; font-weight: 500; }
        footer { background: white; padding: 40px 0; border-top: 1px solid #e2e8f0; margin-top: 0; }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-layer-group me-2"></i>JobFinder</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- LINK PROFIL USER -->
                        <li class="nav-item">
                            <span class="text-muted small me-2">Halo,</span> 
                            <a href="dashboard.php" class="fw-bold text-dark text-decoration-none border-bottom border-2 border-primary pb-1" title="Klik untuk lihat profil">
                                <?= htmlspecialchars($_SESSION['user_name']) ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-danger btn-sm rounded-pill px-4 fw-bold" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold text-dark" href="login.php">Masuk</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" href="register.php">Daftar</a>
                        </li>
                        <li class="nav-item border-start ps-3 ms-2">
                            <!-- Link ke Login Perusahaan -->
                            <a class="btn btn-outline-secondary rounded-pill px-4 btn-sm" href="company/company_login.php">
                                <i class="fas fa-building me-1"></i> Perusahaan
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <span class="badge bg-primary bg-opacity-25 text-info border border-primary border-opacity-25 mb-3 px-3 py-2 rounded-pill">
                        🚀 Platform Pencarian Kerja #1
                    </span>
                    <h1 class="display-4 fw-bold mb-4 text-white">Temukan Karir Impianmu <br>Mulai Dari Sini</h1>
                    <p class="lead text-white-50 mb-0 px-5">
                        Kami menghubungkan talenta terbaik dengan perusahaan impian. Lebih dari 5,000+ lowongan tersedia untuk Anda.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Bar Area -->
    <div class="container search-wrapper mb-5">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="search-container">
                    <form class="row g-2" method="get">
                        <div class="col-lg-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                                <input name="q" class="form-control border-start-0 ps-2" placeholder="Cari posisi, perusahaan..." value="<?= htmlspecialchars($q) ?>">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 ps-3"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                <select name="location" class="form-select border-start-0 ps-2">
                                    <option value="">Lokasi</option>
                                    <?php foreach ($locations as $l): ?>
                                        <option value="<?= htmlspecialchars($l) ?>" <?= ($loc == $l) ? 'selected' : '' ?>><?= htmlspecialchars($l) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <select name="category" class="form-select">
                                <option value="">Kategori</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= htmlspecialchars($c) ?>" <?= ($cat == $c) ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <button class="btn btn-primary w-100 h-100 fw-bold rounded-3">Cari Kerja</button>
                        </div>
                    </form>
                </div>
                
                <div class="mt-4 text-center">
                    <p class="small text-muted mb-2 fw-bold text-uppercase ls-1">Kategori Populer:</p>
                    <a href="?category=IT+%26+Software" class="category-pill"><i class="fas fa-code"></i> IT & Software</a>
                    <a href="?category=Marketing" class="category-pill"><i class="fas fa-bullhorn"></i> Marketing</a>
                    <a href="?category=Desain" class="category-pill"><i class="fas fa-paint-brush"></i> Desain</a>
                    <a href="?category=Akuntansi" class="category-pill"><i class="fas fa-calculator"></i> Akuntansi</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-4">
        <div class="row">
            <!-- Job List -->
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-dark mb-0"><i class="fas fa-briefcase text-primary me-2"></i>Lowongan Terbaru</h4>
                    <span class="text-muted small"><?= $res->num_rows ?> Lowongan ditemukan</span>
                </div>
                
                <?php if ($res && $res->num_rows > 0): ?>
                    <?php while ($job = $res->fetch_assoc()): ?>
                    <div class="job-card p-4">
                        <div class="d-flex align-items-start">
                            <?php 
                                $logoPath = '';
                                if (!empty($job['logo'])) {
                                    if (file_exists('./admin/uploads/'.$job['logo'])) $logoPath = './admin/uploads/'.$job['logo'];
                                    elseif (file_exists('./company/uploads/'.$job['logo'])) $logoPath = './company/uploads/'.$job['logo'];
                                }
                            ?>
                            
                            <div class="logo-box flex-shrink-0 me-3">
                                <?php if($logoPath): ?>
                                    <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo">
                                <?php else: ?>
                                    <i class="fas fa-building text-secondary fa-2x"></i>
                                <?php endif; ?>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold">
                                            <a href="job_detail.php?id=<?= $job['id'] ?>" class="text-decoration-none text-dark stretched-link"><?= htmlspecialchars($job['title']) ?></a>
                                        </h5>
                                        <p class="mb-2 text-muted small">
                                            <?= htmlspecialchars($job['company']) ?> &bull; <?= htmlspecialchars($job['location']) ?>
                                        </p>
                                    </div>
                                    <?php if(!empty($job['type'])): ?>
                                    <span class="badge bg-light text-primary border border-primary border-opacity-10 rounded-pill px-3">
                                        <?= htmlspecialchars($job['type']) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-light">
                                    <div class="d-flex gap-3 align-items-center">
                                        <span class="small text-secondary bg-light px-2 py-1 rounded"><i class="fas fa-tag me-1"></i> <?= htmlspecialchars($job['category']) ?></span>
                                        <span class="small fw-bold text-success"><i class="fas fa-money-bill-wave me-1"></i> <?= htmlspecialchars($job['salary']) ?></span>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem">
                                        <i class="far fa-clock me-1"></i> <?= date('d M Y', strtotime($job['posted_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5 bg-white rounded-4 border border-dashed">
                        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-50">
                        <h5 class="text-muted">Tidak ada lowongan ditemukan.</h5>
                        <p class="text-muted small">Coba ubah kata kunci atau filter pencarian Anda.</p>
                        <a href="index.php" class="btn btn-outline-primary btn-sm mt-2">Reset Filter</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Card Tips -->
                <div class="sidebar-card">
                    <h6 class="fw-bold mb-4">🚀 Tips Karir Sukses</h6>
                    <div class="d-flex gap-3 mb-3">
                        <div class="text-primary"><i class="fas fa-file-alt fa-lg"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1 small">CV yang Menarik</h6>
                            <p class="small text-muted mb-0" style="font-size:0.8rem">Pastikan CV Anda update dan relevan dengan posisi.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="text-primary"><i class="fas fa-envelope-open-text fa-lg"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1 small">Cover Letter Unik</h6>
                            <p class="small text-muted mb-0" style="font-size:0.8rem">Tulis alasan spesifik mengapa Anda cocok.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="text-primary"><i class="fas fa-search fa-lg"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1 small">Riset Perusahaan</h6>
                            <p class="small text-muted mb-0" style="font-size:0.8rem">Pahami visi misi perusahaan sebelum melamar.</p>
                        </div>
                    </div>
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

    <!-- Statistik Real-time -->
    <section class="stats-section text-center">
        <div class="container">
            <p class="text-uppercase text-primary fw-bold small ls-1 mb-4">Statistik Kami</p>
            <div class="row g-4">
                <div class="col-md-4 stat-item">
                    <h3><?= $count_jobs ?>+</h3>
                    <p>Lowongan Aktif</p>
                </div>
                <div class="col-md-4 stat-item">
                    <h3><?= $count_companies ?>+</h3>
                    <p>Perusahaan Terpercaya</p>
                </div>
                <div class="col-md-4 stat-item">
                    <h3><?= $count_users ?>+</h3>
                    <p>Pelamar Bergabung</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="text-center">
        <div class="container">
            <div class="mb-3">
                <a href="#" class="text-secondary mx-2"><i class="fab fa-facebook fa-lg"></i></a>
                <a href="#" class="text-secondary mx-2"><i class="fab fa-twitter fa-lg"></i></a>
                <a href="#" class="text-secondary mx-2"><i class="fab fa-instagram fa-lg"></i></a>
                <a href="#" class="text-secondary mx-2"><i class="fab fa-linkedin fa-lg"></i></a>
            </div>
            <p class="text-muted small mb-0">&copy; <?= date('Y') ?> JobFinder Indonesia. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>