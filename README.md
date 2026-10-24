# BengkelPro — Website Manajemen Servis Kendaraan

Aplikasi administrasi bengkel berbasis **PHP 8+ dan MySQL/MariaDB**, dirancang untuk dijalankan secara lokal menggunakan XAMPP/Laragon atau di hosting PHP.

## Fitur
- Login administrator berbasis session dan password hash.
- Dashboard ringkasan pelanggan, kendaraan, booking, tagihan, pembayaran, dan stok menipis.
- CRUD pelanggan dan kendaraan.
- Katalog jasa servis.
- Booking servis dengan tanggal, waktu, keluhan, dan status.
- Pembuatan tagihan langsung dari pelanggan tanpa membutuhkan perintah kerja.
- Pengelolaan stok suku cadang dan batas minimum stok.
- Tagihan dan status pembayaran; status tagihan menyesuaikan nominal pembayaran yang diinput.
- Pencarian data, tampilan responsif, konfirmasi hapus, prepared statements, dan token CSRF untuk form.

## Cara menjalankan di XAMPP
1. Salin folder `bengkel-service` ke `C:\xampp\htdocs\`.
2. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`.
4. Pilih menu **Import**, pilih file `database.sql` dari folder proyek, lalu klik **Import/Go**. File ini membuat database `bengkel_service`, tabel, beberapa layanan servis contoh, dan contoh suku cadang.
5. Jika username/password MySQL Anda berbeda dari bawaan XAMPP, sesuaikan `config/database.php`.
6. Buka `http://localhost/bengkel-service/setup.php` satu kali untuk membuat akun admin.
7. Login di `http://localhost/bengkel-service/login.php`.

### Akun awal
- Email: `admin@bengkel.local`
- Password: `Admin123!`

**Keamanan:** setelah setup, hapus file `setup.php`, ganti password awal, dan jangan gunakan kredensial bawaan di server publik. Gunakan HTTPS di hosting. Sesuaikan pengaturan database dengan lingkungan server.

## Relasi database inti
- `customers` 1—N `vehicles`
- `customers`, `vehicles`, `services` terhubung ke `bookings`
- `invoices` terhubung langsung ke pelanggan (tanpa perintah kerja)
- `payments` terhubung ke tagihan (tabel tersedia untuk pengembangan pencatatan pembayaran per transaksi)
- `spare_parts` menyimpan inventori dan batas minimum stok

## Catatan pengembangan
Versi ini adalah fondasi aplikasi bengkel yang siap dijalankan untuk administrasi dasar. Sebelum dipakai untuk operasional nyata, disarankan menambahkan otorisasi per peran yang lebih granular, pencatatan pembayaran per transaksi, cetak invoice/PDF, backup database terjadwal, audit log, dan validasi bahwa kendaraan yang dipilih memang milik pelanggan yang dipilih.

## Red Edition v2.0 — Peningkatan Antarmuka
- Halaman login baru dua kolom dengan ilustrasi mobil vektor (SVG lokal, tanpa gambar eksternal), animasi ringan, dan tombol tampil/sembunyikan kata sandi.
- Tema merah premium dan sidebar gelap pada dashboard serta seluruh halaman pengelolaan.
- Animasi hover, tekan/ripple, dan transisi pada tombol, tautan menu, kartu ringkasan, serta quick actions.
- Navigasi cepat **Ctrl+K** (Windows/Linux) atau **Cmd+K** (Mac), pencarian menu, dan dukungan keyboard.
- Dialog konfirmasi hapus data/buat invoice, toast notifikasi sukses, animasi angka statistik, dan indikator proses pada form.
- Navigasi mobile dengan backdrop, tata letak responsif, dan dukungan preferensi `prefers-reduced-motion`.
- File baru `assets/premium.css` dan `assets/app.js` adalah enhancement; struktur database dan seluruh operasi PHP lama tetap dipertahankan. Tidak perlu mengimpor ulang database jika sudah menjalankan versi sebelumnya.

## Red Edition v2.1 — Tanpa fitur perintah kerja
- Menu, form, tombol, navigasi cepat, dan statistik perintah kerja dihapus.
- Pembuatan tagihan kini langsung dari menu **Tagihan & pembayaran**, tanpa memilih perintah kerja.
- Login, dashboard, booking, pelanggan, kendaraan, katalog servis, stok, dan animasi merah tetap tersedia.
- **Instalasi baru:** impor `database.sql` v2.1.
- **Upgrade database v2.0:** lakukan backup database lalu impor `migrations/v2_1_remove_work_orders.sql` di phpMyAdmin **sebelum** membuat tagihan baru. Jangan impor ulang `database.sql` karena dapat menghapus data lama (periksa isi file sebelum impor).
- Data perintah kerja dan invoice lama di database yang sudah ada tidak dihapus; hanya akses fiturnya yang dinonaktifkan. Tabel lama disimpan agar relasi invoice historis tetap valid.
