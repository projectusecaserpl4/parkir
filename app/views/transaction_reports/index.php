<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout">
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="content">
    <div class="page-header"><div><h1>Rekap Transaksi</h1><p>Ringkasan transaksi dan pendapatan pada periode yang dipilih.</p></div></div>

    <div class="panel" style="padding:18px;margin-bottom:20px">
        <form method="get" action="<?= BASE_URL ?>/rekap-transaksi" class="form-grid" style="align-items:end">
            <div class="form-group"><label for="mulai">Tanggal Mulai</label><input type="date" id="mulai" name="mulai" value="<?=e($startDate)?>" max="<?=date('Y-m-d')?>" required></div>
            <div class="form-group"><label for="sampai">Tanggal Sampai</label><input type="date" id="sampai" name="sampai" value="<?=e($endDate)?>" max="<?=date('Y-m-d')?>" required></div>
            <div class="form-group"><button class="btn btn-primary" type="submit">Tampilkan Rekap</button></div>
        </form>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-label">Total Transaksi</div><div class="stat-number"><?=number_format($summary['total_transaksi'])?></div></div>
        <div class="stat-card"><div class="stat-label">Transaksi Lunas</div><div class="stat-number"><?=number_format($summary['transaksi_lunas'])?></div></div>
        <div class="stat-card"><div class="stat-label">Belum Bayar</div><div class="stat-number"><?=number_format($summary['belum_bayar'])?></div></div>
        <div class="stat-card"><div class="stat-label">Total Pendapatan</div><div class="stat-number">Rp <?=number_format($summary['total_pendapatan'],0,',','.')?></div></div>
    </div>

    <div class="panel table-wrap" style="margin-top:20px">
        <div style="padding:18px 18px 0"><h2 style="margin:0">Detail Transaksi</h2><p>Periode <?=formatDateTime($startDate, 'd-m-Y')?> sampai <?=formatDateTime($endDate, 'd-m-Y')?></p></div>
        <table><thead><tr><th>No</th><th>Waktu</th><th>No. Tiket</th><th>Nomor Polisi</th><th>Customer</th><th>Petugas Bayar</th><th>Total</th><th>Metode</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($transactions as $i => $row): ?>
        <tr><td><?=$i+1?></td><td><?= formatDateTime($row['waktu_pembayaran']) ?></td><td><?=e($row['nomor_tiket'])?></td><td><strong><?=e($row['nomor_polisi'])?></strong></td><td><?=e($row['nama_customer'])?></td><td><?=e($row['petugas_bayar'] ?? '-')?></td><td>Rp <?=number_format((float)$row['total_bayar'],0,',','.')?></td><td><?= $row['status']==='lunas' ? e(strtoupper($row['metode_pembayaran'] ?? '-')) : '-' ?></td><td><span class="badge <?=badgeClass($row['status'])?>"><?=e(strtoupper(str_replace('_',' ',$row['status'])))?></span></td></tr>
        <?php endforeach; ?>
        <?php if (!$transactions): ?><tr><td colspan="9" class="empty">Tidak ada transaksi pada periode ini.</td></tr><?php endif; ?>
        </tbody></table>
    </div>
</main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
