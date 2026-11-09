<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout"><?php require __DIR__ . '/../layouts/sidebar.php'; ?><main class="content">
<div class="page-header"><div><h1>Data User</h1><p>Kelola akun Admin, Petugas, dan Owner.</p></div><a href="<?=BASE_URL?>/users/create" class="btn btn-primary">+ Tambah User</a></div>
<?php renderFlash(); ?>
<div class="panel table-wrap"><table><thead><tr><th>No</th><th>Nama</th><th>Username</th><th>Role</th><th>No. HP</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($items as $i=>$row): $self = (int)$row['id_user'] === (int)$_SESSION['user']['id_user']; ?><tr><td><?=$i+1?></td><td><?=e($row['nama_lengkap'])?><?=$self?' <span class="muted-inline">(Anda)</span>':''?></td><td><?=e($row['username'])?></td><td><?=e($row['nama_role'])?></td><td><?=e($row['no_hp'] ?? '') !== '' ? e($row['no_hp']) : '-'?></td><td><span class="badge <?=badgeClass($row['status'])?>"><?=e($row['status'])?></span></td><td class="actions"><a class="btn btn-warning btn-sm" href="<?=BASE_URL?>/users/edit?id=<?=$row['id_user']?>">Edit</a><?php if(!$self): ?><form method="POST" action="<?=BASE_URL?>/users/delete?id=<?=$row['id_user']?>" class="inline-form" onsubmit="return confirm('Hapus user ini?')"><?=csrfField()?><button type="submit" class="btn btn-danger btn-sm">Hapus</button></form><?php endif; ?></td></tr><?php endforeach;?>
<?php if(!$items):?><tr><td colspan="7" class="empty">Belum ada data.</td></tr><?php endif;?>
</tbody></table></div></main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
