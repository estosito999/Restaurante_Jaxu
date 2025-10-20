<?php
declare(strict_types=1);

/**
 * Lee el archivo .env de la raíz del proyecto (Restaurant_Jaxu/.env)
 * Compatible con PHP 7.4 (sin str_starts_with/contains/ends_with).
 */
if (!function_exists('env')) {
    function env(string $key, $default = null) {
        static $vars = null;
        if ($vars !== null) {
            return array_key_exists($key, $vars) ? $vars[$key] : $default;
        }
        $vars = [];
        $path = dirname(__DIR__, 2) . '/.env'; // raíz del proyecto
        if (is_file($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') continue;
                $pos = strpos($line, '=');
                if ($pos === false) continue;
                $k = trim(substr($line, 0, $pos));
                $v = trim(substr($line, $pos + 1));
                // Quitar comillas envolventes "..." o '...'
                if ($v !== '' && (
                    ($v[0] === '"' && substr($v, -1) === '"') ||
                    ($v[0] === "'" && substr($v, -1) === "'")
                )) {
                    $v = substr($v, 1, -1);
                }
                $vars[$k] = $v;
            }
        }
        return array_key_exists($key, $vars) ? $vars[$key] : $default;
    }
}

/** Constantes base del proyecto */
define('BASE_PATH', dirname(__DIR__, 2));   // .../Restaurant_Jaxu
define('APP_PATH',  dirname(__DIR__));      // .../Restaurant_Jaxu/app

/** Entorno/app */
define('APP_ENV',   env('APP_ENV', 'local'));
define('APP_DEBUG', filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN));
define('APP_KEY',   env('APP_KEY', 'key_por_defecto_cambialo'));

/** Base de datos (si quieres, úsalos desde Core\Database) */
define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_PORT', (int) env('DB_PORT', 3306));
define('DB_NAME', env('DB_NAME', 'jaxu_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');
