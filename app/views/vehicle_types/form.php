<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout"><?php require __DIR__ . '/../layouts/sidebar.php'; ?><main class="content">
<div class="page-header"><h1><?=e($title)?></h1></div>
<?php renderFlash(); ?>
<div class="panel form-panel"><form method="POST" action="<?=BASE_URL?>/vehicle-types/<?=$action==='create'?'store':'update?id='.(int)$item['id_jenis_kendaraan']?>" novalidate>
<?=csrfField()?>
<div class="form-group"><label for="nama_jenis">Nama Jenis *</label><input id="nama_jenis" name="nama_jenis" required minlength="3" maxlength="50" value="<?=e(old('nama_jenis', $item['nama_jenis']??''))?>"><span class="form-help">Hanya huruf dan spasi. Jenis bawaan Motor dan Mobil tidak dapat diganti namanya.</span></div>
<div class="form-group"><label for="deskripsi">Deskripsi</label><textarea id="deskripsi" name="deskripsi" maxlength="255"><?=e(old('deskripsi', $item['deskripsi']??''))?></textarea></div>
<div class="form-actions"><button type="submit" class="btn btn-primary">Simpan</button><a class="btn btn-secondary" href="<?=BASE_URL?>/vehicle-types">Batal</a></div>
</form></div></main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
