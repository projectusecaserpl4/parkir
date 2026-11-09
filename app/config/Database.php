<?php

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $dsn = 'mysql:host=' . DB_HOST .
                   ';dbname=' . DB_NAME .
                   ';charset=' . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                // Samakan zona waktu sesi MySQL dengan PHP (NOW(), CURRENT_TIMESTAMP, dsb).
                self::$connection->exec("SET time_zone = '" . DB_TIMEZONE . "'");
            } catch (PDOException $e) {
                error_log('[SistemParkir] Koneksi database gagal: ' . $e->getMessage());
                http_response_code(500);
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                exit(
                    '<h2>Koneksi database gagal.</h2>' .
                    '<p>Pastikan MySQL aktif, database <b>' .
                    htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8') .
                    '</b> sudah di-import, dan konfigurasi database benar.</p>'
                );
            }
        }

        return self::$connection;
    }
}
