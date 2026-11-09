<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout"><?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="content">
<div class="page-header"><div><h1><?=e($title)?></h1></div></div>
<?php renderFlash(); ?>
<div class="panel form-panel">
<form method="POST" action="<?=BASE_URL?>/customers/<?=$action==='create'?'store':'update?id=' . (int)$customer['id_customer']?>" novalidate>
<?=csrfField()?>
<div class="form-grid">
<div class="form-group"><label for="nama_customer">Nama Customer *</label><input id="nama_customer" name="nama_customer" required minlength="3" maxlength="100" value="<?=e(old('nama_customer', $customer['nama_customer']??''))?>"><span class="form-help">Hanya huruf, spasi, titik, apostrof, dan tanda hubung. Minimal 3 karakter.</span></div>
<div class="form-group"><label for="nik">NIK</label><input id="nik" name="nik" inputmode="numeric" maxlength="16" data-digits value="<?=e(old('nik', $customer['nik']??''))?>" placeholder="16 digit angka"><span class="form-help">Opsional. Jika diisi harus 16 digit angka dan unik.</span></div>
<div class="form-group"><label for="no_hp">No. HP</label><input id="no_hp" name="no_hp" inputmode="tel" maxlength="16" value="<?=e(old('no_hp', $customer['no_hp']??''))?>" placeholder="081234567890"><span class="form-help">Opsional. Awalan 0 atau +62, 10-14 digit.</span></div>
<div class="form-group full"><label for="alamat">Alamat</label><textarea id="alamat" name="alamat" maxlength="255"><?=e(old('alamat', $customer['alamat']??''))?></textarea></div>
</div>
<div class="form-actions">
<button type="submit" class="btn btn-primary">Simpan</button>
<a href="<?=BASE_URL?>/customers" class="btn btn-secondary">Batal</a>
</div>
</form></div></main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
