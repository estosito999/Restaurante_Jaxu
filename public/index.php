<?php
// public/index.php
declare(strict_types=1);

session_start();
date_default_timezone_set('America/La_Paz');

// Config + autoload
require __DIR__ . '/../app/config/config.php';
require __DIR__ . '/../app/config/autoload.php';
// ⚠️ No cargues la clase Database desde config para evitar duplicados
// require __DIR__ . '/../app/config/database.php';

// Core
require __DIR__ . '/../app/core/Router.php';
require __DIR__ . '/../app/core/Controller.php';
require __DIR__ . '/../app/core/View.php';
require __DIR__ . '/../app/core/Session.php';
require __DIR__ . '/../app/core/Auth.php';
require __DIR__ . '/../app/core/HttpNotFoundException.php';

// Errores
if (APP_DEBUG) { ini_set('display_errors','1'); error_reporting(E_ALL); }
else           { ini_set('display_errors','0'); error_reporting(0); }

// BASE_URI (soporta subcarpeta /Restaurant_Jaxu/public)
$script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$base   = rtrim(str_replace('\\','/', dirname($script)), '/');   // p.ej. /Restaurant_Jaxu/public
if ($base === '/') { $base = ''; }
if (!defined('BASE_URI'))   define('BASE_URI',   $base === '' ? '/' : $base);
if (!defined('ASSETS_URI')) define('ASSETS_URI', ($base === '' ? '' : $base) . '/assets');

// Ruta solicitada (sin usar str_starts_with)
$reqUri  = $_SERVER['REQUEST_URI'] ?? '/';
$uriPath = parse_url($reqUri, PHP_URL_PATH);
$uriPath = ($uriPath !== false && $uriPath !== null) ? $uriPath : '/';
$path    = ($base !== '' && strpos($uriPath, $base) === 0) ? substr($uriPath, strlen($base)) : $uriPath;
if ($path === '') $path = '/';
if ($path !== '/' && substr($path, -1) === '/') $path = rtrim($path, '/');

// Cargar rutas y despachar
$router = new \Core\Router();
require __DIR__ . '/../app/routes/web.php';

try {
    $router->dispatch($path, $_SERVER['REQUEST_METHOD'] ?? 'GET');
} catch (\Core\HttpNotFoundException $e) {
    http_response_code(404);
    echo APP_DEBUG ? '404 - '.htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') : '404 - Página no encontrada';
} catch (\Throwable $e) {
    http_response_code(500);
    if (APP_DEBUG) {
        echo '<pre>500 - ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "\n" .
             $e->getTraceAsString() . '</pre>';
    } else {
        echo 'Error interno del servidor';
    }
}
