<?php
/**
 * app/views/layouts/main.php
 * Layout principal para Jaxu (nativo).
 *
 * Requisitos previos en tu bootstrap:
 *  - define('BASE_URI', ...) y define('ASSETS_URI', ...)
 *  - $content viene desde Core\View::render()
 *
 * Sugerencia: Crea /public/assets/css/main.css para tus estilos propios.
 */
$logged   = !empty($_SESSION['user_id']);
$userRole = $_SESSION['rol'] ?? null;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Jaxu — Restaurante</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Estilos base (puedes reemplazarlos por tu /assets/css/main.css) -->
  <link rel="stylesheet" href="<?= ASSETS_URI ?>/css/main.css">
  <style>
    /* Estilos mínimos por si aún no creas main.css */
    :root { --bg:#0f172a; --panel:#111827; --txt:#e5e7eb; --muted:#9ca3af; --brand:#22c55e; --danger:#ef4444; }
    * { box-sizing: border-box; }
    body { margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Arial, "Apple Color Emoji", "Segoe UI Emoji"; background:var(--bg); color:var(--txt); }
    a { color: #93c5fd; text-decoration: none; }
    a:hover { text-decoration: underline; }
    header { background: var(--panel); border-bottom: 1px solid #1f2937; }
    .shell { max-width: 1100px; margin: 0 auto; padding: 1rem; }
    .brand { display:flex; align-items:center; gap:.6rem; font-weight:700; }
    .brand .dot { width:.75rem; height:.75rem; background:var(--brand); border-radius:50%; display:inline-block; }
    nav { display:flex; gap:.75rem; flex-wrap: wrap; align-items:center; }
    .nav-row { display:flex; justify-content:space-between; align-items:center; gap:1rem; }
    .menu { display:flex; gap:.75rem; align-items:center; flex-wrap: wrap; }
    .menu a, .menu button { padding:.45rem .7rem; border-radius:.5rem; background:#0b1220; border:1px solid #1f2937; color:var(--txt); }
    .menu a:hover { background:#0a101b; }
    .menu .danger { background:#1a0f12; border-color:#3b0d12; color:#fecaca; }
    main { min-height: calc(100vh - 140px); }
    .content { max-width: 1100px; margin: 0 auto; padding: 1.25rem; }
    footer { border-top:1px solid #1f2937; background: var(--panel); color: var(--muted); }
    .tag { font-size:.75rem; color:var(--muted); padding:.2rem .45rem; border:1px solid #334155; border-radius:.5rem; }
    .flash { background:#052e1c; border:1px solid #14532d; color:#bbf7d0; padding:.7rem 1rem; border-radius:.6rem; margin:.75rem 0; }
    .grid { display:grid; gap:1rem; }
    @media (min-width:768px){ .grid.cols-2{ grid-template-columns:1fr 1fr; } }
    button.link { background: transparent; border: none; color: #93c5fd; cursor: pointer; padding: 0; }
    .sep { height:8px; }
  </style>
</head>
<body>

<header>
  <div class="shell">
    <div class="nav-row">
      <div class="brand">
        <span class="dot"></span>
        <a href="<?= BASE_URI ?>/" style="font-size:1.05rem;">Jaxu</a>
        <?php if ($logged): ?>
          <span class="tag">Rol: <?= htmlspecialchars(strtoupper((string)$userRole)) ?></span>
        <?php endif; ?>
      </div>

      <nav class="menu">
        <?php if ($logged): ?>
          <a href="<?= BASE_URI ?>/platos">Platos</a>
          <a href="<?= BASE_URI ?>/clientes">Clientes</a>
          <a href="<?= BASE_URI ?>/facturas/crear">Nueva Factura</a>
          <?php if (in_array($userRole, ['admin','cajero'], true)): ?>
            <a href="<?= BASE_URI ?>/caja/cierre">Cierres</a>
          <?php endif; ?>
          <a class="danger" href="<?= BASE_URI ?>/logout">Salir</a>
        <?php else: ?>
          <a href="<?= BASE_URI ?>/">Iniciar sesión</a>
        <?php endif; ?>
      </nav>
    </div>
  </div>
</header>

<main>
  <div class="content">
    <?php if (!empty($flash = \Core\Session::flash('msg'))): ?>
      <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <?= $content ?? '' ?>
  </div>
</main>

<footer>
  <div class="shell" style="display:flex; justify-content:space-between; align-items:center; gap:.75rem; padding: .9rem 1rem;">
    <div>© <?= date('Y') ?> Jaxu — Restaurante</div>
    <div class="tag">Bolivia</div>
  </div>
</footer>

<!-- JS de tu app -->
<script src="<?= ASSETS_URI ?>/js/app.js"></script>
</body>
</html>
