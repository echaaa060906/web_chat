<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

# 💬 Aplikasi Web Chat Real-time - Progres Hari ke-4

Aplikasi obrolan (*chatting*) berbasis web modern yang mendukung komunikasi pesan personal (*Private Chat*) dan kelompok (*Group Chat*). Dilengkapi dengan fitur pemantauan status aktif pengguna (*Presence Channel*) secara instan tanpa perlu memuat ulang halaman.

---

## ✨ Fitur yang Berhasil Diselesaikan
* **Real-time Private Chat:** Mengirim dan menerima pesan pribadi antar user secara instan dan interaktif.
* **Real-time Group Chat:** Ruang diskusi berkelompok (*room group*) yang tersinkronisasi langsung ke semua anggota grup.
* **Lampu Indikator Status Online:** Indikator lingkaran di samping nama user yang otomatis menyala **hijau** jika user sedang membuka aplikasi, dan kembali abu-abu jika mereka menutup tab browser.
* **Instant Enter Key Submit:** Mengirim pesan chat langsung menggunakan tombol **Enter** pada keyboard demi kelancaran pengalaman pengguna (*UX*).
* **Autentikasi Pengguna:** Sistem daftar akun (*register*), masuk (*login*), dan keluar (*logout*) yang aman menggunakan gerbang bawaan Laravel Breeze.

---

## 🚀 Panduan Menjalankan Proyek di Lokal

Jika Anda mengunduh (*clone*) repositori ini ke komputer baru, ikuti tahapan instalasi berikut:

### 1. Pasang Dependencies
Jalankan perintah ini melalui terminal untuk memasang paket pustaka PHP dan JavaScript yang dibutuhkan:
```bash
composer install
npm install