<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout">
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="content">
<div class="page-header"><div><h1>Aktivitas Parkir</h1><p>Catatan aktivitas kendaraan dan transaksi yang dilakukan pengguna sistem.</p></div></div>
<div class="panel table-wrap">
<table>
<thead><tr><th>No</th><th>Waktu</th><th>Aktivitas</th><th>Tiket</th><th>Nomor Polisi</th><th>Petugas</th><th>Keterangan</th></tr></thead>
<tbody>
<?php foreach($histories as $i=>$row): ?>
<tr><td><?=$i+1?></td><td><?=formatDateTime($row['waktu_aktivitas'], 'd-m-Y H:i:s')?></td><td><span class="badge"><?=e($row['aktivitas'])?></span></td><td><?=e($row['nomor_tiket'] ?? '-')?></td><td><?=e($row['nomor_polisi'] ?? '-')?></td><td><?=e($row['nama_lengkap'] ?? '-')?></td><td><?=e($row['keterangan'])?></td></tr>
<?php endforeach; ?>
<?php if(!$histories): ?><tr><td colspan="7" class="empty">Belum ada riwayat transaksi.</td></tr><?php endif; ?>
</tbody></table></div>
</main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
