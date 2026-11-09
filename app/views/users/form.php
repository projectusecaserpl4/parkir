<?php
require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php';
$selRole   = (int) old('id_role', (string) ($item['id_role'] ?? 0));
$selStatus = old('status', $item['status'] ?? 'aktif');
$isSelf    = !empty($isSelf);
?>
<div class="app-layout"><?php require __DIR__ . '/../layouts/sidebar.php'; ?><main class="content">
<div class="page-header"><h1><?=e($title)?></h1></div>
<?php renderFlash(); ?>
<?php if ($isSelf): ?><div class="alert alert-warning">Ini akun yang sedang Anda gunakan. Role dan status tidak dapat diubah agar Anda tidak terkunci dari sistem.</div><?php endif; ?>
<div class="panel form-panel">
<form method="POST" action="<?=BASE_URL?>/users/<?=$action==='create'?'store':'update?id='.(int)$item['id_user']?>" novalidate autocomplete="off">
<?=csrfField()?>
<div class="form-grid">
<div class="form-group"><label for="id_role">Role *</label><select id="id_role" name="id_role" required><option value="">- Pilih Role -</option><?php foreach($roles as $r):?><option value="<?=$r['id_role']?>" <?=$selRole===(int)$r['id_role']?'selected':''?>><?=e($r['nama_role'])?></option><?php endforeach;?></select></div>
<div class="form-group"><label for="nama_lengkap">Nama Lengkap *</label><input id="nama_lengkap" name="nama_lengkap" required minlength="3" maxlength="100" value="<?=e(old('nama_lengkap', $item['nama_lengkap']??''))?>"></div>
<div class="form-group"><label for="username">Username *</label><input id="username" name="username" required minlength="4" maxlength="30" value="<?=e(old('username', $item['username']??''))?>" autocomplete="off"><span class="form-help">4-30 karakter: huruf kecil, angka, titik, garis bawah.</span></div>
<div class="form-group"><label for="password">Password <?=($action==='edit'?'(kosongkan jika tidak diubah)':'*')?></label><input id="password" type="password" name="password" maxlength="72" autocomplete="new-password"><span class="form-help">Minimal 6 karakter.</span></div>
<div class="form-group"><label for="no_hp">No. HP</label><input id="no_hp" name="no_hp" inputmode="tel" maxlength="16" value="<?=e(old('no_hp', $item['no_hp']??''))?>" placeholder="081234567890"></div>
<div class="form-group"><label for="status">Status</label><select id="status" name="status"><option value="aktif" <?=$selStatus==='aktif'?'selected':''?>>Aktif</option><option value="nonaktif" <?=$selStatus==='nonaktif'?'selected':''?>>Nonaktif</option></select></div>
</div>
<div class="form-actions"><button type="submit" class="btn btn-primary">Simpan</button><a class="btn btn-secondary" href="<?=BASE_URL?>/users">Batal</a></div>
</form></div></main></div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
