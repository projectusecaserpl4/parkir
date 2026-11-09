<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout"><?php require __DIR__ . '/../layouts/sidebar.php'; ?><main class="content">
<div class="page-header"><div><h1>Jenis Kendaraan</h1><p>Kelola kategori kendaraan.</p></div><a href="<?=BASE_URL?>/vehicle-types/create" class="btn btn-primary">+ Tambah Jenis</a></div>
<?php renderFlash(); ?>
<div class="panel table-wrap"><table><thead><tr><th>No</th><th>Nama Jenis</th><th>Deskripsi</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($items as $i=>$row):?><tr><td><?=$i+1?></td><td><?=e($row['nama_jenis'])?></td><td><?=e($row['deskripsi'] ?? '') !== '' ? e($row['deskripsi']) : '-'?></td><td class="actions"><a class="btn btn-warning btn-sm" href="<?=BASE_URL?>/vehicle-types/edit?id=<?=$row['id_jenis_kendaraan']?>">Edit</a><form method="POST" action="<?=BASE_URL?>/vehicle-types/delete?id=<?=$row['id_jenis_kendaraan']?>" class="inline-form" onsubmit="return confirm('Hapus data ini?')"><?=csrfField()?><button type="submit" class="btn btn-danger btn-sm">Hapus</button></form></td></tr><?php endforeach;?>
<?php if(!$items):?><tr><td colspan="4" class="empty">Belum ada data.</td></tr><?php endif;?>
</tbody></table></div></main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
