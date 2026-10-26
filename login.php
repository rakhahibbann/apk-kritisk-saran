<?php
require 'config.php';

if (!empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$galat = '';
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $sandi = $_POST['password'] ?? '';

    $st = $pdo->prepare('SELECT id, nama, kelas, password FROM users WHERE email = ?');
    $st->execute([$email]);
    $u = $st->fetch();

    if ($u && password_verify($sandi, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $u['id'], 'nama' => $u['nama'], 'kelas' => $u['kelas']];
        header('Location: index.php');
        exit;
    }
    $galat = 'Email atau kata sandi salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk · Kotak Saran <?= e(NAMA_SEKOLAH) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="auth">
  <aside class="buku">
    <div class="buku-isi">
      <p class="sekolah"><?= e(NAMA_SEKOLAH) ?></p>
      <h1>Kotak Saran</h1>
      <p>Sampaikan saran dan kritik untuk sekolah kita, dengan sopan dan membangun.</p>
    </div>
  </aside>

  <section class="sisi-form">
    <form class="form" method="post" novalidate>
      <h2>Selamat datang</h2>
      <p class="petunjuk">Masuk untuk mengirim saran dan kritik.</p>

      <?php if (isset($_GET['daftar'])): ?>
        <div class="alert alert-ok" role="status">Akun berhasil dibuat. Silakan masuk.</div>
      <?php endif; ?>
      <?php if (isset($_GET['keluar'])): ?>
        <div class="alert alert-ok" role="status">Kamu sudah keluar.</div>
      <?php endif; ?>
      <?php if ($galat): ?><div class="alert alert-galat" role="alert"><?= e($galat) ?></div><?php endif; ?>

      <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= e($email) ?>" required autocomplete="email">

      <label for="password">Kata sandi</label>
      <div class="kolom-sandi">
        <input type="password" id="password" name="password" required autocomplete="current-password">
        <button type="button" class="lihat" data-lihat="password">Lihat</button>
      </div>

      <button type="submit" class="tombol">Masuk</button>
      <p class="alih">Belum punya akun? <a href="register.php">Daftar</a></p>
    </form>
  </section>
</main>
<script src="script.js"></script>
</body>
</html>
