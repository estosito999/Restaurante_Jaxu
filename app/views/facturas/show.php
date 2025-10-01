<h1>Factura #<?= (int)$factura['id_factura'] ?></h1>
<p><strong>Fecha:</strong> <?= htmlspecialchars($factura['fecha_hora']) ?></p>
<p><strong>Cliente:</strong> <?= htmlspecialchars(trim(($factura['cliente_nombre']??'').' '.($factura['cliente_apellido']??''))) ?>
<?php if (!empty($factura['cliente_nit'])): ?> (NIT/CI: <?= htmlspecialchars($factura['cliente_nit']) ?>)<?php endif; ?></p>
<p><strong>Atendido por:</strong> <?= htmlspecialchars(trim(($factura['empleado_nombre']??'').' '.($factura['empleado_apellido']??''))) ?></p>

<table>
  <thead><tr><th>Plato</th><th>Cant.</th><th>Precio</th><th>Importe</th></tr></thead>
  <tbody>
    <?php foreach($factura['detalles'] as $d): ?>
    <tr>
      <td><?= htmlspecialchars($d['plato_nombre']) ?></td>
      <td><?= (int)$d['cantidad'] ?></td>
      <td><?= number_format((float)$d['precio_unitario'], 2) ?></td>
      <td><?= number_format((float)$d['subtotal'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr><td colspan="3" style="text-align:right">Subtotal</td>
        <td><?= number_format((float)$factura['subtotal'],2) ?></td></tr>
    <tr><td colspan="3" style="text-align:right">IVA 13%</td>
        <td><?= number_format((float)$factura['iva'],2) ?></td></tr>
    <tr><td colspan="3" style="text-align:right"><strong>Total</strong></td>
        <td><strong><?= number_format((float)$factura['total'],2) ?></strong></td></tr>
  </tfoot>
</table>

<p>
  <a href="<?= BASE_URI ?>/facturas/ticket/<?= (int)$factura['id_factura'] ?>" target="_blank">Imprimir Ticket</a> |
  <a href="<?= BASE_URI ?>/facturas/pdf/<?= (int)$factura['id_factura'] ?>" target="_blank">Exportar PDF</a>
</p>

<style>
  table { width:100%; border-collapse: collapse; }
  th, td { padding: .5rem .6rem; border-bottom: 1px solid #1f2937; text-align: left; }
  th { color:#9ca3af; font-weight:600; }
</style>
