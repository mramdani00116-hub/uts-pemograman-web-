# BengkelPro — Catatan Pembaruan

## v2.0 · Red Edition

### Desain
- Login dua panel dengan ilustrasi mobil SVG, tipografi modern, dan tema merah/charcoal.
- Sidebar gelap, navigasi merah aktif, dashboard dan formulir beraksen merah.
- Animasi halus di tombol, kartu, sidebar, banner, dan formulir.

### Interaksi
- Password visibility toggle.
- Shortcut navigasi Ctrl/Cmd+K, daftar halaman dan pencarian menu.
- Dialog konfirmasi untuk hapus data dan pembuatan tagihan.
- Notifikasi toast untuk aksi berhasil.
- Loading feedback saat menyimpan data dan login.
- Statistik dashboard count-up, menu mobile dengan backdrop.
- Aksesibilitas keyboard, fokus terlihat, dan reduced-motion.

### Kompatibilitas
- Database SQL dan struktur backend PHP tidak diubah.
- Tidak perlu migrasi database dari v1.0.
- JS bersifat progressive enhancement. Fitur CRUD tetap menggunakan form HTML dan PHP.

### Verifikasi
- Pemeriksaan sintaks PHP seluruh berkas: lulus.
- Pemeriksaan sintaks JavaScript: lulus.
- Pengujian end-to-end dengan database MySQL dan browser perlu dilakukan di lingkungan pengguna.

## v2.1 — 24 Oktober 2026
- Hapus fitur Perintah Kerja dari sidebar, dashboard, form, navigasi cepat, dan handler server.
- Invoice bisa dibuat langsung untuk pelanggan tanpa Perintah Kerja.
- Dashboard menampilkan jumlah tagihan belum lunas sebagai pengganti pekerjaan berjalan.
- Skema baru tidak lagi memiliki tabel Perintah Kerja; migrasi versi lama mempertahankan arsip historis.
- Desain Red Edition dan animasi tombol tetap dipertahankan.
