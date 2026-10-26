<?php
// ====== PENGATURAN (ubah di sini) ======
define('NAMA_SEKOLAH', 'SMA Nusantara');

$host = 'localhost';     // alamat MySQL (di XAMPP: localhost)
$db   = 'saran_kritik';  // nama database
$user = 'root';          // username MySQL (XAMPP: root)
$pass = '';              // password MySQL (XAMPP: kosong)
// =======================================

session_start();
date_default_timezone_set('Asia/Jakarta');

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Koneksi database gagal. Pastikan MySQL menyala dan periksa isi config.php.');
}

function e($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

function wajib_login() {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function csrf() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function cek_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        die('Permintaan tidak valid. Muat ulang halaman.');
    }
}

function tanggal($waktu) {
    $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $t = strtotime($waktu);
    return date('j', $t) . ' ' . $bulan[date('n', $t) - 1] . ' ' . date('Y', $t) . ', ' . date('H.i', $t);
}
