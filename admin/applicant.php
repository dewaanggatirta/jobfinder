<?php
// admin/applicant.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');
session_start();

// Cek Sesi Admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// 1. LOGIKA HAPUS LAMARAN
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $stmt_del = $conn->prepare("DELETE FROM applications WHERE id = ?");
    $stmt_del->bind_param('i', $del_id);
    if ($stmt_del->execute()) {
        echo "<script>alert('Data lamaran berhasil dihapus.'); window.location='applicant.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus.'); window.location='applicant.php';</script>";
    }
}

// 2. QUERY DATA
$sql = "SELECT a.*, 
               j.title as job_title, 
               j.company as company_name, 
               j.type,
               u.id as user_id, 
               u.name as pelamar_name, 
               u.email as pelamar_email
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        JOIN users u ON a.user_id = u.id
        ORDER BY a.applied_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Lamaran - Admin</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="../admin/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../admin/css/adminlte.min.css">
    <link rel="stylesheet" href="../admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">

    <style>
    .table td {
        vertical-align: middle;
    }

    .badge-status {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.85rem;
    }
    </style>
</head>

<body class="hold-transition sidebar-mini dark-mode">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-dark">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a></li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
                            <a href="index.php" class="nav-link ">
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
                            <a href="applicant.php" class="nav-link active">
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
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Kelola Lamaran Masuk</h1>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Semua Pelamar</h3>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th>Nama Pelamar</th>
                                        <th>Email</th>
                                        <th>Posisi Dilamar</th>
                                        <th>Perusahaan</th>
                                        <th>CV File</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>

                                        <!-- NAMA PELAMAR (Link ke Detail) -->
                                        <td>
                                            <a href="applicant_detail.php?id=<?= $row['user_id'] ?>"
                                                class="text-info font-weight-bold">
                                                <?= htmlspecialchars($row['pelamar_name']) ?> <i
                                                    class="fas fa-external-link-alt small ml-1"></i>
                                            </a>
                                        </td>

                                        <td><?= htmlspecialchars($row['pelamar_email']) ?></td>

                                        <td>
                                            <?= htmlspecialchars($row['job_title']) ?><br>
                                            <span class="badge badge-secondary"><?= $row['type'] ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($row['company_name']) ?></td>

                                        <td class="text-center">
                                            <a href="../uploads/<?= htmlspecialchars($row['cv_file']) ?>"
                                                target="_blank" class="btn btn-xs btn-outline-light">
                                                <i class="fas fa-file-pdf text-danger"></i> Lihat CV
                                            </a>
                                        </td>

                                        <td><?= date('d M Y', strtotime($row['applied_at'])) ?></td>

                                        <td class="text-center">
                                            <?php 
                                                $st = $row['status'];
                                                if($st=='approved') echo '<span class="badge badge-success">Diterima</span>';
                                                elseif($st=='rejected') echo '<span class="badge badge-danger">Ditolak</span>';
                                                else echo '<span class="badge badge-warning">Pending</span>';
                                            ?>
                                        </td>

                                        <td>
                                            <a href="applicant.php?delete_id=<?= $row['id'] ?>"
                                                class="btn btn-xs btn-danger"
                                                onclick="return confirm('Hapus data lamaran ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fa-3x mb-3 opacity-50"></i><br>
                                            Belum ada data lamaran yang masuk.
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer"><strong>Copyright &copy; 2025 JobFinder.</strong> All rights reserved.</footer>
    </div>

    <script src="../admin/plugins/jquery/jquery.min.js"></script>
    <script src="../admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../admin/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="../admin/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="../admin/js/adminlte.min.js"></script>
    <script>
    $(function() {
        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false
        });
    });
    </script>
</body>

</html>