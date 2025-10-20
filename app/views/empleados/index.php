<?php
// public/index.php
declare(strict_types=1);

// --- Sesión (rápido y seguro) ---
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime'=>0,'path'=>'/','domain'=>'',
        'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on',
        'httponly'=>true,'samesite'=>'Lax',
    ]);
    session_start();
}

// --- Zona horaria y cabeceras básicas ---
date_default_timezone_set('America/La_Paz');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// --- Autoload/config núcleo (sin archivos que creen PDO) ---
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/autoload.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/View.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/Core/HttpNotFoundException.php'; // <- nuevo

// --- Errores: detallados solo en dev ---
if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors','1'); error_reporting(E_ALL);
} else {
    ini_set('display_errors','0'); error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
}

// --- Descubrir base y path (rápido) ---
$script = $_SERVER['SCRIPT_NAME'] ?? '';          // p.ej. /Restaurant_Jaxu/public/index.php
$base   = rtrim(dirname($script), '/\\');         // p.ej. /Restaurant_Jaxu/public
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path   = ($base !== '' && str_starts_with($uri, $base)) ? substr($uri, strlen($base)) : $uri;
$path   = $path === '' ? '/' : $path;
if ($path !== '/' && str_ends_with($path,'/')) $path = rtrim($path,'/');

defined('BASE_URI')   || define('BASE_URI', $base === '' ? '/' : $base);
defined('ASSETS_URI') || define('ASSETS_URI', BASE_URI . '/assets');

// --- Método + override ---
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST') {
    $ov = $_POST['_method'] ?? ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '');
    $ov = strtoupper((string)$ov);
    if ($ov === 'PUT' || $ov === 'PATCH' || $ov === 'DELETE') $method = $ov;
}

// --- Rutas y dispatch (sin magia extra) ---
$router = new \Core\Router();
require __DIR__ . '/../app/routes/web.php';

try {
    $router->dispatch($path, $method);
} catch (\Core\HttpNotFoundException $e) {
    http_response_code(404);
    echo "404 — Página no encontrada";
} catch (\Throwable $e) {
    if (defined('APP_DEBUG') && APP_DEBUG) {
        http_response_code(500);
        echo "<h1>Error</h1><pre>" .
             htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString(), ENT_QUOTES, 'UTF-8') .
             "</pre>";
    } else {
        http_response_code(500);
        echo "Error interno. Intente más tarde.";
    }
}
