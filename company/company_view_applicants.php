<?php
// company/company_view_applicants.php - FIX POPUP FEEDBACK
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');
session_start();

if (!isset($_SESSION['company_id'])) { header('Location: company_login.php'); exit; }

$job_id = intval($_GET['job_id'] ?? 0);
$comp_id = $_SESSION['company_id'];

// --- 1. LOGIKA SIMPAN STATUS & FEEDBACK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_status'])) {
    $app_id = intval($_POST['app_id']);
    $action = $_POST['action_type']; // 'approve' atau 'reject'
    $msg    = trim($_POST['message']); // Pesan Feedback
    
    // Tentukan status untuk database
    $new_status = ($action == 'approve') ? 'approved' : 'rejected';
    
    // Query Update Status + Feedback
    $sql_update = "UPDATE applications a 
                   JOIN jobs j ON a.job_id = j.id
                   SET a.status = ?, a.feedback = ?
                   WHERE a.id = ? AND j.company_id = ?";
                   
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param('ssii', $new_status, $msg, $app_id, $comp_id);
    
    if ($stmt_update->execute()) {
        // Refresh halaman agar data terupdate
        header("Location: company_view_applicants.php?job_id=" . $job_id);
        exit;
    } else {
        echo "<script>alert('Gagal update database.');</script>";
    }
}

// --- 2. AMBIL DATA JOB ---
$stmt = $conn->prepare("SELECT title FROM jobs WHERE id = ? AND company_id = ?");
$stmt->bind_param('ii', $job_id, $comp_id);
$stmt->execute();
$res_job = $stmt->get_result();

if ($res_job->num_rows === 0) { die("Lowongan tidak ditemukan atau bukan milik Anda."); }
$job_data = $res_job->fetch_assoc();

// --- 3. AMBIL DATA PELAMAR ---
$stmt_app = $conn->prepare("SELECT a.*, u.name, u.email FROM applications a JOIN users u ON a.user_id = u.id WHERE a.job_id = ? ORDER BY a.applied_at DESC");
$stmt_app->bind_param('i', $job_id);
$stmt_app->execute();
$applicants = $stmt_app->get_result();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pelamar: <?= htmlspecialchars($job_data['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .card-pelamar { background: white; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 16px; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .st-approved { background: #dcfce7; color: #166534; }
        .st-rejected { background: #fee2e2; color: #991b1b; }
        .st-pending { background: #fef9c3; color: #854d0e; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top bg-white border-bottom py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="company_dashboard.php">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
            <span class="navbar-text fw-bold text-dark">
                Pelamar: <?= htmlspecialchars($job_data['title']) ?>
            </span>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if ($applicants->num_rows > 0): ?>
                    <h6 class="text-muted mb-4 fw-bold text-uppercase small">Daftar Kandidat (<?= $applicants->num_rows ?>)</h6>
                    
                    <?php while($row = $applicants->fetch_assoc()): ?>
                    <div class="card-pelamar p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($row['name']) ?></h5>
                                        <?php if($row['status'] == 'approved'): ?>
                                            <span class="status-badge st-approved"><i class="fas fa-check"></i> Diterima</span>
                                        <?php elseif($row['status'] == 'rejected'): ?>
                                            <span class="status-badge st-rejected"><i class="fas fa-times"></i> Ditolak</span>
                                        <?php else: ?>
                                            <span class="status-badge st-pending"><i class="fas fa-clock"></i> Menunggu</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars($row['email']) ?> • <?= date('d M Y, H:i', strtotime($row['applied_at'])) ?>
                                    </div>
                                    
                                    <?php if(!empty($row['feedback'])): ?>
                                        <div class="mt-2 p-2 rounded bg-light border small text-muted">
                                            <i class="fas fa-comment-dots me-1"></i> <strong>Pesan Anda:</strong> "<?= htmlspecialchars($row['feedback']) ?>"
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <?php 
                                    $cv_path = '../uploads/' . $row['cv_file']; 
                                ?>
                                <?php if(file_exists($cv_path)): ?>
                                    <a href="<?= $cv_path ?>" target="_blank" class="btn btn-light border btn-sm fw-bold text-primary">
                                        <i class="fas fa-file-pdf me-1 text-danger"></i> CV
                                    </a>
                                <?php endif; ?>

                                <?php if($row['status'] == 'pending'): ?>
                                    <button type="button" 
                                            class="btn btn-success btn-sm fw-bold"
                                            onclick="openModal(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>', 'approve')">
                                        <i class="fas fa-check me-1"></i> Terima
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm fw-bold"
                                            onclick="openModal(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>', 'reject')">
                                        <i class="fas fa-times me-1"></i> Tolak
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">Belum ada pelamar untuk posisi ini.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalTitle">Konfirmasi Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="app_id" id="modalAppId">
                        <input type="hidden" name="action_type" id="modalActionType">
                        <input type="hidden" name="submit_status" value="1">
                        
                        <p id="modalText" class="mb-3"></p>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Pesan untuk Pelamar (Feedback)</label>
                            <textarea name="message" class="form-control" rows="3" placeholder="Tulis pesan..." required></textarea>
                            <div class="form-text small">Pesan ini akan terbaca oleh pelamar di dashboard mereka.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="modalBtn">Kirim Keputusan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openModal(id, name, action) {
            // Isi data ke dalam modal sebelum ditampilkan
            document.getElementById('modalAppId').value = id;
            document.getElementById('modalActionType').value = action;
            
            var modalTitle = document.getElementById('modalTitle');
            var modalText = document.getElementById('modalText');
            var modalBtn = document.getElementById('modalBtn');
            var modalHeader = document.querySelector('.modal-header');

            if (action === 'approve') {
                modalTitle.innerText = "Terima Pelamar";
                modalText.innerHTML = "Anda akan menerima <strong>" + name + "</strong>. Silakan tulis pesan selamat atau instruksi selanjutnya (misal: Cek email).";
                modalBtn.innerText = "Terima & Kirim Pesan";
                modalBtn.className = "btn btn-success fw-bold";
                modalHeader.className = "modal-header bg-success text-white";
            } else {
                modalTitle.innerText = "Tolak Pelamar";
                modalText.innerHTML = "Anda akan menolak <strong>" + name + "</strong>. Berikan alasan singkat agar pelamar mengerti.";
                modalBtn.innerText = "Tolak & Kirim Alasan";
                modalBtn.className = "btn btn-danger fw-bold";
                modalHeader.className = "modal-header bg-danger text-white";
            }

            // Tampilkan Modal
            var myModal = new bootstrap.Modal(document.getElementById('statusModal'));
            myModal.show();
        }
    </script>
</body>
</html>