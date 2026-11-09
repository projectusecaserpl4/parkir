<?php
if (!isset($title)) $title = 'Sesi Kedaluwarsa';
require __DIR__ . '/../layouts/header.php';
?>
<main class="forbidden-page">
    <div class="forbidden-card">
        <div class="forbidden-code">419</div>
        <h1>Sesi Formulir Kedaluwarsa</h1>
        <p>Formulir yang Anda kirim sudah tidak berlaku (sesi berakhir atau halaman dibuka terlalu lama). Silakan muat ulang halaman lalu ulangi aksi Anda.</p>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/<?= isLoggedIn() ? 'dashboard' : 'login' ?>"><?= isLoggedIn() ? 'Kembali ke Dashboard' : 'Ke Halaman Login' ?></a>
    </div>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
