<h1>Nueva Factura</h1>
<form method="POST" action="/facturas" id="formFactura">
  <input type="hidden" name="_csrf" value="<?= \Core\Session::getCsrf() ?>">

  <label>Cliente: 
    <select name="id_cliente" required>
      <?php foreach($clientes as $c): ?>
        <option value="<?= (int)$c['id_cliente'] ?>">
          <?= htmlspecialchars($c['nombre'].' '.$c['apellido'].' (NIT: '.$c['nit'].')') ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <h3>Platos</h3>
  <table id="tablaPlatos">
    <thead>
      <tr><th>Plato</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr>
    </thead>
    <tbody></tbody>
    <tfoot>
      <tr><td colspan="3" style="text-align:right">Total:</td><td><span id="total">0.00</span></td><td></td></tr>
    </tfoot>
  </table>

  <div>
    <select id="selPlato">
      <?php foreach($platos as $p): ?>
        <option value='<?= json_encode(["id"=>$p["id_plato"],"nombre"=>$p["nombre"],"precio"=>$p["precio"]]) ?>'>
          <?= htmlspecialchars($p['categoria'].' - '.$p['nombre'].' ('.number_format($p['precio'],2).')') ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="button" id="btnAgregar">Agregar</button>
  </div>

  <br>
  <button type="submit">Guardar Factura</button>
</form>

<script>
(function(){
  const tbody = document.querySelector('#tablaPlatos tbody');
  const totalSpan = document.getElementById('total');
  const selPlato = document.getElementById('selPlato');

  function calcTotal() {
    let t = 0;
    tbody.querySelectorAll('tr').forEach(tr => {
      t += parseFloat(tr.querySelector('.subtotal').textContent) || 0;
    });
    totalSpan.textContent = t.toFixed(2);
  }

  document.getElementById('btnAgregar').addEventListener('click', () => {
    const data = JSON.parse(selPlato.value);
    const tr = document.createElement('tr');

    tr.innerHTML = `
      <td>${data.nombre}<input type="hidden" name="id_plato[]" value="${data.id}"></td>
      <td><input type="number" name="precio_unitario[]" value="${data.precio}" step="0.01" min="0" class="precio"></td>
      <td><input type="number" name="cantidad[]" value="1" min="1" class="cantidad"></td>
      <td class="subtotal">${parseFloat(data.precio).toFixed(2)}</td>
      <td><button type="button" class="del">X</button></td>
    `;
    tbody.appendChild(tr);
    calcTotal();
  });

  tbody.addEventListener('input', (e) => {
    if (e.target.classList.contains('precio') || e.target.classList.contains('cantidad')) {
      const tr = e.target.closest('tr');
      const precio = parseFloat(tr.querySelector('.precio').value) || 0;
      const cant = parseInt(tr.querySelector('.cantidad').value) || 0;
      tr.querySelector('.subtotal').textContent = (precio * cant).toFixed(2);
      calcTotal();
    }
  });
  tbody.addEventListener('click', (e) => {
    if (e.target.classList.contains('del')) {
      e.target.closest('tr').remove();
      calcTotal();
    }
  });
})();
</script>
