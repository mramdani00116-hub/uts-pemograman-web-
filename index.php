<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
$modules = require __DIR__ . '/includes/modules.php';
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = array_merge(['dashboard'], array_keys($modules));
if (!in_array($page, $allowedPages, true)) $page = 'dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $modKey = $_POST['module'] ?? '';
    if (isset($modules[$modKey]) && in_array($action, ['save','delete'], true)) {
        $mod = $modules[$modKey];
        $table = $mod['table'];
        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            try {
                $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id=?");
                $stmt->execute([$id]);
                flash('success', $mod['singular'] . ' berhasil dihapus.');
            } catch (PDOException $e) {
                flash('error', 'Data tidak dapat dihapus karena masih dipakai oleh data lain.');
            }
            redirect('index.php?page=' . $modKey);
        }
        $id = (int)($_POST['id'] ?? 0);
        $values = [];
        $errors = [];
        foreach ($mod['fields'] as $name => $field) {
            $value = $_POST[$name] ?? ($field['default'] ?? '');
            if (is_string($value)) $value = trim($value);
            if (!empty($field['required']) && ($value === '' || $value === null)) $errors[] = $field['label'] . ' wajib diisi.';
            if ($value === '' && (!empty($field['nullable']) || in_array($field['type'], ['select','date','time','datetime-local','email','textarea','text'], true))) $value = null;
            if ($field['type'] === 'datetime-local' && $value) $value = str_replace('T', ' ', $value) . (strlen($value) === 16 ? ':00' : '');
            if (in_array($field['type'], ['number'], true) && $value !== null && $value !== '') {
                if (!is_numeric($value)) $errors[] = $field['label'] . ' harus berupa angka.';
                else $value = $value + 0;
            }
            $values[$name] = $value;
        }
        if ($modKey === 'invoices' && isset($values['paid_amount'], $values['total']) && $values['paid_amount'] !== null && $values['total'] !== null) {
            if ((float)$values['paid_amount'] >= (float)$values['total']) $values['status'] = 'Lunas';
            elseif ((float)$values['paid_amount'] > 0) $values['status'] = 'Sebagian';
            else $values['status'] = 'Belum Lunas';
        }
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('index.php?page=' . $modKey . '&action=' . ($id ? 'edit&id=' . $id : 'new'));
        }
        try {
            if ($id) {
                $sets = array_map(fn($k) => "`{$k}`=?", array_keys($values));
                $sql = "UPDATE `{$table}` SET " . implode(',', $sets) . ' WHERE id=?';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([...array_values($values), $id]);
                flash('success', $mod['singular'] . ' berhasil diperbarui.');
            } else {
                $cols = array_map(fn($k) => "`{$k}`", array_keys($values));
                $marks = array_fill(0, count($values), '?');
                $sql = "INSERT INTO `{$table}` (" . implode(',', $cols) . ') VALUES (' . implode(',', $marks) . ')';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_values($values));
                flash('success', $mod['singular'] . ' berhasil ditambahkan.');
            }
            redirect('index.php?page=' . $modKey);
        } catch (PDOException $e) {
            flash('error', 'Gagal menyimpan data. Periksa data wajib dan pastikan tidak ada kode/nomor yang duplikat.');
            redirect('index.php?page=' . $modKey . '&action=' . ($id ? 'edit&id=' . $id : 'new'));
        }
    }
}

