<?php
require_once('../config.php'); 
session_start(); 

if(!isset($_SESSION['admin_id'])){
    header('Location: login.php'); 
    exit;
}   
// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id == 0) {
    die("ID tidak valid");
}

// Ambil data application berdasarkan ID dengan join ke jobs
$sql = "SELECT a.*, j.title as job_title, j.company 
        FROM applications a 
        LEFT JOIN jobs j ON a.job_id = j.id 
        WHERE a.id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Data tidak ditemukan");
}

$application = $result->fetch_assoc();

// Ambil daftar jobs untuk dropdown
$jobs_sql = "SELECT id, title, company FROM jobs ORDER BY title ASC";
$jobs_result = $conn->query($jobs_sql);

// Proses form ketika submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle upload CV baru
    $cv_file = $application['cv_file']; // Default menggunakan CV lama
    
    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] == 0) {
        $target_dir = "uploads/cv/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION);
        $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $target_file)) {
            // Hapus CV lama jika ada
            if ($application['cv_file'] && file_exists($application['cv_file'])) {
                unlink($application['cv_file']);
            }
            $cv_file = $target_file;
        }
    }
    
    // Ambil data dari form dan escape untuk keamanan
    $job_id = intval($_POST['job_id']);
    $user_id = intval($_POST['user_id']);
    $status = $conn->real_escape_string($_POST['status']);
    $cv_file_escaped = $cv_file ? $conn->real_escape_string($cv_file) : null;
    
    // Update data ke database
    $sql = "UPDATE applications SET 
            job_id = $job_id,
            user_id = $user_id,
            status = '$status',
            cv_file = " . ($cv_file_escaped ? "'$cv_file_escaped'" : "NULL") . "
            WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        $success_message = "Lamaran berhasil diupdate!";
        // Refresh data
        $result = $conn->query("SELECT a.*, j.title as job_title, j.company 
                                FROM applications a 
                                LEFT JOIN jobs j ON a.job_id = j.id 
                                WHERE a.id = $id");
        $application = $result->fetch_assoc();
    } else {
        $error_message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JobFinder | Edit Lamaran</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="./css/adminlte.min.css">
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
                                    <a href="./logout.php" class="nav-link ">
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
                            <h1>Edit Lamaran</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="applicant.php">Kelola Lamaran</a></li>
                                <li class="breadcrumb-item active">Edit Lamaran</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if (isset($success_message)): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-hidden="true">&times;</button>
                                <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
                                <?php echo $success_message; ?>
                            </div>
                            <?php endif; ?>

                            <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-hidden="true">&times;</button>
                                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                                <?php echo $error_message; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Form -->
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Form Edit Lamaran</h3>
                                </div>

                                <form method="POST" action="" enctype="multipart/form-data">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <!-- Application ID (Read Only) -->
                                                <div class="form-group">
                                                    <label>ID Lamaran</label>
                                                    <input type="text" class="form-control"
                                                        value="<?php echo $application['id']; ?>" readonly>
                                                </div>

                                                <!-- Job ID - Dropdown -->
                                                <div class="form-group">
                                                    <label for="job_id">Lowongan Pekerjaan <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-control" id="job_id" name="job_id" required>
                                                        <option value="">-- Pilih Lowongan --</option>
                                                        <?php 
                                                        if ($jobs_result->num_rows > 0) {
                                                            while($job = $jobs_result->fetch_assoc()) {
                                                                $selected = ($job['id'] == $application['job_id']) ? 'selected' : '';
                                                                echo "<option value='{$job['id']}' $selected>{$job['title']} - {$job['company']}</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <!-- User ID -->
                                                <div class="form-group">
                                                    <label for="user_id">User ID <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="user_id"
                                                        name="user_id" value="<?php echo $application['user_id']; ?>"
                                                        required>
                                                    <small class="form-text text-muted">ID pengguna yang melamar</small>
                                                </div>

                                                <!-- Status -->
                                                <div class="form-group">
                                                    <label for="status">Status Lamaran <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-control" id="status" name="status" required>
                                                        <option value="pending"
                                                            <?php echo ($application['status'] == 'pending') ? 'selected' : ''; ?>>
                                                            Pending
                                                        </option>
                                                        <option value="Approved"
                                                            <?php echo ($application['status'] == 'reviewed') ? 'selected' : ''; ?>>
                                                            Approve
                                                        </option>
                                                        <option value="Rejected"
                                                            <?php echo ($application['status'] == 'interview') ? 'selected' : ''; ?>>
                                                            Rejected
                                                        </option>

                                                    </select>
                                                    <small class="form-text text-muted">
                                                        Status saat ini:
                                                        <?php 
                                                            $status = $application['status'];
                                                            $status_badge = 'badge-secondary';
                                                            $status_text = 'Pending';
                                                            
                                                            if ($status == 'pending') {
                                                                $status_badge = 'badge-info';
                                                                $status_text = 'Pending';
                                                            } elseif ($status == 'approved') {
                                                                $status_badge = 'badge-primary';
                                                                $status_text = 'Approve';
                                                            } elseif ($status == 'interview') {
                                                                $status_badge = 'badge-primary';
                                                                $status_text = 'Interview';
                                                            } elseif ($status == 'accepted') {
                                                                $status_badge = 'badge-success';
                                                                $status_text = 'Diterima';
                                                            } elseif ($status == 'rejected') {
                                                                $status_badge = 'badge-danger';
                                                                $status_text = 'rejected';
                                                            }
                                                        ?>
                                                        <span class="badge <?php echo $status_badge; ?>">
                                                            <?php echo $status_text; ?>
                                                        </span>
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <!-- CV File Current -->
                                                <div class="form-group">
                                                    <label>File CV Saat Ini</label>
                                                    <?php if ($application['cv_file']): ?>
                                                    <div class="input-group mb-2">
                                                        <input type="text" class="form-control"
                                                            value="<?php echo basename($application['cv_file']); ?>"
                                                            readonly>
                                                        <div class="input-group-append">
                                                            <a href="<?php echo htmlspecialchars($application['cv_file']); ?>"
                                                                class="btn btn-info" target="_blank">
                                                                <i class="fas fa-eye"></i> Lihat
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <?php else: ?>
                                                    <p class="text-muted">Tidak ada file CV</p>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Upload New CV -->
                                                <div class="form-group">
                                                    <label for="cv_file">Upload CV Baru (Opsional)</label>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="cv_file"
                                                            name="cv_file" accept=".pdf,.doc,.docx">
                                                        <label class="custom-file-label" for="cv_file">Pilih file
                                                            baru</label>
                                                    </div>
                                                    <small class="form-text text-muted">Format: PDF, DOC, DOCX. Maksimal
                                                        5MB. Kosongkan jika tidak ingin mengganti CV.</small>
                                                </div>

                                                <!-- Applied At (Read Only) -->
                                                <div class="form-group">
                                                    <label>Tanggal Melamar</label>
                                                    <input type="text" class="form-control"
                                                        value="<?php echo date('d M Y, H:i:s', strtotime($application['applied_at'])); ?>"
                                                        readonly>
                                                </div>

                                                <!-- Created At (Read Only) -->
                                                <div class="form-group">
                                                    <label>Tanggal Dibuat</label>
                                                    <input type="text" class="form-control"
                                                        value="<?php echo date('d M Y, H:i:s', strtotime($application['created_at'])); ?>"
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Info Alert -->
                                        <div class="alert alert-info">
                                            <i class="icon fas fa-info"></i>
                                            <strong>Info:</strong> Lamaran ini untuk posisi
                                            <strong><?php echo htmlspecialchars($application['job_title']); ?></strong>
                                            di <strong><?php echo htmlspecialchars($application['company']); ?></strong>
                                        </div>
                                    </div>

                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Lamaran
                                        </button>
                                        <a href="applicant.php" class="btn btn-default">
                                            <i class="fas fa-arrow-left"></i> Kembali
                                        </a>
                                        <a href="applicant.php?delete=<?php echo $id; ?>"
                                            class="btn btn-danger float-right"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus lamaran ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 1.0.0
            </div>
            <strong>Copyright &copy; 2024 JobFinder.</strong> All rights reserved.
        </footer>
    </div>

    <!-- jQuery -->
    <script src="./plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- bs-custom-file-input -->
    <script src="./plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
    <!-- AdminLTE App -->
    <script src="./dist/js/adminlte.min.js"></script>

    <script>
    $(function() {
        // Initialize custom file input
        bsCustomFileInput.init();
    });
    </script>
</body>

</html>