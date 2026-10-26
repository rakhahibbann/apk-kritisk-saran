<?php
require 'config.php';
wajib_login();

$uid      = $_SESSION['user']['id'];
$kategori = ['Guru & Pengajaran', 'Fasilitas', 'Kegiatan & Ekstrakurikuler', 'Kebersihan', 'Kantin', 'Lainnya'];
$galat    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();

    // Hapus pesan milik sendiri
    if (($_POST['aksi'] ?? '') === 'hapus') {
        $st = $pdo->prepare('DELETE FROM saran WHERE id = ? AND user_id = ?');
        $st->execute([(int)($_POST['id'] ?? 0), $uid]);
        header('Location: index.php?ok=hapus');
        exit;
    }

    // Kirim pesan baru
    $jenis  = $_POST['jenis'] ?? '';
    $kat    = $_POST['kategori'] ?? '';
    $isi    = trim($_POST['isi'] ?? '');
    $anonim = isset($_POST['anonim']) ? 1 : 0;

    if (!in_array($jenis, ['saran', 'kritik'], true) || !in_array($kat, $kategori, true)) {
        $galat = 'Pilih jenis pesan dan kategori terlebih dahulu.';
    } elseif (mb_strlen($isi) < 10) {
        $galat = 'Isi pesan minimal 10 karakter.';
    } elseif (mb_strlen($isi) > 500) {
        $galat = 'Isi pesan maksimal 500 karakter.';
    } else {
        $st = $pdo->prepare('INSERT INTO saran (user_id, jenis, kategori, isi, anonim) VALUES (?, ?, ?, ?, ?)');
        $st->execute([$uid, $jenis, $kat, $isi, $anonim]);
        header('Location: index.php?ok=kirim');
        exit;
    }
}

// Daftar pesan (bisa disaring)
$saring = $_GET['jenis'] ?? '';
$sql    = 'SELECT s.*, u.nama, u.kelas FROM saran s JOIN users u ON u.id = s.user_id';
$par    = [];
if (in_array($saring, ['saran', 'kritik'], true)) {
    $sql .= ' WHERE s.jenis = ?';
    $par[] = $saring;
}
$sql .= ' ORDER BY s.dibuat_pada DESC LIMIT 50';
$st = $pdo->prepare($sql);
$st->execute($par);
$daftar = $st->fetchAll();

