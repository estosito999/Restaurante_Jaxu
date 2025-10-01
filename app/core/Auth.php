<?php
namespace Core;

class Auth {
    public static function check(): bool {
        return !empty($_SESSION['user_id']);
    }
    public static function requireLogin(): void {
        if (!self::check()) {
            header('Location: /login'); exit;
        }
    }
    public static function userId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }
    public static function userRole(): ?string {
        return $_SESSION['rol'] ?? null;
    }
    public static function requireRole(array $roles): void {
        if (!self::check() || !in_array(self::userRole(), $roles, true)) {
            http_response_code(403); exit('No autorizado');
        }
    }
}
