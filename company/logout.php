<?php
session_start();

// Hapus semua sesi
session_unset();
session_destroy();

// Kembalikan ke halaman Login Perusahaan (di folder yang sama)
header('Location: company_login.php');
exit;
?>