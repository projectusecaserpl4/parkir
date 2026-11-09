<?php if (isLoggedIn()): ?>
<?php
$role = currentRole();
$navLink = static function (string $path, string $label): void {
    $class = isActivePath($path) ? ' class="active" aria-current="page"' : '';
    echo '<a href="' . BASE_URL . $path . '"' . $class . '>' . e($label) . '</a>';
};
?>
<aside class="sidebar sidebar-<?= e($role) ?>">
    <div class="sidebar-heading">
        <span class="sidebar-mark">SP</span>
        <div><strong><?= e(ucfirst($role)) ?></strong><small>Panel <?= e(ucfirst($role)) ?></small></div>
    </div>
    <?php $navLink('/dashboard', 'Dashboard'); ?>
    <?php if (in_array($role, ['owner', 'admin'], true)): ?>
        <div class="menu-section">Master Data</div>
        <?php
        $navLink('/customers', 'Customer');
        $navLink('/vehicles', 'Kendaraan');
        $navLink('/vehicle-types', 'Jenis Kendaraan');
        $navLink('/parking-areas', 'Area Parkir');
        $navLink('/parking-slots', 'Slot Parkir');
        $navLink('/parking-rates', 'Tarif Parkir');
        $navLink('/users', 'User & Hak Akses');
        ?>
    <?php endif; ?>
    <div class="menu-section">Operasional</div>
    <?php
    $navLink('/parking-entries', 'Kendaraan Masuk');
    $navLink('/parking-exits', 'Kendaraan Keluar');
    $navLink('/parking-payments', 'Pembayaran');
    ?>
    <?php if ($role === 'admin'): ?>
        <div class="menu-section">Monitoring & Laporan</div>
        <?php
        $navLink('/parking-history', 'Aktivitas Parkir');
        $navLink('/rekap-transaksi', 'Rekap Transaksi');
        ?>
    <?php endif; ?>
</aside>
<?php endif; ?>
