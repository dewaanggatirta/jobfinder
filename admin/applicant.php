<?php
require_once('../config.php'); 
session_start(); 

if(!isset($_SESSION['admin_id'])){
    header('Location: login.php'); 
    exit;
}

// Handle Status Update
if (isset($_POST['update_status'])) {
    $application_id = intval($_POST['application_id']);
    $new_status = $_POST['status'];
    
    $sql = "UPDATE applications SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_status, $application_id);
    
    if ($stmt->execute()) {
        $success_message = "Status lamaran berhasil diubah!";
    } else {
        $error_message = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Ambil file CV untuk dihapus
    $sql = "SELECT cv_file FROM applications WHERE id = $delete_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['cv_file'] && file_exists($row['cv_file'])) {
            unlink($row['cv_file']);
        }
    }
    
    // Hapus data
    $sql = "DELETE FROM applications WHERE id = $delete_id";
    if ($conn->query($sql) === TRUE) {
        $success_message = "Lamaran berhasil dihapus!";
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Ambil semua data applications dengan join ke tabel jobs
$sql = "SELECT a.*, 
               j.title as job_title, 
               j.company as company_name,
               j.location as job_location,
               j.type as job_type,
               j.category as job_category
        FROM applications a 
        LEFT JOIN jobs j ON a.job_id = j.id 
        ORDER BY a.applied_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JobFinder | Kelola Lamaran</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="./plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="./plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="./css/adminlte.min.css">

    <style>
    .status-select {
        padding: 2px 5px;
        font-size: 12px;
        border-radius: 4px;
    }
    </style>
</head>

<body class="hold-transition sidebar-mini dark-mode">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-dark">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="index.html" class="nav-link">Home</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="#" class="nav-link">Contact</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Navbar Search -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                        <i class="fas fa-search"></i>
                    </a>
                    <div class="navbar-search-block">
                        <form class="form-inline">
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search" placeholder="Search"
                                    aria-label="Search">
                                <div class="input-group-append">
                                    <button class="btn btn-navbar" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </li>

                <!-- Messages Dropdown Menu -->

            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="index.php" class="brand-link">
                <!-- <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8"> -->
                <span class="brand-text font-weight-light">JobFinder</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->


                <!-- SidebarSearch Form -->
                <div class="form-inline mt-2">
                    <div class="input-group" data-widget="sidebar-search">
                        <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                            aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-sidebar">
                                <i class="fas fa-search fa-fw"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                        <li class="nav-item menu-open">
                            <a href="#" class="nav-link active">
                                <!-- <i class="nav-icon fas fa-tachometer-alt"></i> -->
                                <p>
                                    Menu
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="./index.php" class="nav-link ">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Dashboard</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="./jobs.php" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Kelola Lowongan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="./applicant.php" class="nav-link active">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Lihat Lamaran</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="./logout.php" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Logout</p>
                                    </a>
                                </li>
                            </ul>
                        </li>


                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Kelola Lamaran</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item active">Lamaran</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                        <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Daftar Lamaran Masuk</h3>
                                    <div class="card-tools">
                                        <span class="badge badge-primary">
                                            Total: <?php echo $result->num_rows; ?> lamaran
                                        </span>
                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <table id="applicationsTable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th width="4%">ID</th>
                                                <th width="18%">Posisi Dilamar</th>
                                                <th width="15%">Perusahaan</th>
                                                <th width="10%">Tipe</th>
                                                <th width="12%">CV File</th>
                                                <th width="12%">Tgl Melamar</th>
                                                <th width="15%">Status</th>
                                                <th width="8%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result->num_rows > 0): ?>
                                            <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>

                                                <td>
                                                    <strong><?php echo htmlspecialchars($row['job_title'] ?? 'N/A'); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-tag"></i>
                                                        <?php echo htmlspecialchars($row['job_category'] ?? '-'); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <i class="fas fa-building text-primary"></i>
                                                    <?php echo htmlspecialchars($row['company_name'] ?? 'N/A'); ?>
                                                </td>

                                                <td>
                                                    <?php 
                                                        $type = $row['job_type'] ?? '-';
                                                        $badge_class = 'badge-secondary';
                                                        if ($type == 'Full Time') $badge_class = 'badge-success';
                                                        elseif ($type == 'Part Time') $badge_class = 'badge-warning';
                                                        elseif ($type == 'Contract') $badge_class = 'badge-info';
                                                        elseif ($type == 'Freelance') $badge_class = 'badge-primary';
                                                        elseif ($type == 'Internship') $badge_class = 'badge-dark';
                                                        ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo htmlspecialchars($type); ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <?php if ($row['cv_file']): ?>
                                                    <a href="../uploads/<?php echo htmlspecialchars($row['cv_file']); ?>"
                                                        class="btn btn-sm btn-info" target="_blank">
                                                        <i class="fas fa-file-pdf"></i> Lihat CV
                                                    </a>
                                                    <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php echo date('d M Y', strtotime($row['applied_at'])); ?>
                                                        <br>
                                                        <?php echo date('H:i', strtotime($row['applied_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $status = $row['status'];
                                                        $status_badge = 'badge-secondary';
                                                        $status_text = 'Pending';
                                                        
                                                        if ($status == 'pending') {
                                                            $status_badge = 'badge-info';
                                                            $status_text = 'Pending';
                                    
                                                        } elseif ($status == 'approved') {
                                                            $status_badge = 'badge-primary';
                                                            $status_text = 'approved';
                                                        } elseif ($status == 'rejected') {
                                                            $status_badge = 'badge-danger';
                                                            $status_text = 'rejected';
                                                        }
                                                    ?>
                                                    <span class="badge <?php echo $status_badge; ?>">
                                                        <?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="edit-applicant.php?id=<?php echo $row['id']; ?>"
                                                            class="btn btn-warning btn-sm" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?delete=<?php echo $row['id']; ?>"
                                                            class="btn btn-danger btn-sm" title="Hapus"
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus lamaran ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                            <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center">
                                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                    <p>Belum ada lamaran masuk</p>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 1.0.0
            </div>
            <strong>Copyright &copy; 2024 JobFinder.</strong> All rights reserved.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="./plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables  & Plugins -->
    <script src="./plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="./plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="./plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="./plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <!-- AdminLTE App -->
    <script src="./dist/js/adminlte.min.js"></script>

    <script>
    $(function() {
        $("#applicationsTable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "ordering": true,
            "order": [
                [0, "desc"]
            ], // Sort by ID descending
            "pageLength": 10,
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(difilter dari _MAX_ total data)",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    });
    </script>
</body>

</html>