# Sistem Parkir - PHP Native MVC
## Database Terpadu - Tahap 5

Project ini sudah mencakup seluruh tahap sampai:

- Master Data
- Transaksi Kendaraan Masuk
- Transaksi Kendaraan Keluar
- Perhitungan Tarif
- Pembayaran
- Cetak Struk
- Riwayat Parkir
- 3 Role: Admin, Petugas, Owner

## Database

Sekarang project menggunakan **SATU FILE DATABASE SAJA**:

`database/sistem_parkir.sql`

Tidak perlu lagi menjalankan file SQL tahap 1, 2, 3, dan 4 secara terpisah.

### Instalasi database baru

1. Jalankan Apache dan MySQL di Laragon.
2. Buka phpMyAdmin.
3. Import:
   `database/sistem_parkir.sql`
4. Database `sistem_parkir` akan dibuat otomatis.

Script sudah berisi:

- 12 tabel
- Role Admin, Petugas, Owner
- 3 akun login
- Jenis kendaraan
- Area parkir
- Slot parkir
- Tarif parkir
- Data customer contoh
- Data kendaraan contoh
- Kolom pembayaran dan kembalian

Script dibuat agar aman dijalankan pada database baru maupun database yang sudah pernah dibuat dari tahap sebelumnya.

## Login

| Role | Username | Password |
|---|---|---|
| Admin | admin | admin123 |
| Petugas | petugas | petugas123 |
| Owner | owner | owner123 |

## URL

`http://localhost/sistem-parkir/`

## Alur Sistem

Login
→ Master Data
→ Kendaraan Masuk
→ Kendaraan Keluar
→ Pembayaran
→ Cetak Struk
→ Riwayat Parkir

## Struktur database

1. roles
2. users
3. customers
4. vehicle_types
5. vehicles
6. parking_areas
7. parking_slots
8. parking_rates
9. parking_entries
10. parking_exits
11. parking_payments
12. parking_history

## Catatan

Backup project/database lama terlebih dahulu sebelum mengganti project produksi. Untuk project yang sudah berjalan, file SQL terpadu ini dapat dipakai sebagai sumber database lengkap sehingga tidak perlu mengelola migration SQL terpisah lagi.
