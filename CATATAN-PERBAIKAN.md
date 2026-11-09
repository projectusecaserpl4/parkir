# Catatan Perbaikan (persiapan Black Box Testing)

## Cara pasang
1. Backup folder `sistem-parkir` lama + export database `sistem_parkir`.
2. Timpa folder `C:\laragon\www\sistem-parkir` dengan isi ZIP ini.
3. (Database lama) jalankan `database/perbaikan_data.sql` di phpMyAdmin. Database baru: cukup import `database/sistem_parkir.sql`.
4. Pastikan modul Apache `mod_rewrite` aktif (default Laragon) dan PHP >= 8.1.
5. Login ulang, lalu jalankan skenario di `SKENARIO-UJI.md`.

## Bug dari 2 screenshot yang diperbaiki
| Temuan | Perbaikan |
|---|---|
| NIK "123" / "102403011" diterima | NIK opsional, tetapi jika diisi wajib 16 digit angka dan unik |
| Nama "budi" ganda lolos | Nama + No. HP yang sama ditolak sebagai duplikat |
| Nama tidak dirapikan ("eca", "khilat") | Otomatis Title Case ("Eca", "Khilat"); angka/simbol ditolak |
| Mobil "Honda Beat" diterima | Merk/model yang jelas motor ditolak untuk jenis Mobil (dan sebaliknya) |
| Plat bebas format | Wajib format plat Indonesia, otomatis dirapikan `D 1234 UFM`; "D1234UFM" dianggap duplikat "D 1234 UFM" |
| Menu "Dashboard" selalu biru di semua halaman | Menu aktif mengikuti halaman yang dibuka |
| Tombol "+ Tambah ..." melar ke bawah | Perataan header diperbaiki |
| Tombol Edit & Hapus menempel | Diberi jarak |
| Kolom kosong tampak bolong | Ditampilkan "-" |

## Bug lain yang ditemukan saat audit
- Pesan error di halaman form (tambah/edit) tidak pernah tampil -> sekarang tampil, dan isian form tidak hilang saat gagal.
- Edit/hapus data yang tidak ada tetap menampilkan "berhasil" -> kini pesan "tidak ditemukan".
- Hapus data lewat link GET (bisa terpicu tanpa sengaja) -> kini POST + konfirmasi + token CSRF di semua form.
- Slot yang sedang dipakai kendaraan bisa dihapus / diubah manual (merusak data parkir) -> dikunci.
- Status slot "Terisi" bisa diset manual -> hanya sistem yang mengatur (pilihan: Kosong / Rusak).
- Jumlah slot bisa melebihi kapasitas area; kapasitas bisa 0/negatif -> divalidasi.
- Tarif 0 / negatif / teks / tarif maksimal < tarif per jam diterima -> divalidasi.
- Kendaraan boleh masuk walau tarif jenisnya belum ada (lalu gagal saat keluar) -> dicegah sejak masuk.
- Pembayaran: input "50.000" terbaca 50; nominal hidden bisa menggagalkan submit saat ganti ke QRIS; step 100 menolak nominal wajar -> semua diperbaiki.
- Hitung lama parkir memakai jam PHP vs jam MySQL (bisa selisih zona waktu) -> memakai satu jam (WIB) di database.
- Status slot "rusak" ikut jadi "kosong" saat kendaraan keluar -> dijaga.
- Dashboard Petugas menghitung slot rusak sebagai "tersedia"; tombol Owner menuju halaman 403 -> diperbaiki.
- Login: celah password plaintext/hash dibandingkan langsung -> hash wajib; akun nonaktif mendapat pesan jelas.
- User: validasi username unik/format, password min 6, tidak bisa hapus/nonaktifkan/ubah role akun sendiri, minimal 1 Admin aktif.
- Detail error database tidak lagi bocor ke layar; halaman 419/500 yang rapi.
- Folder `app/` dan `database/` (berisi hash password) tidak lagi bisa dibuka lewat browser.
- Pada layar kecil, sidebar tidak lagi hilang.

## Aturan baru yang perlu Anda ketahui
- Jenis kendaraan bawaan **Motor** dan **Mobil** tidak bisa diganti nama / dihapus (dipakai mencocokkan slot).
- Customer/kendaraan/slot/user yang sudah punya riwayat transaksi tidak bisa dihapus; nonaktifkan saja.
- Kendaraan yang sedang parkir tidak bisa dinonaktifkan / diubah jenisnya.
- `APP_DEBUG` di `app/config/config.php` = `false` (error PHP tidak muncul di layar). Ubah ke `true` hanya saat debugging.

## Batas verifikasi (jujur)
Lingkungan saya tidak punya PHP/MySQL, sehingga aplikasi **belum pernah dijalankan end-to-end** oleh saya. Yang sudah diperiksa: sintaks & keseimbangan kurung seluruh file PHP lewat lexer, kecocokan 60 rute <-> controller <-> model <-> view <-> helper, token CSRF di semua form, serta uji aturan validasi (NIK, HP, plat, nama, uang, jenis-vs-merk) terhadap kasus nyata. Mohon jalankan `SKENARIO-UJI.md` sekali di komputer Anda sebelum hari pengujian.