$flash = take_flash();
$action = $_GET['action'] ?? '';
$record = null;
$module = $modules[$page] ?? null;
if ($module && in_array($action, ['new','edit'], true)) {
    if ($action === 'edit') {
        $stmt = $pdo->prepare('SELECT * FROM `' . $module['table'] . '` WHERE id=?');
        $stmt->execute([(int)($_GET['id'] ?? 0)]);
        $record = $stmt->fetch();
        if (!$record) { flash('error','Data tidak ditemukan.'); redirect('index.php?page='.$page); }
    } else $record = [];
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?=e($module['title'] ?? 'Dashboard')?> · BengkelPro</title><link rel="stylesheet" href="assets/style.css"><link rel="stylesheet" href="assets/premium.css"><script defer src="assets/app.js"></script></head><body><div class="app-shell"><div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div><aside class="sidebar" id="sidebar"><a class="brand" href="index.php"><span class="brand-mark">BP<span class="brand-spark">✦</span></span><span><strong>BengkelPro</strong><small>WORKSHOP SYSTEM</small></span></a><div class="nav-caption">MENU UTAMA</div><a class="nav-link <?= $page==='dashboard'?'active':'' ?>" href="index.php"><span class="nav-icon">▦</span>Dashboard</a><div class="nav-caption">OPERASIONAL</div><?php foreach (['bookings','customers','vehicles','services','spare_parts','invoices'] as $key): ?><a class="nav-link <?= $page===$key?'active':'' ?>" href="index.php?page=<?=$key?>"><span class="nav-icon"><?=e($modules[$key]['icon'])?></span><?=e($modules[$key]['title'])?></a><?php endforeach; ?><div class="sidebar-bottom"><div class="user-mini"><div class="avatar"><?=e(strtoupper(substr($_SESSION['user_name'] ?? 'A',0,1)))?></div><div><strong><?=e($_SESSION['user_name'] ?? 'Admin')?></strong><small>Administrator</small></div></div><a class="logout-link" href="logout.php">↪ Keluar</a></div></aside><main class="main-area"><header class="topbar"><button class="mobile-menu" data-menu-toggle aria-expanded="false" aria-label="Buka menu">☰</button><div><div class="breadcrumb">BengkelPro <span>/</span> <?=e($module['title'] ?? 'Dashboard')?></div><h1><?=e($action==='new'?'Tambah '.$module['singular']:($action==='edit'?'Edit '.$module['singular']:($module['title'] ?? 'Dashboard')))?></h1></div><div class="topbar-right"><button class="top-search-trigger" type="button" data-command-trigger title="Navigasi cepat (Ctrl+K)" aria-label="Buka navigasi cepat"><span>⌕</span><kbd>Ctrl K</kbd></button><div class="today-chip"><span class="online-dot"></span> Sistem aktif</div><div class="top-date"><?=date('d M Y')?></div></div></header><section class="content">
<?php if ($flash): ?><div class="alert <?= $flash['type']==='success'?'alert-success':'alert-error' ?>"><?=e($flash['message'])?></div><?php endif; ?>
<?php if ($page === 'dashboard'): ?>
<?php
$stats = [
 ['label'=>'Total pelanggan','value'=>(int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn(),'icon'=>'♙','tone'=>'purple','note'=>'Pelanggan terdaftar'],
 ['label'=>'Kendaraan','value'=>(int)$pdo->query('SELECT COUNT(*) FROM vehicles')->fetchColumn(),'icon'=>'🚘','tone'=>'blue','note'=>'Kendaraan tercatat'],
 ['label'=>'Booking aktif','value'=>(int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status IN ('Baru','Dikonfirmasi','Diproses')")->fetchColumn(),'icon'=>'▦','tone'=>'orange','note'=>'Perlu ditindaklanjuti'],
 ['label'=>'Tagihan belum lunas','value'=>(int)$pdo->query("SELECT COUNT(*) FROM invoices WHERE status != 'Lunas'")->fetchColumn(),'icon'=>'▧','tone'=>'green','note'=>'Perlu penyelesaian pembayaran'],
];
$revenue = (float)$pdo->query("SELECT COALESCE(SUM(paid_amount),0) FROM invoices")->fetchColumn();
$pending = (float)$pdo->query("SELECT COALESCE(SUM(total-paid_amount),0) FROM invoices WHERE status!='Lunas'")->fetchColumn();
$lowStock = $pdo->query('SELECT name,stock_qty,min_stock FROM spare_parts WHERE stock_qty<=min_stock ORDER BY stock_qty ASC LIMIT 5')->fetchAll();
$recent = $pdo->query('SELECT b.id,b.booking_date,b.booking_time,b.status,c.name AS customer_name,v.plate_number,s.name AS service_name FROM bookings b LEFT JOIN customers c ON c.id=b.customer_id LEFT JOIN vehicles v ON v.id=b.vehicle_id LEFT JOIN services s ON s.id=b.service_id ORDER BY b.id DESC LIMIT 6')->fetchAll();
?>
<div class="welcome-banner"><div class="banner-grid"></div><div><div class="eyebrow light">RINGKASAN OPERASIONAL</div><h2>Halo, <?=e($_SESSION['user_name'] ?? 'Admin')?> 👋</h2><p>Berikut ringkasan aktivitas bengkel Anda hari ini.</p></div><div class="welcome-art"><div class="art-ring ring-one"></div><div class="art-ring ring-two"></div><div class="art-car">🏎</div></div></div>
<div class="stats-grid"><?php foreach($stats as $s): ?><div class="stat-card"><div class="stat-top"><span><?=e($s['label'])?></span><span class="stat-icon tone-<?=e($s['tone'])?>"><?=e($s['icon'])?></span></div><div class="stat-number"><?=number_format($s['value'],0,',','.')?></div><div class="stat-note"><span class="note-dot tone-bg-<?=e($s['tone'])?>"></span><?=e($s['note'])?></div></div><?php endforeach; ?></div>
<div class="summary-grid"><div class="panel"><div class="panel-heading"><div><h3>Booking terbaru</h3><p>Jadwal servis yang baru masuk</p></div><a class="text-link" href="index.php?page=bookings">Lihat semua →</a></div><?php if(!$recent): ?><div class="empty-state"><div class="empty-icon">▦</div><strong>Belum ada booking</strong><p>Booking baru akan muncul di sini.</p><a class="btn btn-primary" href="index.php?page=bookings&action=new">+ Buat booking</a></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Pelanggan / Kendaraan</th><th>Servis</th><th>Jadwal</th><th>Status</th></tr></thead><tbody><?php foreach($recent as $r): ?><tr><td><strong><?=e($r['customer_name'])?></strong><small class="cell-sub"><?=e($r['plate_number'])?></small></td><td><?=e($r['service_name'])?></td><td><?=e(date('d M Y',strtotime($r['booking_date'])))?><small class="cell-sub"><?=e(substr((string)$r['booking_time'],0,5))?></small></td><td><?=status_badge($r['status'])?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div><div class="side-stack"><div class="panel money-panel"><div class="panel-heading"><div><h3>Ringkasan keuangan</h3><p>Akumulasi dari tagihan</p></div><span class="money-icon">↗</span></div><div class="money-row"><span>Total pembayaran masuk</span><strong><?=rupiah($revenue)?></strong></div><div class="money-row"><span>Sisa tagihan</span><strong class="text-orange"><?=rupiah($pending)?></strong></div><a class="btn btn-soft btn-wide" href="index.php?page=invoices">Kelola tagihan →</a></div><div class="panel"><div class="panel-heading"><div><h3>Stok menipis</h3><p>Perlu segera diperiksa</p></div><a class="text-link" href="index.php?page=spare_parts">Stok →</a></div><?php if(!$lowStock): ?><div class="stock-ok"><span>✓</span><div><strong>Stok aman</strong><small>Tidak ada stok di bawah batas minimum.</small></div></div><?php else: foreach($lowStock as $item): ?><div class="stock-row"><div><strong><?=e($item['name'])?></strong><small>Batas minimum: <?=e($item['min_stock'])?></small></div><span class="stock-count <?=((int)$item['stock_qty']===0?'stock-zero':'')?>"><?=e($item['stock_qty'])?> unit</span></div><?php endforeach; endif; ?></div></div></div>
<div class="quick-actions"><a href="index.php?page=bookings&action=new"><span class="quick-icon quick-purple">＋</span><span><strong>Booking baru</strong><small>Jadwalkan servis pelanggan</small></span><span class="quick-arrow">→</span></a><a href="index.php?page=invoices&action=new"><span class="quick-icon quick-blue">▧</span><span><strong>Tagihan baru</strong><small>Catat pembayaran servis</small></span><span class="quick-arrow">→</span></a><a href="index.php?page=customers&action=new"><span class="quick-icon quick-orange">♙</span><span><strong>Pelanggan baru</strong><small>Tambah data pelanggan</small></span><span class="quick-arrow">→</span></a></div>
<?php elseif ($module && in_array($action,['new','edit'],true)): ?>
<div class="form-panel"><div class="form-intro"><div class="form-icon"><?=e($module['icon'])?></div><div><h2><?=e($action==='edit'?'Perbarui data':'Isi detail '.$module['singular'])?></h2><p>Lengkapi informasi di bawah ini. Kolom bertanda <span class="required">*</span> wajib diisi.</p></div></div><form method="post" class="data-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="module" value="<?=e($page)?>"><input type="hidden" name="id" value="<?=e($record['id'] ?? '')?>"><div class="form-grid"><?php foreach($module['fields'] as $name=>$field): $value = $record[$name] ?? ($field['default'] ?? ''); if (($field['type']??'')==='datetime-local' && $value) $value = str_replace(' ','T',substr((string)$value,0,16)); ?><div class="form-field <?=($field['type']??'')==='textarea'?'field-full':''?>"><label for="f_<?=e($name)?>"><?=e($field['label'])?> <?php if(!empty($field['required'])): ?><span class="required">*</span><?php endif; ?></label><?php if(($field['type']??'')==='textarea'): ?><textarea id="f_<?=e($name)?>" name="<?=e($name)?>" rows="3" <?=!empty($field['required'])?'required':''?>><?=e($value)?></textarea><?php elseif(($field['type']??'')==='select'): ?><select id="f_<?=e($name)?>" name="<?=e($name)?>" <?=!empty($field['required'])?'required':''?>><option value="">— Pilih <?=e(strtolower($field['label']))?> —</option><?php foreach(field_options($field,$pdo) as $optVal=>$optLabel): ?><option value="<?=e($optVal)?>" <?=((string)$value===(string)$optVal)?'selected':''?>><?=e($optLabel)?></option><?php endforeach; ?></select><?php else: ?><input id="f_<?=e($name)?>" type="<?=e($field['type']??'text')?>" name="<?=e($name)?>" value="<?=e($value)?>" <?=isset($field['min'])?'min="'.e($field['min']).'"':''?> <?=isset($field['max'])?'max="'.e($field['max']).'"':''?> <?=isset($field['step'])?'step="'.e($field['step']).'"':''?> <?=!empty($field['required'])?'required':''?>><?php endif; ?></div><?php endforeach; ?></div><div class="form-actions"><a class="btn btn-ghost" href="index.php?page=<?=e($page)?>">Batal</a><button class="btn btn-primary" type="submit">Simpan data <span>→</span></button></div></form></div>
<?php elseif ($module): ?>
<div class="list-toolbar"><div><p class="muted">Kelola dan perbarui data <?=e(strtolower($module['title']))?> bengkel Anda.</p></div><div class="toolbar-actions"><form class="search-form" method="get"><input type="hidden" name="page" value="<?=e($page)?>"><span>⌕</span><input name="q" value="<?=e($_GET['q']??'')?>" placeholder="Cari data..."><button type="submit">Cari</button></form><a class="btn btn-primary" href="index.php?page=<?=e($page)?>&action=new">＋ Tambah <?=e($module['singular'])?></a></div></div>
<?php
$sql = $module['list_sql']; $params = []; $q = trim($_GET['q'] ?? '');
$orderBy = '';
if (preg_match('/\sORDER BY\s.+$/i', $sql, $orderMatch)) { $orderBy = $orderMatch[0]; $sql = substr($sql, 0, -strlen($orderBy)); }
if ($q !== '' && !empty($module['search'])) { $sql .= ' WHERE (' . implode(' OR ', array_map(fn($c)=>$c.' LIKE ?', $module['search'])) . ')'; foreach($module['search'] as $_) $params[]='%'.$q.'%'; }
$sql .= ' ' . $orderBy;
try { $stmt=$pdo->prepare($sql); $stmt->execute($params); $rows=$stmt->fetchAll(); } catch(Throwable $e) { $rows=[]; }
?>
<div class="panel table-panel"><div class="panel-heading"><div><h3>Daftar <?=e($module['title'])?></h3><p><?=count($rows)?> data ditemukan</p></div><span class="table-total"><?=count($rows)?> data</span></div><div class="table-wrap"><table><thead><tr><?php foreach($module['columns'] as $col=>$label): ?><th><?=e($label)?></th><?php endforeach; ?><th class="actions-head">Aksi</th></tr></thead><tbody><?php if(!$rows): ?><tr><td colspan="<?=count($module['columns'])+1?>"><div class="empty-table"><span>⌕</span><strong>Data belum tersedia</strong><small>Tambah data pertama Anda menggunakan tombol di atas.</small></div></td></tr><?php else: foreach($rows as $row): ?><tr><?php foreach($module['columns'] as $col=>$label): ?><td><?php if(in_array($col,$module['money']??[],true)): ?><strong><?=rupiah($row[$col]??0)?></strong><?php elseif(in_array($col,$module['boolean']??[],true)): ?><?=status_badge(((int)($row[$col]??0)===1)?'Aktif':'Nonaktif')?><?php elseif($col==='status'): ?><?=status_badge($row[$col]??'')?><?php elseif(in_array($col,['booking_date','issued_at','due_date'],true) && !empty($row[$col])): ?><?=e(date('d M Y',strtotime($row[$col])))?><?php elseif($col==='created_at' && !empty($row[$col])): ?><?=e(date('d M Y',strtotime($row[$col])))?><?php elseif(in_array($col,['address','description','complaint','diagnosis'],true)): ?><span class="cell-long" title="<?=e($row[$col]??'')?>"><?=e($row[$col]??'—')?></span><?php else: ?><?=e(($row[$col]??'')!==''?$row[$col]:'—')?><?php endif; ?></td><?php endforeach; ?><td><div class="row-actions"><a class="icon-btn edit-btn" title="Edit" href="index.php?page=<?=e($page)?>&action=edit&id=<?=e($row['id'])?>">✎</a><form method="post" onsubmit="return confirm('Yakin ingin menghapus data ini?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="module" value="<?=e($page)?>"><input type="hidden" name="id" value="<?=e($row['id'])?>"><button class="icon-btn delete-btn" title="Hapus" type="submit">⌫</button></form></div></td></tr><?php endforeach; endif; ?></tbody></table></div></div>
<?php else: ?><div class="panel"><p>Halaman tidak ditemukan.</p></div><?php endif; ?>
<footer class="page-footer"><span>© <?=date('Y')?> BengkelPro</span><span>Dibuat untuk pengelolaan bengkel yang lebih rapi <b>•</b> v2.1 • RED EDITION</span></footer></section></main></div></body></html>
