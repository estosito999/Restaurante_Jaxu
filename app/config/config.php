<?php
function env(string $key, $default = null) {
    static $vars = null;
    if ($vars === null) {
        $vars = [];
        $path = __DIR__ . '/../../.env';
        if (file_exists($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#')) continue;
                [$k, $v] = array_map('trim', explode('=', $line, 2));
                $vars[$k] = $v;
            }
        }
    }
    return $vars[$key] ?? $default;
}

define('APP_DEBUG', env('APP_DEBUG', 'false') === 'true');
define('APP_KEY', env('APP_KEY', 'key'));
define('BASE_PATH', dirname(__DIR__, 2));
