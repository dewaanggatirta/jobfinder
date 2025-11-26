<?php
require_once('./config.php'); session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $job_id = intval($_POST['job_id']);
  if(!isset($_FILES['cv']) || $_FILES['cv']['error']!=UPLOAD_ERR_OK){ $err='Pilih file CV.'; }
  else {
    $allowed = ['pdf','doc','docx']; $ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
    if(!in_array($ext,$allowed)){ $err='Format CV harus PDF/DOC/DOCX.'; }
    else {
      $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/','_', $_FILES['cv']['name']);
      $target = __DIR__.'./uploads/'.$filename;
      if(move_uploaded_file($_FILES['cv']['tmp_name'], $target)){
        $stmt = $conn->prepare('INSERT INTO applications (job_id,user_id,cv_file) VALUES (?,?,?)');
        $stmt->bind_param('iis',$job_id, $_SESSION['user_id'], $filename);
        if($stmt->execute()){ header('Location: dashboard.php'); exit; } else $err='Gagal menyimpan lamaran.';
      } else $err='Gagal mengunggah file.';
    }
  }
}
$job_id = intval($_GET['job_id'] ?? 0);
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lamar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-4"><a href="job_detail.php?id=<?=$job_id?>" class="btn btn-link">&larr; Kembali</a>
        <div class="card p-3">
            <h5>Lamar Pekerjaan</h5><?php if($err): ?><div class="alert alert-danger"><?=htmlspecialchars($err)?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="job_id" value="<?=$job_id?>">
                <div class="mb-3"><label class="form-label">Unggah CV (PDF/DOC)</label><input type="file" name="cv"
                        class="form-control" required></div>
                <button class="btn btn-primary">Kirim Lamaran</button>
            </form>
        </div>
    </div>
</body>

</html>