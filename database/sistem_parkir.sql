-- =========================================================
-- SISTEM PARKIR - DATABASE LENGKAP
-- PHP Native MVC
-- Versi: Tahap 5 (Master Data + Transaksi + Pembayaran + Owner)
--
-- Cukup import FILE INI saja untuk database sistem parkir.
-- Script aman dijalankan pada database baru maupun database
-- yang sudah pernah dibuat dari tahap sebelumnya.
-- =========================================================

CREATE DATABASE IF NOT EXISTS sistem_parkir
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sistem_parkir;

-- =========================================================
-- 1. ROLE
-- =========================================================
CREATE TABLE IF NOT EXISTS roles (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    nama_role VARCHAR(50) NOT NULL,
    deskripsi VARCHAR(255),
    UNIQUE KEY uq_roles_nama_role (nama_role)
) ENGINE=InnoDB;

-- =========================================================
-- 2. USERS
-- =========================================================
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    id_role INT NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20),
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (id_role) REFERENCES roles(id_role)
) ENGINE=InnoDB;

-- =========================================================
-- 3. CUSTOMERS
-- =========================================================
CREATE TABLE IF NOT EXISTS customers (
    id_customer INT AUTO_INCREMENT PRIMARY KEY,
    nama_customer VARCHAR(100) NOT NULL,
    nik VARCHAR(20) UNIQUE,
    no_hp VARCHAR(20),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- 4. VEHICLE TYPES
-- =========================================================
CREATE TABLE IF NOT EXISTS vehicle_types (
    id_jenis_kendaraan INT AUTO_INCREMENT PRIMARY KEY,
    nama_jenis VARCHAR(50) NOT NULL,
    deskripsi VARCHAR(255),
    UNIQUE KEY uq_vehicle_types_nama (nama_jenis)
) ENGINE=InnoDB;

-- =========================================================
-- 5. VEHICLES
-- =========================================================
CREATE TABLE IF NOT EXISTS vehicles (
    id_vehicle INT AUTO_INCREMENT PRIMARY KEY,
    id_customer INT NOT NULL,
    id_jenis_kendaraan INT NOT NULL,
    nomor_polisi VARCHAR(20) NOT NULL UNIQUE,
    merk VARCHAR(50),
    model VARCHAR(50),
    warna VARCHAR(30),
    tahun YEAR,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vehicles_customer FOREIGN KEY (id_customer) REFERENCES customers(id_customer),
    CONSTRAINT fk_vehicles_type FOREIGN KEY (id_jenis_kendaraan) REFERENCES vehicle_types(id_jenis_kendaraan)
) ENGINE=InnoDB;

-- =========================================================
-- 6. PARKING AREAS
-- =========================================================
CREATE TABLE IF NOT EXISTS parking_areas (
    id_area INT AUTO_INCREMENT PRIMARY KEY,
    nama_area VARCHAR(100) NOT NULL,
    lokasi VARCHAR(255),
    kapasitas INT NOT NULL DEFAULT 0,
    status ENUM('tersedia','ditutup') DEFAULT 'tersedia'
) ENGINE=InnoDB;

-- =========================================================
-- 7. PARKING SLOTS
-- =========================================================
CREATE TABLE IF NOT EXISTS parking_slots (
    id_slot INT AUTO_INCREMENT PRIMARY KEY,
    id_area INT NOT NULL,
    kode_slot VARCHAR(20) NOT NULL UNIQUE,
    jenis_kendaraan ENUM('motor','mobil','lainnya') NOT NULL,
    status ENUM('kosong','terisi','rusak') DEFAULT 'kosong',
    CONSTRAINT fk_slots_area FOREIGN KEY (id_area) REFERENCES parking_areas(id_area)
) ENGINE=InnoDB;

-- =========================================================
-- 8. PARKING RATES
-- =========================================================
CREATE TABLE IF NOT EXISTS parking_rates (
    id_tarif INT AUTO_INCREMENT PRIMARY KEY,
    id_jenis_kendaraan INT NOT NULL,
    tarif_per_jam DECIMAL(12,2) NOT NULL,
    tarif_maksimal DECIMAL(12,2),
    berlaku_mulai DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    CONSTRAINT fk_rates_type FOREIGN KEY (id_jenis_kendaraan) REFERENCES vehicle_types(id_jenis_kendaraan)
) ENGINE=InnoDB;

-- =========================================================
-- 9. PARKING ENTRIES
-- =========================================================
CREATE TABLE IF NOT EXISTS parking_entries (
    id_entry INT AUTO_INCREMENT PRIMARY KEY,
    id_vehicle INT NOT NULL,
    id_slot INT,
    id_user INT NOT NULL,
    nomor_tiket VARCHAR(50) NOT NULL UNIQUE,
    waktu_masuk DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    kondisi_kendaraan_masuk TEXT,
    status ENUM('parkir','selesai') DEFAULT 'parkir',
    CONSTRAINT fk_entries_vehicle FOREIGN KEY (id_vehicle) REFERENCES vehicles(id_vehicle),
    CONSTRAINT fk_entries_slot FOREIGN KEY (id_slot) REFERENCES parking_slots(id_slot) ON DELETE SET NULL,
    CONSTRAINT fk_entries_user FOREIGN KEY (id_user) REFERENCES users(id_user)
) ENGINE=InnoDB;

-- =========================================================
-- 10. PARKING EXITS
-- =========================================================
CREATE TABLE IF NOT EXISTS parking_exits (
    id_exit INT AUTO_INCREMENT PRIMARY KEY,
    id_entry INT NOT NULL,
    id_user INT NOT NULL,
    waktu_keluar DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    kondisi_kendaraan_keluar TEXT,
    lama_parkir INT,
    status ENUM('keluar','dibatalkan') DEFAULT 'keluar',
    CONSTRAINT fk_exits_entry FOREIGN KEY (id_entry) REFERENCES parking_entries(id_entry),
    CONSTRAINT fk_exits_user FOREIGN KEY (id_user) REFERENCES users(id_user)
) ENGINE=InnoDB;

-- =========================================================
-- 11. PARKING PAYMENTS
-- =========================================================
CREATE TABLE IF NOT EXISTS parking_payments (
    id_payment INT AUTO_INCREMENT PRIMARY KEY,
    id_entry INT NOT NULL,
    id_exit INT,
    id_tarif INT NOT NULL,
    total_bayar DECIMAL(12,2) NOT NULL,
    jumlah_dibayar DECIMAL(12,2) NULL,
    kembalian DECIMAL(12,2) NOT NULL DEFAULT 0,
    metode_pembayaran ENUM('cash','qris','debit','lainnya') DEFAULT 'cash',
    waktu_pembayaran DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('belum_bayar','lunas','dibatalkan') DEFAULT 'belum_bayar',
    CONSTRAINT fk_payments_entry FOREIGN KEY (id_entry) REFERENCES parking_entries(id_entry),
    CONSTRAINT fk_payments_exit FOREIGN KEY (id_exit) REFERENCES parking_exits(id_exit) ON DELETE SET NULL,
    CONSTRAINT fk_payments_rate FOREIGN KEY (id_tarif) REFERENCES parking_rates(id_tarif)
) ENGINE=InnoDB;

-- =========================================================
-- 12. PARKING HISTORY
-- =========================================================
CREATE TABLE IF NOT EXISTS parking_history (
    id_history INT AUTO_INCREMENT PRIMARY KEY,
    id_entry INT,
    id_exit INT,
    id_user INT,
    aktivitas VARCHAR(100) NOT NULL,
    keterangan TEXT,
    waktu_aktivitas DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_history_entry FOREIGN KEY (id_entry) REFERENCES parking_entries(id_entry) ON DELETE SET NULL,
    CONSTRAINT fk_history_exit FOREIGN KEY (id_exit) REFERENCES parking_exits(id_exit) ON DELETE SET NULL,
    CONSTRAINT fk_history_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- DATA MASTER AWAL
-- =========================================================

INSERT INTO roles (nama_role, deskripsi)
SELECT 'Admin', 'Mengelola seluruh sistem parkir'
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE nama_role = 'Admin');

INSERT INTO roles (nama_role, deskripsi)
SELECT 'Petugas', 'Mengelola kendaraan masuk, keluar dan pembayaran'
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE nama_role = 'Petugas');

