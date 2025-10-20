<?php
// app/views/layouts/main.php
$logged   = !empty($_SESSION['user_id']);
$userRole = $_SESSION['rol'] ?? null;

// Leer mensaje flash (si hay)
$flash = \Core\Session::getFlash('msg') ?? null;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Restaurante Jaxu</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= defined('ASSETS_URI') ? ASSETS_URI : '' ?>/css/main.css">

  <style>
    :root{
      --grad-a:#fd2f00; --grad-b:#6ea834;
      --bg:#f3f4f6; --txt:#2e2e2e;
    }
    body{ margin:0; font-family: system-ui, Arial; background:var(--bg); color:var(--txt); }

    /* Header solo si está logueado */
    header{
      background: linear-gradient(135deg, var(--grad-a), var(--grad-b));
      padding:12px 0; color:#fff; display:<?= $logged ? 'block':'none' ?>;
    }
    .shell{ max-width:1100px; margin:0 auto; padding:0 1rem; }
    .nav-row{ display:flex; justify-content:space-between; align-items:center; }
    .brand{ font-size:1.2rem; font-weight:700; }
    nav{ display:flex; gap:.7rem; flex-wrap:wrap; }
    nav a{
      color:#fff; background:rgba(255,255,255,0.15);
      padding:.45rem .75rem; border-radius:8px; text-decoration:none;
      font-size:.9rem;
    }
    nav a:hover{ background:rgba(255,255,255,0.28); }

    main{ min-height:calc(100vh - 70px); }
    .content{ max-width:1100px; margin:0 auto; padding:1.2rem; }

    .flash{
      background:#e8f5e9; color:#2e7d32; border-left:4px solid #2e7d32;
      padding:.75rem 1rem; border-radius:10px; margin:.5rem 0 1rem;
    }

    footer{
      background: linear-gradient(135deg, var(--grad-a), var(--grad-b));
      color:#fff; padding:1rem 0; text-align:center; font-weight:500;
    }
  </style>
</head>
<body>

<header>
  <div class="shell">
    <div class="nav-row">
      <div class="brand">🍽️ Jaxu</div>
      <nav>
        <a href="<?= BASE_URI ?>/platos">Platos</a>
        <a href="<?= BASE_URI ?>/clientes">Clientes</a>
        <a href="<?= BASE_URI ?>/facturas/crear">Nueva Factura</a>
        <?php if (in_array($userRole, ['admin','cajero'], true)): ?>
          <a href="<?= BASE_URI ?>/caja/cierre">Cierres</a>
        <?php endif; ?>
        <?php if ($logged && $userRole === 'admin'): ?>
          <a href="<?= BASE_URI ?>/empleados/crear">Registrar empleado</a>
        <?php endif; ?>
        <a class="danger" style="background:#b91c1c" href="<?= BASE_URI ?>/logout">Salir</a>
      </nav>
    </div>
  </div>
</header>

<main>
  <div class="content">
    <?php if (!empty($flash)): ?>
      <div class="flash"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?= isset($content) ? $content : '' ?>
  </div>
</main>

<footer>
  © <?= date('Y') ?> Restaurante Jaxu — Bolivia
</footer>

<script src="<?= defined('ASSETS_URI') ? ASSETS_URI : '' ?>/js/app.js"></script>
</body>
</html>
