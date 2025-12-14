<?php
// admin_dashboard.php
require_once('./config.php');
session_start();

// 1. Cek apakah yang akses benar-benar admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// 2. Logika Verifikasi (Terima/Tolak)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    // Tentukan status baru berdasarkan tombol yang dipencet
    if ($action == 'approve') {
        $status = 'active';
    } elseif ($action == 'reject') {
        $status = 'inactive'; // Atau bisa DELETE jika ingin dihapus permanen
    }

    if (isset($status)) {
        $stmt = $conn->prepare("UPDATE jobs SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
    }
    
    // Refresh halaman agar list terupdate
    header('Location: admin_dashboard.php');
    exit;
}

// 3. Ambil data Lowongan yang Statusnya PENDING
$pending_jobs = $conn->query("SELECT * FROM jobs WHERE status = 'pending' ORDER BY posted_at DESC");

// 4. (Opsional) Ambil data Lowongan yang sudah AKTIF
$active_jobs = $conn->query("SELECT * FROM jobs WHERE status = 'active' ORDER BY posted_at DESC");
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="#">JobFinder Admin</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h4 class="mb-3">Verifikasi Lowongan Masuk</h4>
        
        <?php if ($pending_jobs->num_rows > 0): ?>
            <div class="card shadow-sm mb-5">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Posisi</th>
                                    <th>Perusahaan</th>
                                    <th>Lokasi</th>
                                    <th>Deskripsi Singkat</th>
                                    <th style="width: 200px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($job = $pending_jobs->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($job['title']) ?></td>
                                    <td><?= htmlspecialchars($job['company']) ?></td>
                                    <td><?= htmlspecialchars($job['location']) ?></td>
                                    <td><?= substr(htmlspecialchars($job['description']), 0, 50) ?>...</td>
                                    <td>
                                        <a href="admin_dashboard.php?action=approve&id=<?= $job['id'] ?>" 
                                           class="btn btn-success btn-sm w-100 mb-1"
                                           onclick="return confirm('Terima lowongan ini agar muncul di halaman depan?')">
                                           ✅ Terima
                                        </a>
                                        <a href="admin_dashboard.php?action=reject&id=<?= $job['id'] ?>" 
                                           class="btn btn-outline-danger btn-sm w-100"
                                           onclick="return confirm('Tolak lowongan ini?')">
                                           ❌ Tolak
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-success">Tidak ada lowongan pending. Semua aman!</div>
        <?php endif; ?>

        <hr>

        <h5 class="text-muted mt-4">Lowongan yang Sedang Tayang</h5>
        <div class="table-responsive">
            <table class="table table-sm text-secondary">
                <thead><tr><th>Judul</th><th>Perusahaan</th><th>Status</th></tr></thead>
                <tbody>
                    <?php while($row = $active_jobs->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['company']) ?></td>
                        <td><span class="badge bg-success">Active</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>