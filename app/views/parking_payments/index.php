<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout">
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="content">
<div class="page-header">
    <div>
        <h1>Pembayaran</h1>
        <p>Kelola pembayaran tiket parkir yang sudah selesai keluar.</p>
    </div>
</div>

<?php renderFlash(); ?>

<div class="stats-grid payment-stats">
    <div class="stat-card"><div class="stat-label">Total Transaksi</div><div class="stat-number"><?=number_format($summary['total_transaksi'])?></div></div>
    <div class="stat-card"><div class="stat-label">Belum Bayar</div><div class="stat-number"><?=number_format($summary['belum_bayar'])?></div></div>
    <div class="stat-card"><div class="stat-label">Sudah Lunas</div><div class="stat-number"><?=number_format($summary['lunas'])?></div></div>
    <div class="stat-card"><div class="stat-label">Total Pendapatan</div><div class="stat-number">Rp <?=number_format($summary['total_lunas'],0,',','.')?></div></div>
</div>

<div class="panel table-wrap">
<table>
<thead>
<tr><th>No</th><th>Tiket</th><th>Nomor Polisi</th><th>Customer</th><th>Lama</th><th>Total</th><th>Dibayar</th><th>Kembalian</th><th>Metode</th><th>Status</th><th>Aksi</th></tr>
</thead>
<tbody>
<?php foreach($payments as $i=>$row): ?>
<tr>
<td><?=$i+1?></td>
<td><?=e($row['nomor_tiket'])?></td>
<td><strong><?=e($row['nomor_polisi'])?></strong></td>
<td><?=e($row['nama_customer'])?></td>
<td><?=e((string)($row['lama_parkir'] ?? '-'))?> jam</td>
<td>Rp <?=number_format((float)$row['total_bayar'],0,',','.')?></td>
<td><?= $row['status']==='lunas' ? rupiah($row['jumlah_dibayar'] ?? 0) : '-' ?></td>
<td><?= $row['status']==='lunas' ? rupiah($row['kembalian'] ?? 0) : '-' ?></td>
<td><?= $row['status']==='lunas' ? e(strtoupper($row['metode_pembayaran'])) : '-' ?></td>
<td><span class="badge <?=badgeClass($row['status'])?>"><?=e(strtoupper(str_replace('_',' ',$row['status'])))?></span></td>
<td class="actions">
<?php if($row['status']==='belum_bayar'): ?>
<a class="btn btn-primary btn-sm" href="<?=BASE_URL?>/parking-payments/create?id=<?=$row['id_payment']?>">Bayar</a>
<?php else: ?>
<a class="btn btn-secondary btn-sm" target="_blank" href="<?=BASE_URL?>/parking-payments/receipt?id=<?=$row['id_payment']?>">Cetak Struk</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php if(!$payments): ?><tr><td colspan="11" class="empty">Belum ada data pembayaran.</td></tr><?php endif; ?>
</tbody>
</table>
</div>
</main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
