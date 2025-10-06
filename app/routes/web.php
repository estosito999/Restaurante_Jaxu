<?php
use App\Controllers\AuthController;
use App\Controllers\PlatoController;
use App\Controllers\ClienteController;
use App\Controllers\FacturaController;
use App\Controllers\CajaController;
use App\Controllers\EmpleadoController;
use App\Controllers\VentasController;

// Evita ejecución directa del archivo de rutas
if (!isset($router) || !($router instanceof \Core\Router)) {
    http_response_code(403);
    header('Content-Type: text/html; charset=UTF-8');
    echo <<<HTML
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Acceso no permitido</title></head>
<body style="font-family: system-ui; padding:2rem;">
  <h1>Acceso no permitido</h1>
  <p>No deberías estar aquí. Este archivo se carga desde <code>public/index.php</code>.</p>
</body>
</html>
HTML;
    exit;
}


/** AUTH **/
$router->add('GET','/',                 [AuthController::class,   'loginForm']);
$router->add('POST','/login',           [AuthController::class,   'login']);
$router->add('GET','/logout',           [AuthController::class,   'logout']);

/** PLATOS **/
$router->add('GET','/platos',                 [PlatoController::class,   'index']);
$router->add('GET','/platos/crear',           [PlatoController::class,   'create']);
$router->add('POST','/platos',                [PlatoController::class,   'store']);
$router->add('GET','/platos/editar/{id}',     [PlatoController::class,   'edit']);
$router->add('POST','/platos/actualizar/{id}',[PlatoController::class,   'update']);
$router->add('POST','/platos/eliminar/{id}',  [PlatoController::class,   'destroy']);

/** CLIENTES **/
$router->add('GET','/clientes',  [ClienteController::class, 'index']);
$router->add('POST','/clientes', [ClienteController::class, 'store']);

/** FACTURAS **/
$router->add('GET','/facturas/crear',   [FacturaController::class, 'create']);
$router->add('POST','/facturas',        [FacturaController::class, 'store']);
$router->add('GET','/facturas/{id}',    [FacturaController::class, 'show']);
$router->add('GET','/facturas/ticket/{id}', [FacturaController::class, 'ticket']);
$router->add('GET','/facturas/pdf/{id}',    [FacturaController::class, 'pdf']);


/** CAJA **/
$router->add('GET','/caja/cierre',      [CajaController::class,    'index']);
$router->add('POST','/caja/cerrar',     [CajaController::class,    'cerrar']);
$router->add('GET','/caja/abrir',  [CajaController::class, 'abrirForm']);
$router->add('POST','/caja/abrir', [CajaController::class, 'abrir']);

// EMPLEADOS (solo admin)
$router->add('GET',  '/empleados',                   [EmpleadoController::class, 'index']);
$router->add('GET',  '/empleados/crear',             [EmpleadoController::class, 'create']);
$router->add('POST', '/empleados',                   [EmpleadoController::class, 'store']);
$router->add('GET',  '/empleados/editar/{id}',       [EmpleadoController::class, 'edit']);
$router->add('POST', '/empleados/actualizar/{id}',   [EmpleadoController::class, 'update']);
$router->add('GET',  '/empleados/password/{id}',     [EmpleadoController::class, 'passwordForm']);
$router->add('POST', '/empleados/password/{id}',     [EmpleadoController::class, 'passwordUpdate']);
$router->add('POST', '/empleados/eliminar/{id}',     [EmpleadoController::class, 'destroy']);

/** Reportes */
$router->add('GET','/ventas',            [VentasController::class, 'index']);
$router->add('GET','/ventas/dia',        [VentasController::class, 'porDia']);
$router->add('GET','/ventas/mes',        [VentasController::class, 'porMes']);
$router->add('GET','/ventas/empleado',   [VentasController::class, 'porEmpleado']);
$router->add('GET','/ventas/csv',        [VentasController::class, 'csv']);

// Ruta de diagnóstico (opcional, para debug)
$router->add('GET','/diag', function() {
    echo "<pre>";
    echo "BASE_PATH: " . BASE_PATH . PHP_EOL;
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? '') . PHP_EOL;
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . PHP_EOL;

    echo "\nIntentando cargar App\\Controllers\\AuthController...\n";
    if (class_exists(\App\Controllers\AuthController::class)) {
        echo "OK: AuthController cargado\n";
    } else {
        echo "NO: AuthController no cargado\n";
    }
});
