<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout">
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="content">
<div class="page-header">
    <div><h1>Data Customer</h1><p>Kelola pemilik kendaraan.</p></div>
    <a href="<?= BASE_URL ?>/customers/create" class="btn btn-primary">+ Tambah Customer</a>
</div>
<?php renderFlash(); ?>
<div class="panel table-wrap">
<table>
<thead><tr><th>No</th><th>Nama</th><th>NIK</th><th>No. HP</th><th>Alamat</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach($customers as $i=>$row): ?>
<tr>
<td><?=$i+1?></td><td><?=e($row['nama_customer'])?></td><td><?=e($row['nik'] ?? '') !== '' ? e($row['nik']) : '-'?></td>
<td><?=e($row['no_hp'] ?? '') !== '' ? e($row['no_hp']) : '-'?></td><td><?=e($row['alamat'] ?? '') !== '' ? e($row['alamat']) : '-'?></td>
<td class="actions">
<a class="btn btn-warning btn-sm" href="<?=BASE_URL?>/customers/edit?id=<?=$row['id_customer']?>">Edit</a>
<form method="POST" action="<?=BASE_URL?>/customers/delete?id=<?=$row['id_customer']?>" class="inline-form" onsubmit="return confirm('Hapus customer ini?')"><?=csrfField()?><button type="submit" class="btn btn-danger btn-sm">Hapus</button></form>
</td>
</tr>
<?php endforeach; ?>
<?php if(!$customers): ?><tr><td colspan="6" class="empty">Belum ada data.</td></tr><?php endif; ?>
</tbody></table></div>
</main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
