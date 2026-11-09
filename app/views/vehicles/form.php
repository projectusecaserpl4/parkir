<?php
require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php';
$selCustomer = (int) old('id_customer', (string) ($vehicle['id_customer'] ?? 0));
$selType     = (int) old('id_jenis_kendaraan', (string) ($vehicle['id_jenis_kendaraan'] ?? 0));
$selStatus   = old('status', $vehicle['status'] ?? 'aktif');
?>
<div class="app-layout"><?php require __DIR__ . '/../layouts/sidebar.php'; ?><main class="content">
<div class="page-header"><h1><?=e($title)?></h1></div>
<?php renderFlash(); ?>
<div class="panel form-panel"><form method="POST" action="<?=BASE_URL?>/vehicles/<?=$action==='create'?'store':'update?id='.(int)$vehicle['id_vehicle']?>" novalidate>
<?=csrfField()?>
<div class="form-grid">
<div class="form-group"><label for="id_customer">Pemilik *</label><select id="id_customer" name="id_customer" required><option value="">- Pilih Customer -</option><?php foreach($customers as $c):?><option value="<?=$c['id_customer']?>" <?=$selCustomer===(int)$c['id_customer']?'selected':''?>><?=e($c['nama_customer'])?><?=!empty($c['no_hp']) ? ' - '.e($c['no_hp']) : ''?></option><?php endforeach;?></select></div>
<div class="form-group"><label for="id_jenis_kendaraan">Jenis Kendaraan *</label><select id="id_jenis_kendaraan" name="id_jenis_kendaraan" required><option value="">- Pilih Jenis -</option><?php foreach($types as $t):?><option value="<?=$t['id_jenis_kendaraan']?>" <?=$selType===(int)$t['id_jenis_kendaraan']?'selected':''?>><?=e($t['nama_jenis'])?></option><?php endforeach;?></select></div>
<div class="form-group"><label for="nomor_polisi">Nomor Polisi *</label><input id="nomor_polisi" name="nomor_polisi" required maxlength="12" data-uppercase value="<?=e(old('nomor_polisi', $vehicle['nomor_polisi']??''))?>" placeholder="D 1234 AB"><span class="form-help">Format: 1-2 huruf, 1-4 angka, 0-3 huruf.</span></div>
<div class="form-group"><label for="merk">Merk</label><input id="merk" name="merk" maxlength="50" value="<?=e(old('merk', $vehicle['merk']??''))?>" placeholder="Honda"></div>
<div class="form-group"><label for="model">Model</label><input id="model" name="model" maxlength="50" value="<?=e(old('model', $vehicle['model']??''))?>" placeholder="Beat / Avanza"></div>
<div class="form-group"><label for="warna">Warna</label><input id="warna" name="warna" maxlength="30" value="<?=e(old('warna', $vehicle['warna']??''))?>" placeholder="Hitam"></div>
<div class="form-group"><label for="tahun">Tahun</label><input id="tahun" type="number" name="tahun" min="1950" max="<?=date('Y')+1?>" value="<?=e(old('tahun', (string)($vehicle['tahun']??'')))?>" placeholder="<?=date('Y')?>"></div>
<div class="form-group"><label for="status">Status</label><select id="status" name="status"><option value="aktif" <?=$selStatus==='aktif'?'selected':''?>>Aktif</option><option value="nonaktif" <?=$selStatus==='nonaktif'?'selected':''?>>Nonaktif</option></select></div>
</div>
<div class="form-actions"><button type="submit" class="btn btn-primary">Simpan</button><a class="btn btn-secondary" href="<?=BASE_URL?>/vehicles">Batal</a></div>
</form></div></main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
