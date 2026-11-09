# Skenario Uji Cepat (centang satu per satu)

| # | Langkah | Hasil yang diharapkan |
|---|---|---|
| 1 | Login admin/admin123 | Masuk dashboard Admin |
| 2 | Login salah password | "Username atau password salah." |
| 3 | Kosongkan username lalu Login | Pesan wajib diisi |
| 4 | Customer: nama kosong | Pesan nama wajib diisi, form tidak kosong lagi |
| 5 | Customer: nama "Budi123" | Ditolak (angka) |
| 6 | Customer: NIK "123" | "NIK harus ... 16 digit" |
| 7 | Customer: HP "abc123" | Ditolak |
| 8 | Customer: NIK 16 digit valid, HP 081234567890 | Tersimpan, nama jadi Title Case |
| 9 | Customer: NIK sama dengan customer lain | "NIK ... sudah terdaftar" |
| 10 | Customer: nama + HP sama persis | Ditolak duplikat |
| 11 | Hapus customer yang punya kendaraan | Ditolak dengan pesan jelas |
| 12 | Kendaraan: plat "1234" | Format plat tidak valid |
| 13 | Kendaraan: plat "d1234ufm" | Tersimpan sebagai `D 1234 UFM` |
| 14 | Kendaraan: plat sama lagi (`D 1234 UFM`) | Ditolak sudah terdaftar |
| 15 | Kendaraan: jenis Mobil + merk Honda model Beat | Ditolak (termasuk motor) |
| 16 | Kendaraan: tahun 1800 | Ditolak |
| 17 | Jenis kendaraan: tambah "Motor" lagi | Ditolak sudah ada |
| 18 | Jenis kendaraan: hapus Motor/Mobil | Ditolak (bawaan sistem) |
| 19 | Area: kapasitas 0 / -5 / "abc" | Ditolak |
| 20 | Slot: kode duplikat | Ditolak |
| 21 | Slot: tambah melebihi kapasitas area | Ditolak |
| 22 | Tarif: per jam 0, atau maksimal < per jam | Ditolak |
| 23 | User: username "ab" / ada spasi | Ditolak |
| 24 | User: password < 6 karakter | Ditolak |
| 25 | User: hapus akun sendiri | Tombol tidak ada / ditolak |
| 26 | Kendaraan Masuk: simpan tanpa pilih kendaraan | Pesan wajib dipilih |
| 27 | Kendaraan Masuk: pilih motor | Hanya slot motor tampil |
| 28 | Kendaraan Masuk valid | Nomor tiket muncul; slot jadi Terisi |
| 29 | Masukkan kendaraan yang sama lagi | Tidak muncul di pilihan |
| 30 | Slot Parkir: coba edit/hapus slot yang terisi | Dikunci / ditolak |
| 31 | Kendaraan Keluar: proses | Tarif dihitung, slot jadi Kosong, tagihan "belum bayar" |
| 32 | Pembayaran cash kurang dari total | "Uang yang diterima kurang" |
| 33 | Pembayaran cash isi "abc" / 50.000 | Ditolak (format angka) |
| 34 | Pembayaran cash lebih | Lunas, kembalian benar |
| 35 | Pembayaran QRIS | Lunas tanpa isi nominal |
| 36 | Cetak struk pembayaran lunas | Struk tampil lengkap |
| 37 | Buka struk belum lunas lewat URL | Ditolak + pesan |
| 38 | Petugas buka /users | 403 |
| 39 | Owner buka /rekap-transaksi | 403 (khusus Admin) |
| 40 | Admin: Aktivitas Parkir & Rekap Transaksi | Data tampil; filter tanggal berfungsi |
| 41 | Buka /customers/delete?id=1 lewat URL | 404 (hapus hanya via tombol POST) |
| 42 | Logout lalu tekan Back, buka /dashboard | Kembali ke login |
| 43 | Klik sidebar menu mana pun | Menu yang dibuka berwarna aktif |
