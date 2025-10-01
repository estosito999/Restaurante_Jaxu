<?php
/** @var array $pendientes */
/** @var array $cierres */
/** @var array|null $abierta */

$totalPend = 0.0;
foreach ($pendientes ?? [] as $p) $totalPend += (float)$p['monto_venta'];
?>

<style>
  :root { --ok:#16a34a; --warn:#ca8a04; --err:#dc2626; --muted:#6b7280; --bg:#0b1220; --card:#111827; --border:#1f2937; --text:#e5e7eb; }
  body { color: var(--text); }
  .wrap{max-width:1100px;margin:24px auto;padding:0 16px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,"Noto Sans","Helvetica Neue",Arial,"Apple Color Emoji","Segoe UI Emoji";}
  h1{font-size:28px;margin:0 0 16px}
  h3{font-size:18px;margin:24px 0 12px}
  .grid{display:grid;gap:16px}
  .cards-2{grid-template-columns:1fr}
  @media(min-width:900px){.cards-2{grid-template-columns:1fr 1fr}}
  .card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px}
  .muted{color:var(--muted)}
  .table{width:100%;border-collapse:separate;border-spacing:0}
  .table thead th{font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--muted);text-align:left;padding:10px;border-bottom:1px solid var(--border)}
  .table tbody td{padding:10px;border-bottom:1px solid var(--border);vertical-align:top}
  .table tbody tr:nth-child(odd){background:rgba(255,255,255,.02)}
  .num{text-align:right;white-space:nowrap}
  .tfoot td{font-weight:600}
  .badge{display:inline-block;padding:4px 8px;border-radius:999px;font-size:12px;border:1px solid transparent}
  .badge.ok{background:rgba(22,163,74,.15);color:#86efac;border-color:rgba(22,163,74,.35)}
  .badge.warn{background:rgba(234,179,8,.12);color:#fde68a;border-color:rgba(234,179,8,.35)}
  .btn{appearance:none;background:#2563eb;color:#fff;border:none;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:600}
  .btn[disabled]{opacity:.5;cursor:not-allowed}
  .btn.secondary{background:#374151}
  .row{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
  .field{display:flex;gap:8px;align-items:center}
  input[type="number"]{background:#0b1220;color:var(--text);border:1px solid var(--border);border-radius:8px;padding:8px 10px;min-width:180px}
  a{color:#93c5fd;text-decoration:none}
  a:hover{text-decoration:underline}
  .help{font-size:12px;color:var(--muted);margin-top:6px}
</style>

<div class="wrap">
  <h1>Cierres de Caja</h1>

  <?php if($msg = \Core\Session::flash('msg')): ?>
    <p class="badge ok"><?= htmlspecialchars($msg) ?></p>
  <?php endif; ?>

  <!-- Estado de caja -->
  <div class="card">
    <?php if($abierta): ?>
      <div class="row">
        <span class="badge ok">Caja ABIERTA</span>
        <span class="muted">desde <?= htmlspecialchars($abierta['fecha_hora_apertura']) ?></span>
      </div>
      <p class="muted">Saldo inicial: <strong>Bs <?= number_format((float)$abierta['saldo_inicial'],2) ?></strong></p>
    <?php else: ?>
      <div class="row">
        <span class="badge warn">Caja CERRADA</span>
        <a class="btn secondary" href="<?= BASE_URI ?>/caja/abrir">Abrir ahora</a>
      </div>
    <?php endif; ?>
  </div>

  <div class="grid cards-2">

    <!-- Pendientes de cierre -->
    <div class="card">
      <h3>Ventas pendientes de cierre</h3>
      <?php if (!empty($pendientes)): ?>
        <table class="table">
          <thead>
            <tr>
              <th>ID Registro</th>
              <th>Factura</th>
              <th>Fecha</th>
              <th class="num">Monto</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($pendientes as $p): ?>
              <tr>
                <td>#<?= (int)$p['id_registro'] ?></td>
                <td><a href="<?= BASE_URI ?>/facturas/<?= (int)$p['id_factura'] ?>">#<?= (int)$p['id_factura'] ?></a></td>
                <td><?= htmlspecialchars($p['fecha_hora']) ?></td>
                <td class="num">Bs <?= number_format((float)$p['monto_venta'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr class="tfoot">
              <td colspan="3" class="num">Total pendiente:</td>
              <td class="num">Bs <?= number_format($totalPend,2) ?></td>
            </tr>
          </tfoot>
        </table>
      <?php else: ?>
        <p class="muted">No hay ventas pendientes 🎉</p>
      <?php endif; ?>
    </div>

    <!-- Realizar cierre -->
    <div class="card">
      <h3>Realizar cierre</h3>
      <form method="POST" action="<?= BASE_URI ?>/caja/cerrar" class="grid" style="gap:12px">
        <input type="hidden" name="_csrf" value="<?= \Core\Session::getCsrf() ?>">
        <div class="field">
          <label for="monto_efectivo_contado">Efectivo contado:</label>
          <input
            id="monto_efectivo_contado"
            type="number"
            step="0.01"
            name="monto_efectivo_contado"
            value="<?= number_format($totalPend,2,'.','') ?>"
          >
          <button type="button" class="btn secondary" id="btnUsarTotal">Usar total</button>
        </div>
        <div class="help">Sugerencia: ingresa el efectivo físico contado al cierre.</div>
        <div class="row">
          <button type="submit" class="btn" <?= $totalPend<=0 ? 'disabled' : '' ?>>Cerrar caja</button>
          <?php if ($totalPend<=0): ?><span class="muted">No hay nada pendiente por cerrar.</span><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- Historial -->
  <div class="card">
    <h3>Historial de cierres</h3>
    <?php if (!empty($cierres)): ?>
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th class="num">Total Ventas</th>
            <th class="num">Efectivo</th>
            <th class="num">Diferencia</th>
            <th>Responsable</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($cierres as $c): ?>
            <tr>
              <td>#<?= (int)$c['id_cierre'] ?></td>
              <td><?= htmlspecialchars($c['fecha_hora_cierre']) ?></td>
              <td class="num">Bs <?= number_format((float)$c['monto_total_ventas'],2) ?></td>
              <td class="num">Bs <?= number_format((float)$c['monto_efectivo_contado'],2) ?></td>
              <td class="num"><?= (float)$c['diferencia'] == 0.0 ? '<span class="badge ok">Bs 0.00</span>' : 'Bs '.number_format((float)$c['diferencia'],2) ?></td>
              <td><?= htmlspecialchars(($c['empleado_nombre'] ?? '').' '.($c['empleado_apellido'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="muted">Sin cierres registrados.</p>
    <?php endif; ?>
  </div>
</div>

<script>
  (function(){
    const btn = document.getElementById('btnUsarTotal');
    const input = document.getElementById('monto_efectivo_contado');
    if(btn && input){
      btn.addEventListener('click', function(){
        input.value = "<?= number_format($totalPend,2,'.','') ?>";
        input.focus();
      });
    }
  })();
</script>
