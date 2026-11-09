<?php require __DIR__ . '/../layouts/header.php'; require __DIR__ . '/../layouts/navbar.php'; ?>
<div class="app-layout">
<?php require __DIR__ . '/../layouts/sidebar.php'; ?>
<main class="content">
<div class="page-header">
    <div><h1><?=e($title)?></h1><p>Periksa tagihan dan masukkan pembayaran pelanggan.</p></div>
</div>
<?php renderFlash(); ?>

<div class="panel">
    <div class="detail-grid">
        <div><span class="detail-label">Nomor Tiket</span><strong><?=e($payment['nomor_tiket'])?></strong></div>
        <div><span class="detail-label">Nomor Polisi</span><strong><?=e($payment['nomor_polisi'])?></strong></div>
        <div><span class="detail-label">Customer</span><?=e($payment['nama_customer'])?></div>
        <div><span class="detail-label">Jenis</span><?=e($payment['nama_jenis'])?></div>
        <div><span class="detail-label">Lama Parkir</span><?=e((string)($payment['lama_parkir'] ?? '-'))?> jam</div>
        <div><span class="detail-label">Waktu Keluar</span><?=formatDateTime($payment['waktu_keluar'] ?? null, 'd-m-Y H:i:s')?></div>
    </div>
    <hr>
    <div class="payment-total">Rp <?=number_format((float)$payment['total_bayar'],0,',','.')?></div>
    <div class="form-help">Total yang harus dibayar pelanggan.</div>
</div>

<div class="panel form-panel">
<form method="POST" action="<?=BASE_URL?>/parking-payments/store" id="paymentForm" novalidate>
<?=csrfField()?>
<input type="hidden" name="id_payment" value="<?=$payment['id_payment']?>">

<div class="form-group">
<label>Metode Pembayaran *</label>
<select name="metode_pembayaran" id="metodePembayaran" required>
<?php $selMethod = old('metode_pembayaran', 'cash'); ?>
<option value="cash" <?=$selMethod==='cash'?'selected':''?>>Cash</option>
<option value="qris" <?=$selMethod==='qris'?'selected':''?>>QRIS</option>
<option value="debit" <?=$selMethod==='debit'?'selected':''?>>Debit</option>
<option value="lainnya" <?=$selMethod==='lainnya'?'selected':''?>>Lainnya</option>
</select>
</div>

<div class="form-group" id="cashGroup">
<label>Jumlah Uang Diterima *</label>
<input type="number" name="jumlah_dibayar" id="jumlahDibayar" min="<?=htmlspecialchars((string)$payment['total_bayar'])?>" max="999999999" step="any" value="<?=e(old('jumlah_dibayar'))?>" placeholder="Masukkan nominal uang">
<span class="form-help">Untuk cash, nominal harus sama dengan atau lebih besar dari total tagihan.</span>
</div>

<div class="payment-preview" id="paymentPreview">
    <div><span>Total Tagihan</span><strong id="previewTotal">Rp <?=number_format((float)$payment['total_bayar'],0,',','.')?></strong></div>
    <div><span>Uang Diterima</span><strong id="previewReceived">Rp 0</strong></div>
    <div><span>Kembalian</span><strong id="previewChange">Rp 0</strong></div>
</div>

<div class="form-actions"><button class="btn btn-primary" type="submit" onclick="return confirm('Konfirmasi pembayaran ini sebagai LUNAS?')">Konfirmasi Pembayaran</button>
<a href="<?=BASE_URL?>/parking-payments" class="btn btn-secondary">Batal</a></div>
</form>
</div>
</main></div>

<script>
(function () {
    const method = document.getElementById('metodePembayaran');
    const cashGroup = document.getElementById('cashGroup');
    const amount = document.getElementById('jumlahDibayar');
    const total = <?=json_encode((float)$payment['total_bayar'])?>;
    const received = document.getElementById('previewReceived');
    const change = document.getElementById('previewChange');

    function rupiah(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    function refresh() {
        const isCash = method.value === 'cash';
        cashGroup.style.display = isCash ? '' : 'none';
        amount.required = isCash;
        amount.disabled = !isCash;

        const paid = isCash ? Number(amount.value || 0) : total;
        received.textContent = rupiah(paid);
        change.textContent = rupiah(Math.max(0, paid - total));
    }

    method.addEventListener('change', refresh);
    amount.addEventListener('input', refresh);
    refresh();
})();
</script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
