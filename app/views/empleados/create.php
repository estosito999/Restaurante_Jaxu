<h1>Nuevo empleado</h1>

<form method="POST" action="<?= BASE_URI ?>/empleados">
  <input type="hidden" name="_csrf" value="<?= \Core\Session::getCsrf() ?>">

  <label>Nombre *<br><input name="nombre" required maxlength="50"></label><br>
  <label>Apellido<br><input name="apellido" maxlength="50"></label><br>
  <label>CI *<br><input name="ci" required maxlength="20"></label><br>
  <label>Puesto<br><input name="puesto" maxlength="20" placeholder="cajero, mesero, cocinero..."></label><br>

  <label>Rol *<br>
    <select name="rol" required>
      <option value="mesero">mesero</option>
      <option value="cajero">cajero</option>
      <option value="admin">admin</option>
    </select>
  </label><br>

  <label>Contraseña (mín. 6)<br><input type="password" name="password" minlength="6"></label><br><br>

  <button type="submit">Guardar</button>
  <a href="<?= BASE_URI ?>/empleados">Cancelar</a>
</form>
