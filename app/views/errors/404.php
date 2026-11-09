<?php
$title = '404 - Halaman Tidak Ditemukan';
require __DIR__ . '/../layouts/header.php';
?>

<div class="error-page">
    <h1>404</h1>
    <h2>Halaman tidak ditemukan</h2>
    <p>URL yang Anda buka tidak tersedia.</p>
    <a href="<?= BASE_URL ?>/<?= isLoggedIn() ? 'dashboard' : 'login' ?>" class="btn btn-primary"><?= isLoggedIn() ? 'Kembali ke Dashboard' : 'Ke Halaman Login' ?></a>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
