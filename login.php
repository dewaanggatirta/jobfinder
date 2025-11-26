<?php
require_once('./config.php');
session_start(); $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=trim($_POST['email']); $password=$_POST['password'];
  $stmt = $conn->prepare('SELECT id,name,password FROM users WHERE email = ?'); $stmt->bind_param('s',$email); $stmt->execute();
  $res = $stmt->get_result();
  if($row = $res->fetch_assoc()){
    if(password_verify($password, $row['password'])){ $_SESSION['user_id']=$row['id']; $_SESSION['user_name']=$row['name']; header('Location: index.php'); exit; }
    else $err='Email atau password salah.';
  } else $err='Email atau password salah.';
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Masuk</h4>
                        <?php if($err): ?><div class="alert alert-danger"><?=htmlspecialchars($err)?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email"
                                    class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">Password</label><input type="password"
                                    name="password" class="form-control" required></div>
                            <button class="btn btn-primary">Masuk</button> <a href="register.php"
                                class="btn btn-link">Daftar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>