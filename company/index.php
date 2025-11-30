<?php
// File ini bertugas untuk mengarahkan user secara otomatis.
// Jadi saat akses 'localhost/jobfinder/company/',
// user langsung dilempar ke 'company_login.php'.

header('Location: company_login.php');
exit;
?>