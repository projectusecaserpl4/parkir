<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout">
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="content">
<div class="page-header">
    <div><h1>Kendaraan Masuk</h1><p>Catat kendaraan yang masuk ke area parkir.</p></div>
    <a href="<?= BASE_URL ?>/parking-entries/create" class="btn btn-primary">+ Kendaraan Masuk</a>
</div>
<?php renderFlash(); ?>
<div class="panel table-wrap">
<table>
<thead><tr><th>No</th><th>Tiket</th><th>Nomor Polisi</th><th>Customer</th><th>Jenis</th><th>Slot</th><th>Waktu Masuk</th><th>Petugas</th></tr></thead>
<tbody>
<?php foreach($entries as $i=>$row): ?>
<tr>
<td><?=$i+1?></td>
<td><?=e($row['nomor_tiket'])?></td>
<td><strong><?=e($row['nomor_polisi'])?></strong></td>
<td><?=e($row['nama_customer'])?></td>
<td><?=e($row['nama_jenis'])?></td>
<td><?=e($row['kode_slot'] ?? '-')?> <span class="muted-inline"><?=e($row['nama_area'] ?? '')?></span></td>
<td><?=formatDateTime($row['waktu_masuk'])?></td>
<td><?=e($row['petugas'])?></td>
</tr>
<?php endforeach; ?>
<?php if(!$entries): ?><tr><td colspan="8" class="empty">Tidak ada kendaraan yang sedang parkir.</td></tr><?php endif; ?>
</tbody></table></div>
</main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
