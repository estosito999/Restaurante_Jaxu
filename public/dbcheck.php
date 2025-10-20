<?php
/**
 * Jaxu - DB Checker (public/dbcheck.php)
 * Verifica conexión PDO y esquema mínimo de la BD.
 * Seguridad: requiere token por query (?token=EL_TOKEN).
 *
 * Uso:
 *   /dbcheck.php?token=EL_TOKEN
 *   /dbcheck.php?token=EL_TOKEN&write=1   # incluye prueba de escritura (temporal)
 */

// ─────────────────────────────────────────────────────────────
// CONFIGURACIÓN
// ─────────────────────────────────────────────────────────────
const DBCHECK_TOKEN = 'dev123';     // <-- CAMBIAR
const PROJECT_ROOT  = __DIR__ . '/..';
const TRY_WRITE     = false;        // por defecto NO (puedes habilitar con &write=1)

// Esquema mínimo esperado (ajústalo a tu BD real)
$SCHEMA = [
  'empleado'       => ['id_empleado','nombre','apellido','ci','password_hash','rol'],
  'plato_bebidas'  => ['id_plato','nombre','precio','stock','categoria','tipo','id_cocinero'],
  'cliente'        => ['id_cliente','nombre','nit'],
  'factura'        => ['id_factura','fecha_hora','estado','id_cliente','id_empleado','monto_total'],
  'detalle_venta'  => ['id_detalle','id_factura','id_plato','cantidad','precio_unitario','subtotal'],
  'registro_venta' => ['id_registro','id_factura','id_apertura','id_empleado','fecha_venta','monto_venta'],
  'apertura_caja'  => ['id_apertura','fecha_hora_apertura','saldo_inicial','detalle_gastos','id_empleado','estado'],
  'cierre_caja'    => ['id_cierre','fecha_hora_cierre','monto_total_ventas','monto_efectivo_contado','diferencia','id_empleado'],
];

// ─────────────────────────────────────────────────────────────
// GUARD CLÁUSULAS
// ─────────────────────────────────────────────────────────────
$token = isset($_GET['token']) ? (string)$_GET['token'] : '';
if (!hash_equals(DBCHECK_TOKEN, $token)) {
  http_response_code(403);
  echo "<h1>403</h1><p>Token inválido.</p>";
  exit;
}
$runWrite = (isset($_GET['write']) && $_GET['write'] === '1') || TRY_WRITE;

// ─────────────────────────────────────────────────────────────
// HELPERS (compatibles con PHP 7.4)
// ─────────────────────────────────────────────────────────────
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function str_starts_with_74($haystack, $needle) {
  $haystack = (string)$haystack; $needle = (string)$needle;
  return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
}
function str_contains_74($haystack, $needle) {
  return $needle === '' || strpos((string)$haystack, (string)$needle) !== false;
}

function parse_env($path){
  if (!is_file($path)) return [];
  $vars = [];
  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    $pos = strpos($line, '=');
    if ($pos === false) continue;
    $k = trim(substr($line, 0, $pos));
    $v = trim(substr($line, $pos + 1));
    // quitar comillas envolventes
    if ($v !== '' && (
        ($v[0] === '"' && substr($v, -1) === '"') ||
        ($v[0] === "'" && substr($v, -1) === "'")
    )) {
      $v = substr($v, 1, -1);
    }
    $vars[$k] = $v;
  }
  return $vars;
}

function mask($s){
  if ($s === null || $s === '') return '';
  $s = (string)$s;
  $len = strlen($s);
  if ($len <= 2) return str_repeat('*', $len);
  return substr($s, 0, 1) . str_repeat('*', max(0, $len-2)) . substr($s, -1);
}

// ─────────────────────────────────────────────────────────────
// CARGA CONFIG (.env)
// ─────────────────────────────────────────────────────────────
$dbconf = [
  'host'    => '127.0.0.1',
  'name'    => 'jaxu_db',
  'user'    => 'root',
  'pass'    => '',
  'charset' => 'utf8mb4',
  'port'    => 3306,
];
$notes  = [];
$errors = [];

