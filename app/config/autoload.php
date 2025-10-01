<?php
// app/config/autoload.php
declare(strict_types=1);

defined('APP_PATH') || define('APP_PATH', dirname(__DIR__));
defined('APP_DEBUG') || define('APP_DEBUG', true); // o léelo de .env

spl_autoload_register(static function (string $class): void {
    // cache por request: clase => archivo
    static $cache = [];
    static $prefixes = null;

    // quitar backslash inicial si viene
    $class = ltrim($class, '\\');

    // fast-path: ya resuelta en este request
    if (isset($cache[$class])) {
        require $cache[$class];
        return;
    }

    // mapa PSR-4 (prefijos => base dir). Usa tus carpetas reales (en minúsculas).
    if ($prefixes === null) {
        $prefixes = [
            'App\\Controllers\\' => APP_PATH . '/controllers/',
            'App\\Models\\'      => APP_PATH . '/models/',
            // 'App\\Views\\'     => APP_PATH . '/views/', // normal: las vistas no se autoload
            'Core\\'             => APP_PATH . '/core/',
            'App\\'              => APP_PATH . '/',       // fallback
        ];
        // prefijos más largos primero
        uksort($prefixes, static fn($a, $b) => strlen($b) <=> strlen($a));
    }

    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }

        $relative = substr($class, strlen($prefix));

        // seguridad: no permitir traversal ni caracteres inválidos en paths
        if (str_contains($relative, '..') || strpbrk($relative, ":*?\"<>|") !== false) {
            return;
        }

        $file = $baseDir . strtr($relative, '\\', DIRECTORY_SEPARATOR) . '.php';

        if (is_file($file)) {
            $cache[$class] = $file;
            require $file;
            return;
        }
    }

    // ayuda en desarrollo
    if (APP_DEBUG) {
        error_log("[autoload] No se pudo cargar la clase: {$class}");
    }
}, /* throw */ true, /* prepend */ true);
