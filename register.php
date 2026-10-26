<?php
require 'config.php';

if (!empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$galat = '';
$nama  = trim($_POST['nama'] ?? '');
$kelas = trim($_POST['kelas'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $sandi    = $_POST['password'] ?? '';
    $konfirm  = $_POST['konfirmasi'] ?? '';

    if (mb_strlen($nama) < 3) {
        $galat = 'Nama minimal 3 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $galat = 'Format email belum benar.';
    } elseif (strlen($sandi) < 6) {
        $galat = 'Kata sandi minimal 6 karakter.';
    } elseif ($sandi !== $konfirm) {
        $galat = 'Konfirmasi kata sandi tidak sama.';
    } else {
        $cek = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $cek->execute([$email]);
        if ($cek->fetch()) {
            $galat = 'Email ini sudah terdaftar. Silakan masuk.';
        } else {
            $simpan = $pdo->prepare('INSERT INTO users (nama, kelas, email, password) VALUES (?, ?, ?, ?)');
            $simpan->execute([$nama, $kelas ?: null, $email, password_hash($sandi, PASSWORD_DEFAULT)]);
            header('Location: login.php?daftar=ok');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar · Kotak Saran <?= e(NAMA_SEKOLAH) ?></title>
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
      <p>Setiap pendapat berarti. Daftar dan mulai sampaikan saran atau kritik untuk sekolah kita.</p>
    </div>
  </aside>

  <section class="sisi-form">
    <form class="form" method="post" novalidate>
      <h2>Buat akun</h2>
      <p class="petunjuk">Gunakan nama asli agar mudah dikenali guru.</p>

      <?php if ($galat): ?><div class="alert alert-galat" role="alert"><?= e($galat) ?></div><?php endif; ?>

      <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

      <label for="nama">Nama lengkap</label>
      <input type="text" id="nama" name="nama" value="<?= e($nama) ?>" required autocomplete="name">

      <label for="kelas">Kelas <span class="opsional">(boleh dikosongkan)</span></label>
      <input type="text" id="kelas" name="kelas" value="<?= e($kelas) ?>" placeholder="Contoh: XI IPA 2">

      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= e($email) ?>" required autocomplete="email">

      <label for="password">Kata sandi</label>
      <div class="kolom-sandi">
        <input type="password" id="password" name="password" required autocomplete="new-password">
        <button type="button" class="lihat" data-lihat="password">Lihat</button>
      </div>

      <label for="konfirmasi">Ulangi kata sandi</label>
      <input type="password" id="konfirmasi" name="konfirmasi" required autocomplete="new-password">

      <button type="submit" class="tombol">Daftar</button>
      <p class="alih">Sudah punya akun? <a href="login.php">Masuk</a></p>
    </form>
  </section>
</main>
<script src="script.js"></script>
</body>
</html>
