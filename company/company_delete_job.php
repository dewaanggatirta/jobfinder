<?php
// company/company_delete_job.php
// Pastikan error reporting nyala agar kalau ada error kelihatan
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');
session_start();

// Cek apakah user sudah login sebagai perusahaan
if (!isset($_SESSION['company_id'])) {
    header('Location: company_login.php');
    exit;
}

// Cek apakah ada ID yang dikirim
if (!isset($_GET['id'])) {
    header('Location: company_dashboard.php');
    exit;
}

$job_id = intval($_GET['id']);
$comp_id = $_SESSION['company_id'];

// Pastikan hanya menghapus lowongan milik sendiri
// Kita tambahkan pengecekan company_id di klausa WHERE
$stmt = $conn->prepare("DELETE FROM jobs WHERE id = ? AND company_id = ?");
$stmt->bind_param('ii', $job_id, $comp_id);

if ($stmt->execute()) {
    // Cek apakah ada baris yang terhapus
    if ($stmt->affected_rows > 0) {
        $_SESSION['msg'] = "Lowongan berhasil dihapus.";
    } else {
        $_SESSION['msg'] = "Gagal menghapus. Lowongan tidak ditemukan atau bukan milik Anda.";
    }
} else {
    $_SESSION['msg'] = "Terjadi kesalahan database: " . $stmt->error;
}

// Kembalikan ke dashboard
header('Location: company_dashboard.php');
exit;
?>