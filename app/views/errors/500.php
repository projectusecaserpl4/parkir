<?php
if (!isset($title)) $title = 'Terjadi Kesalahan';
require __DIR__ . '/../layouts/header.php';
?>
<main class="forbidden-page">
    <div class="forbidden-card">
        <div class="forbidden-code">500</div>
        <h1>Terjadi Kesalahan</h1>
        <p>Maaf, terjadi kesalahan pada sistem saat memproses permintaan Anda. Silakan coba lagi. Jika masalah berlanjut, hubungi administrator.</p>
        <a class="btn btn-primary" href="<?= BASE_URL ?>/<?= isLoggedIn() ? 'dashboard' : 'login' ?>"><?= isLoggedIn() ? 'Kembali ke Dashboard' : 'Ke Halaman Login' ?></a>
    </div>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
