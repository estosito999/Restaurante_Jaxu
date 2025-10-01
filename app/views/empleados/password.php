<h1>Cambiar contraseña — <?= htmlspecialchars($emp['nombre'].' '.$emp['apellido']) ?></h1>

<form method="POST" action="<?= BASE_URI ?>/empleados/password/<?= (int)$emp['id_empleado'] ?>">
  <input type="hidden" name="_csrf" value="<?= \Core\Session::getCsrf() ?>">

  <label>Nueva contraseña (mín. 6)<br><input type="password" name="password" minlength="6" required></label><br>
  <label>Repetir contraseña<br><input type="password" name="password2" minlength="6" required></label><br><br>

  <button type="submit">Actualizar</button>
  <a href="<?= BASE_URI ?>/empleados">Cancelar</a>
</form>
