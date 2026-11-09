<?php
if (!isset($title)) $title = 'Akses Ditolak';
require __DIR__ . '/../layouts/header.php';
?>
<main class="forbidden-page">
    <div class="forbidden-card">
        <div class="forbidden-code">403</div>
        <h1>Akses Ditolak</h1>
        <p>Akun dengan role <strong><?= e(ucfirst(currentRole())) ?></strong> tidak memiliki izin untuk membuka halaman ini.</p>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/dashboard">Kembali ke Dashboard</a>
    </div>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
