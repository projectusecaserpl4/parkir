<?php
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/navbar.php';
$roleLabel = ucfirst($role ?? currentRole());
?>
<div class="app-layout role-layout role-<?= e($role ?? currentRole()) ?>">
    <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="content">
        <?php renderFlash(); ?>
        <div class="page-header dashboard-hero">
            <div>
                <span class="eyebrow">SISTEM PARKIR / <?= e(strtoupper($roleLabel)) ?></span>
                <h1>Dashboard <?= e($roleLabel) ?></h1>
                <p>Halo, <?= e($_SESSION['user']['nama_lengkap']) ?>. <?= e($welcome ?? '') ?></p>
            </div>
            <div class="role-stamp"><?= e(strtoupper($roleLabel)) ?></div>
        </div>
        <?php if (($role ?? '') === 'owner'): ?>
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-label">Total Pendapatan</div><div class="stat-number stat-money">Rp <?= number_format($stats['revenue'], 0, ',', '.') ?></div></div>
                <div class="stat-card"><div class="stat-label">Customer</div><div class="stat-number"><?= number_format($stats['customers']) ?></div></div>
                <div class="stat-card"><div class="stat-label">Kendaraan Terdaftar</div><div class="stat-number"><?= number_format($stats['vehicles']) ?></div></div>
                <div class="stat-card"><div class="stat-label">Sedang Parkir</div><div class="stat-number"><?= number_format($stats['activeParking']) ?></div></div>
                <div class="stat-card"><div class="stat-label">Pembayaran Tertunda</div><div class="stat-number"><?= number_format($stats['pendingPayments']) ?></div></div>
            </div>
            <section class="panel role-panel"><h2>Panel Owner</h2><p>Pantau pendapatan, transaksi pembayaran, kendaraan yang sedang parkir, data master, dan pengguna dari satu tempat.</p><a class="btn btn-primary" href="<?= BASE_URL ?>/parking-payments">Lihat pembayaran</a></section>
        <?php elseif (($role ?? '') === 'admin'): ?>
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-label">Customer</div><div class="stat-number"><?= number_format($stats['customers']) ?></div></div>
                <div class="stat-card"><div class="stat-label">Kendaraan</div><div class="stat-number"><?= number_format($stats['vehicles']) ?></div></div>
                <div class="stat-card"><div class="stat-label">Total Slot</div><div class="stat-number"><?= number_format($stats['slots']) ?></div></div>
                <div class="stat-card"><div class="stat-label">Slot Terisi</div><div class="stat-number"><?= number_format($stats['occupied']) ?></div></div>
                <div class="stat-card"><div class="stat-label">Sedang Parkir</div><div class="stat-number"><?= number_format($stats['activeParking']) ?></div></div>
            </div>
            <section class="panel role-panel"><h2>Panel Admin</h2><p>Kelola pengguna, kendaraan, area dan slot parkir, tarif, serta pantau proses transaksi harian.</p><a class="btn btn-primary" href="<?= BASE_URL ?>/users">Kelola pengguna</a></section>
        <?php else: ?>
            <div class="stats-grid stats-grid-petugas">
                <div class="stat-card"><div class="stat-label">Slot Tersedia</div><div class="stat-number"><?= number_format($stats['available']) ?></div></div>
                <div class="stat-card"><div class="stat-label">Slot Terisi</div><div class="stat-number"><?= number_format($stats['occupied']) ?></div></div>
                <div class="stat-card"><div class="stat-label">Kendaraan Sedang Parkir</div><div class="stat-number"><?= number_format($stats['activeParking']) ?></div></div>
            </div>
            <section class="panel role-panel"><h2>Panel Petugas</h2><p>Gunakan menu operasional untuk mencatat kendaraan masuk, memproses kendaraan keluar, dan mencatat pembayaran.</p><div class="quick-actions"><a class="btn btn-primary" href="<?= BASE_URL ?>/parking-entries/create">+ Kendaraan Masuk</a><a class="btn btn-secondary" href="<?= BASE_URL ?>/parking-exits">Proses Kendaraan Keluar</a><a class="btn btn-secondary" href="<?= BASE_URL ?>/parking-payments">Pembayaran</a></div></section>
        <?php endif; ?>
    </main>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
