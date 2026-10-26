// Tombol "Lihat" / "Sembunyikan" kata sandi
document.querySelectorAll('[data-lihat]').forEach(function (tombol) {
  tombol.addEventListener('click', function () {
    var kolom = document.getElementById(tombol.dataset.lihat);
    var tersembunyi = kolom.type === 'password';
    kolom.type = tersembunyi ? 'text' : 'password';
    tombol.textContent = tersembunyi ? 'Sembunyikan' : 'Lihat';
  });
});

// Penghitung karakter pada kotak isi pesan
var isi = document.getElementById('isi');
var hitung = document.getElementById('hitung');
if (isi && hitung) {
  var perbarui = function () { hitung.textContent = isi.value.length + ' / 500'; };
  isi.addEventListener('input', perbarui);
  perbarui();
}

// Konfirmasi sebelum menghapus pesan
document.querySelectorAll('form[data-hapus]').forEach(function (form) {
  form.addEventListener('submit', function (e) {
    if (!confirm('Hapus pesan ini?')) e.preventDefault();
  });
});

// Pesan sukses hilang sendiri setelah beberapa detik
document.querySelectorAll('.alert-ok').forEach(function (el) {
  setTimeout(function () { el.style.display = 'none'; }, 4000);
});

// ===== Pencocok pertanyaan =====
var KATA_BUANG = ['yang','di','ke','dari','apa','apakah','saya','aku','ini','itu','dong','ya','nih','kah','dengan','untuk','berapa','adalah','sih','tolong','mau','ada','gimana','bagaimana','kapan','cara','aja','saja','ga','gak','nggak','kak','min','bisa'];

function pecahKata(teks) {
  var hasil = [];
  String(teks).toLowerCase().replace(/[^a-z0-9]+/g, ' ').split(' ').forEach(function (k) {
    if (k && KATA_BUANG.indexOf(k) === -1 && hasil.indexOf(k) === -1) hasil.push(k);
  });
  return hasil;
}

function sama(a, b) {
  if (a === b) return true;
  if (a.length < 4 || b.length < 4) return false;
  return a.indexOf(b) === 0 || b.indexOf(a) === 0;
}

function punyaKata(daftar, kata) {
  return daftar.some(function (k) { return sama(kata, k); });
}

// Cari FAQ paling cocok. Kata yang jarang muncul di banyak FAQ bernilai lebih tinggi.
function cariJawaban(teks, faq) {
  var kataUser = pecahKata(teks);
  if (!kataUser.length) return null;

  var terbaik = null, nilaiTerbaik = 0;
  faq.forEach(function (f) {
    if (!f.kata) f.kata = pecahKata(f.kata_kunci);
  });

  faq.forEach(function (f) {
    var nilai = 0;
    kataUser.forEach(function (k) {
      if (!punyaKata(f.kata, k)) return;
      var df = faq.filter(function (x) { return punyaKata(x.kata, k); }).length;
      nilai += 1 / df;
    });
    nilai = nilai / Math.sqrt(f.kata.length || 1);
    if (nilai > nilaiTerbaik) { nilaiTerbaik = nilai; terbaik = f; }
  });

  return nilaiTerbaik >= 0.15 ? terbaik : null;
}

// ===== Tanya cepat =====
(function () {
  var dataEl = document.getElementById('data-faq');
  var panel = document.getElementById('tanya-panel');
  if (!dataEl || !panel) return;

  var faq = [];
  try { faq = JSON.parse(dataEl.textContent); } catch (e) { faq = []; }

  var tombol = document.getElementById('tanya-buka');
  var tutup = document.getElementById('tanya-tutup');
  var isi = document.getElementById('tanya-isi');
  var saran = document.getElementById('tanya-saran');
  var form = document.getElementById('tanya-form');
  var input = document.getElementById('tanya-input');
  var sudahSapa = false;

  function tambah(teks, dari) {
    var d = document.createElement('div');
    d.className = 'gelembung ' + dari;
    d.textContent = teks;
    isi.appendChild(d);
    isi.scrollTop = isi.scrollHeight;
  }

  function jawab(teks) {
    tambah(teks, 'saya');
    var kecil = teks.toLowerCase();
    if (!faq.length) {
      tambah('Data tanya cepat belum ada. Import file faq.sql lewat phpMyAdmin dulu.', 'bot');
    } else if (/^(halo|hai|hi|hello|assalamualaikum|selamat (pagi|siang|sore|malam))/.test(kecil)) {
      tambah('Halo! Silakan tanya, misalnya jam buka atau cara kirim saran.', 'bot');
    } else if (/(terima kasih|makasih|thanks|terimakasih)/.test(kecil)) {
      tambah('Sama-sama!', 'bot');
    } else {
      var hasil = cariJawaban(teks, faq);
      tambah(hasil ? hasil.jawaban : 'Maaf, pertanyaan itu belum terdaftar. Kamu bisa menuliskannya sebagai saran di formulir.', 'bot');
    }
  }

  function buka() {
    panel.hidden = false;
    tombol.setAttribute('aria-expanded', 'true');
    if (!sudahSapa) {
      sudahSapa = true;
      tambah('Halo! Tanyakan sesuatu tentang sekolah atau cara memakai Kotak Saran.', 'bot');
      faq.filter(function (_, i) { return i % 3 === 0; }).slice(0, 6).forEach(function (f) {
        var b = document.createElement('button');
        b.type = 'button';
        b.textContent = f.pertanyaan;
        b.addEventListener('click', function () { jawab(f.pertanyaan); });
        saran.appendChild(b);
      });
    }
    input.focus();
  }

  function tutupPanel() {
    panel.hidden = true;
    tombol.setAttribute('aria-expanded', 'false');
    tombol.focus();
  }

  tombol.addEventListener('click', function () { panel.hidden ? buka() : tutupPanel(); });
  tutup.addEventListener('click', tutupPanel);
  panel.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutupPanel(); });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var teks = input.value.trim();
    if (!teks) return;
    input.value = '';
    jawab(teks);
  });
})();
