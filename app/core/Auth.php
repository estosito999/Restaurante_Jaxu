<?php
namespace Core;

class Auth
{
    /** ¿hay usuario logueado? */
    public static function check(): bool
    {
        return (bool) Session::get('user_id');
    }

    /** Obliga a estar logueado (redirecciona a /login) */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . (defined('BASE_URI') ? BASE_URI : '') . '/login');
            exit;
        }
    }

    /** ID de usuario (o null) */
    public static function id(): ?int
    {
        $id = Session::get('user_id');
        return $id !== null ? (int)$id : null;
    }

    /** Alias más explícito */
    public static function userId(): ?int
    {
        return self::id();
    }

    /** Rol actual (o null) */
    public static function role(): ?string
    {
        // En tu login guardas el rol en $_SESSION['rol']
        return isset($_SESSION['rol']) ? (string)$_SESSION['rol'] : null;
    }

    /**
     * Exigir uno de varios roles.
     * Ej: Auth::requireRole(['admin','cajero']);
     */
    public static function requireRole(array $allowed): void
    {
        self::requireLogin();
        $role = self::role();
        if ($role === null || !in_array($role, $allowed, true)) {
            http_response_code(403);
            exit('Acceso denegado');
        }
    }

    /** Inicia sesión */
    public static function login(int $userId, ?string $role = null): void
    {
        Session::set('user_id', $userId);
        if ($role !== null) {
            $_SESSION['rol'] = $role;
        }
    }

    /** Cierra sesión */
    public static function logout(): void
    {
        Session::forget('user_id');
        unset($_SESSION['rol']);
        Session::regenerateCsrf();
    }
}
