<?php
require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php';
$selType   = (int) old('id_jenis_kendaraan', (string) ($item['id_jenis_kendaraan'] ?? 0));
$selStatus = old('status', $item['status'] ?? 'aktif');
$perJamVal = isset($item['tarif_per_jam']) ? (string) (int) round((float) $item['tarif_per_jam']) : '';
$maxVal    = isset($item['tarif_maksimal']) && $item['tarif_maksimal'] !== null ? (string) (int) round((float) $item['tarif_maksimal']) : '';
$startVal  = !empty($item['berlaku_mulai']) ? date('Y-m-d\TH:i', strtotime($item['berlaku_mulai'])) : date('Y-m-d\TH:i');
?>
<div class="app-layout"><?php require __DIR__ . '/../layouts/sidebar.php'; ?><main class="content">
<div class="page-header"><h1><?=e($title)?></h1></div>
<?php renderFlash(); ?>
<div class="panel form-panel">
<form method="POST" action="<?=BASE_URL?>/parking-rates/<?=$action==='create'?'store':'update?id='.(int)$item['id_tarif']?>" novalidate>
<?=csrfField()?>
<div class="form-grid">
<div class="form-group"><label for="id_jenis_kendaraan">Jenis Kendaraan *</label><select id="id_jenis_kendaraan" name="id_jenis_kendaraan" required><option value="">- Pilih -</option><?php foreach($types as $t):?><option value="<?=$t['id_jenis_kendaraan']?>" <?=$selType===(int)$t['id_jenis_kendaraan']?'selected':''?>><?=e($t['nama_jenis'])?></option><?php endforeach;?></select></div>
<div class="form-group"><label for="tarif_per_jam">Tarif Per Jam (Rp) *</label><input id="tarif_per_jam" type="number" name="tarif_per_jam" min="1" max="99999999" step="1" required value="<?=e(old('tarif_per_jam', $perJamVal))?>" placeholder="3000"></div>
<div class="form-group"><label for="tarif_maksimal">Tarif Maksimal (Rp)</label><input id="tarif_maksimal" type="number" name="tarif_maksimal" min="1" max="99999999" step="1" value="<?=e(old('tarif_maksimal', $maxVal))?>" placeholder="15000"><span class="form-help">Opsional. Batas tarif maksimal per transaksi parkir; tidak boleh lebih kecil dari tarif per jam.</span></div>
<div class="form-group"><label for="berlaku_mulai">Berlaku Mulai</label><input id="berlaku_mulai" type="datetime-local" name="berlaku_mulai" value="<?=e(old('berlaku_mulai', $startVal))?>"></div>
<div class="form-group"><label for="status">Status</label><select id="status" name="status"><option value="aktif" <?=$selStatus==='aktif'?'selected':''?>>Aktif</option><option value="nonaktif" <?=$selStatus==='nonaktif'?'selected':''?>>Nonaktif</option></select></div>
</div>
<div class="form-actions"><button type="submit" class="btn btn-primary">Simpan</button><a class="btn btn-secondary" href="<?=BASE_URL?>/parking-rates">Batal</a></div>
</form></div></main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
