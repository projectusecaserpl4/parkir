<?php
require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php';
$selStatus = old('status', $item['status'] ?? 'tersedia');
?>
<div class="app-layout"><?php require __DIR__ . '/../layouts/sidebar.php'; ?><main class="content">
<div class="page-header"><h1><?=e($title)?></h1></div>
<?php renderFlash(); ?>
<div class="panel form-panel">
<form method="POST" action="<?=BASE_URL?>/parking-areas/<?=$action==='create'?'store':'update?id='.(int)$item['id_area']?>" novalidate>
<?=csrfField()?>
<div class="form-grid">
<div class="form-group"><label for="nama_area">Nama Area *</label><input id="nama_area" name="nama_area" required minlength="3" maxlength="100" value="<?=e(old('nama_area', $item['nama_area']??''))?>"></div>
<div class="form-group"><label for="lokasi">Lokasi</label><input id="lokasi" name="lokasi" maxlength="255" value="<?=e(old('lokasi', $item['lokasi']??''))?>"></div>
<div class="form-group"><label for="kapasitas">Kapasitas *</label><input id="kapasitas" type="number" name="kapasitas" min="1" max="10000" step="1" required value="<?=e(old('kapasitas', (string)($item['kapasitas']??'')))?>"><span class="form-help">Jumlah maksimal slot di area ini (1 - 10.000).</span></div>
<div class="form-group"><label for="status">Status</label><select id="status" name="status"><option value="tersedia" <?=$selStatus==='tersedia'?'selected':''?>>Tersedia</option><option value="ditutup" <?=$selStatus==='ditutup'?'selected':''?>>Ditutup</option></select></div>
</div>
<div class="form-actions"><button type="submit" class="btn btn-primary">Simpan</button><a class="btn btn-secondary" href="<?=BASE_URL?>/parking-areas">Batal</a></div>
</form></div></main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
