<?php
$f = $factura;
function fmt($n){ return number_format((float)$n,2); }
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Ticket #<?= (int)$f['id_factura'] ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
/* Tamaño aproximado de rollo 80mm */
@page { size: 80mm auto; margin: 4mm; }
body { font-family: monospace, system-ui; font-size: 12px; color:#000; }
.ticket { width: 72mm; margin: 0 auto; }
.center { text-align:center; }
.row { display:flex; justify-content:space-between; }
hr { border:none; border-top:1px dashed #000; margin:6px 0; }
.tbl { width:100%; }
.tbl th, .tbl td { padding:2px 0; }
.small { font-size:11px; }
.btns { margin:6px 0 12px; }
@media print {
  .no-print { display:none; }
}
</style>
</head>
<body>
<div class="ticket">
  <div class="center">
    <div><strong>JAXU — Restaurante</strong></div>
    <div class="small">La Paz - Bolivia</div>
  </div>
  <hr>
  <div>Factura: #<?= (int)$f['id_factura'] ?></div>
  <div>Fecha: <?= htmlspecialchars($f['fecha_hora']) ?></div>
  <div>Cliente: <?= htmlspecialchars(trim(($f['cliente_nombre']??'').' '.($f['cliente_apellido']??''))) ?></div>
  <?php if(!empty($f['cliente_nit'])): ?><div>NIT/CI: <?= htmlspecialchars($f['cliente_nit']) ?></div><?php endif; ?>
  <div>Atiende: <?= htmlspecialchars(trim(($f['empleado_nombre']??'').' '.($f['empleado_apellido']??''))) ?></div>
  <hr>
  <table class="tbl">
    <thead>
      <tr><th style="text-align:left">Desc</th><th style="text-align:right">Cant</th><th style="text-align:right">Imp</th></tr>
    </thead>
    <tbody>
      <?php foreach(($f['detalles'] ?? []) as $d): ?>
      <tr>
        <td><?= htmlspecialchars($d['plato_nombre']) ?></td>
        <td style="text-align:right"><?= (int)$d['cantidad'] ?></td>
        <td style="text-align:right"><?= fmt($d['subtotal']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <hr>
  <div class="row"><span>Subtotal</span><strong><?= fmt($f['subtotal']) ?></strong></div>
  <div class="row"><span>IVA 13%</span><strong><?= fmt($f['iva']) ?></strong></div>
  <div class="row"><span>Total</span><strong><?= fmt($f['total']) ?></strong></div>
  <hr>
  <div class="center small">¡Gracias por su preferencia!</div>

  <div class="btns no-print">
    <button onclick="window.print()">Imprimir</button>
  </div>
</div>
</body>
</html>
