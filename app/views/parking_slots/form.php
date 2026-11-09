<?php
require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php';
$selArea   = (int) old('id_area', (string) ($item['id_area'] ?? 0));
$selJenis  = old('jenis_kendaraan', $item['jenis_kendaraan'] ?? 'motor');
$selStatus = old('status', ($item['status'] ?? 'kosong') === 'rusak' ? 'rusak' : 'kosong');
?>
<div class="app-layout"><?php require __DIR__ . '/../layouts/sidebar.php'; ?><main class="content">
<div class="page-header"><h1><?=e($title)?></h1></div>
<?php renderFlash(); ?>
<?php if (!empty($locked)): ?><div class="alert alert-warning">Slot ini sedang dipakai kendaraan yang parkir dan tidak dapat diubah sampai kendaraan keluar.</div><?php endif; ?>
<div class="panel form-panel">
<form method="POST" action="<?=BASE_URL?>/parking-slots/<?=$action==='create'?'store':'update?id='.(int)$item['id_slot']?>" novalidate>
<?=csrfField()?>
<div class="form-grid">
<div class="form-group"><label for="id_area">Area *</label><select id="id_area" name="id_area" required><option value="">- Pilih Area -</option><?php foreach($areas as $a):?><option value="<?=$a['id_area']?>" <?=$selArea===(int)$a['id_area']?'selected':''?>><?=e($a['nama_area'])?></option><?php endforeach;?></select></div>
<div class="form-group"><label for="kode_slot">Kode Slot *</label><input id="kode_slot" name="kode_slot" required minlength="2" maxlength="20" data-uppercase value="<?=e(old('kode_slot', $item['kode_slot']??''))?>" placeholder="M-A01"><span class="form-help">Huruf, angka, dan tanda hubung. Contoh: M-A01.</span></div>
<div class="form-group"><label for="jenis_kendaraan">Jenis Kendaraan *</label><select id="jenis_kendaraan" name="jenis_kendaraan"><option value="motor" <?=$selJenis==='motor'?'selected':''?>>Motor</option><option value="mobil" <?=$selJenis==='mobil'?'selected':''?>>Mobil</option><option value="lainnya" <?=$selJenis==='lainnya'?'selected':''?>>Lainnya</option></select></div>
<div class="form-group"><label for="status">Status</label><select id="status" name="status"><option value="kosong" <?=$selStatus==='kosong'?'selected':''?>>Kosong</option><option value="rusak" <?=$selStatus==='rusak'?'selected':''?>>Rusak</option></select><span class="form-help">Status "Terisi" diatur otomatis saat kendaraan masuk.</span></div>
</div>
<div class="form-actions"><button type="submit" class="btn btn-primary" <?=!empty($locked)?'disabled':''?>>Simpan</button><a class="btn btn-secondary" href="<?=BASE_URL?>/parking-slots">Batal</a></div>
</form></div></main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
