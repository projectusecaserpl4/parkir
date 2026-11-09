<?php
$title = 'Login - Sistem Parkir';
require __DIR__ . '/../layouts/header.php';
?>

<div class="login-page">
    <div class="login-card">
        <h1>Sistem Parkir</h1>
        <p class="muted">Silakan login untuk masuk ke sistem.</p>

        <?php renderFlash(); ?>

        <form action="<?= BASE_URL ?>/login" method="POST" autocomplete="off">
            <?= csrfField() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Masukkan username"
                    maxlength="50"
                    value="<?= e(old('username')) ?>"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    maxlength="255"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Login
            </button>
        </form>

    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
