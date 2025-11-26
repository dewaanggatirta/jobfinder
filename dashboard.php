   <?php
require_once('./config.php'); session_start();
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
$stmt = $conn->prepare('SELECT a.id,a.cv_file,a.applied_at,j.title FROM applications a JOIN jobs j ON a.job_id=j.id WHERE a.user_id=? ORDER BY a.applied_at DESC');
$stmt->bind_param('i',$_SESSION['user_id']); $stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Pelamar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-4"><a href="index.php" class="btn btn-link">&larr; Lowongan</a>
        <h4>Dashboard Pelamar</h4>
        <div class="card">
            <div class="card-body">
                <?php while($r = $res->fetch_assoc()): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div><strong><?=htmlspecialchars($r['title'])?></strong>
                        <div class="small text-muted"><?=htmlspecialchars($r['applied_at'])?></div>
                    </div>
                    <div><a class="btn btn-sm btn-outline-secondary" href="./uploads/<?=urlencode($r['cv_file'])?>"
                            target="_blank">Download CV</a></div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>

</html>