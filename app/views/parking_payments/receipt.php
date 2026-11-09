<?php require __DIR__ . '/../layouts/header.php'; ?>
<div class="receipt-page">
    <div class="receipt-card">
        <div class="receipt-head">
            <h1>SISTEM PARKIR</h1>
            <p>STRUK PEMBAYARAN PARKIR</p>
        </div>

        <div class="receipt-status">LUNAS</div>

        <div class="receipt-row"><span>No. Tiket</span><strong><?=e($payment['nomor_tiket'])?></strong></div>
        <div class="receipt-row"><span>Nomor Polisi</span><strong><?=e($payment['nomor_polisi'])?></strong></div>
        <div class="receipt-row"><span>Customer</span><span><?=e($payment['nama_customer'])?></span></div>
        <div class="receipt-row"><span>Jenis</span><span><?=e($payment['nama_jenis'])?></span></div>
        <div class="receipt-row"><span>Waktu Masuk</span><span><?=formatDateTime($payment['waktu_masuk'])?></span></div>
        <div class="receipt-row"><span>Waktu Keluar</span><span><?=formatDateTime($payment['waktu_keluar'] ?? null)?></span></div>
        <div class="receipt-row"><span>Lama Parkir</span><span><?=e((string)($payment['lama_parkir'] ?? '-'))?> jam</span></div>
        <hr>
        <div class="receipt-row"><span>Total</span><strong>Rp <?=number_format((float)$payment['total_bayar'],0,',','.')?></strong></div>
        <div class="receipt-row"><span>Metode</span><span><?=e(strtoupper($payment['metode_pembayaran']))?></span></div>
        <div class="receipt-row"><span>Uang Diterima</span><span>Rp <?=number_format((float)$payment['jumlah_dibayar'],0,',','.')?></span></div>
        <div class="receipt-row"><span>Kembalian</span><strong>Rp <?=number_format((float)$payment['kembalian'],0,',','.')?></strong></div>
        <div class="receipt-row"><span>Petugas</span><span><?=e($payment['petugas_bayar'] ?? '-')?></span></div>
        <div class="receipt-row"><span>Waktu Bayar</span><span><?=formatDateTime($payment['waktu_pembayaran'], 'd-m-Y H:i:s')?></span></div>

        <p class="receipt-thanks">Terima kasih telah menggunakan layanan parkir.</p>

        <div class="receipt-actions no-print">
            <button class="btn btn-primary" onclick="window.print()">Cetak Struk</button>
            <a class="btn btn-secondary" href="<?=BASE_URL?>/parking-payments">Kembali</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
