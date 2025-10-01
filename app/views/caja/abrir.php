<h1>Apertura de caja</h1>
<form method="POST" action="<?= BASE_URI ?>/caja/abrir">
  <input type="hidden" name="_csrf" value="<?= \Core\Session::getCsrf() ?>">
  <label>Saldo inicial: <input type="number" step="0.01" name="saldo_inicial" value="0.00"></label>
  <button type="submit">Abrir caja</button>
</form>
