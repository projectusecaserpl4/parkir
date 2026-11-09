<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout">
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="content">
<div class="page-header"><div><h1>Kendaraan Keluar</h1><p>Proses kendaraan yang selesai parkir dan hitung tarif otomatis.</p></div></div>
<?php renderFlash(); ?>
<div class="panel table-wrap">
<table>
<thead><tr><th>No</th><th>Tiket</th><th>Nomor Polisi</th><th>Customer</th><th>Slot</th><th>Waktu Masuk</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach($entries as $i=>$row): ?>
<tr>
<td><?=$i+1?></td><td><?=e($row['nomor_tiket'])?></td><td><strong><?=e($row['nomor_polisi'])?></strong></td>
<td><?=e($row['nama_customer'])?></td><td><?=e($row['kode_slot'] ?? '-')?></td>
<td><?=formatDateTime($row['waktu_masuk'])?></td>
<td><a class="btn btn-primary btn-sm" href="<?=BASE_URL?>/parking-exits/create?id=<?=$row['id_entry']?>">Proses Keluar</a></td>
</tr>
<?php endforeach; ?>
<?php if(!$entries): ?><tr><td colspan="7" class="empty">Tidak ada kendaraan aktif.</td></tr><?php endif; ?>
</tbody></table></div>
</main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
