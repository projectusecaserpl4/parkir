<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout"><?php require __DIR__ . '/../layouts/sidebar.php'; ?><main class="content">
<div class="page-header"><div><h1>Data Kendaraan</h1><p>Kelola kendaraan milik customer.</p></div><a href="<?=BASE_URL?>/vehicles/create" class="btn btn-primary">+ Tambah Kendaraan</a></div>
<?php renderFlash(); ?>
<div class="panel table-wrap"><table><thead><tr><th>No</th><th>No. Polisi</th><th>Pemilik</th><th>Jenis</th><th>Merk/Model</th><th>Warna</th><th>Tahun</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($vehicles as $i=>$row): $merkModel = trim(($row['merk'] ?? '').' '.($row['model'] ?? '')); ?>
<tr><td><?=$i+1?></td><td><strong><?=e($row['nomor_polisi'])?></strong></td><td><?=e($row['nama_customer'])?></td><td><?=e($row['nama_jenis'])?></td><td><?=$merkModel !== '' ? e($merkModel) : '-'?></td><td><?=e($row['warna'] ?? '') !== '' ? e($row['warna']) : '-'?></td><td><?=e((string)($row['tahun'] ?? '')) !== '' ? e((string)$row['tahun']) : '-'?></td><td><span class="badge <?=badgeClass($row['status'])?>"><?=e($row['status'])?></span></td><td class="actions"><a class="btn btn-warning btn-sm" href="<?=BASE_URL?>/vehicles/edit?id=<?=$row['id_vehicle']?>">Edit</a><form method="POST" action="<?=BASE_URL?>/vehicles/delete?id=<?=$row['id_vehicle']?>" class="inline-form" onsubmit="return confirm('Hapus kendaraan ini?')"><?=csrfField()?><button type="submit" class="btn btn-danger btn-sm">Hapus</button></form></td></tr>
<?php endforeach;?>
<?php if(!$vehicles):?><tr><td colspan="9" class="empty">Belum ada data.</td></tr><?php endif;?>
</tbody></table></div></main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