INSERT INTO roles (nama_role, deskripsi)
SELECT 'Owner', 'Melihat operasional, pembayaran dan laporan sistem parkir'
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE nama_role = 'Owner');

INSERT INTO vehicle_types (nama_jenis, deskripsi)
SELECT 'Motor', 'Kendaraan roda dua'
WHERE NOT EXISTS (SELECT 1 FROM vehicle_types WHERE nama_jenis = 'Motor');

INSERT INTO vehicle_types (nama_jenis, deskripsi)
SELECT 'Mobil', 'Kendaraan roda empat'
WHERE NOT EXISTS (SELECT 1 FROM vehicle_types WHERE nama_jenis = 'Mobil');

INSERT INTO vehicle_types (nama_jenis, deskripsi)
SELECT 'Lainnya', 'Jenis kendaraan lainnya'
WHERE NOT EXISTS (SELECT 1 FROM vehicle_types WHERE nama_jenis = 'Lainnya');

INSERT INTO parking_areas (nama_area, lokasi, kapasitas, status)
SELECT 'Area Motor A', 'Gedung A', 50, 'tersedia'
WHERE NOT EXISTS (SELECT 1 FROM parking_areas WHERE nama_area = 'Area Motor A');

INSERT INTO parking_areas (nama_area, lokasi, kapasitas, status)
SELECT 'Area Mobil A', 'Gedung A', 30, 'tersedia'
WHERE NOT EXISTS (SELECT 1 FROM parking_areas WHERE nama_area = 'Area Mobil A');

