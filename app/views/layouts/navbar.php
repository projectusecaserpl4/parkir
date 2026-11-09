<?php if (isLoggedIn()): ?>
<header class="navbar navbar-<?= e(currentRole()) ?>">
    <div class="brand"><span class="brand-icon">SP</span>Sistem Parkir</div>
    <div class="nav-user">
        <span><?= e($_SESSION['user']['nama_lengkap']) ?></span>
        <span class="role role-<?= e(currentRole()) ?>"><?= e(ucfirst(currentRole())) ?></span>
        <a href="<?= BASE_URL ?>/logout" class="btn btn-danger btn-sm">Logout</a>
    </div>
</header>
<?php endif; ?>
