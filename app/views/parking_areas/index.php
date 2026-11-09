<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout"><?php require __DIR__ . '/../layouts/sidebar.php'; ?><main class="content">
<div class="page-header"><div><h1>Area Parkir</h1><p>Kelola lokasi area parkir.</p></div><a href="<?=BASE_URL?>/parking-areas/create" class="btn btn-primary">+ Tambah Area</a></div>
<?php renderFlash(); ?>
<div class="panel table-wrap"><table><thead><tr><th>No</th><th>Nama Area</th><th>Lokasi</th><th>Kapasitas</th><th>Slot Dibuat</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($items as $i=>$row):?><tr><td><?=$i+1?></td><td><?=e($row['nama_area'])?></td><td><?=e($row['lokasi'] ?? '') !== '' ? e($row['lokasi']) : '-'?></td><td><?=(int)$row['kapasitas']?></td><td><?=(int)$row['jumlah_slot']?></td><td><span class="badge <?=badgeClass($row['status'])?>"><?=e($row['status'])?></span></td><td class="actions"><a class="btn btn-warning btn-sm" href="<?=BASE_URL?>/parking-areas/edit?id=<?=$row['id_area']?>">Edit</a><form method="POST" action="<?=BASE_URL?>/parking-areas/delete?id=<?=$row['id_area']?>" class="inline-form" onsubmit="return confirm('Hapus area ini?')"><?=csrfField()?><button type="submit" class="btn btn-danger btn-sm">Hapus</button></form></td></tr><?php endforeach;?>
<?php if(!$items):?><tr><td colspan="7" class="empty">Belum ada data.</td></tr><?php endif;?>
</tbody></table></div></main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
