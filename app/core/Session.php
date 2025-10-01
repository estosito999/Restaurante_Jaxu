<?php // app/Core/Session.php
namespace Core;

class Session {
    protected static function start(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    // Genera y devuelve token CSRF
    public static function getCsrf(): string {
        self::start();
        if (empty($_SESSION['_csrf'])) {
            // más seguro que hash(session_id())
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    // Valida token recibido
    public static function checkCsrf(?string $token): bool {
        self::start();
        return is_string($token) && hash_equals($_SESSION['_csrf'] ?? '', $token);
    }

    // Flash: set/get con el mismo namespace
    public static function flash(string $key, ?string $value = null) {
        self::start();
        if ($value === null) {
            $val = $_SESSION['_flash'][$key] ?? null;
            if (isset($_SESSION['_flash'][$key])) {
                unset($_SESSION['_flash'][$key]); // se consume una vez
            }
            return $val;
        }
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    // Helper para obtener un flash “por defecto” (opcional)
    public static function getFlash() {
        self::start();
        $flash = $_SESSION['_flash']['default'] ?? null;
        unset($_SESSION['_flash']['default']);
        return $flash;
    }
}