$env = parse_env(PROJECT_ROOT . '/.env');
if ($env) {
  $dbconf['host']    = isset($env['DB_HOST']) ? $env['DB_HOST'] : $dbconf['host'];
  $dbconf['name']    = isset($env['DB_NAME']) ? $env['DB_NAME'] : $dbconf['name'];
  $dbconf['user']    = isset($env['DB_USER']) ? $env['DB_USER'] : $dbconf['user'];
  $dbconf['pass']    = isset($env['DB_PASS']) ? $env['DB_PASS'] : $dbconf['pass'];
  $dbconf['charset'] = isset($env['DB_CHARSET']) ? $env['DB_CHARSET'] : $dbconf['charset'];
  $dbconf['port']    = isset($env['DB_PORT']) ? (int)$env['DB_PORT'] : $dbconf['port'];
} else {
  $notes[] = ".env no encontrado; usando valores por defecto";
}

// Mostrar errores detallados si APP_DEBUG=true en .env
define('APP_DEBUG', isset($env['APP_DEBUG']) ? (bool)$env['APP_DEBUG'] : false);

// Validación de config
foreach (['host','name','user'] as $k) {
  if (empty($dbconf[$k])) $errors[] = "Falta configuración de BD: {$k}";
}

// ─────────────────────────────────────────────────────────────
// CHECKS
// ─────────────────────────────────────────────────────────────
$checks = [];

// Versión de PHP (tu proyecto corre en 7.4)
$okPhp = version_compare(PHP_VERSION, '7.4.0', '>=');
$checks[] = [
  'label' => 'PHP versión',
  'ok'    => $okPhp,
  'info'  => PHP_VERSION . ' (recomendado >= 7.4.0)',
];

$checks[] = [
  'label' => 'Extensión PDO',
  'ok'    => extension_loaded('pdo'),
  'info'  => extension_loaded('pdo') ? 'pdo OK' : 'pdo faltante',
];

$checks[] = [
  'label' => 'PDO MySQL',
  'ok'    => extension_loaded('pdo_mysql'),
  'info'  => extension_loaded('pdo_mysql') ? 'pdo_mysql OK' : 'pdo_mysql faltante',
];

// Conexión
$pdo = null;
$mysqlVersion = '-';
if (!$errors) {
  $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $dbconf['host'], $dbconf['port'], $dbconf['name'], $dbconf['charset']
  );
  try {
    $pdo = new \PDO($dsn, $dbconf['user'], $dbconf['pass'], [
      \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
      \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
      \PDO::ATTR_TIMEOUT => 5,
    ]);
    $checks[] = ['label'=>'Conexión a BD', 'ok'=>true, 'info'=>"Conectado a {$dbconf['name']}@" . $dbconf['host']];
    $mysqlVersion = $pdo->query('SELECT VERSION()')->fetchColumn();
  } catch (\Throwable $e) {
    $checks[] = ['label'=>'Conexión a BD', 'ok'=>false, 'info'=>$e->getMessage()];
  }
}

// Versión MySQL/MariaDB
if ($pdo) {
  $checks[] = [
    'label' => 'MySQL/MariaDB versión',
    'ok'    => true,
    'info'  => (string)$mysqlVersion,
  ];
}

// Tablas y columnas
$missingTables = [];
$missingCols   = [];

if ($pdo) {
  try {
    // Listar tablas existentes
    $tables = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()")
                  ->fetchAll(\PDO::FETCH_COLUMN);
    $tables = array_map('strtolower', $tables);
    foreach ($SCHEMA as $table => $cols) {
      if (!in_array(strtolower($table), $tables, true)) {
        $missingTables[] = $table;
        continue;
      }
      // columnas
      $q = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t");
      $q->execute([':t'=>$table]);
      $existing = array_map('strtolower', $q->fetchAll(\PDO::FETCH_COLUMN));
      foreach ($cols as $c) {
        if (!in_array(strtolower($c), $existing, true)) {
          $missingCols[$table][] = $c;
        }
      }
    }
    $checks[] = [
      'label'=>'Esquema mínimo',
      'ok'   => empty($missingTables) && empty($missingCols),
      'info' => empty($missingTables) && empty($missingCols)
                ? 'OK'
                : 'Faltantes: ' .
                  ( $missingTables ? ('tablas ['.implode(', ', $missingTables).']') : '' ) .
                  ( $missingCols ? ('; columnas '.json_encode($missingCols)) : '' ),
    ];
  } catch (\Throwable $e) {
    $checks[] = ['label'=>'Esquema mínimo', 'ok'=>false, 'info'=>$e->getMessage()];
  }
}

