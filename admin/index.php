<?php
// admin/index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');
session_start();

// Cek Sesi Admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// --- HITUNG STATISTIK DARI DATABASE ---
$res_jobs = $conn->query("SELECT COUNT(*) FROM jobs");
$count_jobs = $res_jobs->fetch_row()[0];

$res_apps = $conn->query("SELECT COUNT(*) FROM applications");
$count_apps = $res_apps->fetch_row()[0];

$res_comps = $conn->query("SELECT COUNT(*) FROM companies");
$count_comps = $res_comps->fetch_row()[0];

$res_users = $conn->query("SELECT COUNT(*) FROM users");
$count_users = $res_users->fetch_row()[0];
// -------------------------------------
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - JobFinder</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="../admin/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../admin/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini dark-mode">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a href="logout.php" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index.php" class="brand-link">
            <span class="brand-text font-weight-light">JobFinder Admin</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    
                    <!-- MENU LANGSUNG (FLAT) -->
                    <li class="nav-item">
                        <a href="index.php" class="nav-link active">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="jobs.php" class="nav-link">
                            <i class="nav-icon fas fa-briefcase"></i>
                            <p>Kelola Lowongan</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="applicant.php" class="nav-link">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Lihat Lamaran</p>
                        </a>
                    </li>

                    <!-- MENU BARU: DATA PERUSAHAAN -->
                    <li class="nav-item">
                        <a href="manage_companies.php" class="nav-link">
                            <i class="nav-icon fas fa-building"></i>
                            <p>Data Perusahaan</p>
                        </a>
                    </li>

                    <!-- MENU BARU: DATA USER -->
                    <li class="nav-item">
                        <a href="manage_users.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Data User</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="logout.php" class="nav-link text-danger">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                    </li>

                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard Statistik</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    
                    <!-- 1. TOTAL LOWONGAN (Biru) -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $count_jobs ?></h3>
                                <p>Total Lowongan</p>
                            </div>
                            <div class="icon"><i class="fas fa-briefcase"></i></div>
                            <a href="jobs.php" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <!-- 2. TOTAL LAMARAN (Hijau) -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $count_apps ?></h3>
                                <p>Total Lamaran</p>
                            </div>
                            <div class="icon"><i class="fas fa-file-invoice"></i></div>
                            <a href="applicant.php" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <!-- 3. TOTAL PERUSAHAAN (Kuning) -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $count_comps ?></h3>
                                <p>Total Perusahaan</p>
                            </div>
                            <div class="icon"><i class="fas fa-building"></i></div>
                            <a href="manage_companies.php" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                    <!-- 4. TOTAL USER (Merah) - Ini yang kamu minta ditambahkan -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= $count_users ?></h3>
                                <p>Total User</p>
                            </div>
                            <div class="icon"><i class="fas fa-users"></i></div>
                            <a href="manage_users.php" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-gradient-dark">
                            <div class="card-body text-center py-5">
                                <h3><i class="fas fa-user-shield mb-3 text-warning" style="font-size: 3rem;"></i></h3>
                                <h4>Selamat Datang di Panel Admin JobFinder</h4>
                                <p class="text-muted">Kelola seluruh data dari menu di samping kiri.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2025 JobFinder.</strong> All rights reserved.
    </footer>
</div>

<script src="../admin/plugins/jquery/jquery.min.js"></script>
<script src="../admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../admin/js/adminlte.min.js"></script>
</body>
</html>