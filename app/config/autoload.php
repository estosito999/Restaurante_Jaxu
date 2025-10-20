<?php
// app/config/autoload.php
declare(strict_types=1);

/**
 * Autoloader PSR-4 muy simple para namespaces:
 *  - Core\  => app/core/
 *  - App\   => app/
 */
if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__));
}

spl_autoload_register(function ($class) {
    static $map = null;
    if ($map === null) {
        $map = [
            'Core\\' => APP_PATH . '/core/',
            'App\\'  => APP_PATH . '/',
        ];
    }

    // Normaliza
    $class = ltrim($class, '\\');

    foreach ($map as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($class, $prefix, $len) !== 0) {
            continue;
        }
        $relative = substr($class, $len);
        $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        if (is_file($file)) {
            require $file;
            return true;
        }
    }

    // Ayuda en dev
    if (defined('APP_DEBUG') && APP_DEBUG) {
        error_log("[autoload] No se pudo cargar la clase: {$class}");
    }
    return false;
}, true, true);
