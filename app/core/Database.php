<?php
// app/core/Database.php
namespace Core;

use PDO;
use PDOException;

/**
 * Conexión PDO en modo singleton (lazy).
 * Compatible con PHP 7.4.
 */
class Database
{
    /** @var PDO|null */
    private static $pdo = null;

    /**
     * Devuelve una única instancia de PDO (se crea la primera vez).
     */
    public static function getInstance()
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        // 1) Intentar leer de .env (si existe)
        $env = self::parseEnv(__DIR__ . '/../.env'); // ajusta si tu .env está en otra ruta

        // 2) Si no hay .env, usar constantes definidas en app/config/config.php (si existen)
        $host    = isset($env['DB_HOST'])    ? $env['DB_HOST']    : (defined('DB_HOST') ? DB_HOST : '127.0.0.1');
        $port    = isset($env['DB_PORT'])    ? (int)$env['DB_PORT'] : (defined('DB_PORT') ? (int)DB_PORT : 3306);
        $name    = isset($env['DB_NAME'])    ? $env['DB_NAME']    : (defined('DB_NAME') ? DB_NAME : 'jaxu_db');
        $user    = isset($env['DB_USER'])    ? $env['DB_USER']    : (defined('DB_USER') ? DB_USER : 'root');
        $pass    = isset($env['DB_PASS'])    ? $env['DB_PASS']    : (defined('DB_PASS') ? DB_PASS : '');
        $charset = isset($env['DB_CHARSET']) ? $env['DB_CHARSET'] : (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');

        // En Windows/XAMPP, "localhost" puede ser más lento → usa 127.0.0.1
        if ($host === 'localhost') {
            $host = '127.0.0.1';
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 2,  // evita “congelarse” si MySQL no responde
            // PDO::ATTR_PERSISTENT      => true, // opcional: habilita conexiones persistentes
        ];

        try {
            self::$pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Lanza el error para que lo capture tu controlador/index.php
            throw $e;
        }

        return self::$pdo;
    }

    /**
     * Lee un .env simple (clave=valor).
     */
    private static function parseEnv($path)
    {
        if (!is_file($path)) return [];
        $vars = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;
            list($k, $v) = array_map('trim', explode('=', $line, 2));
            $vars[$k] = trim($v, "\"'");
        }
        return $vars;
    }
}
