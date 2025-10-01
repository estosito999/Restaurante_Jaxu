<h2>Ventas por empleado</h2>
<form method="GET" action="<?= BASE_URI ?>/ventas/empleado">
  Desde <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
  Hasta <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
  <button type="submit">Filtrar</button>
</form>
<table>
  <thead><tr><th>Empleado</th><th>N° Ventas</th><th>Total</th></tr></thead>
  <tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['nombre'].' '.$r['apellido']) ?> (ID <?= (int)$r['id_empleado'] ?>)</td>
      <td><?= (int)$r['nventas'] ?></td>
      <td><?= number_format((float)$r['total'],2) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
