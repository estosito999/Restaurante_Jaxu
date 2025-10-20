<?php
namespace Core;

class Session
{
    // Usamos sin tipado para máxima compatibilidad 7.4
    private static $booted = false;

    /* ---------- núcleo ---------- */
    private static function boot(): void
    {
        if (self::$booted) return;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (!isset($_SESSION['__flash']))  $_SESSION['__flash'] = [];
        if (empty($_SESSION['__csrf']))    $_SESSION['__csrf']  = bin2hex(random_bytes(16));

        self::$booted = true;
    }

    // Alias para proyectos que llaman start()
    public static function start(): void { self::boot(); }

    /* ---------- KV ---------- */
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

    /* ---------- Flash ---------- */
    // Compat: si $value === null => GET+consume; si trae valor => SET
    public static function flash(string $key, ?string $value = null)
    {
        self::boot();
        if ($value === null) {
            if (!isset($_SESSION['__flash'][$key])) return null;
            $v = (string)$_SESSION['__flash'][$key];
            unset($_SESSION['__flash'][$key]);
            return $v;
        }
        $_SESSION['__flash'][$key] = $value;
        return null;
    }

    // Si prefieres usar explícitos:
    public static function flashSet(string $key, string $value): void { self::flash($key, $value); }
    public static function getFlash(string $key): ?string { return self::flash($key, null); }

    /* ---------- CSRF ---------- */
    public static function getCsrf(): string
    {
        self::boot();
        return (string)$_SESSION['__csrf'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        self::boot();
        return is_string($token) && hash_equals($_SESSION['__csrf'], $token);
    }

    // Alias de compatibilidad con controladores antiguos
    public static function checkCsrf(?string $token): bool { return self::verifyCsrf($token); }

    public static function regenerateCsrf(): void
    {
        self::boot();
        $_SESSION['__csrf'] = bin2hex(random_bytes(16));
    }

    /* ---------- Destroy ---------- */
    public static function destroy(): void
    {
        self::boot();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], !empty($p['secure']), !empty($p['httponly']));
        }
        @session_destroy();
        self::$booted = false;
    }
}
