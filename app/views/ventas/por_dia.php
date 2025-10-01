<h2>Ventas por día</h2>
<form method="GET" action="<?= BASE_URI ?>/ventas/dia">
  Desde <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
  Hasta <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
  <button type="submit">Filtrar</button>
  <a href="<?= BASE_URI ?>/ventas/csv?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>">Exportar CSV</a>
</form>
<table>
  <thead><tr><th>Fecha</th><th>N° Ventas</th><th>Total</th></tr></thead>
  <tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['fecha']) ?></td>
      <td><?= (int)$r['nventas'] ?></td>
      <td><?= number_format((float)$r['total'],2) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
