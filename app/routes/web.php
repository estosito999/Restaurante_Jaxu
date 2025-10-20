<?php
use App\Controllers\AuthController;
use App\Controllers\PlatoController;
use App\Controllers\ClienteController;
use App\Controllers\FacturaController;
use App\Controllers\CajaController;
use App\Controllers\EmpleadoController;
use App\Controllers\VentasController;
use App\Controllers\RegistroVentaController;
use App\Controllers\DetalleVentaController;

if (!isset($router) || !($router instanceof \Core\Router)) { http_response_code(403); exit; }

/** AUTH **/
$router->add('GET','/',       [AuthController::class, 'showLogin']);  // <- antes decía loginForm
$router->add('GET','/login',  [AuthController::class, 'showLogin']);  // útil para redirects
$router->add('POST','/login', [AuthController::class, 'login']);
$router->add('GET','/logout', [AuthController::class, 'logout']);

/** PLATOS **/
$router->add('GET','/platos',                  [PlatoController::class,   'index']);
$router->add('GET','/platos/crear',            [PlatoController::class,   'create']);
$router->add('POST','/platos',                 [PlatoController::class,   'store']);
$router->add('GET','/platos/editar/{id}',      [PlatoController::class,   'edit']);
$router->add('POST','/platos/actualizar/{id}', [PlatoController::class,   'update']);
$router->add('POST','/platos/eliminar/{id}',   [PlatoController::class,   'destroy']);

/** CLIENTES **/
$router->add('GET','/clientes',  [ClienteController::class, 'index']);
$router->add('POST','/clientes', [ClienteController::class, 'store']);

/** FACTURAS **/
$router->add('GET','/facturas/crear',       [FacturaController::class, 'create']);
$router->add('POST','/facturas',            [FacturaController::class, 'store']);
$router->add('GET','/facturas/{id}',        [FacturaController::class, 'show']);
$router->add('GET','/facturas/ticket/{id}', [FacturaController::class, 'ticket']);
$router->add('GET','/facturas/pdf/{id}',    [FacturaController::class, 'pdf']);

/** CAJA **/
$router->add('GET','/caja/cierre',  [CajaController::class, 'index']);
$router->add('POST','/caja/cerrar', [CajaController::class, 'cerrar']);
$router->add('GET','/caja/abrir',   [CajaController::class, 'abrirForm']);
$router->add('POST','/caja/abrir',  [CajaController::class, 'abrir']);

/** EMPLEADOS **/
$router->add('GET',  '/empleados',                 [EmpleadoController::class, 'index']);
$router->add('GET',  '/empleados/crear',           [EmpleadoController::class, 'create']);
$router->add('POST', '/empleados',                 [EmpleadoController::class, 'store']);
$router->add('GET',  '/empleados/editar/{id}',     [EmpleadoController::class, 'edit']);
$router->add('POST', '/empleados/actualizar/{id}', [EmpleadoController::class, 'update']);
$router->add('GET',  '/empleados/password/{id}',   [EmpleadoController::class, 'passwordForm']);
$router->add('POST', '/empleados/password/{id}',   [EmpleadoController::class, 'passwordUpdate']);
$router->add('POST', '/empleados/eliminar/{id}',   [EmpleadoController::class, 'destroy']);

/** Reportes de ventas (mueve a /reportes/ventas para no chocar con RegistroVenta) */
$router->add('GET','/reportes/ventas',        [VentasController::class, 'index']);
$router->add('GET','/reportes/ventas/dia',    [VentasController::class, 'porDia']);
$router->add('GET','/reportes/ventas/mes',    [VentasController::class, 'porMes']);
$router->add('GET','/reportes/ventas/empleado',[VentasController::class, 'porEmpleado']);
$router->add('GET','/reportes/ventas/csv',    [VentasController::class, 'csv']);

/** Registro de ventas (deja POST /ventas; cambia el GET para no duplicar) */
$router->add('GET',  '/ventas/registro',      [RegistroVentaController::class, 'index']);
$router->add('POST', '/ventas',               [RegistroVentaController::class, 'create']);
$router->add('GET',  '/detalle_venta/{id}',   [DetalleVentaController::class, 'show']);
