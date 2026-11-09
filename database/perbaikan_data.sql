-- =========================================================
-- PERBAIKAN DATA LAMA (jalankan SEKALI di phpMyAdmin > tab SQL)
-- Aman dijalankan berulang. Tidak menghapus data apa pun.
-- Backup database dulu (Export) sebelum menjalankan.
-- =========================================================
USE sistem_parkir;

-- 1. Rapikan isian kosong / spasi berlebih
UPDATE customers SET nama_customer = TRIM(nama_customer);
UPDATE customers SET nik    = NULL WHERE nik    IS NOT NULL AND TRIM(nik)    = '';
UPDATE customers SET no_hp  = NULL WHERE no_hp  IS NOT NULL AND TRIM(no_hp)  = '';
UPDATE customers SET alamat = NULL WHERE alamat IS NOT NULL AND TRIM(alamat) = '';

-- 2. Sinkronkan status slot dengan data parkir yang sebenarnya
--    (slot "terisi" tanpa kendaraan parkir -> kosong)
UPDATE parking_slots ps
SET ps.status = 'kosong'
WHERE ps.status = 'terisi'
  AND NOT EXISTS (
      SELECT 1 FROM parking_entries pe
      WHERE pe.id_slot = ps.id_slot AND pe.status = 'parkir'
  );
--    (slot "kosong" padahal ada kendaraan parkir -> terisi)
UPDATE parking_slots ps
INNER JOIN parking_entries pe ON pe.id_slot = ps.id_slot AND pe.status = 'parkir'
SET ps.status = 'terisi'
WHERE ps.status = 'kosong';

-- 3. DAFTAR DATA YANG PERLU DIPERBAIKI MANUAL lewat menu Edit di aplikasi
--    (aturan validasi baru menolak data seperti ini saat disimpan)

-- 3a. NIK yang bukan 16 digit angka
SELECT id_customer, nama_customer, nik AS nik_tidak_valid
FROM customers
WHERE nik IS NOT NULL AND nik NOT REGEXP '^[0-9]{16}$';

-- 3b. Nomor HP tidak valid
SELECT id_customer, nama_customer, no_hp AS hp_tidak_valid
FROM customers
WHERE no_hp IS NOT NULL
  AND REPLACE(REPLACE(REPLACE(no_hp,' ',''),'-',''),'.','') NOT REGEXP '^(\\+62|62|0)[0-9]{9,13}$';

-- 3c. Customer ganda (nama + HP sama)
SELECT LOWER(nama_customer) AS nama, COALESCE(no_hp,'') AS hp, COUNT(*) AS jumlah, GROUP_CONCAT(id_customer) AS id_customer
FROM customers
GROUP BY LOWER(nama_customer), COALESCE(no_hp,'')
HAVING COUNT(*) > 1;

-- 3d. Nomor polisi dengan format tidak valid
SELECT id_vehicle, nomor_polisi AS plat_tidak_valid
FROM vehicles
WHERE UPPER(REPLACE(nomor_polisi,' ','')) NOT REGEXP '^[A-Z]{1,2}[0-9]{1,4}[A-Z]{0,3}$';

-- 3e. Kendaraan berjenis Mobil tetapi merk/modelnya motor (contoh: Honda Beat)
SELECT v.id_vehicle, v.nomor_polisi, vt.nama_jenis, v.merk, v.model
FROM vehicles v
INNER JOIN vehicle_types vt ON vt.id_jenis_kendaraan = v.id_jenis_kendaraan
WHERE (vt.nama_jenis = 'Mobil' AND LOWER(CONCAT_WS(' ', v.merk, v.model)) REGEXP '(^| )(beat|vario|scoopy|nmax|pcx|aerox|mio|vixion|revo|supra x)( |$)')
   OR (vt.nama_jenis = 'Motor' AND LOWER(CONCAT_WS(' ', v.merk, v.model)) REGEXP '(^| )(avanza|xenia|brio|jazz|civic|innova|ertiga|xpander)( |$)');
