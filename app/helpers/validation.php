<?php

/* ==================================================================
 | Helper validasi & normalisasi input
 ================================================================== */

function textLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

/** Rapikan spasi berlebih dan hapus spasi di awal/akhir. */
function cleanText(string $value): string
{
    $clean = preg_replace('/\s+/u', ' ', $value);
    return trim($clean ?? '');
}

/** "budi santoso" -> "Budi Santoso" */
function titleCase(string $value): string
{
    return function_exists('mb_convert_case')
        ? mb_convert_case($value, MB_CASE_TITLE, 'UTF-8')
        : ucwords(strtolower($value));
}

/** Nama orang: huruf, spasi, titik, apostrof, strip. Tidak boleh angka. */
function isValidPersonName(string $value, int $min = 3, int $max = 100): bool
{
    $len = textLength($value);
    return $len >= $min && $len <= $max
        && preg_match('/^\p{L}[\p{L} .\'\-]*$/u', $value) === 1;
}

/** NIK: tepat 16 digit angka. */
function isValidNik(string $value): bool
{
    return preg_match('/^\d{16}$/', $value) === 1;
}

/** Hapus spasi, tanda hubung, titik, dan kurung dari nomor telepon. */
function normalizePhone(string $value): string
{
    return preg_replace('/[\s\-().]/', '', trim($value)) ?? '';
}

/** Nomor HP Indonesia: awalan 0 / 62 / +62, total 10-14 digit. */
function isValidPhone(string $value): bool
{
    return preg_match('/^(?:\+62|62|0)\d{9,13}$/', $value) === 1;
}

/**
 * Nomor polisi Indonesia -> "D 1234 ABC".
 * Return null jika format tidak valid (1-2 huruf, 1-4 angka, 0-3 huruf).
 */
function normalizePlate(string $value): ?string
{
    $plain = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value) ?? '');
    if (preg_match('/^([A-Z]{1,2})(\d{1,4})([A-Z]{0,3})$/', $plain, $m) !== 1) {
        return null;
    }
    return $m[3] === '' ? $m[1] . ' ' . $m[2] : $m[1] . ' ' . $m[2] . ' ' . $m[3];
}

/**
 * Angka desimal sederhana: "50000", "50000.5", "50000,5".
 * Format ribuan seperti "50.000" SENGAJA ditolak (null) agar tidak terbaca 50.
 */
function parseDecimal(string $value): ?float
{
    $value = trim($value);
    if (preg_match('/^\d{1,12}(?:[.,]\d{1,2})?$/', $value) !== 1) {
        return null;
    }
    return (float) str_replace(',', '.', $value);
}

/** Terima "Y-m-d\TH:i", "Y-m-d H:i", atau "Y-m-d H:i:s"; hasil "Y-m-d H:i:s". */
function parseDateTimeInput(string $value): ?string
{
    $value = trim($value);
    foreach (['Y-m-d\TH:i', 'Y-m-d H:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s'] as $format) {
        $date = DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();
        if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
            return $date->format('Y-m-d H:i:s');
        }
    }
    return null;
}

/**
 * Deteksi kategori kendaraan dari teks merk/model.
 * Return 'motor', 'mobil', atau null jika tidak dikenali / ambigu.
 * Dipakai untuk mencegah data tidak masuk akal seperti Mobil "Honda Beat".
 */
function detectVehicleCategory(string $text): ?string
{
    static $motor = [
        'beat', 'vario', 'scoopy', 'genio', 'pcx', 'adv', 'revo', 'blade', 'cbr', 'cb150r',
        'crf', 'forza', 'supra x', 'supra gt', 'nmax', 'xmax', 'tmax', 'aerox', 'mio', 'fino',
        'freego', 'lexi', 'xeon', 'r15', 'r25', 'r6', 'mt-15', 'mt15', 'mt-25', 'mt25',
        'vixion', 'byson', 'jupiter', 'fazzio', 'filano', 'satria', 'gsx', 'nex ii', 'burgman',
        'ninja', 'klx', 'w175', 'vespa', 'primavera', 'megapro', 'tiger revo', 'karisma',
        'verza', 'spacy', 'cs1', 'smash', 'xsr', 'x-ride', 'cb150', 'gl pro', 'rx king',
    ];
    static $car = [
        'avanza', 'xenia', 'brio', 'jazz', 'civic', 'accord', 'crv', 'cr-v', 'hrv', 'hr-v',
        'brv', 'br-v', 'mobilio', 'agya', 'ayla', 'calya', 'sigra', 'rush', 'terios', 'innova',
        'fortuner', 'alphard', 'vellfire', 'camry', 'corolla', 'yaris', 'vios', 'raize', 'rocky',
        'xpander', 'pajero', 'triton', 'l300', 'ertiga', 'apv', 'swift', 'ignis', 'baleno',
        'jimny', 'grand vitara', 'sirion', 'gran max', 'granmax', 'luxio', 'almaz', 'confero',
        'cortez', 'ioniq', 'creta', 'stargazer', 'palisade', 'santa fe', 'sportage', 'seltos',
        'carnival', 'picanto', 'livina', 'serena', 'x-trail', 'xtrail', 'juke', 'kicks', 'hiace',
        'commuter', 'cx-5', 'cx5', 'mazda2', 'mazda3', 'fortuner', 'veloz', 'avanza veloz',
        'city hatchback', 'wuling air', 'air ev', 'bingo', 'sienta', 'voxy', 'noah',
    ];

    $haystack = ' ' . preg_replace('/[^a-z0-9\- ]+/', ' ', strtolower($text)) . ' ';
    $haystack = preg_replace('/\s+/', ' ', $haystack) ?? $haystack;

    $matches = static function (array $words) use ($haystack): bool {
        foreach ($words as $word) {
            if (strpos($haystack, ' ' . $word . ' ') !== false) {
                return true;
            }
        }
        return false;
    };

    $isMotor = $matches($motor);
    $isCar = $matches($car);

    if ($isMotor && !$isCar) {
        return 'motor';
    }
    if ($isCar && !$isMotor) {
        return 'mobil';
    }
    return null;
}
