<?php
// login.php - Dengan Fitur 'Show Password' (Mata)
require_once('./config.php');
session_start(); 
$err='';

if(isset($_SESSION['user_id'])){ header('Location: index.php'); exit; }

if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=trim($_POST['email']); 
  $password=$_POST['password'];
  
  $stmt = $conn->prepare('SELECT id,name,password FROM users WHERE email = ?'); 
  $stmt->bind_param('s',$email); 
  $stmt->execute();
  $res = $stmt->get_result();
  
  if($row = $res->fetch_assoc()){
    if(password_verify($password, $row['password'])){ 
        $_SESSION['user_id']=$row['id']; 
        $_SESSION['user_name']=$row['name']; 
        header('Location: index.php'); 
        exit; 
    }
    else $err='Email atau password salah.';
  } else $err='Email atau password salah.';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - JobFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary">JobFinder</h3>
                </div>
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4 fw-bold">Masuk</h4>
                        <?php if($err): ?>
                            <div class="alert alert-danger py-2 small"><?=htmlspecialchars($err)?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="passInput" class="form-control border-end-0" required>
                                    <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" onclick="togglePassword()">
                                        <i class="fas fa-eye text-muted" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>
                            <button class="btn btn-primary w-100 py-2 fw-bold rounded-3">Masuk</button>
                        </form>
                        
                        <div class="text-center mt-3 pt-3 border-top">
                            <small class="text-muted">Belum punya akun? <a href="register.php" class="text-decoration-none fw-bold">Daftar</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            var input = document.getElementById("passInput");
            var icon = document.getElementById("eyeIcon");
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>