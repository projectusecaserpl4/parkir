<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout">
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="content">
<div class="page-header"><div><h1><?=e($title)?></h1><p>Periksa data sebelum kendaraan dikeluarkan.</p></div></div>
<?php renderFlash(); ?>
<div class="panel">
<h2>Informasi Parkir</h2>
<div class="detail-grid">
<div><span class="detail-label">Nomor Tiket</span><strong><?=e($entry['nomor_tiket'])?></strong></div>
<div><span class="detail-label">Nomor Polisi</span><strong><?=e($entry['nomor_polisi'])?></strong></div>
<div><span class="detail-label">Customer</span><?=e($entry['nama_customer'])?></div>
<div><span class="detail-label">Jenis Kendaraan</span><?=e($entry['nama_jenis'])?></div>
<div><span class="detail-label">Slot</span><?=e($entry['kode_slot'] ?? '-')?> - <?=e($entry['nama_area'] ?? '-')?></div>
<div><span class="detail-label">Waktu Masuk</span><?=formatDateTime($entry['waktu_masuk'], 'd-m-Y H:i:s')?></div>
</div>
</div>
<div class="panel">
<h2>Perhitungan Tarif</h2>
<div class="fare-box">
<div><span>Lama Parkir</span><strong><?=$calculation['lama_parkir']?> jam</strong></div>
<div><span>Tarif / Jam</span><strong>Rp <?=number_format($calculation['tarif_per_jam'],0,',','.')?></strong></div>
<div><span>Tarif Maksimal</span><strong><?= $calculation['tarif_maksimal'] !== null ? 'Rp '.number_format($calculation['tarif_maksimal'],0,',','.') : 'Tidak ada' ?></strong></div>
<div class="fare-total"><span>Total Bayar</span><strong>Rp <?=number_format($calculation['total_bayar'],0,',','.')?></strong></div>
</div>
<p class="form-help">Perhitungan menggunakan pembulatan ke atas per jam dan tarif maksimal jika tersedia.</p>
</div>
<div class="panel form-panel">
<form method="POST" action="<?=BASE_URL?>/parking-exits/store" novalidate>
<?=csrfField()?>
<input type="hidden" name="id_entry" value="<?=$entry['id_entry']?>">
<div class="form-group full">
<label>Kondisi Kendaraan Saat Keluar</label>
<textarea name="kondisi_kendaraan_keluar" rows="4" maxlength="500" placeholder="Contoh: Kondisi normal, tidak ada kerusakan tambahan."></textarea>
</div>
<div class="form-actions"><button class="btn btn-primary" type="submit" onclick="return confirm('Proses kendaraan keluar dan kosongkan slot ini?')">Konfirmasi Kendaraan Keluar</button>
<a href="<?=BASE_URL?>/parking-exits" class="btn btn-secondary">Batal</a></div>
</form>
</div>
</main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
