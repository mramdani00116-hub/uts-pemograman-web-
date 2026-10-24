<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verify_csrf(): void {
    $sent = $_POST['csrf_token'] ?? '';
    if (!$sent || !hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(419);
        exit('Sesi formulir tidak valid atau kedaluwarsa. Silakan kembali dan coba lagi.');
    }
}
function rupiah($number): string {
    return 'Rp ' . number_format((float)$number, 0, ',', '.');
}
function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function take_flash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
function status_badge(?string $status): string {
    $map = [
        'Baru' => 'blue', 'Dikonfirmasi' => 'blue', 'Menunggu' => 'amber',
        'Diproses' => 'amber', 'Dikerjakan' => 'amber', 'Selesai' => 'green',
        'Dibatalkan' => 'red', 'Belum Lunas' => 'red', 'Lunas' => 'green',
        'Sebagian' => 'amber', 'Aktif' => 'green', 'Nonaktif' => 'gray'
    ];
    $color = $map[$status ?? ''] ?? 'gray';
    return '<span class="badge badge-' . $color . '">' . e($status ?: '-') . '</span>';
}
function field_options(array $field, PDO $pdo): array {
    if (isset($field['options'])) return $field['options'];
    if (!empty($field['options_sql'])) {
        $rows = $pdo->query($field['options_sql'])->fetchAll();
        $result = [];
        foreach ($rows as $row) $result[$row[$field['option_value'] ?? 'id']] = $row[$field['option_label'] ?? 'name'];
        return $result;
    }
    return [];
}
