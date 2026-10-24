<?php
require_once __DIR__ . '/config/database.php';
$message = '';
try {
    $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
        $stmt->execute(['Administrator','admin@bengkel.local',password_hash('Admin123!', PASSWORD_DEFAULT),'admin']);
        $message = 'Akun administrator berhasil dibuat.';
    } else {
        $message = 'Tabel users sudah memiliki akun. Setup tidak membuat akun baru.';
    }
} catch (Throwable $e) {
    $message = 'Setup gagal. Pastikan database.sql sudah diimpor dan konfigurasi database benar.';
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Setup BengkelPro</title><link rel="stylesheet" href="assets/style.css"><link rel="stylesheet" href="assets/premium.css"><script defer src="assets/app.js"></script></head><body class="login-body setup-page"><div class="login-wrap"><div class="login-card"><div class="brand-mark">BP</div><div class="eyebrow">PERSIAPAN SISTEM</div><h2>Setup BengkelPro<span class="red-dot">.</span></h2><p><?=htmlspecialchars($message,ENT_QUOTES,'UTF-8')?></p><div class="setup-creds"><strong>Jika akun baru saja dibuat:</strong><br>Email: <code>admin@bengkel.local</code><br>Kata sandi: <code>Admin123!</code></div><p class="alert alert-warn">Penting: demi keamanan, hapus file <code>setup.php</code> setelah setup selesai dan segera ganti kata sandi awal.</p><a class="btn btn-primary btn-wide" href="login.php">Buka halaman login →</a></div></div></body></html>
