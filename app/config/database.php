<?php
// app/config/database.php
declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;

/**
 * Conexión PDO singleton (PHP 7.4 compatible)
 * Usa constantes definidas en config.php (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS).
 */
class Database
{
    /** @var PDO|null */
    private static $pdo = null;

    /**
     * @return PDO
     */
    public static function pdo()
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
        $port = defined('DB_PORT') ? DB_PORT : 3306;
        $db   = defined('DB_NAME') ? DB_NAME : 'jaxu_db';
        $user = defined('DB_USER') ? DB_USER : 'root';
        $pass = defined('DB_PASS') ? DB_PASS : '';
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            self::$pdo = new PDO($dsn, $user, $pass, $opts);
        } catch (PDOException $e) {
            error_log('[DB] ' . $e->getMessage());
            http_response_code(500);
            exit('Error de conexión a base de datos.');
        }

        return self::$pdo;
    }

    private function __construct() {}
}
