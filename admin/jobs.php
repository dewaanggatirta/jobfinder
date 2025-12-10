<?php
require_once('../config.php'); 
session_start(); 

// Cek sesi Admin
if(!isset($_SESSION['admin_id'])){
    header('Location: login.php'); 
    exit;
}

// ==========================================
// 1. LOGIKA BARU: HANDLE TERIMA / TOLAK
// ==========================================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_job = intval($_GET['id']);
    $action = $_GET['action'];
    $new_status = '';

    if ($action == 'approve') {
        $new_status = 'active'; // Status aktif agar muncul di user
        $msg = "Lowongan berhasil disetujui (Active).";
    } elseif ($action == 'reject') {
        $new_status = 'inactive'; // Status inactive (ditolak)
        $msg = "Lowongan berhasil ditolak.";
    }

    if ($new_status) {
        $stmt = $conn->prepare("UPDATE jobs SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $new_status, $id_job);
        
        if($stmt->execute()){
            $_SESSION['success_message'] = $msg;
        } else {
            $_SESSION['error_message'] = "Gagal mengubah status.";
        }
        
        // Redirect agar URL bersih kembali
        header('Location: jobs.php');
        exit;
    }
}

// ==========================================
// 2. LOGIKA LAMA: DELETE
// ==========================================
if(isset($_GET['delete_id'])){
    $delete_id = intval($_GET['delete_id']);
    
    // Get logo file path before deleting
    $logo_query = "SELECT logo FROM jobs WHERE id = $delete_id";
    $logo_result = mysqli_query($conn, $logo_query);
    if($logo_row = mysqli_fetch_assoc($logo_result)){
        if($logo_row['logo'] && file_exists($logo_row['logo'])){
            unlink($logo_row['logo']); // Delete logo file
        }
    }
    
    $delete_query = "DELETE FROM jobs WHERE id = $delete_id";
    
    if(mysqli_query($conn, $delete_query)){
        $_SESSION['success_message'] = "Lowongan berhasil dihapus!";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus lowongan: " . mysqli_error($conn);
    }
    
    header('Location: jobs.php');
    exit;
}

// Fetch all jobs
$query = "SELECT * FROM jobs ORDER BY posted_at DESC";
$result = mysqli_query($conn, $query);

if(!$result){
    die("Query error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JobFinder | Kelola Lowongan</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="./plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="./plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="./plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="./css/adminlte.min.css">

    <style>
    .alert {
        margin: 15px;
    }

    .description-cell {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .action-buttons {
        white-space: nowrap;
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
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
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
                            <a href="jobs.php" class="nav-link active">
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

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Kelola Lowongan Pekerjaan</h1>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">

                    <!-- Notifikasi Pesan -->
                    <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Data Lowongan Pekerjaan</h3>
                                    <div class="card-tools">
                                        <a href="add_job.php" class="btn btn-success btn-sm">
                                            <i class="fas fa-plus"></i> Tambah Lowongan
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th width="3%">ID</th>
                                                <th width="15%">Judul</th>
                                                <th width="12%">Perusahaan</th>
                                                <th width="10%">Lokasi</th>
                                                <th width="10%">Kategori</th>
                                                <th width="8%">Tipe</th>
                                                <th width="10%">Gaji</th>
                                                <th width="8%">Status</th>
                                                <th width="12%">Tanggal</th>
                                                <th width="15%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                <td><?php echo htmlspecialchars($row['company']); ?></td>
                                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                                <td><small><?php echo htmlspecialchars($row['category']); ?></small>
                                                </td>
                                                <td><span
                                                        class="badge badge-secondary"><?php echo htmlspecialchars($row['type']); ?></span>
                                                </td>
                                                <td><small><?php echo htmlspecialchars($row['salary']); ?></small></td>

                                                <!-- KOLOM STATUS -->
                                                <td>
                                                    <?php 
                                                        $st = $row['status'];
                                                        if($st == 'active') echo '<span class="badge badge-success">Active</span>';
                                                        elseif($st == 'pending') echo '<span class="badge badge-warning">Pending</span>';
                                                        else echo '<span class="badge badge-danger">Inactive</span>';
                                                    ?>
                                                </td>

                                                <td>
                                                    <small>
                                                        <?php echo date('d M Y', strtotime($row['posted_at'])); ?>
                                                    </small>
                                                </td>

                                                <!-- KOLOM ACTION YANG SUDAH DIEDIT -->
                                                <td class="action-buttons">

                                                    <?php if($row['status'] == 'pending'): ?>
                                                    <!-- TOMBOL KHUSUS STATUS PENDING -->
                                                    <a href="jobs.php?action=approve&id=<?= $row['id'] ?>"
                                                        class="btn btn-sm btn-success mb-1"
                                                        onclick="return confirm('Terima lowongan ini?')"
                                                        title="Terima / Approve">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <a href="jobs.php?action=reject&id=<?= $row['id'] ?>"
                                                        class="btn btn-sm btn-danger mb-1"
                                                        onclick="return confirm('Tolak lowongan ini?')"
                                                        title="Tolak / Reject">
                                                        <i class="fas fa-times"></i>
                                                    </a>

                                                    <?php else: ?>
                                                    <!-- TOMBOL LAMA (EDIT & DELETE) UNTUK YANG SUDAH TIDAK PENDING -->
                                                    <a href="edit_job.php?id=<?php echo $row['id']; ?>"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                                        title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>

                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-block"><b>Version</b> 1.0.0</div>
            <strong>Copyright &copy; 2024 JobFinder.</strong> All rights reserved.
        </footer>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus lowongan "<strong id="jobTitle"></strong>"?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="./plugins/jquery/jquery.min.js"></script>
    <script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="./plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="./plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="./plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="./js/adminlte.min.js"></script>

    <script>
    $(function() {
        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "pageLength": 10,
            "order": [
                [0, "desc"]
            ]
        });

        $(document).on('click', '.delete-btn', function() {
            var jobId = $(this).data('id');
            var jobTitle = $(this).data('title');
            $('#jobTitle').text(jobTitle);
            $('#confirmDelete').attr('href', 'jobs.php?delete_id=' + jobId);
            $('#deleteModal').modal('show');
        });
    });
    </script>
</body>

</html>