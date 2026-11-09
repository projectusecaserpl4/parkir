<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout">
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="content">
<div class="page-header">
    <div>
        <h1><?=e($title)?></h1>
        <p>Pilih kendaraan aktif yang belum sedang parkir, kemudian pilih slot sesuai jenis kendaraan.</p>
    </div>
</div>
<?php renderFlash(); ?>

<?php if (($vehicleCount ?? 0) === 0): ?>
<div class="alert alert-danger">
    <strong>Belum ada kendaraan yang bisa diparkir.</strong><br>
    Pastikan sudah ada Customer dan Kendaraan dengan status <strong>Aktif</strong>.
    <br><br>
    <a class="btn btn-primary btn-sm" href="<?=BASE_URL?>/customers/create">+ Tambah Customer</a>
    <a class="btn btn-primary btn-sm" href="<?=BASE_URL?>/vehicles/create">+ Tambah Kendaraan</a>
</div>
<?php endif; ?>

<?php if (($slotCount ?? 0) === 0): ?>
<div class="alert alert-danger">
    <strong>Belum ada slot parkir kosong.</strong><br>
    Periksa menu <strong>Slot Parkir</strong>. Slot harus berstatus <strong>kosong</strong> dan area harus <strong>tersedia</strong>.
    <br><br>
    <a class="btn btn-primary btn-sm" href="<?=BASE_URL?>/parking-slots/create">+ Tambah Slot Parkir</a>
</div>
<?php endif; ?>

<div class="panel form-panel">
<form method="POST" action="<?=BASE_URL?>/parking-entries/store" novalidate>
<?=csrfField()?>
<div class="form-grid">
<div class="form-group full">
<label>Kendaraan *</label>
<select name="id_vehicle" id="id_vehicle" required <?=empty($vehicles)?'disabled':''?>>
<option value="">-- Pilih Kendaraan --</option>
<?php foreach($vehicles as $vehicle): ?>
<option value="<?=$vehicle['id_vehicle']?>" <?=(int)old('id_vehicle')===(int)$vehicle['id_vehicle']?'selected':''?> data-jenis="<?=e(strtolower($vehicle['jenis_kendaraan']))?>">
<?=e($vehicle['nomor_polisi'])?> - <?=e($vehicle['nama_customer'])?> - <?=e($vehicle['nama_jenis'])?><?=!empty($vehicle['merk'])?' - '.e($vehicle['merk']):''?>
</option>
<?php endforeach; ?>
</select>
<small class="form-help">Hanya kendaraan aktif yang belum tercatat sedang parkir yang ditampilkan.</small>
</div>

<div class="form-group full">
<label>Slot Parkir *</label>
<select name="id_slot" id="id_slot" required <?=empty($slots)?'disabled':''?>>
<option value="">-- Pilih kendaraan terlebih dahulu --</option>
<?php foreach($slots as $slot): ?>
<option value="<?=$slot['id_slot']?>" <?=(int)old('id_slot')===(int)$slot['id_slot']?'selected':''?> data-jenis="<?=e(strtolower($slot['jenis_kendaraan']))?>">
<?=e($slot['kode_slot'])?> - <?=e($slot['nama_area'])?> (<?=ucfirst(e($slot['jenis_kendaraan']))?>)
</option>
<?php endforeach; ?>
</select>
<small id="slot-help" class="form-help">Slot akan otomatis menjadi TERISI setelah transaksi berhasil.</small>
</div>

<div class="form-group full">
<label>Kondisi Kendaraan Saat Masuk</label>
<textarea name="kondisi_kendaraan_masuk" rows="4" maxlength="500" placeholder="Contoh: Kondisi normal, terdapat goresan kecil pada bagian kanan."><?=e(old('kondisi_kendaraan_masuk'))?></textarea>
</div>
</div>
<div class="form-actions"><button class="btn btn-primary" type="submit" <?=empty($vehicles)||empty($slots)?'disabled':''?>>Simpan Kendaraan Masuk</button>
<a href="<?=BASE_URL?>/parking-entries" class="btn btn-secondary">Batal</a></div>
</form>
</div>
</main></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const vehicle = document.getElementById('id_vehicle');
    const slot = document.getElementById('id_slot');
    const help = document.getElementById('slot-help');
    const allSlots = Array.from(slot.querySelectorAll('option[data-jenis]'));

    function renderSlots() {
        const selected = vehicle.options[vehicle.selectedIndex];
        const jenis = selected ? (selected.dataset.jenis || '') : '';
        const current = slot.value;
        slot.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = jenis ? '-- Pilih Slot ' + jenis + ' --' : '-- Pilih kendaraan terlebih dahulu --';
        slot.appendChild(placeholder);

        let count = 0;
        allSlots.forEach(function (source) {
            if (jenis && source.dataset.jenis === jenis) {
                const option = source.cloneNode(true);
                slot.appendChild(option);
                count++;
            }
        });

        if (current && count > 0) {
            const exists = Array.from(slot.options).some(o => o.value === current);
            if (exists) slot.value = current;
        }

        if (!jenis) {
            help.textContent = 'Pilih kendaraan terlebih dahulu agar slot sesuai jenis kendaraan.';
        } else if (count === 0) {
            help.textContent = 'Tidak ada slot kosong untuk jenis kendaraan ' + jenis + '.';
        } else {
            help.textContent = count + ' slot kosong tersedia untuk jenis kendaraan ' + jenis + '.';
        }
    }

    vehicle.addEventListener('change', renderSlots);
    renderSlots();
});
</script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
