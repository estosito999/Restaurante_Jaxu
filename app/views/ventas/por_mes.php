<h2>Ventas por mes</h2>
<form method="GET" action="<?= BASE_URI ?>/ventas/mes">
  Año <input type="number" name="year" value="<?= (int)$year ?>" min="2000" max="2100">
  <button type="submit">Filtrar</button>
</form>
<table>
  <thead><tr><th>Mes</th><th>N° Ventas</th><th>Total</th></tr></thead>
  <tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['ym']) ?></td>
      <td><?= (int)$r['nventas'] ?></td>
      <td><?= number_format((float)$r['total'],2) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
