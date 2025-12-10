<?php
// admin/applicant_detail.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = intval($_GET['id'] ?? 0);
if ($user_id === 0) {
    header('Location: applicant.php');
    exit;
}

// 1. AMBIL PROFIL USER
$stmt_user = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

if (!$user) { die("User tidak ditemukan."); }

// 2. AMBIL RIWAYAT LAMARAN USER INI
$sql_history = "SELECT a.*, j.title, j.company, j.status as job_status 
                FROM applications a
                JOIN jobs j ON a.job_id = j.id
                WHERE a.user_id = ?
                ORDER BY a.applied_at DESC";
$stmt_hist = $conn->prepare($sql_history);
$stmt_hist->bind_param('i', $user_id);
$stmt_hist->execute();
$history = $stmt_hist->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Pelamar - <?= htmlspecialchars($user['name']) ?></title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="../admin/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../admin/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini dark-mode">
<div class="wrapper">

    <!-- Navbar & Sidebar (Sama seperti halaman lain) -->
    <nav class="main-header navbar navbar-expand navbar-dark">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a href="logout.php" class="nav-link text-danger">Logout</a></li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index.php" class="brand-link"><span class="brand-text font-weight-light">JobFinder Admin</span></a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item menu-open">
                        <a href="#" class="nav-link active">
                            <p>Menu <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="index.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Dashboard</p></a></li>
                            <li class="nav-item"><a href="jobs.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Kelola Lowongan</p></a></li>
                            <li class="nav-item"><a href="applicant.php" class="nav-link active"><i class="far fa-circle nav-icon"></i><p>Lihat Lamaran</p></a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- CONTENT -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Detail Profil Pelamar</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="applicant.php">Daftar Pelamar</a></li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- PROFIL CARD -->
                    <div class="col-md-4">
                        <div class="card card-primary card-outline">
                            <div class="card-body box-profile">
                                <div class="text-center">
                                    <div class="img-circle elevation-2 d-flex align-items-center justify-content-center bg-light mx-auto" style="width:100px; height:100px; font-size:3rem; color:#007bff;">
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    </div>
                                </div>
                                <h3 class="profile-username text-center mt-3"><?= htmlspecialchars($user['name']) ?></h3>
                                <p class="text-muted text-center">Pencari Kerja</p>
                                <ul class="list-group list-group-unbordered mb-3">
                                    <li class="list-group-item bg-dark">
                                        <b>Email</b> <a class="float-right"><?= htmlspecialchars($user['email']) ?></a>
                                    </li>
                                    <li class="list-group-item bg-dark">
                                        <b>Bergabung</b> <a class="float-right"><?= date('d M Y', strtotime($user['created_at'])) ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- RIWAYAT LAMARAN -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header p-2">
                                <ul class="nav nav-pills">
                                    <li class="nav-item"><a class="nav-link active" href="#history" data-toggle="tab">Riwayat Lamaran</a></li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="active tab-pane" id="history">
                                        <?php if($history->num_rows > 0): ?>
                                            <div class="timeline timeline-inverse">
                                                <?php while($h = $history->fetch_assoc()): ?>
                                                <div>
                                                    <i class="fas fa-briefcase bg-primary"></i>
                                                    <div class="timeline-item border-0">
                                                        <span class="time"><i class="far fa-clock"></i> <?= date('d M Y H:i', strtotime($h['applied_at'])) ?></span>
                                                        <h3 class="timeline-header"><a href="#"><?= htmlspecialchars($h['title']) ?></a> di <?= htmlspecialchars($h['company']) ?></h3>
                                                        <div class="timeline-body">
                                                            Status Lamaran: 
                                                            <?php 
                                                                if($h['status']=='approved') echo '<span class="badge badge-success">Diterima</span>';
                                                                elseif($h['status']=='rejected') echo '<span class="badge badge-danger">Ditolak</span>';
                                                                else echo '<span class="badge badge-warning">Pending</span>';
                                                            ?>
                                                            <br>
                                                            <a href="../uploads/<?= htmlspecialchars($h['cv_file']) ?>" target="_blank" class="btn btn-xs btn-outline-info mt-2">Lihat CV yang dikirim</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endwhile; ?>
                                                <div>
                                                    <i class="far fa-clock bg-gray"></i>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-center text-muted">User ini belum pernah melamar pekerjaan.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="../admin/plugins/jquery/jquery.min.js"></script>
<script src="../admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../admin/js/adminlte.min.js"></script>
</body>
</html>