// Prueba de escritura (temporal)
if ($pdo && $runWrite) {
  try {
    $pdo->exec("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_jaxu_probe (id INT PRIMARY KEY AUTO_INCREMENT, t TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("INSERT INTO tmp_jaxu_probe() VALUES ()");
    $row = $pdo->query("SELECT COUNT(*) FROM tmp_jaxu_probe")->fetchColumn();
    $checks[] = ['label'=>'Prueba de escritura (temp)', 'ok'=>true, 'info'=>"Insert OK ({$row} fila/s)"];
  } catch (\Throwable $e) {
    $checks[] = ['label'=>'Prueba de escritura (temp)', 'ok'=>false, 'info'=>$e->getMessage()];
  }
}

// ─────────────────────────────────────────────────────────────
// SALIDA HTML
// ─────────────────────────────────────────────────────────────
$maskedUser = mask($dbconf['user']);
$maskedPass = mask($dbconf['pass']);
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>DB Check — Jaxu</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{ --bg:#0f172a; --panel:#111827; --txt:#e5e7eb; --muted:#94a3b8; --ok:#22c55e; --bad:#ef4444; --warn:#f59e0b; --bd:#1f2937; }
    body{ margin:0; font-family:system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Arial; background:var(--bg); color:var(--txt); }
    .wrap{ max-width:980px; margin:0 auto; padding:24px; }
    h1{ margin:0 0 10px; font-size:1.4rem; }
    .card{ background:var(--panel); border:1px solid var(--bd); border-radius:12px; padding:16px; margin:14px 0; }
    .grid{ display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .row{ display:flex; justify-content:space-between; border-bottom:1px dashed var(--bd); padding:8px 0; }
    .tag{ font-size:.82rem; padding:.15rem .45rem; border-radius:.4rem; border:1px solid var(--bd); color:var(--muted); }
    .ok{ color:var(--ok); }
    .bad{ color:var(--bad); }
    .warn{ color:var(--warn); }
    code{ background:#0b1220; padding:.1rem .3rem; border:1px solid var(--bd); border-radius:.3rem; }
    .small{ font-size:.9rem; color:var(--muted); }
    @media (max-width:760px){ .grid{ grid-template-columns:1fr; } }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Verificador de Base de Datos — Jaxu</h1>

    <div class="card">
      <div class="grid">
        <div><div class="small">Host</div><div><?= h($dbconf['host'] ?: '-') ?></div></div>
        <div><div class="small">Base</div><div><?= h($dbconf['name'] ?: '-') ?></div></div>
        <div><div class="small">Usuario</div><div><?= h($maskedUser) ?></div></div>
        <div><div class="small">Password</div><div><?= h($maskedPass) ?></div></div>
      </div>
      <?php if ($notes): ?>
        <div style="margin-top:10px" class="small">Notas: <?= h(implode(' · ', $notes)) ?></div>
      <?php endif; ?>
      <?php if ($errors): ?>
        <div style="margin-top:10px" class="bad"><?= h(implode(' · ', $errors)) ?></div>
      <?php endif; ?>
    </div>

    <div class="card">
      <?php foreach ($checks as $c): ?>
        <div class="row">
          <div><?= h($c['label']) ?></div>
          <div>
            <?= $c['ok'] ? '<span class="ok">✔</span>' : '<span class="bad">✖</span>' ?>
            <span class="small"> <?= h($c['info']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="card">
      <div class="small">
        Seguridad: este checker requiere <code>?token=...</code>.<br>
        Para probar escritura: añade <code>&write=1</code>.<br>
        <b>Recomendación:</b> eliminar este archivo cuando termines.
      </div>
    </div>
  </div>
</body>
</html>
