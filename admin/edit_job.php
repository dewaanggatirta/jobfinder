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

// Ambil data job berdasarkan ID
$sql = "SELECT * FROM jobs WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Data tidak ditemukan");
}

$job = $result->fetch_assoc();

// Proses form ketika submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle upload logo baru
    $logo = $job['logo']; // Default menggunakan logo lama
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $target_dir = "uploads/logos/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
            // Hapus logo lama jika ada
            if ($job['logo'] && file_exists($job['logo'])) {
                unlink($job['logo']);
            }
            $logo = $target_file;
        }
    }
    
    // Ambil data dari form dan escape untuk keamanan
    $title = $conn->real_escape_string($_POST['title']);
    $company = $conn->real_escape_string($_POST['company']);
    $location = $conn->real_escape_string($_POST['location']);
    $category = $conn->real_escape_string($_POST['category']);
    $type = $conn->real_escape_string($_POST['type']);
    $salary = $conn->real_escape_string($_POST['salary']);
    $description = $conn->real_escape_string($_POST['description']);
    $status = $conn->real_escape_string($_POST['status']);
    $logo_escaped = $logo ? $conn->real_escape_string($logo) : null;
    
    // Update data ke database
    $sql = "UPDATE jobs SET 
            title = '$title',
            company = '$company',
            location = '$location',
            category = '$category',
            type = '$type',
            salary = '$salary',
            description = '$description',
            status = '$status',
            logo = " . ($logo_escaped ? "'$logo_escaped'" : "NULL") . "
            WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        $success_message = "Lowongan kerja berhasil diupdate!";
        // Refresh data
        $result = $conn->query("SELECT * FROM jobs WHERE id = $id");
        $job = $result->fetch_assoc();
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
    <title>JobFinder | Edit Lowongan</title>

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
                    <a href="index.php" class="nav-link">Home</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                        <i class="fas fa-search"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="index.php" class="brand-link">
                <span class="brand-text font-weight-light">JobFinder</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
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
                        <li class="nav-item menu-open">
                            <a href="#" class="nav-link active">
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
                                    <a href="./jobs.php" class="nav-link active">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Kelola Lowongan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="./applicant.php" class="nav-link">
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
                            <h1>Edit Lowongan Kerja</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="jobs.php">Kelola Lowongan</a></li>
                                <li class="breadcrumb-item active">Edit Lowongan</li>
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
                                    <h3 class="card-title">Form Edit Lowongan Kerja</h3>
                                </div>

                                <form method="POST" action="" enctype="multipart/form-data">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <!-- Title -->
                                                <div class="form-group">
                                                    <label for="title">Judul Lowongan <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="title" name="title"
                                                        value="<?php echo htmlspecialchars($job['title']); ?>"
                                                        placeholder="Contoh: Frontend Developer" required>
                                                </div>

                                                <!-- Company -->
                                                <div class="form-group">
                                                    <label for="company">Nama Perusahaan <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="company" name="company"
                                                        value="<?php echo htmlspecialchars($job['company']); ?>"
                                                        placeholder="Contoh: PT. Tech Indonesia" required>
                                                </div>

                                                <!-- Location -->
                                                <div class="form-group">
                                                    <label for="location">Lokasi <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="location"
                                                        name="location"
                                                        value="<?php echo htmlspecialchars($job['location']); ?>"
                                                        placeholder="Contoh: Jakarta, Indonesia" required>
                                                </div>

                                                <!-- Category -->
                                                <div class="form-group">
                                                    <label for="category">Kategori <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-control" id="category" name="category" required>
                                                        <option value="">-- Pilih Kategori --</option>
                                                        <option value="IT & Software"
                                                            <?php echo ($job['category'] == 'IT & Software') ? 'selected' : ''; ?>>
                                                            IT & Software</option>
                                                        <option value="Marketing"
                                                            <?php echo ($job['category'] == 'Marketing') ? 'selected' : ''; ?>>
                                                            Marketing</option>
                                                        <option value="Finance"
                                                            <?php echo ($job['category'] == 'Finance') ? 'selected' : ''; ?>>
                                                            Finance</option>
                                                        <option value="Human Resources"
                                                            <?php echo ($job['category'] == 'Human Resources') ? 'selected' : ''; ?>>
                                                            Human Resources</option>
                                                        <option value="Sales"
                                                            <?php echo ($job['category'] == 'Sales') ? 'selected' : ''; ?>>
                                                            Sales</option>
                                                        <option value="Design"
                                                            <?php echo ($job['category'] == 'Design') ? 'selected' : ''; ?>>
                                                            Design</option>
                                                        <option value="Customer Service"
                                                            <?php echo ($job['category'] == 'Customer Service') ? 'selected' : ''; ?>>
                                                            Customer Service</option>
                                                        <option value="Operations"
                                                            <?php echo ($job['category'] == 'Operations') ? 'selected' : ''; ?>>
                                                            Operations</option>
                                                        <option value="Engineering"
                                                            <?php echo ($job['category'] == 'Engineering') ? 'selected' : ''; ?>>
                                                            Engineering</option>
                                                        <option value="Other"
                                                            <?php echo ($job['category'] == 'Other') ? 'selected' : ''; ?>>
                                                            Other</option>
                                                    </select>
                                                </div>

                                                <!-- Type -->
                                                <div class="form-group">
                                                    <label for="type">Tipe Pekerjaan <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-control" id="type" name="type" required>
                                                        <option value="">-- Pilih Tipe --</option>
                                                        <option value="Full Time"
                                                            <?php echo ($job['type'] == 'Full Time') ? 'selected' : ''; ?>>
                                                            Full Time</option>
                                                        <option value="Part Time"
                                                            <?php echo ($job['type'] == 'Part Time') ? 'selected' : ''; ?>>
                                                            Part Time</option>
                                                        <option value="Contract"
                                                            <?php echo ($job['type'] == 'Contract') ? 'selected' : ''; ?>>
                                                            Contract</option>
                                                        <option value="Freelance"
                                                            <?php echo ($job['type'] == 'Freelance') ? 'selected' : ''; ?>>
                                                            Freelance</option>
                                                        <option value="Internship"
                                                            <?php echo ($job['type'] == 'Internship') ? 'selected' : ''; ?>>
                                                            Internship</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <!-- Salary -->
                                                <div class="form-group">
                                                    <label for="salary">Gaji</label>
                                                    <input type="text" class="form-control" id="salary" name="salary"
                                                        value="<?php echo htmlspecialchars($job['salary']); ?>"
                                                        placeholder="Contoh: Rp 5.000.000 - Rp 8.000.000">
                                                    <small class="form-text text-muted">Opsional. Kosongkan jika tidak
                                                        ingin menampilkan gaji.</small>
                                                </div>

                                                <!-- Status -->
                                                <div class="form-group">
                                                    <label for="status">Status Lowongan <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-control" id="status" name="status" required>
                                                        <?php $current_status = isset($job['status']) ? $job['status'] : 'active'; ?>
                                                        <option value="Pending"
                                                            <?php echo ($current_status == 'pending') ? 'selected' : ''; ?>>
                                                            Pending
                                                        </option>
                                                        <option value="inactive"
                                                            <?php echo ($current_status == 'inactive') ? 'selected' : ''; ?>>
                                                            Inactive
                                                        </option>
                                                        <option value="active"
                                                            <?php echo ($current_status == 'Active') ? 'selected' : ''; ?>>
                                                            Active
                                                        </option>

                                                    </select>
                                                    <small class="form-text text-muted">
                                                        Status saat ini:
                                                        <?php 
                                                            $status_badge = 'badge-secondary';
                                                            $status_text = 'Active';
                                                            
                                                            if ($current_status == 'active') {
                                                                $status_badge = 'badge-success';
                                                                $status_text = 'Aktif';
                                                            } elseif ($current_status == 'closed') {
                                                                $status_badge = 'badge-danger';
                                                                $status_text = 'Ditutup';
                                                            } elseif ($current_status == 'draft') {
                                                                $status_badge = 'badge-secondary';
                                                                $status_text = 'Draft';
                                                            } elseif ($current_status == 'paused') {
                                                                $status_badge = 'badge-warning';
                                                                $status_text = 'Dijeda';
                                                            }
                                                        ?>
                                                        <span class="badge <?php echo $status_badge; ?>">
                                                            <?php echo $status_text; ?>
                                                        </span>
                                                    </small>
                                                </div>

                                                <!-- Logo -->
                                                <div class="form-group">
                                                    <label for="logo">Logo Perusahaan</label>
                                                    <?php if ($job['logo']): ?>
                                                    <div class="mb-2">
                                                        <img src="<?php echo htmlspecialchars($job['logo']); ?>"
                                                            alt="Logo" style="max-width: 150px; max-height: 150px;"
                                                            class="img-thumbnail">
                                                        <p class="text-sm text-muted mt-1">Logo saat ini</p>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="logo"
                                                            name="logo" accept="image/*">
                                                        <label class="custom-file-label" for="logo">Pilih file baru
                                                            (opsional)</label>
                                                    </div>
                                                    <small class="form-text text-muted">Format: JPG, PNG, GIF. Maksimal
                                                        2MB. Kosongkan jika tidak ingin mengganti logo.</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        <div class="form-group">
                                            <label for="description">Deskripsi Pekerjaan <span
                                                    class="text-danger">*</span></label>
                                            <textarea class="form-control" id="description" name="description" rows="8"
                                                placeholder="Masukkan deskripsi lengkap tentang pekerjaan ini..."
                                                required><?php echo htmlspecialchars($job['description']); ?></textarea>
                                        </div>

                                        <!-- Info posting date -->
                                        <div class="alert alert-info">
                                            <i class="icon fas fa-info"></i>
                                            <strong>Info:</strong> Lowongan ini diposting pada
                                            <?php echo date('d M Y, H:i', strtotime($job['posted_at'])); ?>
                                        </div>
                                    </div>

                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Lowongan
                                        </button>
                                        <a href="jobs.php" class="btn btn-default">
                                            <i class="fas fa-arrow-left"></i> Kembali
                                        </a>
                                        <a href="jobs.php?delete_id=<?php echo $id; ?>"
                                            class="btn btn-danger float-right"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus lowongan ini?')">
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