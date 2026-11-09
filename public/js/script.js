document.addEventListener('DOMContentLoaded', function () {
    // Kolom khusus angka (mis. NIK): buang karakter non-angka saat mengetik/menempel.
    document.querySelectorAll('input[data-digits]').forEach(function (input) {
        input.addEventListener('input', function () {
            var cleaned = input.value.replace(/\D+/g, '');
            if (cleaned !== input.value) {
                input.value = cleaned;
            }
        });
    });

    // Kolom yang harus huruf besar (nomor polisi, kode slot).
    document.querySelectorAll('input[data-uppercase]').forEach(function (input) {
        input.addEventListener('input', function () {
            var start = input.selectionStart;
            var end = input.selectionEnd;
            input.value = input.value.toUpperCase();
            try { input.setSelectionRange(start, end); } catch (e) { /* abaikan */ }
        });
    });

    // Cegah klik ganda pada tombol simpan (mencegah data/transaksi tercatat dua kali).
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (event.defaultPrevented) {
                return; // dibatalkan oleh konfirmasi "Hapus?" dsb.
            }
            if (form.dataset.submitting === '1') {
                event.preventDefault();
                return;
            }
            form.dataset.submitting = '1';
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach(function (button) {
                button.disabled = true;
            });
        });
    });

    // Jika user menekan tombol Back (halaman dipulihkan dari cache), aktifkan lagi tombolnya.
    window.addEventListener('pageshow', function (event) {
        if (!event.persisted) {
            return;
        }
        document.querySelectorAll('form').forEach(function (form) {
            form.dataset.submitting = '0';
            form.querySelectorAll('button').forEach(function (button) {
                button.disabled = false;
            });
        });
    });
});
