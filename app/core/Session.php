<?php
namespace Core;

class Session
{
    private static bool $booted = false;

    private static function boot(): void
    {
        if (self::$booted) return;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (!isset($_SESSION['__flash'])) {
            $_SESSION['__flash'] = [];
        }
        if (empty($_SESSION['__csrf'])) {
            $_SESSION['__csrf'] = bin2hex(random_bytes(16));
        }
        self::$booted = true;
    }

    public static function get(string $key, $default = null)
    {
        self::boot();
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }

    public static function set(string $key, $value): void
    {
        self::boot();
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        self::boot();
        unset($_SESSION[$key]);
    }

    // Flash simple (mensaje de una sola lectura)
    public static function flash(string $key, string $value): void
    {
        self::boot();
        $_SESSION['__flash'][$key] = $value;
    }

    public static function getFlash(string $key): ?string
    {
        self::boot();
        if (!isset($_SESSION['__flash'][$key])) return null;
        $v = (string) $_SESSION['__flash'][$key];
        unset($_SESSION['__flash'][$key]);
        return $v;
    }

    // CSRF
    public static function getCsrf(): string
    {
        self::boot();
        return (string) $_SESSION['__csrf'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        self::boot();
        return is_string($token) && hash_equals($_SESSION['__csrf'], $token);
    }

    public static function regenerateCsrf(): void
    {
        self::boot();
        $_SESSION['__csrf'] = bin2hex(random_bytes(16));
    }

    public static function destroy(): void
    {
        self::boot();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                isset($params['secure']) ? (bool)$params['secure'] : false,
                isset($params['httponly']) ? (bool)$params['httponly'] : true
            );
        }
        @session_destroy();
        self::$booted = false;
    }
}