INSERT INTO parking_areas (nama_area, lokasi, kapasitas, status)
SELECT 'Area Motor B', 'Gedung B', 40, 'tersedia'
WHERE NOT EXISTS (SELECT 1 FROM parking_areas WHERE nama_area = 'Area Motor B');

-- Slot awal menggunakan pencarian id_area agar aman walaupun id AUTO_INCREMENT berbeda.
INSERT INTO parking_slots (id_area, kode_slot, jenis_kendaraan)
SELECT a.id_area, 'M-A01', 'motor' FROM parking_areas a
WHERE a.nama_area = 'Area Motor A' AND NOT EXISTS (SELECT 1 FROM parking_slots WHERE kode_slot = 'M-A01');
INSERT INTO parking_slots (id_area, kode_slot, jenis_kendaraan)
SELECT a.id_area, 'M-A02', 'motor' FROM parking_areas a
WHERE a.nama_area = 'Area Motor A' AND NOT EXISTS (SELECT 1 FROM parking_slots WHERE kode_slot = 'M-A02');
INSERT INTO parking_slots (id_area, kode_slot, jenis_kendaraan)
SELECT a.id_area, 'M-A03', 'motor' FROM parking_areas a
WHERE a.nama_area = 'Area Motor A' AND NOT EXISTS (SELECT 1 FROM parking_slots WHERE kode_slot = 'M-A03');
INSERT INTO parking_slots (id_area, kode_slot, jenis_kendaraan)
SELECT a.id_area, 'M-A04', 'motor' FROM parking_areas a
WHERE a.nama_area = 'Area Motor A' AND NOT EXISTS (SELECT 1 FROM parking_slots WHERE kode_slot = 'M-A04');
INSERT INTO parking_slots (id_area, kode_slot, jenis_kendaraan)
SELECT a.id_area, 'M-A05', 'motor' FROM parking_areas a
WHERE a.nama_area = 'Area Motor A' AND NOT EXISTS (SELECT 1 FROM parking_slots WHERE kode_slot = 'M-A05');
INSERT INTO parking_slots (id_area, kode_slot, jenis_kendaraan)
SELECT a.id_area, 'C-A01', 'mobil' FROM parking_areas a
WHERE a.nama_area = 'Area Mobil A' AND NOT EXISTS (SELECT 1 FROM parking_slots WHERE kode_slot = 'C-A01');
INSERT INTO parking_slots (id_area, kode_slot, jenis_kendaraan)
SELECT a.id_area, 'C-A02', 'mobil' FROM parking_areas a
WHERE a.nama_area = 'Area Mobil A' AND NOT EXISTS (SELECT 1 FROM parking_slots WHERE kode_slot = 'C-A02');
INSERT INTO parking_slots (id_area, kode_slot, jenis_kendaraan)
SELECT a.id_area, 'C-A03', 'mobil' FROM parking_areas a
WHERE a.nama_area = 'Area Mobil A' AND NOT EXISTS (SELECT 1 FROM parking_slots WHERE kode_slot = 'C-A03');

INSERT INTO parking_rates (id_jenis_kendaraan, tarif_per_jam, tarif_maksimal, status)
SELECT vt.id_jenis_kendaraan, 3000, 15000, 'aktif'
FROM vehicle_types vt
WHERE vt.nama_jenis = 'Motor'
  AND NOT EXISTS (
      SELECT 1 FROM parking_rates pr
      WHERE pr.id_jenis_kendaraan = vt.id_jenis_kendaraan
  );

INSERT INTO parking_rates (id_jenis_kendaraan, tarif_per_jam, tarif_maksimal, status)
SELECT vt.id_jenis_kendaraan, 5000, 25000, 'aktif'
FROM vehicle_types vt
WHERE vt.nama_jenis = 'Mobil'
  AND NOT EXISTS (
      SELECT 1 FROM parking_rates pr
      WHERE pr.id_jenis_kendaraan = vt.id_jenis_kendaraan
  );

