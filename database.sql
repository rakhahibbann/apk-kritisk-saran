CREATE DATABASE IF NOT EXISTS saran_kritik
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saran_kritik;

CREATE TABLE IF NOT EXISTS users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nama        VARCHAR(100) NOT NULL,
  kelas       VARCHAR(30)  NULL,
  email       VARCHAR(120) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS saran (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  jenis       ENUM('saran','kritik') NOT NULL,
  kategori    VARCHAR(40) NOT NULL,
  isi         TEXT NOT NULL,
  anonim      TINYINT(1) NOT NULL DEFAULT 0,
  dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Pertanyaan & jawaban cepat (Tanya cepat)
CREATE TABLE IF NOT EXISTS faq (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  pertanyaan VARCHAR(150) NOT NULL,
  kata_kunci VARCHAR(255) NOT NULL,
  jawaban    TEXT NOT NULL
);

DELETE FROM faq;

INSERT INTO faq (pertanyaan, kata_kunci, jawaban) VALUES
('Buka jam berapa?', 'buka, jam buka, dibuka, bukanya', 'Jam 10.'),
('Tutup jam berapa?', 'tutup, jam tutup, ditutup, tutupnya', 'Jam 16.00 WIB.'),
('Jam masuk sekolah jam berapa?', 'masuk, jam masuk, bel masuk, datang', 'Jam masuk sekolah pukul 07.00 WIB.'),
('Jam pulang sekolah jam berapa?', 'pulang, jam pulang, bel pulang', 'Jam pulang sekolah pukul 14.30 WIB.'),
('Jam istirahat jam berapa?', 'istirahat, jam istirahat, rehat', 'Istirahat pertama pukul 09.30–10.00 WIB, istirahat kedua pukul 12.00–12.30 WIB.'),
('Di mana alamat sekolah?', 'alamat, lokasi, dimana, letak, maps', 'Alamat sekolah: Jl. Pendidikan No. 1.'),
('Berapa nomor telepon sekolah?', 'telepon, telpon, nomor, hubungi, kontak, wa, whatsapp', 'Nomor telepon sekolah: 0812-3456-7890.'),
('Apa email sekolah?', 'email, surel, surat elektronik', 'Email sekolah: info@smanusantara.sch.id'),
('Bagaimana cara mengirim saran atau kritik?', 'cara kirim, mengirim, menulis, tulis, kirim pesan, cara saran, cara kritik', 'Di halaman utama, pilih jenis pesan (Saran atau Kritik), pilih kategori, tulis isi pesan, lalu klik Kirim pesan.'),
('Bisa kirim pesan tanpa nama?', 'anonim, tanpa nama, rahasia, sembunyikan, samar', 'Bisa. Centang pilihan "Sembunyikan nama saya di daftar" sebelum mengirim, maka pesanmu tampil sebagai Anonim.'),
('Bagaimana cara menghapus pesan?', 'hapus, menghapus, delete, buang, batalkan', 'Klik tombol Hapus di bagian bawah pesanmu sendiri. Pesan orang lain tidak bisa dihapus.'),
('Lupa kata sandi, bagaimana?', 'lupa, sandi, password, kata sandi, reset, ganti sandi', 'Saat ini belum ada reset otomatis. Silakan hubungi admin sekolah atau daftar ulang dengan email lain.'),
('Berapa batas panjang pesan?', 'batas, panjang, karakter, maksimal, minimal, kepanjangan', 'Pesan minimal 10 karakter dan maksimal 500 karakter.'),
('Siapa yang bisa membaca pesan saya?', 'baca, membaca, melihat, lihat, siapa, dilihat, dibaca', 'Semua warga sekolah yang sudah masuk dapat melihat daftar pesan. Pilih Anonim jika tidak ingin namamu tampil.'),
('Ekstrakurikuler apa saja yang ada?', 'ekstrakurikuler, ekskul, kegiatan, pramuka, klub, organisasi', 'Ekstrakurikuler yang tersedia: Pramuka, PMR, Paskibra, Basket, Futsal, Rohis, dan Seni.'),
('Perpustakaan buka jam berapa?', 'perpustakaan, perpus, buku, pinjam, buka, jam', 'Perpustakaan buka pukul 07.30–15.00 WIB.'),
('Kantin buka jam berapa?', 'kantin, jajan, makan, minum, jajanan, buka, jam', 'Kantin buka pukul 06.30–14.00 WIB.'),
('Bagaimana cara membuat akun?', 'daftar, register, akun, buat akun, pendaftaran, mendaftar', 'Klik Daftar di halaman masuk, isi nama, email, dan kata sandi (minimal 6 karakter), lalu klik Daftar.');
