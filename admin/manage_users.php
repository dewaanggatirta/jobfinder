<?php
// admin/manage_users.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// HAPUS USER
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    
    // Hapus dari DB
    $stmt_del = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt_del->bind_param('i', $del_id);
    if ($stmt_del->execute()) {
        echo "<script>alert('User pelamar berhasil dihapus.'); window.location='manage_users.php';</script>";
    }
}

// AMBIL DATA
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Pelamar - Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="../admin/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../admin/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini dark-mode">
<div class="wrapper">
    
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a href="logout.php" class="nav-link text-danger">Logout</a></li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index.php" class="brand-link"><span class="brand-text font-weight-light">JobFinder Admin</span></a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item"><a href="index.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Dashboard</p></a></li>
                    <li class="nav-item menu-open">
                        <a href="#" class="nav-link active"><i class="nav-icon fas fa-users-cog"></i><p>Kelola Pengguna <i class="right fas fa-angle-left"></i></p></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="manage_companies.php" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Data Perusahaan</p></a></li>
                            <li class="nav-item"><a href="manage_users.php" class="nav-link active"><i class="far fa-circle nav-icon"></i><p>Data Pelamar</p></a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1>Daftar Pelamar (User)</h1>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>Bergabung Sejak</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mr-2" style="width:30px; height:30px;">
                                                <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                            </div>
                                            <?= htmlspecialchars($row['name']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <a href="applicant_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Detail</a>
                                        <a href="manage_users.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus user ini? Semua data lamarannya juga akan hilang.')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
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
  $(function () {
    $("#example1").DataTable({ "responsive": true, "lengthChange": false, "autoWidth": false });
  });
</script>
</body>
</html>