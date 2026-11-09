<?php

// URL utama aplikasi saat folder project berada di C:\laragon\www\sistem-parkir
// Aplikasi dapat dibuka dari: http://localhost/sistem-parkir/
define('BASE_URL', '/sistem-parkir');
// File CSS/JS berada di folder public.
define('ASSET_URL', BASE_URL . '/public');

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'sistem_parkir');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// false  = error PHP tidak ditampilkan ke layar (aman untuk pengujian/produksi),
//          detail error tetap dicatat di log PHP.
// true   = tampilkan error PHP di layar (hanya untuk debugging saat coding).
define('APP_DEBUG', false);

date_default_timezone_set('Asia/Jakarta');
// Zona waktu MySQL dipaksa sama dengan PHP (WIB) agar jam masuk/keluar/tarif konsisten.
define('DB_TIMEZONE', '+07:00');