-- =========================================================
-- AKUN AWAL
-- Password:
-- admin   = admin123
-- petugas = petugas123
-- owner   = owner123
-- =========================================================
INSERT INTO users (id_role, nama_lengkap, username, password, no_hp, status)
SELECT r.id_role, 'Administrator', 'admin',
       '$2y$12$rA0JO8nwhmWks0/.UBTYquNYSS9bl3CvJgsf3equhBxOdPmQgMU36',
       '081234567890', 'aktif'
FROM roles r
WHERE r.nama_role = 'Admin'
  AND NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

INSERT INTO users (id_role, nama_lengkap, username, password, no_hp, status)
SELECT r.id_role, 'Petugas Parkir', 'petugas',
       '$2y$12$jEdzonqp9Jv4MzF7Z3UUhu0Cvsddm2ms0YdqdYEjN.1aYn82Us2nO',
       '081234567891', 'aktif'
FROM roles r
WHERE r.nama_role = 'Petugas'
  AND NOT EXISTS (SELECT 1 FROM users WHERE username = 'petugas');

INSERT INTO users (id_role, nama_lengkap, username, password, no_hp, status)
SELECT r.id_role, 'Pemilik / Owner', 'owner',
       '$2y$12$c2Tu8IFqSxIutODs.qRPw.nAvCraiyseogFi7///ci598TpSX6.PG',
       '081234567892', 'aktif'
FROM roles r
WHERE r.nama_role = 'Owner'
  AND NOT EXISTS (SELECT 1 FROM users WHERE username = 'owner');

-- =========================================================
-- DATA CONTOH UNTUK UJI TRANSAKSI
-- =========================================================
INSERT INTO customers (nama_customer, nik, no_hp, alamat)
SELECT 'Budi Santoso', '3273010101010001', '081234567892', 'Bandung'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE nik = '3273010101010001');

INSERT INTO customers (nama_customer, nik, no_hp, alamat)
SELECT 'Siti Aminah', '3273010202020002', '081234567893', 'Bandung'
WHERE NOT EXISTS (SELECT 1 FROM customers WHERE nik = '3273010202020002');

INSERT INTO vehicles (id_customer, id_jenis_kendaraan, nomor_polisi, merk, model, warna, tahun, status)
SELECT c.id_customer, vt.id_jenis_kendaraan, 'D 1234 AB', 'Honda', 'Beat', 'Hitam', 2023, 'aktif'
FROM customers c CROSS JOIN vehicle_types vt
WHERE c.nik = '3273010101010001'
  AND vt.nama_jenis = 'Motor'
  AND NOT EXISTS (SELECT 1 FROM vehicles WHERE nomor_polisi = 'D 1234 AB');

INSERT INTO vehicles (id_customer, id_jenis_kendaraan, nomor_polisi, merk, model, warna, tahun, status)
SELECT c.id_customer, vt.id_jenis_kendaraan, 'D 5678 CD', 'Toyota', 'Avanza', 'Putih', 2022, 'aktif'
FROM customers c CROSS JOIN vehicle_types vt
WHERE c.nik = '3273010202020002'
  AND vt.nama_jenis = 'Mobil'
  AND NOT EXISTS (SELECT 1 FROM vehicles WHERE nomor_polisi = 'D 5678 CD');

-- =========================================================
-- KOMPATIBILITAS DATABASE LAMA
-- Jika database lama belum memiliki kolom pembayaran ini,
-- tambahkan otomatis. Jika sudah ada, tidak melakukan apa-apa.
-- =========================================================
SET @jumlah_dibayar_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'parking_payments'
      AND COLUMN_NAME = 'jumlah_dibayar'
);
SET @sql = IF(
    @jumlah_dibayar_exists = 0,
    'ALTER TABLE parking_payments ADD COLUMN jumlah_dibayar DECIMAL(12,2) NULL AFTER total_bayar',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @kembalian_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'parking_payments'
      AND COLUMN_NAME = 'kembalian'
);
SET @sql = IF(
    @kembalian_exists = 0,
    'ALTER TABLE parking_payments ADD COLUMN kembalian DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER jumlah_dibayar',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE parking_payments
SET jumlah_dibayar = total_bayar,
    kembalian = 0
WHERE status = 'lunas'
  AND jumlah_dibayar IS NULL;

-- =========================================================
-- SELESAI
-- Total tabel: 12
-- =========================================================
