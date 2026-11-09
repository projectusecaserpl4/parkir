<?php

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}

/**
 * Normalisasi role agar tetap cocok jika database menyimpan "Petugas",
 * "petugas", atau variasi kapitalisasi lainnya.
 */
function currentRole(): string
{
    return strtolower(trim((string) ($_SESSION['user']['nama_role'] ?? '')));
}

function showForbidden(): never
{
    http_response_code(403);
    $title = 'Akses Ditolak';
    require __DIR__ . '/../views/errors/403.php';
    exit;
}

function requireRole(array $allowedRoles): void
{
    requireLogin();

    $allowed = array_map(
        static fn ($role) => strtolower(trim((string) $role)),
        $allowedRoles
    );

    if (!in_array(currentRole(), $allowed, true)) {
        showForbidden();
    }
}

/* ------------------------------------------------------------------
 | Flash message & isian lama form
 ------------------------------------------------------------------ */

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return $value;
}

/** Simpan beberapa pesan error sekaligus sebagai satu flash 'error'. */
function flashErrors(array $errors): void
{
    $errors = array_values(array_filter($errors, 'strlen'));
    if (count($errors) === 1) {
        flash('error', $errors[0]);
        return;
    }
    flash('error', "Periksa kembali isian Anda:\n- " . implode("\n- ", $errors));
}

/** Tampilkan semua flash message (success/error/warning) sekali saja per halaman. */
function renderFlash(): void
{
    foreach (['success' => 'alert-success', 'error' => 'alert-danger', 'warning' => 'alert-warning'] as $key => $class) {
        $message = flash($key);
        if ($message !== null && $message !== '') {
            echo '<div class="alert ' . $class . '" role="alert">' . nl2br(e($message)) . '</div>';
        }
    }
}

function setOld(array $data): void
{
    unset($data['password'], $data['_csrf']);
    $_SESSION['_old'] = $data;
}

/** Ambil isian lama form (setelah gagal validasi). Jika tidak ada, pakai $default. */
function old(string $key, $default = ''): string
{
    static $old = null;
    if ($old === null) {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);
    }

    $value = array_key_exists($key, $old) ? $old[$key] : $default;
    return is_scalar($value) ? (string) $value : '';
}

/* ------------------------------------------------------------------
 | CSRF
 ------------------------------------------------------------------ */

function csrfToken(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrfToken()) . '">';
}

function verifyCsrfToken(): bool
{
    $sent = $_POST['_csrf'] ?? '';
    $real = $_SESSION['_csrf'] ?? '';
    return is_string($sent) && $real !== '' && hash_equals($real, $sent);
}

/* ------------------------------------------------------------------
 | Input, URL, dan tampilan
 ------------------------------------------------------------------ */

/** Ambil nilai POST sebagai string (array/abnormal -> string kosong). */
function post(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}

function queryId(): int
{
    $value = $_GET['id'] ?? 0;
    return is_string($value) ? max(0, (int) $value) : 0;
}

/** Path aplikasi saat ini tanpa BASE_URL, mis. "/customers/edit". */
function currentPath(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $base = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');

    if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
        $path = substr($path, strlen($base));
    }
    if ($path === '/public' || str_starts_with($path, '/public/')) {
        $path = substr($path, strlen('/public'));
    }

    $path = '/' . trim($path, '/');
    return $path === '' ? '/' : $path;
}

function isActivePath(string $menuPath): bool
{
    $current = currentPath();
    return $current === $menuPath || str_starts_with($current, $menuPath . '/');
}

function formatDateTime(?string $value, string $format = 'd-m-Y H:i'): string
{
    if ($value === null || $value === '') {
        return '-';
    }
    $time = strtotime($value);
    return $time === false ? '-' : date($format, $time);
}

function rupiah($amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

/** Kelas warna badge untuk status. */
function badgeClass(?string $status): string
{
    return match (strtolower((string) $status)) {
        'aktif', 'tersedia', 'kosong', 'lunas', 'keluar', 'selesai' => 'badge-success',
        'terisi', 'parkir', 'belum_bayar' => 'badge-warning',
        'nonaktif', 'ditutup', 'rusak', 'dibatalkan' => 'badge-danger',
        default => '',
    };
}

/* ------------------------------------------------------------------
 | Hak akses per role:
 | - owner  : seluruh modul kecuali Aktivitas Parkir & Rekap Transaksi
 | - admin  : master data, operasional, Aktivitas Parkir, Rekap Transaksi
 | - petugas: transaksi operasional saja
 ------------------------------------------------------------------ */
function authorizeRoute(string $method, string $path): void
{
    if (!isLoggedIn() || $path === '/login' || $path === '/logout') {
        return;
    }

    $role = currentRole();

    // Aktivitas Parkir dan Rekap Transaksi khusus ADMIN.
    if (in_array($path, ['/parking-history', '/rekap-transaksi'], true) && $role !== 'admin') {
        showForbidden();
    }

    if ($role === 'owner' || $role === 'admin') {
        return;
    }

    $petugasRoutes = [
        '/dashboard',
        '/parking-entries',
        '/parking-entries/create',
        '/parking-entries/slots',
        '/parking-entries/store',
        '/parking-exits',
        '/parking-exits/create',
        '/parking-exits/store',
        '/parking-payments',
        '/parking-payments/create',
        '/parking-payments/store',
        '/parking-payments/receipt',
    ];

    if ($role === 'petugas' && in_array($path, $petugasRoutes, true)) {
        return;
    }

    showForbidden();
}
