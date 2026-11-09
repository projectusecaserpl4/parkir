# Perubahan Role Sistem Parkir

## Role dan dashboard
- **Owner**: dashboard pemilik dengan total pendapatan, customer, kendaraan, parkir aktif, dan pembayaran tertunda. Dapat membuka semua modul.
- **Admin**: dashboard pengelolaan data dengan statistik customer, kendaraan, slot, dan parkir aktif. Dapat mengelola master data, user, dan transaksi.
- **Petugas**: dashboard operasional dengan slot tersedia/terisi dan tombol cepat kendaraan masuk, keluar, dan pembayaran.

## Keamanan akses
- Sidebar menampilkan menu sesuai role.
- Pemeriksaan akses juga dilakukan oleh router di backend. Petugas yang mencoba membuka URL master data akan mendapatkan halaman **403 Akses Ditolak**.
- Nama role dibaca tanpa membedakan kapitalisasi, jadi `Owner`, `owner`, `Admin`, `admin`, `Petugas`, dan `petugas` dapat dikenali.

## Cara menerapkan
1. Backup folder `sistem-parkir` lama terlebih dahulu.
2. Ekstrak ZIP ini.
3. Salin folder hasil ekstraksi ke `C:\laragon\www\sistem-parkir` (gantikan file lama setelah backup).
4. Pastikan database `sistem_parkir` dan tabel `roles` memiliki role `Owner`, `Admin`, dan `Petugas`.
5. Login dengan akun masing-masing. SQL proyek sebelumnya menyertakan akun awal `owner`, `admin`, dan `petugas`; password awal tercantum di bagian AKUN AWAL file `database/sistem_parkir.sql`.
6. Uji dengan login sebagai masing-masing role. Untuk menguji pembatasan, login sebagai Petugas lalu coba buka `http://localhost/sistem-parkir/users` — seharusnya muncul 403.

## Validasi
File PHP diperiksa menggunakan `php -l` dan semuanya lulus pemeriksaan sintaks. Aplikasi belum diuji end-to-end terhadap database lokal pengguna dari lingkungan ini.


## Fitur ADMIN: Aktivitas Parkir dan Rekap Transaksi

- **Aktivitas Parkir** menampilkan log aktivitas kendaraan/transaksi dari tabel `parking_history`.
- **Rekap Transaksi** menampilkan ringkasan transaksi, jumlah lunas/belum bayar, pendapatan transaksi lunas, serta detail berdasarkan rentang tanggal.
- Kedua menu hanya ditampilkan pada sidebar ADMIN dan dilindungi di backend; OWNER dan PETUGAS akan menerima halaman 403 jika mencoba membuka URL langsung.
- URL: `/sistem-parkir/parking-history` dan `/sistem-parkir/rekap-transaksi`.