// Data pertanyaan cepat (tabel faq). Kalau tabel belum diimpor, daftar dibuat kosong.
try {
    $faq = $pdo->query('SELECT pertanyaan, kata_kunci, jawaban FROM faq ORDER BY id')->fetchAll();
} catch (PDOException $e) {
    $faq = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kotak Saran · <?= e(NAMA_SEKOLAH) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="halaman">
<header class="bar">
  <div class="bar-isi">
    <div class="merek">
      <strong>Kotak Saran</strong>
      <span><?= e(NAMA_SEKOLAH) ?></span>
    </div>
    <div class="bar-user">
      <span><?= e($_SESSION['user']['nama']) ?><?= $_SESSION['user']['kelas'] ? ' · ' . e($_SESSION['user']['kelas']) : '' ?></span>
      <a class="keluar" href="logout.php">Keluar</a>
    </div>
  </div>
</header>

<main class="isi">
  <section class="kartu kirim">
    <h2>Tulis pesan</h2>

    <?php if (($_GET['ok'] ?? '') === 'kirim'): ?>
      <div class="alert alert-ok" role="status">Terima kasih, pesanmu sudah terkirim.</div>
    <?php elseif (($_GET['ok'] ?? '') === 'hapus'): ?>
      <div class="alert alert-ok" role="status">Pesan sudah dihapus.</div>
    <?php endif; ?>
    <?php if ($galat): ?><div class="alert alert-galat" role="alert"><?= e($galat) ?></div><?php endif; ?>

    <form method="post" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">

      <fieldset class="pilih-jenis">
        <legend>Jenis pesan</legend>
        <label class="jenis">
          <input type="radio" name="jenis" value="saran" <?= ($_POST['jenis'] ?? '') === 'saran' ? 'checked' : '' ?>>
          <span>Saran</span>
        </label>
        <label class="jenis">
          <input type="radio" name="jenis" value="kritik" <?= ($_POST['jenis'] ?? '') === 'kritik' ? 'checked' : '' ?>>
          <span>Kritik</span>
        </label>
      </fieldset>

      <label for="kategori">Kategori</label>
      <select id="kategori" name="kategori" required>
        <option value="">Pilih kategori</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= e($k) ?>" <?= ($_POST['kategori'] ?? '') === $k ? 'selected' : '' ?>><?= e($k) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="isi">Isi pesan</label>
      <textarea id="isi" name="isi" rows="6" maxlength="500" placeholder="Tulis dengan sopan dan jelas, supaya mudah ditindaklanjuti."><?= e($_POST['isi'] ?? '') ?></textarea>
      <p class="hitung" id="hitung">0 / 500</p>

      <label class="centang">
        <input type="checkbox" name="anonim" <?= isset($_POST['anonim']) ? 'checked' : '' ?>>
        <span>Sembunyikan nama saya di daftar</span>
      </label>

      <button type="submit" class="tombol">Kirim pesan</button>
    </form>
  </section>

  <section class="daftar">
    <div class="daftar-kepala">
      <h2>Pesan dari warga sekolah</h2>
      <nav class="saring" aria-label="Saring pesan">
        <a href="index.php" class="<?= $saring === '' ? 'aktif' : '' ?>">Semua</a>
        <a href="index.php?jenis=saran" class="<?= $saring === 'saran' ? 'aktif' : '' ?>">Saran</a>
        <a href="index.php?jenis=kritik" class="<?= $saring === 'kritik' ? 'aktif' : '' ?>">Kritik</a>
      </nav>
    </div>

    <?php if (!$daftar): ?>
      <div class="kosong">Belum ada pesan. Jadilah yang pertama menulis di sini.</div>
    <?php endif; ?>

    <?php foreach ($daftar as $p): ?>
      <article class="pesan <?= e($p['jenis']) ?>">
        <div class="pesan-atas">
          <span class="label"><?= $p['jenis'] === 'saran' ? 'Saran' : 'Kritik' ?></span>
          <span class="kat"><?= e($p['kategori']) ?></span>
        </div>
        <p class="pesan-isi"><?= nl2br(e($p['isi'])) ?></p>
        <div class="pesan-bawah">
          <span>
            <?= $p['anonim'] ? 'Anonim' : e($p['nama']) . ($p['kelas'] ? ' (' . e($p['kelas']) . ')' : '') ?>
            · <?= e(tanggal($p['dibuat_pada'])) ?>
          </span>
          <?php if ((int)$p['user_id'] === (int)$uid): ?>
            <form method="post" data-hapus>
              <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
              <input type="hidden" name="aksi" value="hapus">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button type="submit" class="hapus">Hapus</button>
            </form>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
</main>
<script type="application/json" id="data-faq"><?= json_encode($faq, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

<button type="button" class="tanya-buka" id="tanya-buka" aria-expanded="false" aria-controls="tanya-panel">Tanya cepat</button>
<section class="tanya" id="tanya-panel" aria-label="Tanya cepat" hidden>
  <header class="tanya-kepala">
    <strong>Tanya cepat</strong>
    <button type="button" id="tanya-tutup" aria-label="Tutup">&times;</button>
  </header>
  <div class="tanya-isi" id="tanya-isi" aria-live="polite"></div>
  <div class="tanya-saran" id="tanya-saran"></div>
  <form class="tanya-form" id="tanya-form" autocomplete="off">
    <input type="text" id="tanya-input" placeholder="Contoh: Buka jam berapa?" aria-label="Tulis pertanyaan">
    <button type="submit">Kirim</button>
  </form>
</section>

<script src="script.js"></script>
</body>
</html>
