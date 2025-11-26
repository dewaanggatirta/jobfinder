<?php
require_once('./config.php');
session_start();
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['name']); $email=trim($_POST['email']); $password=$_POST['password']; $password2=$_POST['password2'];
    if(!$name || !$email || !$password){ $err='Lengkapi semua field.'; }
    elseif($password !== $password2){ $err='Password tidak sama.'; }
    else{
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?'); $stmt->bind_param('s',$email); $stmt->execute(); $stmt->store_result();
        if($stmt->num_rows>0){ $err='Email sudah terdaftar.'; }
        else{
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)');
            $ins->bind_param('sss',$name,$email,$hash);
            if($ins->execute()){
                $_SESSION['user_id'] = $ins->insert_id; $_SESSION['user_name'] = $name; header('Location: index.php'); exit;
            } else $err='Gagal mendaftar.';
        }
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Daftar Akun</h4>
                        <?php if($err): ?><div class="alert alert-danger"><?=htmlspecialchars($err)?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3"><label class="form-label">Nama</label><input name="name"
                                    class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email"
                                    class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">Password</label><input type="password"
                                    name="password" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">Konfirmasi Password</label><input
                                    type="password" name="password2" class="form-control" required></div>
                            <button class="btn btn-primary">Daftar</button> <a href="login.php"
                                class="btn btn-link">Sudah punya akun? Masuk</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>