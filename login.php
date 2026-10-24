<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if (!empty($_SESSION['user_id'])) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id,name,email,password,role FROM users WHERE email=? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        redirect('index.php');
    }
    $error = 'Email atau kata sandi tidak sesuai.';
}
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#b50e24"><title>Login — BengkelPro</title><link rel="stylesheet" href="assets/style.css"><link rel="stylesheet" href="assets/premium.css"><script defer src="assets/app.js"></script></head>
<body class="login-body premium-login"><div class="auth-layout">
<section class="auth-visual" aria-label="BengkelPro Workshop Management"><div class="auth-grid"></div><div class="auth-glow"></div>
<div class="auth-visual-inner"><a href="login.php" class="auth-logo"><span class="brand-mark">BP<span class="brand-spark">✦</span></span><span><strong>BENGKEL<span>PRO</span></strong><small>WORKSHOP MANAGEMENT</small></span></a>
<div class="auth-story"><div class="auth-pill"><span class="online-dot"></span> SISTEM MANAJEMEN BENGKEL</div><h1>Drive your<br><em>business</em> forward<span class="auth-period">.</span></h1><p>Semua aktivitas bengkel, dari booking hingga pembayaran, dikelola dalam satu sistem yang cepat dan praktis.</p></div>
<div class="auth-car-scene" aria-hidden="true"><div class="car-halo"></div><svg class="car-svg" viewBox="0 0 640 275" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="paint" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#ff6271"/><stop offset=".46" stop-color="#d91a34"/><stop offset="1" stop-color="#78091c"/></linearGradient><linearGradient id="glass" x1="0" x2="1"><stop stop-color="#091421"/><stop offset="1" stop-color="#3c4252"/></linearGradient></defs><ellipse cx="325" cy="237" rx="269" ry="21" fill="#05040b" opacity=".45"/><path d="M67 178 106 157 160 149 217 89Q235 70 273 67H405Q435 68 462 101L508 147 560 162 588 181 593 215 570 230H91L66 214Z" fill="url(#paint)" stroke="#ff8b93" stroke-width="3"/><path d="m231 92-48 58h313l-45-51q-16-19-41-20H270q-25 0-39 13Z" fill="url(#glass)" stroke="#c8d0d8" stroke-width="3"/><path d="m348 81 2 68" stroke="#9ba7b3" stroke-width="3"/><path d="M81 180h51l-21 18H69Z" fill="#f9e9e4"/><path d="m552 173 32 10 4 18-41-3Z" fill="#fa4054"/><path d="M182 155h312" stroke="#ffb1b5" stroke-width="3" opacity=".5"/><path d="M95 213h470" stroke="#450913" stroke-width="6"/><circle cx="177" cy="221" r="48" fill="#11131b" stroke="#33343b" stroke-width="7"/><circle cx="177" cy="221" r="28" fill="#a7adb7" stroke="#4b515b" stroke-width="6"/><circle cx="177" cy="221" r="11" fill="#343945"/><circle cx="486" cy="221" r="48" fill="#11131b" stroke="#33343b" stroke-width="7"/><circle cx="486" cy="221" r="28" fill="#a7adb7" stroke="#4b515b" stroke-width="6"/><circle cx="486" cy="221" r="11" fill="#343945"/><path d="M365 169h33" stroke="#f8b4bc" stroke-width="4" stroke-linecap="round"/></svg></div>
<div class="auth-visual-footer"><span>BUILT FOR THE MODERN WORKSHOP</span><span>01 / 03</span></div></div></section>
<section class="auth-form-side"><div class="auth-mobile-logo"><span class="brand-mark">BP</span><strong>BengkelPro</strong></div><div class="auth-form-inner"><div class="form-step">PORTAL ADMINISTRATOR <span class="step-line"></span> 01</div><h2>Selamat datang<span class="red-dot">.</span></h2><p class="auth-subtitle">Masuk ke akun Anda untuk mulai mengelola operasional bengkel.</p>
<?php if ($error): ?><div class="alert alert-error" role="alert"><?=e($error)?></div><?php endif; ?>
<form method="post" class="auth-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><div class="auth-field"><label for="email">Alamat email</label><div class="input-shell"><span class="field-symbol" aria-hidden="true">✉</span><input id="email" type="email" name="email" placeholder="nama@bengkel.com" autocomplete="username" required autofocus></div></div><div class="auth-field"><label for="password">Kata sandi</label><div class="input-shell"><span class="field-symbol" aria-hidden="true">⚿</span><input id="password" type="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required><button type="button" class="password-toggle" data-password-toggle aria-label="Tampilkan kata sandi" aria-pressed="false"><span data-eye>◉</span></button></div></div><div class="auth-assurance"><span>🔒 &nbsp; Koneksi sesi terlindungi</span></div><button class="btn btn-primary auth-submit" type="submit"><span>Masuk ke Dashboard</span><span class="btn-arrow">↗</span></button></form>
<div class="auth-help">Belum memiliki akun? <span>Hubungi administrator sistem.</span></div></div><div class="auth-form-footer"><span>© <?=date('Y')?> BengkelPro</span><span>POWERED BY PRECISION <b>✦</b></span></div></section></div></body></html>