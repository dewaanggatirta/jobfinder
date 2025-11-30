<?php
// apply.php - Form Upload CV untuk Pelamar
require_once('./config.php'); 
session_start();

// 1. Cek Login (Hanya User yang boleh akses)
if(!isset($_SESSION['user_id'])){ 
    header('Location: login.php'); 
    exit; 
}

$job_id = intval($_GET['job_id'] ?? 0);
$err = '';

// 2. Ambil Informasi Lowongan (Untuk ditampilkan di judul)
$stmt = $conn->prepare("SELECT title, company FROM jobs WHERE id = ?");
$stmt->bind_param('i', $job_id);
$stmt->execute();
$res = $stmt->get_result();
$job = $res->fetch_assoc();

// Jika lowongan tidak valid, kembalikan ke home
if(!$job) { header('Location: index.php'); exit; }

// 3. Proses Upload CV saat tombol diklik
if($_SERVER['REQUEST_METHOD']==='POST'){
  $job_id_post = intval($_POST['job_id']);
  
  // Validasi File
  if(!isset($_FILES['cv']) || $_FILES['cv']['error']!=UPLOAD_ERR_OK){ 
      $err='Wajib memilih file CV untuk diupload.'; 
  } else {
    $allowed = ['pdf','doc','docx']; 
    $ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
    
    if(!in_array($ext,$allowed)){ 
        $err='Format file tidak didukung. Harap upload PDF, DOC, atau DOCX.'; 
    } else {
      // Generate nama file unik (Timestamp + Nama Asli yang dibersihkan)
      $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','_', $_FILES['cv']['name']);
      
      // Simpan di folder 'uploads' (Pastikan folder ini ada di root)
      $target_dir = __DIR__.'/uploads/';
      if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
      
      $target = $target_dir.$filename;
      
      if(move_uploaded_file($_FILES['cv']['tmp_name'], $target)){
        // Simpan ke Database
        $stmt_ins = $conn->prepare('INSERT INTO applications (job_id, user_id, cv_file, status) VALUES (?, ?, ?, "pending")');
        $stmt_ins->bind_param('iis', $job_id_post, $_SESSION['user_id'], $filename);
        
        if($stmt_ins->execute()){ 
            // SUKSES: Redirect ke dashboard user
            header('Location: dashboard.php'); 
            exit; 
        } else {
            $err='Terjadi kesalahan sistem database.';
        }
      } else {
          $err='Gagal mengunggah file ke server. Cek perizinan folder.';
      }
    }
  }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lamar: <?=htmlspecialchars($job['title'])?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8fafc; 
            color: #334155;
        }
        .upload-card {
            background: white; border-radius: 16px; border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .upload-header {
            background: #eff6ff; padding: 30px; border-bottom: 1px solid #e2e8f0;
            text-align: center;
        }
        .upload-body { padding: 40px; }
        .upload-zone {
            border: 2px dashed #cbd5e1; border-radius: 12px; padding: 40px 20px;
            text-align: center; background: #f8fafc; cursor: pointer;
            transition: all 0.2s; position: relative;
        }
        .upload-zone:hover { border-color: #2563eb; background: #eff6ff; }
        .icon-upload { font-size: 2.5rem; color: #94a3b8; margin-bottom: 15px; }
        
        .form-control[type=file] {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer;
        }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <!-- Tombol Kembali -->
                <a href="job_detail.php?id=<?=$job_id?>" class="text-decoration-none text-secondary fw-bold mb-3 d-inline-block">
                    <i class="fas fa-arrow-left me-2"></i> Batal & Kembali
                </a>

                <div class="upload-card">
                    <div class="upload-header">
                        <h4 class="fw-bold text-primary mb-1">Kirim Lamaran</h4>
                        <p class="text-muted small mb-0">
                            Untuk posisi <strong><?=htmlspecialchars($job['title'])?></strong><br>
                            di <?=htmlspecialchars($job['company'])?>
                        </p>
                    </div>

                    <div class="upload-body">
                        <!-- Pesan Error -->
                        <?php if($err): ?>
                            <div class="alert alert-danger d-flex align-items-center mb-4 small" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div><?=htmlspecialchars($err)?></div>
                            </div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="job_id" value="<?=$job_id?>">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-secondary mb-2">UPLOAD CV / RESUME</label>
                                
                                <!-- Area Upload Keren -->
                                <div class="upload-zone">
                                    <input type="file" name="cv" class="form-control" required accept=".pdf,.doc,.docx" onchange="previewFile(this)">
                                    <i class="fas fa-cloud-upload-alt icon-upload"></i>
                                    <h6 class="fw-bold text-dark mb-1">Klik untuk upload file</h6>
                                    <p class="text-muted small mb-0" id="fileNameDisplay">Format: PDF, DOC, DOCX (Maks 2MB)</p>
                                </div>
                            </div>

                            <button class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
                                <i class="fas fa-paper-plane me-2"></i> Kirim Lamaran Sekarang
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4 text-muted small">
                    <i class="fas fa-lock me-1"></i> Data Anda aman dan hanya dikirim ke perusahaan terkait.
                </div>

            </div>
        </div>
    </div>

    <!-- Script Sederhana untuk Ubah Nama File saat Dipilih -->
    <script>
        function previewFile(input) {
            var file = input.files[0];
            if(file){
                document.getElementById('fileNameDisplay').innerHTML = 
                    "<span class='text-success fw-bold'><i class='fas fa-check-circle'></i> " + file.name + "</span>";
                document.querySelector('.icon-upload').classList.remove('text-muted');
                document.querySelector('.icon-upload').classList.add('text-primary');
            }
        }
    </script>
</body>
</html>