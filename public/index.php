<?php
// public/index.php
declare(strict_types=1);

/**
 * Front Controller para Jaxu (nativo, sin frameworks)
 * - Detecta base path correctamente aun si accedes como /Restaurant_Jaxu/public
 * - Inicializa config, DB (PDO), Router y despacha rutas
 * - Incluye helpers: manejo de errores, método override y preflight OPTIONS
 */

// -------------------------------
// 0) Bootstrap básico
// -------------------------------
session_start();

// Zona horaria Bolivia
date_default_timezone_set('America/La_Paz');

// Carga config/env y núcleo
require __DIR__ . '/../app/config/config.php';
require __DIR__ . '/../app/config/database.php';
require __DIR__ . '/../app/core/Router.php';
require __DIR__ . '/../app/core/Controller.php';
require __DIR__ . '/../app/core/View.php';
require __DIR__ . '/../app/core/Session.php';
require __DIR__ . '/../app/core/Auth.php';
require __DIR__ . '/../app/config/autoload.php';

// Cabeceras base
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Manejo de errores según APP_DEBUG
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
}

// Convertir warnings/notice en excepciones (ayuda a detectar problemas en dev)
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// -------------------------------
// 1) Descubrir base path y normalizar ruta
// -------------------------------
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';                    // ej: /Restaurant_Jaxu/public/index.php
$base       = rtrim(dirname($scriptName), '/\\');               // ej: /Restaurant_Jaxu/public
$uri        = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; // ej: /Restaurant_Jaxu/public/platos
$path       = substr($uri, strlen($base));                      // ej: /platos (o false si no matchea)
$path       = $path === false ? '/' : '/' . ltrim($path, '/');  // asegura empezar con /

// Quita la barra final si no es raíz
if ($path !== '/' && substr($path, -1) === '/') {
    $path = rtrim($path, '/');
}

// Define constantes útiles para vistas y assets
define('BASE_URI', $base === '' ? '/' : $base);
define('ASSETS_URI', BASE_URI . '/assets');

// -------------------------------
// 2) Método HTTP, override y preflight
// -------------------------------
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Override de método (para formularios HTML que simulan PUT/DELETE)
if ($method === 'POST') {
    if (!empty($_POST['_method'])) {
        $override = strtoupper((string)$_POST['_method']);
        if (in_array($override, ['PUT','PATCH','DELETE'], true)) {
            $method = $override;
        }
    } elseif (!empty($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
        $override = strtoupper((string)$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        if (in_array($override, ['PUT','PATCH','DELETE'], true)) {
            $method = $override;
        }
    }
}

// Responder preflight CORS si lo necesitas (útil para futuras peticiones fetch)
// Por defecto lo dejamos neutro; descomenta si requieres CORS.
// if ($method === 'OPTIONS') {
//     header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
//     header('Access-Control-Allow-Headers: Content-Type, X-HTTP-Method-Override');
//     http_response_code(204);
//     exit;
// }

// -------------------------------
// 3) Registrar rutas y despachar
// -------------------------------
$router = new \Core\Router();

// Cargar definición de rutas de la app (usa $router->add(...))
require __DIR__ . '/../app/routes/web.php';

// IMPORTANTE: nuestro Router calcula su propio base, pero aquí ya
// tenemos $path normalizado. Vamos a despachar con el URI completo
// para mantener compatibilidad con Router->dispatch():
try {
    // Opción A (compatible con Router actual): pásale la REQUEST_URI original
    // $router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $method);

    // Opción B: forzamos el path normalizado reemplazando REQUEST_URI temporalmente
    $originalRequestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $_SERVER['REQUEST_URI'] = (rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\')) . ($path ?? '/'); // armoniza con el parse interno del Router
    $router->dispatch($_SERVER['REQUEST_URI'], $method);
    $_SERVER['REQUEST_URI'] = $originalRequestUri;
} catch (Throwable $e) {
    if (APP_DEBUG) {
        http_response_code(500);
        echo "<h1>Error</h1>";
        echo "<pre>" . htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "Error interno. Intente más tarde.";
    }
    exit;
}
